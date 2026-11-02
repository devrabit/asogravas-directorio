<?php
/**
 * Campos que usan la biblioteca de medios: archivo único, galería ordenable y video.
 *
 * @package Asogravas_Directorio
 */

defined( 'ABSPATH' ) || exit;

class ASDIR_Media {

	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'encolar' ) );
	}

	public function encolar( string $hook ): void {
		$pantalla = get_current_screen();

		if ( ! $pantalla || ASDIR_Post_Type::SLUG !== $pantalla->post_type ) {
			return;
		}

		$es_edicion = in_array( $hook, array( 'post.php', 'post-new.php' ), true );

		if ( $es_edicion ) {
			wp_enqueue_media();
		}

		wp_enqueue_style(
			'asdir-admin',
			ASDIR_URL . 'assets/css/admin.css',
			array(),
			ASDIR_VERSION
		);

		wp_enqueue_script(
			'asdir-admin',
			ASDIR_URL . 'assets/js/admin.js',
			array( 'jquery', 'jquery-ui-sortable' ),
			ASDIR_VERSION,
			true
		);

		wp_localize_script(
			'asdir-admin',
			'asdirAdmin',
			array(
				'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'asdir_admin' ),
				'imagenes' => array(
					'titulo' => __( 'Seleccionar imágenes', 'asogravas-directorio' ),
					'boton'  => __( 'Usar estas imágenes', 'asogravas-directorio' ),
				),
				'pdf'      => array(
					'titulo' => __( 'Seleccionar PDF', 'asogravas-directorio' ),
					'boton'  => __( 'Usar este PDF', 'asogravas-directorio' ),
				),
				'imagen'   => array(
					'titulo' => __( 'Seleccionar imagen', 'asogravas-directorio' ),
					'boton'  => __( 'Usar esta imagen', 'asogravas-directorio' ),
				),
				'quitar'     => __( 'Quitar', 'asogravas-directorio' ),
				'maxExcerpt' => 160,
				'excerptTpl' => __( '%1$s / %2$s caracteres', 'asogravas-directorio' ),
			)
		);
	}

	/**
	 * Campo de archivo único (imagen o PDF).
	 */
	public static function render_archivo( string $id, string $name, string $valor, string $tipo ): void {
		$attachment_id = absint( $valor );
		$existe        = $attachment_id > 0 && get_post( $attachment_id );

		printf(
			'<div class="asdir-archivo" data-tipo="%s"><input type="hidden" id="%s" name="%s" value="%s" class="asdir-archivo__valor">',
			esc_attr( $tipo ),
			esc_attr( $id ),
			esc_attr( $name ),
			esc_attr( $existe ? (string) $attachment_id : '' )
		);

		echo '<div class="asdir-archivo__previa">';

		if ( $existe ) {
			self::render_previa_archivo( $attachment_id, $tipo );
		}

		echo '</div>';

		printf(
			'<p class="asdir-archivo__acciones"><button type="button" class="button asdir-archivo__elegir">%s</button> <button type="button" class="button-link asdir-archivo__quitar" %s>%s</button></p>',
			esc_html( 'pdf' === $tipo ? __( 'Seleccionar PDF', 'asogravas-directorio' ) : __( 'Seleccionar imagen', 'asogravas-directorio' ) ),
			$existe ? '' : 'hidden',
			esc_html__( 'Quitar', 'asogravas-directorio' )
		);

		echo '</div>';
	}

	private static function render_previa_archivo( int $attachment_id, string $tipo ): void {
		if ( 'pdf' === $tipo ) {
			$peso = asdir_peso_adjunto( $attachment_id );

			printf(
				'<span class="asdir-archivo__pdf"><span class="dashicons dashicons-pdf"></span> <a href="%s" target="_blank" rel="noopener">%s</a>%s</span>',
				esc_url( (string) wp_get_attachment_url( $attachment_id ) ),
				esc_html( get_the_title( $attachment_id ) ),
				'' === $peso ? '' : esc_html( ' (' . $peso . ')' )
			);

			return;
		}

		echo wp_get_attachment_image( $attachment_id, 'thumbnail', false, array( 'class' => 'asdir-archivo__img' ) );
	}

	/**
	 * Campo de galería: lista ordenable de miniaturas con un campo oculto que guarda los IDs
	 * en el orden visible.
	 */
	public static function render_galeria( string $id, string $name, string $valor ): void {
		$ids = array_filter( array_map( 'absint', explode( ',', $valor ) ) );

		printf(
			'<div class="asdir-galeria"><input type="hidden" id="%s" name="%s" value="%s" class="asdir-galeria__valor">',
			esc_attr( $id ),
			esc_attr( $name ),
			esc_attr( implode( ',', $ids ) )
		);

		echo '<ul class="asdir-galeria__lista">';

		foreach ( $ids as $attachment_id ) {
			if ( ! get_post( $attachment_id ) ) {
				continue;
			}

			self::render_item_galeria( $attachment_id );
		}

		echo '</ul>';

		printf(
			'<p><button type="button" class="button asdir-galeria__agregar">%s</button></p></div>',
			esc_html__( 'Añadir imágenes', 'asogravas-directorio' )
		);
	}

	private static function render_item_galeria( int $attachment_id ): void {
		printf(
			'<li class="asdir-galeria__item" data-id="%d">%s<button type="button" class="asdir-galeria__quitar" aria-label="%s">&times;</button></li>',
			$attachment_id,
			wp_get_attachment_image( $attachment_id, 'thumbnail' ),
			esc_attr__( 'Quitar imagen', 'asogravas-directorio' )
		);
	}

	/**
	 * Campo de video: input de URL más la miniatura del video reconocido, que se actualiza al
	 * pegar el enlace.
	 */
	public static function render_video( string $id, string $name, string $valor, array $campo ): void {
		$video_id = '' === $valor ? '' : asdir_youtube_id( $valor );

		echo '<div class="asdir-video">';

		printf(
			'<input type="url" id="%s" name="%s" value="%s" placeholder="%s" class="asdir-campo__input asdir-video__url">',
			esc_attr( $id ),
			esc_attr( $name ),
			esc_attr( $valor ),
			esc_attr( $campo['placeholder'] ?? '' )
		);

		echo '<div class="asdir-video__previa">';

		if ( '' !== $video_id ) {
			printf(
				'<img src="%s" alt="" width="200" height="150">',
				esc_url( 'https://i.ytimg.com/vi/' . $video_id . '/mqdefault.jpg' )
			);
		}

		echo '</div></div>';
	}
}
