<?php
/**
 * Consultas públicas del directorio.
 *
 * @package Asogravas_Directorio
 */

defined( 'ABSPATH' ) || exit;

class ASDIR_Query {

	public static function boot(): void {
		add_filter( 'posts_clauses', array( self::class, 'clausulas' ), 10, 2 );
	}

	/**
	 * Lee filtros de la petición HTTP y los combina con los atributos del shortcode.
	 *
	 * @param array<string, mixed> $attrs
	 * @param array<string, mixed> $fuente  GET o POST ya sin slashes.
	 * @return array<string, mixed>
	 */
	public static function request( array $attrs = array(), array $fuente = array() ): array {
		if ( ! $fuente ) {
			$fuente = wp_unslash( $_GET ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		$categorias_base = self::slugs( $attrs['categoria'] ?? '' );
		$categorias_user = self::slugs( $fuente['asdir_cat'] ?? array() );

		if ( $categorias_base ) {
			$categorias = $categorias_user
				? array_values( array_intersect( $categorias_user, $categorias_base ) )
				: $categorias_base;
		} else {
			$categorias = $categorias_user;
		}

		$ubicacion = isset( $fuente['asdir_ubicacion'] ) ? sanitize_key( (string) $fuente['asdir_ubicacion'] ) : '';
		if ( ! in_array( $ubicacion, array( 'colombia', 'internacional' ), true ) ) {
			$ubicacion = '';
		}

		$orden_permitido = array( 'destacadas', 'alfabetico', 'reciente' );
		$orden_attr      = isset( $attrs['orden'] ) ? sanitize_key( (string) $attrs['orden'] ) : 'destacadas';
		$orden_req       = isset( $fuente['asdir_orden'] ) ? sanitize_key( (string) $fuente['asdir_orden'] ) : '';
		$orden           = in_array( $orden_req, $orden_permitido, true ) ? $orden_req : $orden_attr;
		$orden           = in_array( $orden, $orden_permitido, true ) ? $orden : 'destacadas';

		$por_pagina = isset( $attrs['por_pagina'] ) ? absint( $attrs['por_pagina'] ) : 9;
		$por_pagina = max( 1, min( 48, $por_pagina ) );

		$pagina = isset( $fuente['asdir_pag'] ) ? absint( $fuente['asdir_pag'] ) : 1;
		$pagina = max( 1, $pagina );

		return array(
			'q'              => isset( $fuente['asdir_q'] ) ? sanitize_text_field( (string) $fuente['asdir_q'] ) : '',
			'ubicacion'      => $ubicacion,
			'categorias'     => $categorias,
			'categorias_base'=> $categorias_base,
			'orden'          => $orden,
			'por_pagina'     => $por_pagina,
			'pagina'         => $pagina,
			'solo_destacadas'=> ! empty( $attrs['solo_destacadas'] ) && 'si' === $attrs['solo_destacadas'],
			'columnas'       => self::columnas( $attrs['columnas'] ?? 3 ),
		);
	}

	/**
	 * @param array<string, mixed> $filtros
	 */
	public static function empresas( array $filtros ): WP_Query {
		$args = array(
			'post_type'           => ASDIR_Post_Type::SLUG,
			'post_status'         => 'publish',
			'posts_per_page'      => (int) ( $filtros['por_pagina'] ?? 9 ),
			'paged'               => (int) ( $filtros['pagina'] ?? 1 ),
			'ignore_sticky_posts' => true,
			'asdir_directorio'    => true,
			'asdir_orden'         => $filtros['orden'] ?? 'destacadas',
			'asdir_q'             => $filtros['q'] ?? '',
			'asdir_ubicacion'     => $filtros['ubicacion'] ?? '',
		);

		if ( ! empty( $filtros['solo_ids'] ) ) {
			$args['fields'] = 'ids';
			$args['nopaging'] = true;
			$args['posts_per_page'] = -1;
			$args['paged'] = 1;
		}

		$tax = array();

		if ( ! empty( $filtros['categorias'] ) ) {
			$tax[] = array(
				'taxonomy' => ASDIR_Taxonomy::SLUG,
				'field'    => 'slug',
				'terms'    => $filtros['categorias'],
				'operator' => 'IN',
			);
		}

		if ( $tax ) {
			$args['tax_query'] = $tax;
		}

		$meta = array();

		if ( ! empty( $filtros['solo_destacadas'] ) ) {
			$meta[] = array(
				'key'   => '_asdir_destacada',
				'value' => '1',
			);
		}

		if ( $meta ) {
			$args['meta_query'] = $meta;
		}

		return new WP_Query( $args );
	}

	public static function destacadas( int $total ): WP_Query {
		return new WP_Query(
			array(
				'post_type'           => ASDIR_Post_Type::SLUG,
				'post_status'         => 'publish',
				'posts_per_page'      => max( 1, $total ),
				'ignore_sticky_posts' => true,
				'orderby'             => 'title',
				'order'               => 'ASC',
				'asdir_directorio'    => true,
				'meta_query'          => array(
					array(
						'key'   => '_asdir_destacada',
						'value' => '1',
					),
				),
			)
		);
	}

	/**
	 * Contadores por slug de categoría, aplicando búsqueda y ubicación pero no las categorías marcadas.
	 *
	 * @param array<string, mixed> $filtros
	 * @return array<string, int>
	 */
	public static function contadores( array $filtros ): array {
		$para_conteo = $filtros;
		$para_conteo['categorias'] = $filtros['categorias_base'] ?? array();
		$para_conteo['solo_ids']   = true;
		$para_conteo['pagina']     = 1;

		$query = self::empresas( $para_conteo );
		$ids   = array_map( 'intval', $query->posts );

		if ( ! $ids ) {
			return array();
		}

		$terminos = wp_get_object_terms(
			$ids,
			ASDIR_Taxonomy::SLUG,
			array( 'fields' => 'all_with_object_id' )
		);

		$conteos = array();

		if ( is_wp_error( $terminos ) ) {
			return $conteos;
		}

		foreach ( $terminos as $termino ) {
			$slug = $termino->slug;
			$conteos[ $slug ] = ( $conteos[ $slug ] ?? 0 ) + 1;
		}

		return $conteos;
	}

	/**
	 * Categorías que tienen al menos una empresa publicada.
	 *
	 * @param string[] $restringir
	 * @return WP_Term[]
	 */
	public static function categorias_visibles( array $restringir = array() ): array {
		$ids = get_posts(
			array(
				'post_type'      => ASDIR_Post_Type::SLUG,
				'post_status'    => 'publish',
				'numberposts'    => -1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);

		if ( ! $ids ) {
			return array();
		}

		$args = array(
			'taxonomy'   => ASDIR_Taxonomy::SLUG,
			'object_ids' => $ids,
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
		);

		if ( $restringir ) {
			$args['slug'] = $restringir;
		}

		$terminos = get_terms( $args );

		return is_wp_error( $terminos ) ? array() : array_values( $terminos );
	}

	/**
	 * @param array<string, string> $clauses
	 * @return array<string, string>
	 */
	public static function clausulas( array $clauses, WP_Query $query ): array {
		global $wpdb;

		if ( ! $query->get( 'asdir_directorio' ) ) {
			return $clauses;
		}

		$q = trim( (string) $query->get( 'asdir_q' ) );

		if ( '' !== $q ) {
			$like = '%' . $wpdb->esc_like( $q ) . '%';

			$clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS asdir_busca
				ON ( asdir_busca.post_id = {$wpdb->posts}.ID
				AND asdir_busca.meta_key IN ('_asdir_productos','_asdir_servicios','_asdir_ciudad') )";

			$clauses['where'] .= $wpdb->prepare(
				" AND (
					{$wpdb->posts}.post_title LIKE %s
					OR {$wpdb->posts}.post_content LIKE %s
					OR {$wpdb->posts}.post_excerpt LIKE %s
					OR asdir_busca.meta_value LIKE %s
				)",
				$like,
				$like,
				$like,
				$like
			);

		}

		$ubicacion = (string) $query->get( 'asdir_ubicacion' );

		if ( 'colombia' === $ubicacion ) {
			$clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS asdir_pais
				ON ( asdir_pais.post_id = {$wpdb->posts}.ID AND asdir_pais.meta_key = '_asdir_pais' )";
			$clauses['where'] .= " AND ( asdir_pais.meta_value IS NULL OR asdir_pais.meta_value = '' OR LOWER(asdir_pais.meta_value) = 'colombia' )";
		} elseif ( 'internacional' === $ubicacion ) {
			$clauses['join'] .= " INNER JOIN {$wpdb->postmeta} AS asdir_pais
				ON ( asdir_pais.post_id = {$wpdb->posts}.ID AND asdir_pais.meta_key = '_asdir_pais' )";
			$clauses['where'] .= " AND asdir_pais.meta_value <> '' AND LOWER(asdir_pais.meta_value) <> 'colombia'";
		}

		$clauses['groupby'] = "{$wpdb->posts}.ID";

		$orden = (string) $query->get( 'asdir_orden' );

		if ( 'destacadas' === $orden ) {
			$clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS asdir_ord
				ON ( asdir_ord.post_id = {$wpdb->posts}.ID AND asdir_ord.meta_key = '_asdir_destacada' )";
			$clauses['orderby'] = "MAX(asdir_ord.meta_value) DESC, {$wpdb->posts}.post_title ASC";
		} elseif ( 'alfabetico' === $orden ) {
			$clauses['orderby'] = "{$wpdb->posts}.post_title ASC";
		} elseif ( 'reciente' === $orden ) {
			$clauses['orderby'] = "{$wpdb->posts}.post_date DESC";
		}

		return $clauses;
	}

	/**
	 * @param mixed $valor
	 * @return string[]
	 */
	public static function slugs( $valor ): array {
		if ( is_array( $valor ) ) {
			$partes = $valor;
		} else {
			$partes = explode( ',', (string) $valor );
		}

		$slugs = array();

		foreach ( $partes as $parte ) {
			$slug = sanitize_title( (string) $parte );

			if ( '' !== $slug ) {
				$slugs[] = $slug;
			}
		}

		return array_values( array_unique( $slugs ) );
	}

	public static function columnas( $valor ): int {
		$n = absint( $valor );

		return in_array( $n, array( 2, 3, 4 ), true ) ? $n : 3;
	}

	/**
	 * @param array<string, mixed> $filtros
	 */
	public static function url( array $filtros, int $pagina = 0 ): string {
		$base = ASDIR_Settings::url_directorio();
		$args = array();

		if ( ! empty( $filtros['q'] ) ) {
			$args['asdir_q'] = $filtros['q'];
		}

		if ( ! empty( $filtros['ubicacion'] ) ) {
			$args['asdir_ubicacion'] = $filtros['ubicacion'];
		}

		if ( ! empty( $filtros['categorias'] ) && empty( $filtros['categorias_base'] ) ) {
			$args['asdir_cat'] = implode( ',', $filtros['categorias'] );
		} elseif ( ! empty( $filtros['categorias'] ) && ! empty( $filtros['categorias_base'] )
			&& $filtros['categorias'] !== $filtros['categorias_base'] ) {
			$args['asdir_cat'] = implode( ',', $filtros['categorias'] );
		}

		if ( ! empty( $filtros['orden'] ) && 'destacadas' !== $filtros['orden'] ) {
			$args['asdir_orden'] = $filtros['orden'];
		}

		$pagina = $pagina > 0 ? $pagina : (int) ( $filtros['pagina'] ?? 1 );

		if ( $pagina > 1 ) {
			$args['asdir_pag'] = $pagina;
		}

		return add_query_arg( $args, $base );
	}

	public static function hay_filtros( array $filtros ): bool {
		if ( ! empty( $filtros['q'] ) || ! empty( $filtros['ubicacion'] ) ) {
			return true;
		}

		$base = $filtros['categorias_base'] ?? array();
		$cats = $filtros['categorias'] ?? array();

		if ( $base ) {
			return $cats !== $base && ! empty( $cats );
		}

		return ! empty( $cats );
	}

	/**
	 * Hasta 3 empresas relacionadas: misma categoría (destacadas primero), luego otras destacadas, luego A–Z.
	 *
	 * @return int[]
	 */
	public static function relacionadas( int $post_id, int $total = 3 ): array {
		$total   = max( 1, min( 6, $total ) );
		$excluir = array( $post_id );
		$ids     = array();
		$cat     = asdir_categoria( $post_id );

		if ( $cat instanceof WP_Term ) {
			$candidatas = self::ids_publicados(
				array(
					'posts_per_page' => 40,
					'post__not_in'   => $excluir,
					'tax_query'      => array(
						array(
							'taxonomy' => ASDIR_Taxonomy::SLUG,
							'field'    => 'term_id',
							'terms'    => array( $cat->term_id ),
						),
					),
					'orderby'        => 'title',
					'order'          => 'ASC',
				)
			);
			$ids = self::ordenar_destacadas_primero( $candidatas );
			$ids = array_slice( $ids, 0, $total );
		}

		if ( count( $ids ) < $total ) {
			$faltan = $total - count( $ids );
			$mas    = self::ids_publicados(
				array(
					'posts_per_page' => $faltan,
					'post__not_in'   => array_merge( $excluir, $ids ),
					'meta_query'     => array(
						array(
							'key'   => '_asdir_destacada',
							'value' => '1',
						),
					),
					'orderby'        => 'title',
					'order'          => 'ASC',
				)
			);
			$ids = array_merge( $ids, $mas );
		}

		if ( count( $ids ) < $total ) {
			$faltan = $total - count( $ids );
			$mas    = self::ids_publicados(
				array(
					'posts_per_page' => $faltan,
					'post__not_in'   => array_merge( $excluir, $ids ),
					'orderby'        => 'title',
					'order'          => 'ASC',
				)
			);
			$ids = array_merge( $ids, $mas );
		}

		return array_values( array_unique( array_map( 'intval', $ids ) ) );
	}

	/**
	 * @param int[] $ids
	 * @return int[]
	 */
	private static function ordenar_destacadas_primero( array $ids ): array {
		$destacadas = array();
		$resto      = array();

		foreach ( $ids as $id ) {
			if ( asdir_es_destacada( (int) $id ) ) {
				$destacadas[] = (int) $id;
			} else {
				$resto[] = (int) $id;
			}
		}

		return array_merge( $destacadas, $resto );
	}

	/**
	 * @param array<string, mixed> $args
	 * @return int[]
	 */
	private static function ids_publicados( array $args ): array {
		$defaults = array(
			'post_type'              => ASDIR_Post_Type::SLUG,
			'post_status'            => 'publish',
			'fields'                 => 'ids',
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => true,
		);

		$query = new WP_Query( array_merge( $defaults, $args ) );

		return array_map( 'intval', $query->posts );
	}
}
