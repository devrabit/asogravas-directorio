<?php
/**
 * Funciones de apoyo compartidas entre el administrador y el frontend.
 *
 * @package Asogravas_Directorio
 */

defined( 'ABSPATH' ) || exit;

/**
 * Lee un meta de empresa como cadena.
 */
function asdir_meta( int $post_id, string $key ): string {
	$valor = get_post_meta( $post_id, $key, true );

	return is_scalar( $valor ) ? (string) $valor : '';
}

function asdir_solo_digitos( string $valor ): string {
	return preg_replace( '/\D/', '', $valor ) ?? '';
}

/**
 * Convierte un teléfono escrito a mano en algo que `tel:` pueda marcar.
 */
function asdir_tel_link( string $telefono ): string {
	$limpio = preg_replace( '/[^0-9+]/', '', $telefono ) ?? '';

	// Un «+» solo tiene sentido al inicio del número.
	$limpio = ( str_starts_with( $limpio, '+' ) ? '+' : '' ) . asdir_solo_digitos( $limpio );

	return '' === asdir_solo_digitos( $limpio ) ? '' : 'tel:' . $limpio;
}

/**
 * Extrae el ID de un video de YouTube desde cualquiera de sus formatos de enlace.
 * Devuelve cadena vacía si no reconoce ninguno.
 */
function asdir_youtube_id( string $url ): string {
	$url = trim( $url );

	if ( '' === $url ) {
		return '';
	}

	// Un ID suelto, pegado sin la URL completa.
	if ( preg_match( '/^[A-Za-z0-9_-]{11}$/', $url ) ) {
		return $url;
	}

	$patrones = array(
		'#youtube\.com/watch\?(?:.*&)?v=([A-Za-z0-9_-]{11})#i',
		'#youtu\.be/([A-Za-z0-9_-]{11})#i',
		'#youtube\.com/embed/([A-Za-z0-9_-]{11})#i',
		'#youtube\.com/shorts/([A-Za-z0-9_-]{11})#i',
		'#youtube\.com/live/([A-Za-z0-9_-]{11})#i',
		'#youtube-nocookie\.com/embed/([A-Za-z0-9_-]{11})#i',
	);

	foreach ( $patrones as $patron ) {
		if ( preg_match( $patron, $url, $coincidencias ) ) {
			return $coincidencias[1];
		}
	}

	return '';
}

/**
 * IDs de adjuntos de la galería, en el orden guardado.
 *
 * @return int[]
 */
function asdir_galeria_ids( int $post_id ): array {
	$crudo = asdir_meta( $post_id, '_asdir_galeria' );

	if ( '' === $crudo ) {
		return array();
	}

	return array_values( array_filter( array_map( 'absint', explode( ',', $crudo ) ) ) );
}

/**
 * Peso legible de un adjunto, para mostrarlo junto al botón de descarga.
 */
function asdir_peso_adjunto( int $attachment_id ): string {
	$ruta = get_attached_file( $attachment_id );

	if ( ! $ruta || ! file_exists( $ruta ) ) {
		return '';
	}

	return size_format( (int) filesize( $ruta ), 1 );
}

function asdir_es_colombia( int $post_id ): bool {
	$pais = trim( asdir_meta( $post_id, '_asdir_pais' ) );

	return '' === $pais || 0 === strcasecmp( $pais, 'Colombia' );
}

/**
 * País si la empresa no es colombiana; si lo es, ciudad y departamento.
 */
function asdir_ubicacion( int $post_id ): string {
	if ( ! asdir_es_colombia( $post_id ) ) {
		return asdir_meta( $post_id, '_asdir_pais' );
	}

	$partes = array_filter(
		array(
			asdir_meta( $post_id, '_asdir_ciudad' ),
			asdir_meta( $post_id, '_asdir_departamento' ),
		)
	);

	return implode( ', ', $partes );
}

function asdir_categoria( int $post_id ): ?WP_Term {
	$terminos = get_the_terms( $post_id, ASDIR_Taxonomy::SLUG );

	if ( ! $terminos || is_wp_error( $terminos ) ) {
		return null;
	}

	return array_values( $terminos )[0];
}

function asdir_resumen( int $post_id ): string {
	$post = get_post( $post_id );

	if ( ! $post instanceof WP_Post ) {
		return '';
	}

	return trim( wp_strip_all_tags( $post->post_excerpt ) );
}

function asdir_portada_id( int $post_id ): int {
	return absint( asdir_meta( $post_id, '_asdir_portada' ) );
}

/**
 * Localiza una plantilla: primero el tema, luego el plugin.
 */
function asdir_localizar_plantilla( string $relativo ): string {
	$relativo = ltrim( $relativo, '/' );
	$tema     = locate_template( array( 'asogravas-directorio/' . $relativo ) );

	if ( $tema ) {
		return $tema;
	}

	$plugin = ASDIR_PATH . 'templates/' . $relativo;

	return is_readable( $plugin ) ? $plugin : '';
}

/**
 * @param array<string, mixed> $args
 */
function asdir_plantilla( string $relativo, array $args = [] ): void {
	$archivo = asdir_localizar_plantilla( $relativo );

	if ( '' === $archivo ) {
		return;
	}

	extract( $args, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- ámbito acotado a la plantilla.
	include $archivo;
}

function asdir_ajuste( string $clave ): string {
	return ASDIR_Settings::get( $clave );
}

function asdir_logo_html( int $post_id, string $size = 'asdir-logo' ): string {
	if ( has_post_thumbnail( $post_id ) ) {
		return get_the_post_thumbnail(
			$post_id,
			$size,
			array(
				'class'    => 'asdir-logo',
				'alt'      => get_the_title( $post_id ),
				'loading'  => 'lazy',
				'decoding' => 'async',
			)
		);
	}

	$marca = asdir_inicial_y_color( get_the_title( $post_id ) );

	return sprintf(
		'<span class="asdir-iniciales" aria-hidden="true">%s</span>',
		esc_html( $marca['inicial'] )
	);
}

function asdir_icono( string $nombre ): string {
	$iconos = array(
		'lupa'      => '<svg class="asdir-icono" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/><path d="M20 20l-3.5-3.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
		'pin'       => '<svg class="asdir-icono" width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 22s7-7.2 7-12a7 7 0 1 0-14 0c0 4.8 7 12 7 12z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="10" r="2.5" fill="currentColor"/></svg>',
		'estrella'  => '<svg class="asdir-icono" width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 3.2l2.4 4.9 5.4.8-3.9 3.8.9 5.4L12 15.6 7.2 18.1l.9-5.4L4.2 8.9l5.4-.8L12 3.2z"/></svg>',
		'flecha'    => '<svg class="asdir-icono" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
		'whatsapp'  => '<svg class="asdir-icono" width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12.04 2C6.58 2 2.15 6.4 2.15 11.84c0 1.98.58 3.82 1.58 5.38L2 22l4.95-1.63a9.86 9.86 0 0 0 5.09 1.4h.01c5.46 0 9.89-4.4 9.89-9.84C21.94 6.4 17.5 2 12.04 2zm5.76 13.98c-.24.68-1.4 1.24-1.93 1.32-.5.07-1.13.1-1.82-.11-.42-.13-.96-.31-1.66-.61-2.92-1.26-4.82-4.2-4.97-4.4-.14-.19-1.18-1.57-1.18-3 0-1.42.74-2.12 1-2.41.26-.29.57-.36.76-.36h.55c.17 0 .41-.07.64.49.24.58.82 2 .89 2.15.07.14.12.31.02.5-.1.19-.14.31-.29.48-.14.17-.3.37-.43.5-.14.14-.29.29-.12.57.17.29.75 1.23 1.61 2 .99.88 1.82 1.16 2.11 1.29.29.14.45.12.62-.07.17-.19.72-.84.91-1.13.19-.29.38-.24.64-.14.26.1 1.66.78 1.94.93.29.14.48.21.55.33.07.12.07.68-.17 1.36z"/></svg>',
		'web'       => '<svg class="asdir-icono" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M14 4h6v6M10 14 20 4M20 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
		'telefono'  => '<svg class="asdir-icono" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6.5 3h3l1.5 5-2 1.5a12 12 0 0 0 5.5 5.5L16 13l5 1.5v3A2 2 0 0 1 19 20 15 15 0 0 1 4 5a2 2 0 0 1 2.5-2z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>',
		'correo'    => '<svg class="asdir-icono" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="2"/><path d="m4 7 8 6 8-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
		'check'     => '<svg class="asdir-icono" width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/><path d="m8 12 3 3 5-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
		'comillas'  => '<svg class="asdir-icono" width="28" height="28" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M7.2 18c-2.2 0-4-1.8-4-4.1 0-3.3 2.4-6.5 5.8-8.1l.8 1.4C7.4 8.7 6 10.7 6 12.6c.4-.2.9-.3 1.4-.3 1.6 0 2.8 1.2 2.8 2.8S8.9 18 7.2 18zm9.6 0c-2.2 0-4-1.8-4-4.1 0-3.3 2.4-6.5 5.8-8.1l.8 1.4c-2.4 1.5-3.8 3.5-3.8 5.4.4-.2.9-.3 1.4-.3 1.6 0 2.8 1.2 2.8 2.8s-1.3 2.9-3 2.9z"/></svg>',
		'pdf'       => '<svg class="asdir-icono" width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 3h7l5 5v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z" stroke="currentColor" stroke-width="2"/><path d="M14 3v5h5" stroke="currentColor" stroke-width="2"/><path d="M8.5 16h2.2a1.4 1.4 0 0 0 0-2.8H8.5V12H11a2.6 2.6 0 1 1 0 5.2H8.5V16zm6.2-4v5h1.3c1.6 0 2.7-.9 2.7-2.5S17.6 12 16 12h-1.3zm1.3 1.2h.3c.8 0 1.3.4 1.3 1.3s-.5 1.3-1.3 1.3h-.3v-2.6z" fill="currentColor"/></svg>',
		'play'      => '<svg class="asdir-icono" width="28" height="28" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M9 7.5v9l8-4.5-8-4.5z"/></svg>',
		'caja'      => '<svg class="asdir-icono" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 8.5 12 4l8 4.5v7L12 20l-8-4.5v-7z" stroke="currentColor" stroke-width="2"/><path d="M4 8.5 12 13l8-4.5M12 13v7" stroke="currentColor" stroke-width="2"/></svg>',
		'portapapeles' => '<svg class="asdir-icono" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 4h6a2 2 0 0 1 2 2v1h1a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h1V6a2 2 0 0 1 2-2z" stroke="currentColor" stroke-width="2"/><path d="M9 4a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2" stroke="currentColor" stroke-width="2"/></svg>',
		'chevron-izq' => '<svg class="asdir-icono" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m15 6-6 6 6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
		'chevron-der' => '<svg class="asdir-icono" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m9 6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
		'facebook'  => '<svg class="asdir-icono" width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M14 9h3V6h-3c-2.2 0-4 1.8-4 4v2H8v3h2v7h3v-7h3l1-3h-4v-2c0-.6.4-1 1-1z"/></svg>',
		'x'         => '<svg class="asdir-icono" width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M4 4h4.2l4 5.5L16.8 4H20l-6.1 7.3L20.5 20H16.3l-4.4-6-5 6H3.5l6.5-7.7L4 4z"/></svg>',
		'linkedin'  => '<svg class="asdir-icono" width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M6.5 9.5H3.8V20h2.7V9.5zM5.1 4A1.6 1.6 0 1 0 5.1 7.2 1.6 1.6 0 0 0 5.1 4zM20.2 20h-2.7v-5.6c0-1.6-.6-2.7-2-2.7-1.1 0-1.7.7-2 1.4-.1.3-.1.6-.1.9V20h-2.7s.1-8.7 0-9.6h2.7v1.5c.4-.7 1.3-1.8 3.2-1.8 2.3 0 4.1 1.5 4.1 4.8V20z"/></svg>',
		'instagram' => '<svg class="asdir-icono" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="2"/><circle cx="17.5" cy="6.5" r="1.2" fill="currentColor"/></svg>',
		'youtube'   => '<svg class="asdir-icono" width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M22 12.2c0-2.2-.3-3.8-.5-4.6-.3-.9-.9-1.5-1.8-1.8C18.2 5.4 12 5.4 12 5.4s-6.2 0-7.7.4c-.9.3-1.5.9-1.8 1.8C2.3 8.4 2 10 2 12.2s.3 3.8.5 4.6c.3.9.9 1.5 1.8 1.8 1.5.4 7.7.4 7.7.4s6.2 0 7.7-.4c.9-.3 1.5-.9 1.8-1.8.2-.8.5-2.4.5-4.6zM10 15.3V9.1l5.2 3.1L10 15.3z"/></svg>',
		'tiktok'    => '<svg class="asdir-icono" width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M16.5 3c.4 2.4 1.8 4.1 4 4.6v2.5c-1.4.1-2.7-.3-4-1.1v5.5a5.9 5.9 0 1 1-5.9-5.9c.3 0 .6 0 .9.1v2.7a3.2 3.2 0 1 0 2.3 3.1V3h2.7z"/></svg>',
	);

	return $iconos[ $nombre ] ?? '';
}

/**
 * ID de la foto de la tarjeta Descripción: portada, o la primera de la galería.
 */
function asdir_foto_descripcion_id( int $post_id ): int {
	$portada = asdir_portada_id( $post_id );

	if ( $portada ) {
		return $portada;
	}

	$galeria = asdir_galeria_ids( $post_id );

	return $galeria[0] ?? 0;
}

/**
 * Convierte el HTML de productos/servicios en filas de lista cuando hay `li` o líneas.
 *
 * @return array{modo: string, items?: string[], html?: string}
 */
function asdir_items_lista( string $html ): array {
	$html = trim( $html );

	if ( '' === $html ) {
		return array( 'modo' => 'vacio' );
	}

	if ( preg_match_all( '/<li\b[^>]*>(.*?)<\/li>/is', $html, $coincidencias ) ) {
		$items = array();

		foreach ( $coincidencias[1] as $item ) {
			$texto = trim( wp_kses_post( $item ) );

			if ( '' !== wp_strip_all_tags( $texto ) ) {
				$items[] = $texto;
			}
		}

		if ( $items ) {
			return array(
				'modo'  => 'lista',
				'items' => $items,
			);
		}
	}

	$plano = trim( wp_strip_all_tags( $html ) );
	$lineas = preg_split( '/\R+/u', $plano ) ?: array();
	$lineas = array_values(
		array_filter(
			array_map( 'trim', $lineas ),
			static function ( string $linea ): bool {
				return '' !== $linea;
			}
		)
	);

	if ( count( $lineas ) > 1 ) {
		return array(
			'modo'  => 'lista',
			'items' => array_map( 'esc_html', $lineas ),
		);
	}

	return array(
		'modo' => 'html',
		'html' => wp_kses_post( $html ),
	);
}

/**
 * Destino del botón «Contactar empresa». Cotización aún no existe (SPEC 04).
 */
function asdir_url_contacto( int $post_id ): string {
	$email = asdir_meta( $post_id, '_asdir_email' );

	if ( $email && is_email( $email ) ) {
		return 'mailto:' . $email;
	}

	$whatsapp = asdir_meta( $post_id, '_asdir_whatsapp' );

	return $whatsapp ?: '';
}

/**
 * URL del directorio, preservando filtros si el referrer es del mismo sitio.
 */
function asdir_url_volver_directorio(): string {
	$base = ASDIR_Settings::url_directorio();
	$ref  = wp_get_referer();

	if ( ! $ref ) {
		return $base;
	}

	$home = home_url( '/' );

	if ( 0 !== strpos( $ref, $home ) ) {
		return $base;
	}

	$partes = wp_parse_url( $ref );

	if ( empty( $partes['query'] ) ) {
		return $base;
	}

	parse_str( $partes['query'], $params );

	$filtros = array();

	foreach ( $params as $clave => $valor ) {
		if ( is_string( $clave ) && str_starts_with( $clave, 'asdir_' ) ) {
			$filtros[ $clave ] = $valor;
		}
	}

	if ( ! $filtros ) {
		return $base;
	}

	return add_query_arg( $filtros, $base );
}

function asdir_tiene_contacto( int $post_id ): bool {
	return '' !== asdir_meta( $post_id, '_asdir_telefono' )
		|| '' !== asdir_meta( $post_id, '_asdir_email' )
		|| '' !== asdir_meta( $post_id, '_asdir_whatsapp' );
}

/**
 * Redes sociales con valor, listas para pintar iconos.
 *
 * @return array<string, array{label: string, url: string}>
 */
function asdir_redes_sociales( int $post_id ): array {
	$claves = array(
		'_asdir_facebook'  => 'facebook',
		'_asdir_x'         => 'x',
		'_asdir_linkedin'  => 'linkedin',
		'_asdir_instagram' => 'instagram',
		'_asdir_youtube'   => 'youtube',
		'_asdir_tiktok'    => 'tiktok',
	);

	$redes = array();

	foreach ( $claves as $meta_key => $slug ) {
		$url = asdir_meta( $post_id, $meta_key );

		if ( '' === $url ) {
			continue;
		}

		$campo = ASDIR_Fields::get( $meta_key );

		$redes[ $slug ] = array(
			'label' => $campo['label'] ?? $slug,
			'url'   => $url,
		);
	}

	return $redes;
}

function asdir_es_destacada( int $post_id ): bool {
	return '1' === asdir_meta( $post_id, '_asdir_destacada' );
}

/**
 * Sigla visible cuando no hay logo: misma pieza que un logo, no un título.
 * Nombres cortos (ERAL) se muestran enteros; el resto, primera letra.
 * Si el nombre trae un número, se combina: «Empresa Ejemplo 02» → E2.
 */
function asdir_inicial_y_color( string $nombre ): array {
	$nombre   = trim( wp_strip_all_tags( $nombre ) );
	$compacto = preg_replace( '/\s+/', '', $nombre ) ?? $nombre;
	$letra    = '' === $nombre ? '?' : mb_strtoupper( mb_substr( $nombre, 0, 1 ) );

	if ( '' === $compacto ) {
		$marca = '?';
	} elseif ( mb_strlen( $compacto ) <= 4 && ! preg_match( '/\s/u', $nombre ) ) {
		$marca = mb_strtoupper( $compacto );
	} elseif ( preg_match( '/(?:^|\s)(\d{1,2})\s*$/u', $nombre, $numero ) ) {
		$marca = $letra . $numero[1];
	} else {
		$marca = $letra;
	}

	$tono = (int) ( hexdec( substr( md5( $nombre ), 0, 6 ) ) % 360 );

	return array(
		'inicial' => $marca,
		'color'   => sprintf( 'hsl(%d, 45%%, 42%%)', $tono ),
	);
}
