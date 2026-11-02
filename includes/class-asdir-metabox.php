<?php
/**
 * Metaboxes del formulario de empresa: render, guardado y avisos de validación.
 *
 * @package Asogravas_Directorio
 */

defined( 'ABSPATH' ) || exit;

class ASDIR_Metabox {

	const NONCE_ACTION = 'asdir_empresa_meta';
	const NONCE_FIELD  = 'asdir_empresa_nonce';

	public function __construct() {
		add_action( 'add_meta_boxes_' . ASDIR_Post_Type::SLUG, array( $this, 'registrar' ) );
		add_action( 'save_post_' . ASDIR_Post_Type::SLUG, array( $this, 'guardar' ), 10, 2 );
		add_action( 'admin_notices', array( $this, 'mostrar_avisos' ) );
	}

	public function registrar(): void {
		foreach ( ASDIR_Fields::groups() as $id => $grupo ) {
			add_meta_box(
				$id,
				$grupo['title'],
				array( $this, 'render' ),
				ASDIR_Post_Type::SLUG,
				$grupo['context'],
				$grupo['priority'],
				array( 'grupo' => $id )
			);
		}
	}

	public function render( WP_Post $post, array $caja ): void {
		$grupos = ASDIR_Fields::groups();
		$grupo  = $grupos[ $caja['args']['grupo'] ] ?? null;

		if ( ! $grupo ) {
			return;
		}

		// Un único nonce para toda la pantalla: se imprime con el primer metabox que se renderice.
		static $nonce_impreso = false;

		if ( ! $nonce_impreso ) {
			wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
			$nonce_impreso = true;
		}

		printf(
			'<div class="asdir-campos asdir-campos--cols-%d">',
			(int) $grupo['columns']
		);

		foreach ( $grupo['fields'] as $key ) {
			$campo = ASDIR_Fields::get( $key );

			if ( $campo ) {
				$this->render_campo( $key, $campo, $post->ID );
			}
		}

		echo '</div>';
	}

	private function render_campo( string $key, array $campo, int $post_id ): void {
		$valor = asdir_meta( $post_id, $key );

		if ( '' === $valor && isset( $campo['default'] ) ) {
			$valor = (string) $campo['default'];
		}

		$id    = ltrim( $key, '_' );
		$name  = 'asdir[' . $key . ']';

		echo '<div class="asdir-campo asdir-campo--' . esc_attr( $campo['type'] ) . '">';

		if ( 'checkbox' !== $campo['type'] ) {
			printf(
				'<label class="asdir-campo__label" for="%s">%s</label>',
				esc_attr( $id ),
				esc_html( $campo['label'] )
			);
		}

		switch ( $campo['type'] ) {
			case 'editor':
				$this->render_editor( $id, $name, $valor );
				break;

			case 'checkbox':
				printf(
					'<label class="asdir-campo__check"><input type="checkbox" id="%s" name="%s" value="1" %s> %s</label>',
					esc_attr( $id ),
					esc_attr( $name ),
					checked( $valor, '1', false ),
					esc_html( $campo['label'] )
				);
				break;

			case 'image':
			case 'pdf':
				ASDIR_Media::render_archivo( $id, $name, $valor, $campo['type'] );
				break;

			case 'gallery':
				ASDIR_Media::render_galeria( $id, $name, $valor );
				break;

			case 'youtube':
				ASDIR_Media::render_video( $id, $name, $valor, $campo );
				break;

			case 'date':
				printf(
					'<input type="date" id="%s" name="%s" value="%s" class="asdir-campo__input">',
					esc_attr( $id ),
					esc_attr( $name ),
					esc_attr( $valor )
				);
				break;

			default:
				$tipos_html = array(
					'email' => 'email',
					'url'   => 'url',
					'tel'   => 'tel',
				);

				printf(
					'<input type="%s" id="%s" name="%s" value="%s" placeholder="%s" %s class="asdir-campo__input">',
					esc_attr( $tipos_html[ $campo['type'] ] ?? 'text' ),
					esc_attr( $id ),
					esc_attr( $name ),
					esc_attr( $valor ),
					esc_attr( $campo['placeholder'] ?? '' ),
					isset( $campo['maxlength'] ) ? 'maxlength="' . (int) $campo['maxlength'] . '"' : ''
				);
				break;
		}

		if ( ! empty( $campo['description'] ) ) {
			printf( '<p class="description">%s</p>', esc_html( $campo['description'] ) );
		}

		echo '</div>';
	}

	private function render_editor( string $id, string $name, string $valor ): void {
		wp_editor(
			$valor,
			$id,
			array(
				'textarea_name' => $name,
				'textarea_rows' => 8,
				'media_buttons' => true,
				'teeny'         => false,
				'quicktags'     => true,
			)
		);
	}

	public function guardar( int $post_id, WP_Post $post ): void {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) || 'auto-draft' === $post->post_status ) {
			return;
		}

		$nonce = isset( $_POST[ self::NONCE_FIELD ] )
			? sanitize_text_field( wp_unslash( $_POST[ self::NONCE_FIELD ] ) )
			: '';

		if ( '' === $nonce || ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- cada valor se sanitiza en ASDIR_Sanitize según su tipo.
		$enviado = isset( $_POST['asdir'] ) && is_array( $_POST['asdir'] ) ? wp_unslash( $_POST['asdir'] ) : array();
		$errores = array();

		foreach ( ASDIR_Fields::all() as $key => $campo ) {
			$crudo     = $enviado[ $key ] ?? '';
			$resultado = ASDIR_Sanitize::field( $crudo, $campo );

			if ( '' !== $resultado['error'] ) {
				$errores[] = $resultado['error'];
				continue;
			}

			if ( '' === $resultado['value'] ) {
				delete_post_meta( $post_id, $key );
			} else {
				update_post_meta( $post_id, $key, $resultado['value'] );
			}

			if ( 'youtube' === $campo['type'] ) {
				$this->guardar_video_id( $post_id, (string) $resultado['value'] );
			}
		}

		if ( $errores ) {
			set_transient( $this->clave_avisos( $post_id ), $errores, MINUTE_IN_SECONDS );
		}
	}

	private function guardar_video_id( int $post_id, string $url ): void {
		$video_id = '' === $url ? '' : asdir_youtube_id( $url );

		if ( '' === $video_id ) {
			delete_post_meta( $post_id, '_asdir_video_id' );
		} else {
			update_post_meta( $post_id, '_asdir_video_id', $video_id );
		}
	}

	/**
	 * Los errores se guardan por usuario además de por post: dos editores trabajando a la vez
	 * no deben ver los avisos del otro.
	 */
	private function clave_avisos( int $post_id ): string {
		return 'asdir_avisos_' . $post_id . '_' . get_current_user_id();
	}

	public function mostrar_avisos(): void {
		$pantalla = get_current_screen();

		if ( ! $pantalla || ASDIR_Post_Type::SLUG !== $pantalla->post_type ) {
			return;
		}

		$post_id = isset( $_GET['post'] ) ? absint( wp_unslash( $_GET['post'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( 0 === $post_id ) {
			return;
		}

		$avisos = get_transient( $this->clave_avisos( $post_id ) );

		if ( ! is_array( $avisos ) || ! $avisos ) {
			return;
		}

		delete_transient( $this->clave_avisos( $post_id ) );

		echo '<div class="notice notice-warning"><p><strong>';
		esc_html_e( 'Algunos campos no se guardaron:', 'asogravas-directorio' );
		echo '</strong></p><ul style="list-style:disc;margin-left:20px">';

		foreach ( $avisos as $aviso ) {
			printf( '<li>%s</li>', esc_html( $aviso ) );
		}

		echo '</ul></div>';
	}
}
