<?php
/**
 * Categorías de empresa.
 *
 * Se usa la taxonomía nativa de WordPress, compartida con las entradas, en lugar de registrar
 * una propia. Lo único que añade el plugin es la selección de un único término.
 *
 * @package Asogravas_Directorio
 */

defined( 'ABSPATH' ) || exit;

class ASDIR_Taxonomy {

	const SLUG = 'category';

	public function __construct() {
		add_action( 'init', array( $this, 'registrar' ) );
		add_action( 'add_meta_boxes', array( $this, 'reemplazar_metabox' ), 20 );
		add_action( 'save_post_' . ASDIR_Post_Type::SLUG, array( $this, 'guardar' ), 10, 2 );
	}

	public function registrar(): void {
		register_taxonomy_for_object_type( self::SLUG, ASDIR_Post_Type::SLUG );
	}

	/**
	 * El metabox nativo permite marcar varias categorías. Lo sustituimos por un desplegable de
	 * selección única, que es lo definido en el SPEC.
	 */
	public function reemplazar_metabox(): void {
		remove_meta_box( 'categorydiv', ASDIR_Post_Type::SLUG, 'side' );

		add_meta_box(
			'asdir_categoria_unica',
			__( 'Categoría', 'asogravas-directorio' ),
			array( $this, 'render_metabox' ),
			ASDIR_Post_Type::SLUG,
			'side',
			'default'
		);
	}

	public function render_metabox( WP_Post $post ): void {
		$asignados = wp_get_object_terms( $post->ID, self::SLUG, array( 'fields' => 'ids' ) );
		$actual    = is_wp_error( $asignados ) || empty( $asignados ) ? 0 : (int) $asignados[0];

		wp_nonce_field( 'asdir_guardar_categoria', 'asdir_categoria_nonce' );

		wp_dropdown_categories(
			array(
				'taxonomy'          => self::SLUG,
				'name'              => 'asdir_categoria',
				'id'                => 'asdir_categoria',
				'class'             => 'widefat',
				'selected'          => $actual,
				'hierarchical'      => true,
				'hide_empty'        => false,
				'orderby'           => 'name',
				'show_option_none'  => __( '— Sin categoría —', 'asogravas-directorio' ),
				'option_none_value' => 0,
			)
		);

		if ( current_user_can( 'manage_categories' ) ) {
			printf(
				'<p class="description"><a href="%s">%s</a></p>',
				esc_url( admin_url( 'edit-tags.php?taxonomy=' . self::SLUG . '&post_type=' . ASDIR_Post_Type::SLUG ) ),
				esc_html__( 'Gestionar categorías', 'asogravas-directorio' )
			);
		}
	}

	public function guardar( int $post_id, WP_Post $post ): void {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		$nonce = isset( $_POST['asdir_categoria_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['asdir_categoria_nonce'] ) ) : '';

		if ( '' === $nonce || ! wp_verify_nonce( $nonce, 'asdir_guardar_categoria' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$termino = isset( $_POST['asdir_categoria'] ) ? absint( wp_unslash( $_POST['asdir_categoria'] ) ) : 0;

		if ( 0 === $termino ) {
			wp_set_object_terms( $post_id, array(), self::SLUG );

			return;
		}

		if ( term_exists( $termino, self::SLUG ) ) {
			wp_set_object_terms( $post_id, array( $termino ), self::SLUG );
		}
	}
}
