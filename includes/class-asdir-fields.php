<?php
/**
 * Definición única de los campos de una empresa.
 *
 * Cualquier pantalla que necesite saber qué campos existen (metaboxes, guardado, listado,
 * grilla pública, ficha) lee de aquí. No duplicar estas listas en otros archivos.
 *
 * @package Asogravas_Directorio
 */

defined( 'ABSPATH' ) || exit;

class ASDIR_Fields {

	/**
	 * Campos indexados por meta key.
	 *
	 * Claves de cada definición:
	 * - label        Etiqueta visible.
	 * - type         text | email | url | tel | whatsapp | date | checkbox | editor | image | pdf | gallery | youtube
	 * - description  Ayuda bajo el campo.
	 * - placeholder  Texto de ejemplo.
	 * - maxlength    Solo para campos de texto.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function all(): array {
		static $campos = null;

		if ( null !== $campos ) {
			return $campos;
		}

		$campos = array(
			'_asdir_ciudad'             => array(
				'label'       => __( 'Ciudad', 'asogravas-directorio' ),
				'type'        => 'text',
				'maxlength'   => 120,
				'placeholder' => __( 'Santiago', 'asogravas-directorio' ),
			),
			'_asdir_departamento'       => array(
				'label'       => __( 'Departamento / Región', 'asogravas-directorio' ),
				'type'        => 'text',
				'maxlength'   => 120,
				'placeholder' => __( 'Región Metropolitana', 'asogravas-directorio' ),
			),
			'_asdir_pais'               => array(
				'label'       => __( 'País', 'asogravas-directorio' ),
				'type'        => 'text',
				'maxlength'   => 80,
				'placeholder' => 'Colombia',
				'default'     => 'Colombia',
				'description' => __( 'Si lo dejas vacío se trata como Colombia. Las empresas de otro país aparecen en el filtro Internacional.', 'asogravas-directorio' ),
			),
			'_asdir_portada'            => array(
				'label'       => __( 'Foto de portada', 'asogravas-directorio' ),
				'type'        => 'image',
				'description' => __( 'Imagen de fondo de la tarjeta destacada. Independiente del logo.', 'asogravas-directorio' ),
			),
			'_asdir_telefono'           => array(
				'label'       => __( 'Teléfono', 'asogravas-directorio' ),
				'type'        => 'tel',
				'placeholder' => '+56 223645900',
				'description' => __( 'Se muestra tal como lo escribas; el enlace para llamar se genera automáticamente.', 'asogravas-directorio' ),
			),
			'_asdir_email'              => array(
				'label'       => __( 'Correo electrónico', 'asogravas-directorio' ),
				'type'        => 'email',
				'placeholder' => 'contacto@empresa.com',
			),
			'_asdir_whatsapp'           => array(
				'label'       => __( 'WhatsApp', 'asogravas-directorio' ),
				'type'        => 'whatsapp',
				'placeholder' => '+56 9 1234 5678',
				'description' => __( 'Escribe el número o pega el enlace de wa.me: se guarda siempre como enlace.', 'asogravas-directorio' ),
			),
			'_asdir_sitio_web'          => array(
				'label'       => __( 'Página web', 'asogravas-directorio' ),
				'type'        => 'url',
				'placeholder' => 'https://empresa.com',
			),
			'_asdir_productos'          => array(
				'label' => __( 'Productos', 'asogravas-directorio' ),
				'type'  => 'editor',
			),
			'_asdir_servicios'          => array(
				'label' => __( 'Servicios', 'asogravas-directorio' ),
				'type'  => 'editor',
			),
			'_asdir_casos_exito'        => array(
				'label' => __( 'Casos de éxito', 'asogravas-directorio' ),
				'type'  => 'editor',
			),
			'_asdir_casos_exito_pdf'    => array(
				'label'       => __( 'Casos de éxito en PDF', 'asogravas-directorio' ),
				'type'        => 'pdf',
				'description' => __( 'Opcional e independiente del texto anterior: puedes usar los dos, uno o ninguno.', 'asogravas-directorio' ),
			),
			'_asdir_galeria'            => array(
				'label'       => __( 'Galería de imágenes', 'asogravas-directorio' ),
				'type'        => 'gallery',
				'description' => __( 'Arrastra las miniaturas para cambiar el orden en que se mostrarán.', 'asogravas-directorio' ),
			),
			'_asdir_video'              => array(
				'label'       => __( 'Video corporativo', 'asogravas-directorio' ),
				'type'        => 'youtube',
				'placeholder' => 'https://www.youtube.com/watch?v=rG1-WWstuNo',
				'description' => __( 'Enlace de YouTube. También se aceptan enlaces youtu.be y de Shorts.', 'asogravas-directorio' ),
			),
			'_asdir_catalogo_pdf'       => array(
				'label' => __( 'Catálogo descargable', 'asogravas-directorio' ),
				'type'  => 'pdf',
			),
			'_asdir_facebook'           => array(
				'label'       => __( 'Facebook', 'asogravas-directorio' ),
				'type'        => 'url',
				'placeholder' => 'https://www.facebook.com/empresa',
			),
			'_asdir_x'                  => array(
				'label'       => __( 'X (Twitter)', 'asogravas-directorio' ),
				'type'        => 'url',
				'placeholder' => 'https://x.com/empresa',
			),
			'_asdir_linkedin'           => array(
				'label'       => __( 'LinkedIn', 'asogravas-directorio' ),
				'type'        => 'url',
				'placeholder' => 'https://www.linkedin.com/company/empresa/',
			),
			'_asdir_instagram'          => array(
				'label'       => __( 'Instagram', 'asogravas-directorio' ),
				'type'        => 'url',
				'placeholder' => 'https://www.instagram.com/empresa/',
			),
			'_asdir_youtube'            => array(
				'label'       => __( 'YouTube', 'asogravas-directorio' ),
				'type'        => 'url',
				'placeholder' => 'https://www.youtube.com/channel/...',
			),
			'_asdir_tiktok'             => array(
				'label'       => __( 'TikTok', 'asogravas-directorio' ),
				'type'        => 'url',
				'placeholder' => 'https://www.tiktok.com/@empresa',
			),
			'_asdir_datos_extra'        => array(
				'label'       => __( 'Datos extra', 'asogravas-directorio' ),
				'type'        => 'editor',
				'description' => __( 'Contenido libre que se muestra al final de la ficha pública.', 'asogravas-directorio' ),
			),
			'_asdir_destacada'          => array(
				'label' => __( 'Destacar en la página principal', 'asogravas-directorio' ),
				'type'  => 'checkbox',
			),
			'_asdir_fecha_inicio'       => array(
				'label' => __( 'Fecha de inicio', 'asogravas-directorio' ),
				'type'  => 'date',
			),
			'_asdir_cotizacion_activa'  => array(
				'label'       => __( 'Habilitar solicitud de cotización', 'asogravas-directorio' ),
				'type'        => 'checkbox',
				'description' => __( 'Reservado: el formulario de cotización todavía está en definición y el botón no se muestra.', 'asogravas-directorio' ),
			),
			'_asdir_cotizacion_email'   => array(
				'label'       => __( 'Correo que recibe las cotizaciones', 'asogravas-directorio' ),
				'type'        => 'email',
				'description' => __( 'Si lo dejas vacío se usará el correo de contacto.', 'asogravas-directorio' ),
			),
		);

		return $campos;
	}

	/**
	 * Metaboxes y el orden de los campos dentro de cada uno.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function groups(): array {
		return array(
			'asdir_datos'        => array(
				'title'    => __( 'Datos de la empresa', 'asogravas-directorio' ),
				'context'  => 'normal',
				'priority' => 'high',
				'columns'  => 2,
				'fields'   => array(
					'_asdir_ciudad',
					'_asdir_departamento',
					'_asdir_pais',
					'_asdir_telefono',
					'_asdir_email',
					'_asdir_whatsapp',
					'_asdir_sitio_web',
				),
			),
			'asdir_oferta'       => array(
				'title'    => __( 'Productos y servicios', 'asogravas-directorio' ),
				'context'  => 'normal',
				'priority' => 'default',
				'columns'  => 1,
				'fields'   => array( '_asdir_productos', '_asdir_servicios' ),
			),
			'asdir_casos'        => array(
				'title'    => __( 'Casos de éxito', 'asogravas-directorio' ),
				'context'  => 'normal',
				'priority' => 'default',
				'columns'  => 1,
				'fields'   => array( '_asdir_casos_exito', '_asdir_casos_exito_pdf' ),
			),
			'asdir_multimedia'   => array(
				'title'    => __( 'Multimedia y documentos', 'asogravas-directorio' ),
				'context'  => 'normal',
				'priority' => 'default',
				'columns'  => 1,
				'fields'   => array( '_asdir_galeria', '_asdir_video', '_asdir_catalogo_pdf' ),
			),
			'asdir_redes'        => array(
				'title'    => __( 'Redes sociales', 'asogravas-directorio' ),
				'context'  => 'normal',
				'priority' => 'default',
				'columns'  => 2,
				'fields'   => array(
					'_asdir_facebook',
					'_asdir_x',
					'_asdir_linkedin',
					'_asdir_instagram',
					'_asdir_youtube',
					'_asdir_tiktok',
				),
			),
			'asdir_extra'        => array(
				'title'    => __( 'Datos extra', 'asogravas-directorio' ),
				'context'  => 'normal',
				'priority' => 'low',
				'columns'  => 1,
				'fields'   => array( '_asdir_datos_extra' ),
			),
			'asdir_publicacion'  => array(
				'title'    => __( 'Publicación en el directorio', 'asogravas-directorio' ),
				'context'  => 'side',
				'priority' => 'default',
				'columns'  => 1,
				'fields'   => array(
					'_asdir_portada',
					'_asdir_destacada',
					'_asdir_fecha_inicio',
					'_asdir_cotizacion_activa',
					'_asdir_cotizacion_email',
				),
			),
		);
	}

	public static function get( string $key ): ?array {
		$campos = self::all();

		return $campos[ $key ] ?? null;
	}

	/**
	 * Meta keys derivadas que el plugin calcula y guarda solo, sin campo en el formulario.
	 *
	 * @return string[]
	 */
	public static function derived_keys(): array {
		return array( '_asdir_video_id' );
	}
}
