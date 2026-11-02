<?php
/**
 * Custom post type de empresas.
 *
 * @package Asogravas_Directorio
 */

defined( 'ABSPATH' ) || exit;

class ASDIR_Post_Type {

	const SLUG = 'asdir_empresa';

	public function __construct() {
		add_action( 'init', array( $this, 'registrar' ) );
		add_action( 'after_setup_theme', array( $this, 'registrar_tamano_logo' ) );
		add_action( 'add_meta_boxes_' . self::SLUG, array( $this, 'ajustar_resumen' ) );
		add_filter( 'use_block_editor_for_post_type', array( $this, 'forzar_editor_clasico' ), 10, 2 );
		add_filter( 'enter_title_here', array( $this, 'texto_del_titulo' ), 10, 2 );
		add_filter( 'post_updated_messages', array( $this, 'mensajes' ) );
		add_filter( 'wp_insert_post_data', array( $this, 'limitar_resumen' ), 10, 2 );
	}

	public function registrar(): void {
		$etiquetas = array(
			'name'                  => __( 'Empresas', 'asogravas-directorio' ),
			'singular_name'         => __( 'Empresa', 'asogravas-directorio' ),
			'menu_name'             => __( 'Empresas', 'asogravas-directorio' ),
			'add_new'               => __( 'Añadir nueva', 'asogravas-directorio' ),
			'add_new_item'          => __( 'Añadir nueva empresa', 'asogravas-directorio' ),
			'edit_item'             => __( 'Editar empresa', 'asogravas-directorio' ),
			'new_item'              => __( 'Nueva empresa', 'asogravas-directorio' ),
			'view_item'             => __( 'Ver empresa', 'asogravas-directorio' ),
			'view_items'            => __( 'Ver empresas', 'asogravas-directorio' ),
			'search_items'          => __( 'Buscar empresas', 'asogravas-directorio' ),
			'not_found'             => __( 'No hay empresas registradas', 'asogravas-directorio' ),
			'not_found_in_trash'    => __( 'No hay empresas en la papelera', 'asogravas-directorio' ),
			'all_items'             => __( 'Todas las empresas', 'asogravas-directorio' ),
			'archives'              => __( 'Directorio de empresas', 'asogravas-directorio' ),
			'featured_image'        => __( 'Logo de la empresa', 'asogravas-directorio' ),
			'set_featured_image'    => __( 'Establecer logo', 'asogravas-directorio' ),
			'remove_featured_image' => __( 'Quitar logo', 'asogravas-directorio' ),
			'use_featured_image'    => __( 'Usar como logo', 'asogravas-directorio' ),
			'item_published'        => __( 'Empresa publicada.', 'asogravas-directorio' ),
			'item_updated'          => __( 'Empresa actualizada.', 'asogravas-directorio' ),
		);

		register_post_type(
			self::SLUG,
			array(
				'labels'          => $etiquetas,
				'public'          => true,
				'show_in_rest'    => true,
				'has_archive'     => true,
				'menu_icon'       => 'dashicons-building',
				'menu_position'   => 25,
				'supports'        => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
				'rewrite'         => array(
					'slug'       => 'empresas',
					'with_front' => false,
				),
				'capability_type' => array( 'asdir_empresa', 'asdir_empresas' ),
				'map_meta_cap'    => true,
			)
		);
	}

	/**
	 * Recorte usado por la tarjeta de la grilla. `false` en el tercer parámetro conserva la
	 * proporción del logo en lugar de recortarlo.
	 */
	public function registrar_tamano_logo(): void {
		add_image_size( 'asdir-logo', 400, 300, false );
		add_image_size( 'asdir-portada', 800, 600, true );
	}

	/**
	 * El excerpt nativo pasa a llamarse Resumen corto y se mueve a la barra lateral,
	 * junto al logo y la portada.
	 */
	public function ajustar_resumen(): void {
		remove_meta_box( 'postexcerpt', self::SLUG, 'normal' );
		add_meta_box(
			'postexcerpt',
			__( 'Resumen corto', 'asogravas-directorio' ),
			'post_excerpt_meta_box',
			self::SLUG,
			'side',
			'high'
		);
	}

	/**
	 * @param array<string, mixed> $datos
	 * @param array<string, mixed> $postarr
	 * @return array<string, mixed>
	 */
	public function limitar_resumen( array $datos, array $postarr ): array {
		if ( self::SLUG !== ( $datos['post_type'] ?? '' ) ) {
			return $datos;
		}

		$datos['post_excerpt'] = mb_substr( sanitize_textarea_field( (string) ( $datos['post_excerpt'] ?? '' ) ), 0, 160 );

		return $datos;
	}

	/**
	 * La captura depende de metaboxes con TinyMCE, que en el editor de bloques quedan relegados
	 * al panel de compatibilidad. Para esta pantalla el editor clásico da una experiencia mejor.
	 */
	public function forzar_editor_clasico( bool $usar, string $tipo ): bool {
		return self::SLUG === $tipo ? false : $usar;
	}

	public function texto_del_titulo( string $texto, WP_Post $post ): string {
		return self::SLUG === $post->post_type
			? __( 'Nombre de la empresa', 'asogravas-directorio' )
			: $texto;
	}

	public function mensajes( array $mensajes ): array {
		$mensajes[ self::SLUG ] = array(
			0 => '',
			1 => __( 'Empresa actualizada.', 'asogravas-directorio' ),
			4 => __( 'Empresa actualizada.', 'asogravas-directorio' ),
			6 => __( 'Empresa publicada.', 'asogravas-directorio' ),
			7 => __( 'Empresa guardada.', 'asogravas-directorio' ),
			8 => __( 'Empresa enviada para revisión.', 'asogravas-directorio' ),
			10 => __( 'Borrador de empresa actualizado.', 'asogravas-directorio' ),
		);

		return $mensajes;
	}

	/**
	 * Capabilities derivadas de `capability_type`, que WordPress no crea por sí solo.
	 *
	 * @return string[]
	 */
	public static function capabilities(): array {
		return array(
			'edit_asdir_empresa',
			'read_asdir_empresa',
			'delete_asdir_empresa',
			'create_asdir_empresas',
			'edit_asdir_empresas',
			'edit_others_asdir_empresas',
			'edit_private_asdir_empresas',
			'edit_published_asdir_empresas',
			'publish_asdir_empresas',
			'read_private_asdir_empresas',
			'delete_asdir_empresas',
			'delete_private_asdir_empresas',
			'delete_published_asdir_empresas',
			'delete_others_asdir_empresas',
		);
	}
}
