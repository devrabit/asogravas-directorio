<?php
/**
 * Plantillas del archivo y de la ficha de empresa.
 *
 * @package Asogravas_Directorio
 */

defined( 'ABSPATH' ) || exit;

class ASDIR_Templates {

	public function __construct() {
		add_filter( 'template_include', array( $this, 'resolver' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'encolar_ficha' ) );
	}

	public function resolver( string $plantilla ): string {
		if ( is_singular( ASDIR_Post_Type::SLUG ) ) {
			$propia = asdir_localizar_plantilla( 'single-asdir_empresa.php' );

			return $propia ?: $plantilla;
		}

		if ( is_post_type_archive( ASDIR_Post_Type::SLUG ) ) {
			$propia = asdir_localizar_plantilla( 'archive-asdir_empresa.php' );

			return $propia ?: $plantilla;
		}

		return $plantilla;
	}

	public function encolar_ficha(): void {
		if ( ! is_singular( ASDIR_Post_Type::SLUG ) ) {
			return;
		}

		wp_enqueue_style(
			'asdir-directorio',
			ASDIR_URL . 'assets/css/directorio.css',
			array(),
			ASDIR_VERSION
		);

		wp_enqueue_style(
			'asdir-empresa',
			ASDIR_URL . 'assets/css/empresa.css',
			array( 'asdir-directorio' ),
			ASDIR_VERSION
		);

		wp_enqueue_script(
			'asdir-empresa',
			ASDIR_URL . 'assets/js/empresa.js',
			array(),
			ASDIR_VERSION,
			true
		);
	}
}
