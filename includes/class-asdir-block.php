<?php
/**
 * Bloque Gutenberg que envuelve el shortcode.
 *
 * @package Asogravas_Directorio
 */

defined( 'ABSPATH' ) || exit;

class ASDIR_Block {

	public function __construct() {
		add_action( 'init', array( $this, 'registrar' ) );
	}

	public function registrar(): void {
		wp_register_script(
			'asdir-block',
			ASDIR_URL . 'assets/js/block.js',
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n' ),
			ASDIR_VERSION,
			true
		);

		register_block_type(
			'asogravas/directorio',
			array(
				'api_version'     => 2,
				'title'           => __( 'Directorio de empresas', 'asogravas-directorio' ),
				'description'     => __( 'Grilla pública del directorio de empresas.', 'asogravas-directorio' ),
				'category'        => 'widgets',
				'icon'            => 'building',
				'editor_script'   => 'asdir-block',
				'render_callback' => array( $this, 'render' ),
				'attributes'      => array(
					'hero'             => array( 'type' => 'string', 'default' => 'si' ),
					'destacadas'       => array( 'type' => 'string', 'default' => 'si' ),
					'destacadas_total' => array( 'type' => 'string', 'default' => '3' ),
					'cta'              => array( 'type' => 'string', 'default' => 'si' ),
					'filtros'          => array( 'type' => 'string', 'default' => 'si' ),
					'por_pagina'       => array( 'type' => 'string', 'default' => '9' ),
					'columnas'         => array( 'type' => 'string', 'default' => '3' ),
					'orden'            => array( 'type' => 'string', 'default' => 'destacadas' ),
					'solo_destacadas'  => array( 'type' => 'string', 'default' => 'no' ),
				),
			)
		);
	}

	/**
	 * @param array<string, string> $attributes
	 */
	public function render( array $attributes ): string {
		$shortcode = sprintf(
			'[asogravas_directorio hero="%s" destacadas="%s" destacadas_total="%s" cta="%s" filtros="%s" por_pagina="%s" columnas="%s" orden="%s" solo_destacadas="%s"]',
			esc_attr( $attributes['hero'] ?? 'si' ),
			esc_attr( $attributes['destacadas'] ?? 'si' ),
			esc_attr( $attributes['destacadas_total'] ?? '3' ),
			esc_attr( $attributes['cta'] ?? 'si' ),
			esc_attr( $attributes['filtros'] ?? 'si' ),
			esc_attr( $attributes['por_pagina'] ?? '9' ),
			esc_attr( $attributes['columnas'] ?? '3' ),
			esc_attr( $attributes['orden'] ?? 'destacadas' ),
			esc_attr( $attributes['solo_destacadas'] ?? 'no' )
		);

		return do_shortcode( $shortcode );
	}
}
