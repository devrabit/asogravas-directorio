<?php
/**
 * Datos estructurados y Open Graph de la ficha.
 *
 * @package Asogravas_Directorio
 */

defined( 'ABSPATH' ) || exit;

class ASDIR_Schema {

	public function __construct() {
		add_action( 'wp_head', array( $this, 'cabecera_ficha' ), 5 );
	}

	public function cabecera_ficha(): void {
		if ( ! is_singular( ASDIR_Post_Type::SLUG ) ) {
			return;
		}

		$post_id = get_queried_object_id();

		if ( ! $post_id ) {
			return;
		}

		$this->json_ld_organizacion( $post_id );

		if ( ! self::hay_plugin_seo() ) {
			$this->open_graph( $post_id );
			echo '<link rel="canonical" href="' . esc_url( get_permalink( $post_id ) ) . '" />' . "\n";
		}
	}

	public static function hay_plugin_seo(): bool {
		return defined( 'WPSEO_VERSION' )
			|| defined( 'RANK_MATH_VERSION' )
			|| defined( 'SEOPRESS_VERSION' )
			|| class_exists( 'RankMath' )
			|| function_exists( 'seopress_init' );
	}

	private function json_ld_organizacion( int $post_id ): void {
		$datos = array(
			'@context' => 'https://schema.org',
			'@type'    => array( 'Organization', 'LocalBusiness' ),
			'name'     => get_the_title( $post_id ),
			'url'      => get_permalink( $post_id ),
		);

		$resumen = asdir_resumen( $post_id );

		if ( '' === $resumen ) {
			$resumen = wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ), 40 );
		}

		if ( $resumen ) {
			$datos['description'] = $resumen;
		}

		if ( has_post_thumbnail( $post_id ) ) {
			$logo = wp_get_attachment_image_url( get_post_thumbnail_id( $post_id ), 'full' );

			if ( $logo ) {
				$datos['logo']  = $logo;
				$datos['image'] = $logo;
			}
		}

		$telefono = asdir_meta( $post_id, '_asdir_telefono' );
		$email    = asdir_meta( $post_id, '_asdir_email' );
		$web      = asdir_meta( $post_id, '_asdir_sitio_web' );

		if ( $telefono ) {
			$datos['telephone'] = $telefono;
		}

		if ( $email ) {
			$datos['email'] = $email;
		}

		if ( $web ) {
			$datos['sameAs']   = $datos['sameAs'] ?? array();
			$datos['sameAs'][] = $web;
		}

		$ciudad       = asdir_meta( $post_id, '_asdir_ciudad' );
		$departamento = asdir_meta( $post_id, '_asdir_departamento' );
		$pais         = asdir_meta( $post_id, '_asdir_pais' ) ?: 'Colombia';

		if ( $ciudad || $departamento || $pais ) {
			$direccion = array( '@type' => 'PostalAddress' );

			if ( $ciudad ) {
				$direccion['addressLocality'] = $ciudad;
			}

			if ( $departamento ) {
				$direccion['addressRegion'] = $departamento;
			}

			$direccion['addressCountry'] = $pais;
			$datos['address']            = $direccion;
		}

		foreach ( asdir_redes_sociales( $post_id ) as $red ) {
			$datos['sameAs']   = $datos['sameAs'] ?? array();
			$datos['sameAs'][] = $red['url'];
		}

		echo '<script type="application/ld+json">' . wp_json_encode( $datos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
	}

	private function open_graph( int $post_id ): void {
		$titulo  = get_the_title( $post_id );
		$resumen = asdir_resumen( $post_id );
		$url     = get_permalink( $post_id );
		$imagen  = '';

		if ( has_post_thumbnail( $post_id ) ) {
			$imagen = wp_get_attachment_image_url( get_post_thumbnail_id( $post_id ), 'large' ) ?: '';
		}

		if ( ! $imagen ) {
			$portada = asdir_portada_id( $post_id );
			$imagen  = $portada ? ( wp_get_attachment_image_url( $portada, 'large' ) ?: '' ) : '';
		}

		$metas = array(
			'og:type'        => 'website',
			'og:title'       => $titulo,
			'og:url'         => $url,
			'og:site_name'   => get_bloginfo( 'name' ),
			'twitter:card'   => 'summary',
			'twitter:title'  => $titulo,
		);

		if ( $resumen ) {
			$metas['og:description']      = $resumen;
			$metas['twitter:description'] = $resumen;
		}

		if ( $imagen ) {
			$metas['og:image']      = $imagen;
			$metas['twitter:image'] = $imagen;
			$metas['twitter:card']  = 'summary_large_image';
		}

		foreach ( $metas as $propiedad => $contenido ) {
			$attr = str_starts_with( $propiedad, 'twitter:' ) ? 'name' : 'property';
			printf(
				'<meta %1$s="%2$s" content="%3$s" />' . "\n",
				esc_attr( $attr ),
				esc_attr( $propiedad ),
				esc_attr( $contenido )
			);
		}
	}

	public static function item_list( WP_Query $query ): void {
		if ( ! $query->have_posts() ) {
			return;
		}

		$elementos = array();
		$posicion  = 1;

		foreach ( $query->posts as $post ) {
			if ( ! $post instanceof WP_Post ) {
				$post = get_post( $post );
			}

			if ( ! $post instanceof WP_Post ) {
				continue;
			}

			$elementos[] = array(
				'@type'    => 'ListItem',
				'position' => $posicion,
				'url'      => get_permalink( $post ),
				'name'     => get_the_title( $post ),
			);
			++$posicion;
		}

		$datos = array(
			'@context'        => 'https://schema.org',
			'@type'           => 'ItemList',
			'itemListElement' => $elementos,
		);

		echo '<script type="application/ld+json">' . wp_json_encode( $datos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>';
	}
}
