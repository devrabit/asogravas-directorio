<?php
/**
 * Shortcode [asogravas_directorio].
 *
 * @package Asogravas_Directorio
 */

defined( 'ABSPATH' ) || exit;

class ASDIR_Shortcode {

	private static bool $presente = false;

	public function __construct() {
		add_shortcode( 'asogravas_directorio', array( $this, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'encolar' ) );
		add_filter( 'redirect_canonical', array( $this, 'no_redirigir_paginacion' ) );
	}

	/**
	 * @param array<string, string>|string $atts
	 */
	public function render( $atts ): string {
		self::$presente = true;
		$this->encolar_assets();

		$atts = shortcode_atts(
			array(
				'hero'             => 'si',
				'destacadas'       => 'si',
				'destacadas_total' => '3',
				'cta'              => 'si',
				'filtros'          => 'si',
				'por_pagina'       => '9',
				'columnas'         => '3',
				'categoria'        => '',
				'orden'            => 'destacadas',
				'solo_destacadas'  => 'no',
			),
			is_array( $atts ) ? $atts : array(),
			'asogravas_directorio'
		);

		$filtros = ASDIR_Query::request( $atts );
		$query   = ASDIR_Query::empresas( $filtros );

		$contexto = array(
			'atts'       => $atts,
			'filtros'    => $filtros,
			'query'      => $query,
			'contadores' => ASDIR_Query::contadores( $filtros ),
			'categorias' => ASDIR_Query::categorias_visibles( $filtros['categorias_base'] ),
			'hay_mas'    => (int) $query->max_num_pages > (int) $filtros['pagina'],
		);

		ob_start();
		echo '<div class="asdir-directorio" data-columnas="' . esc_attr( (string) $filtros['columnas'] ) . '">';

		if ( 'si' === $atts['hero'] ) {
			asdir_plantilla( 'parts/hero.php', $contexto );
		}

		if ( 'si' === $atts['destacadas'] ) {
			$destacadas = ASDIR_Query::destacadas( absint( $atts['destacadas_total'] ) );
			if ( $destacadas->have_posts() ) {
				asdir_plantilla( 'parts/destacadas.php', array( 'query' => $destacadas ) );
			}
			wp_reset_postdata();
		}

		echo '<div class="asdir-cuerpo" id="asdir-resultados">';

		if ( 'si' === $atts['filtros'] ) {
			asdir_plantilla( 'parts/filtros.php', $contexto );
		}

		asdir_plantilla( 'parts/resultados.php', $contexto );

		echo '</div>';

		if ( 'si' === $atts['cta'] ) {
			asdir_plantilla( 'parts/cta.php', $contexto );
		}

		ASDIR_Schema::item_list( $query );

		echo '</div>';

		wp_reset_postdata();

		return (string) ob_get_clean();
	}

	public function encolar(): void {
		if ( is_singular() ) {
			$post = get_post();
			if ( $post && ( has_shortcode( (string) $post->post_content, 'asogravas_directorio' ) || has_block( 'asogravas/directorio', $post ) ) ) {
				self::$presente = true;
			}
		}

		if ( is_post_type_archive( ASDIR_Post_Type::SLUG ) ) {
			self::$presente = true;
		}

		if ( self::$presente ) {
			$this->encolar_assets();
		}
	}

	private function encolar_assets(): void {
		wp_enqueue_style(
			'asdir-directorio',
			ASDIR_URL . 'assets/css/directorio.css',
			array(),
			ASDIR_VERSION
		);

		wp_enqueue_script(
			'asdir-directorio',
			ASDIR_URL . 'assets/js/directorio.js',
			array(),
			ASDIR_VERSION,
			true
		);

		wp_localize_script(
			'asdir-directorio',
			'asdirDir',
			array(
				'ajax'    => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'asdir_filtrar' ),
				'i18n'    => array(
					'error' => __( 'No se pudieron actualizar los resultados. Recarga la página.', 'asogravas-directorio' ),
				),
			)
		);
	}

	/**
	 * Evita que WordPress trate asdir_pag como paginación nativa y redirija.
	 *
	 * @param string|false $redirect
	 * @return string|false
	 */
	public function no_redirigir_paginacion( $redirect ) {
		if ( isset( $_GET['asdir_pag'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return false;
		}

		return $redirect;
	}

	public static function esta_presente(): bool {
		return self::$presente;
	}
}
