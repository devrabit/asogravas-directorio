<?php
/**
 * Sanitización y validación de los campos de empresa.
 *
 * Cada método devuelve un array con `value` (el valor a guardar) y `error` (mensaje para el
 * usuario, o cadena vacía). Un valor con error no se guarda: se conserva el anterior.
 *
 * @package Asogravas_Directorio
 */

defined( 'ABSPATH' ) || exit;

class ASDIR_Sanitize {

	/**
	 * @param mixed $valor Valor crudo, ya sin slashes.
	 *
	 * @return array{value: mixed, error: string}
	 */
	public static function field( $valor, array $campo ): array {
		$metodo = 'tipo_' . $campo['type'];

		if ( ! method_exists( self::class, $metodo ) ) {
			return self::ok( sanitize_text_field( (string) $valor ) );
		}

		return self::$metodo( $valor, $campo );
	}

	private static function ok( $valor ): array {
		return array(
			'value' => $valor,
			'error' => '',
		);
	}

	private static function error( string $mensaje ): array {
		return array(
			'value' => null,
			'error' => $mensaje,
		);
	}

	private static function tipo_text( $valor, array $campo ): array {
		$limpio = sanitize_text_field( (string) $valor );

		if ( isset( $campo['maxlength'] ) ) {
			$limpio = mb_substr( $limpio, 0, (int) $campo['maxlength'] );
		}

		return self::ok( $limpio );
	}

	private static function tipo_tel( $valor ): array {
		// Se conserva el formato que escribió el usuario; solo se quitan caracteres que no
		// pertenecen a un teléfono. El enlace tel: se construye aparte, en asdir_tel_link().
		$limpio = trim( preg_replace( '/[^0-9+()\-.\s]/', '', (string) $valor ) ?? '' );

		return self::ok( mb_substr( $limpio, 0, 40 ) );
	}

	private static function tipo_email( $valor, array $campo ): array {
		$limpio = sanitize_email( trim( (string) $valor ) );

		if ( '' === $limpio ) {
			return self::ok( '' );
		}

		if ( ! is_email( $limpio ) ) {
			return self::error(
				sprintf(
					/* translators: %s: nombre del campo. */
					__( 'El correo del campo «%s» no es válido y no se guardó.', 'asogravas-directorio' ),
					$campo['label']
				)
			);
		}

		return self::ok( $limpio );
	}

	private static function tipo_url( $valor, array $campo ): array {
		$crudo = trim( (string) $valor );

		if ( '' === $crudo ) {
			return self::ok( '' );
		}

		// Escribir «empresa.com» es lo natural; sin esquema el enlace apuntaría a una ruta
		// relativa del sitio.
		if ( ! preg_match( '#^https?://#i', $crudo ) ) {
			$crudo = 'https://' . ltrim( $crudo, '/' );
		}

		$limpio = esc_url_raw( $crudo );

		if ( '' === $limpio ) {
			return self::error(
				sprintf(
					/* translators: %s: nombre del campo. */
					__( 'La dirección web del campo «%s» no es válida y no se guardó.', 'asogravas-directorio' ),
					$campo['label']
				)
			);
		}

		return self::ok( $limpio );
	}

	/**
	 * Acepta un número en cualquier formato o un enlace de WhatsApp, y guarda siempre el enlace
	 * canónico para que la ficha no tenga que adivinar qué le dieron.
	 */
	private static function tipo_whatsapp( $valor, array $campo ): array {
		$crudo = trim( (string) $valor );

		if ( '' === $crudo ) {
			return self::ok( '' );
		}

		$digitos = asdir_solo_digitos( $crudo );

		if ( strlen( $digitos ) < 8 ) {
			return self::error(
				sprintf(
					/* translators: %s: nombre del campo. */
					__( 'El número del campo «%s» es demasiado corto y no se guardó.', 'asogravas-directorio' ),
					$campo['label']
				)
			);
		}

		return self::ok( 'https://wa.me/' . $digitos );
	}

	private static function tipo_date( $valor, array $campo ): array {
		$crudo = trim( (string) $valor );

		if ( '' === $crudo ) {
			return self::ok( '' );
		}

		$fecha = DateTime::createFromFormat( 'Y-m-d', $crudo );

		if ( ! $fecha || $fecha->format( 'Y-m-d' ) !== $crudo ) {
			return self::error(
				sprintf(
					/* translators: %s: nombre del campo. */
					__( 'La fecha del campo «%s» no es válida y no se guardó.', 'asogravas-directorio' ),
					$campo['label']
				)
			);
		}

		return self::ok( $crudo );
	}

	private static function tipo_checkbox( $valor ): array {
		return self::ok( empty( $valor ) ? '0' : '1' );
	}

	private static function tipo_editor( $valor ): array {
		return self::ok( wp_kses_post( (string) $valor ) );
	}

	private static function tipo_image( $valor, array $campo ): array {
		return self::adjunto( $valor, $campo, 'image' );
	}

	private static function tipo_pdf( $valor, array $campo ): array {
		return self::adjunto( $valor, $campo, 'application/pdf' );
	}

	private static function adjunto( $valor, array $campo, string $tipo_esperado ): array {
		$id = absint( $valor );

		if ( 0 === $id ) {
			return self::ok( '' );
		}

		$mime = get_post_mime_type( $id );

		if ( ! $mime ) {
			return self::error(
				sprintf(
					/* translators: %s: nombre del campo. */
					__( 'El archivo del campo «%s» ya no existe en la biblioteca y no se guardó.', 'asogravas-directorio' ),
					$campo['label']
				)
			);
		}

		$coincide = 'image' === $tipo_esperado
			? str_starts_with( $mime, 'image/' )
			: $mime === $tipo_esperado;

		if ( ! $coincide ) {
			return self::error(
				sprintf(
					/* translators: %s: nombre del campo. */
					__( 'El archivo del campo «%s» no tiene el formato esperado y no se guardó.', 'asogravas-directorio' ),
					$campo['label']
				)
			);
		}

		return self::ok( (string) $id );
	}

	private static function tipo_gallery( $valor ): array {
		$ids = array_filter( array_map( 'absint', explode( ',', (string) $valor ) ) );

		// Se descartan los adjuntos borrados de la biblioteca para que la ficha no intente
		// pintar imágenes inexistentes.
		$validos = array();

		foreach ( $ids as $id ) {
			$mime = get_post_mime_type( $id );

			if ( $mime && str_starts_with( $mime, 'image/' ) ) {
				$validos[] = $id;
			}
		}

		return self::ok( implode( ',', array_unique( $validos ) ) );
	}

	/**
	 * Devuelve la URL tal cual la pegó el usuario; el ID extraído se guarda aparte, en
	 * `_asdir_video_id`, y es lo que usa la plantilla.
	 */
	private static function tipo_youtube( $valor, array $campo ): array {
		$crudo = trim( (string) $valor );

		if ( '' === $crudo ) {
			return self::ok( '' );
		}

		if ( '' === asdir_youtube_id( $crudo ) ) {
			return self::error(
				sprintf(
					/* translators: %s: nombre del campo. */
					__( 'No se reconoció un video de YouTube en el campo «%s» y no se guardó.', 'asogravas-directorio' ),
					$campo['label']
				)
			);
		}

		return self::ok( esc_url_raw( $crudo ) );
	}
}
