<?php
/**
 * Filtrado AJAX de la grilla pública.
 *
 * @package Asogravas_Directorio
 */

defined( 'ABSPATH' ) || exit;

class ASDIR_Ajax {

	public function __construct() {
		add_action( 'wp_ajax_asdir_filtrar', array( $this, 'filtrar' ) );
		add_action( 'wp_ajax_nopriv_asdir_filtrar', array( $this, 'filtrar' ) );
	}

	public function filtrar(): void {
		check_ajax_referer( 'asdir_filtrar', 'nonce' );

		$fuente = wp_unslash( $_POST );
		$atts   = array(
			'por_pagina'      => isset( $fuente['por_pagina'] ) ? absint( $fuente['por_pagina'] ) : 9,
			'columnas'        => $fuente['columnas'] ?? 3,
			'categoria'       => isset( $fuente['categoria'] ) ? sanitize_text_field( (string) $fuente['categoria'] ) : '',
			'orden'           => $fuente['orden'] ?? 'destacadas',
			'solo_destacadas' => isset( $fuente['solo_destacadas'] ) ? sanitize_text_field( (string) $fuente['solo_destacadas'] ) : 'no',
		);

		$filtros = ASDIR_Query::request( $atts, $fuente );
		$anexar  = ! empty( $fuente['anexar'] );
		$query   = ASDIR_Query::empresas( $filtros );

		ob_start();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				asdir_plantilla( 'parts/card-empresa.php', array( 'post_id' => get_the_ID() ) );
			}
		}

		$html = (string) ob_get_clean();
		wp_reset_postdata();

		$conteo = (int) $query->found_posts;
		$hay_mas = (int) $query->max_num_pages > (int) $filtros['pagina'];

		wp_send_json_success(
			array(
				'html'       => $html,
				'conteo'     => $conteo,
				'conteo_txt' => self::texto_conteo( $conteo ),
				'hay_mas'    => $hay_mas,
				'pagina'     => (int) $filtros['pagina'],
				'contadores' => ASDIR_Query::contadores( $filtros ),
				'url'        => ASDIR_Query::url( $filtros ),
				'anexar'     => $anexar,
				'vacio'      => 0 === $conteo,
				'vacio_html' => 0 === $conteo ? self::html_vacio() : '',
			)
		);
	}

	public static function texto_conteo( int $n ): string {
		return sprintf(
			/* translators: %s: número de empresas */
			_n( '%s empresa', '%s empresas', $n, 'asogravas-directorio' ),
			number_format_i18n( $n )
		);
	}

	private static function html_vacio(): string {
		ob_start();
		asdir_plantilla( 'parts/sin-resultados.php', array() );
		return (string) ob_get_clean();
	}
}
