<?php
/**
 * Listado de empresas en el administrador: columnas, orden, filtros y búsqueda extendida.
 *
 * @package Asogravas_Directorio
 */

defined( 'ABSPATH' ) || exit;

class ASDIR_Admin_List {

	public function __construct() {
		add_filter( 'manage_' . ASDIR_Post_Type::SLUG . '_posts_columns', array( $this, 'columnas' ) );
		add_action( 'manage_' . ASDIR_Post_Type::SLUG . '_posts_custom_column', array( $this, 'contenido_columna' ), 10, 2 );
		add_filter( 'manage_edit-' . ASDIR_Post_Type::SLUG . '_sortable_columns', array( $this, 'columnas_ordenables' ) );

		add_action( 'restrict_manage_posts', array( $this, 'filtros' ) );
		add_action( 'pre_get_posts', array( $this, 'aplicar_filtros' ) );
		add_filter( 'posts_clauses', array( $this, 'ordenar_por_meta' ), 10, 2 );
		add_filter( 'posts_search', array( $this, 'ampliar_busqueda' ), 10, 2 );

		add_action( 'wp_ajax_asdir_toggle_destacada', array( $this, 'ajax_toggle_destacada' ) );
	}

	public function columnas( array $columnas ): array {
		// La columna de categorías la aporta la taxonomía nativa; aquí solo se reubica y se
		// renombra en singular, porque cada empresa tiene una sola.
		$hay_categorias = isset( $columnas['categories'] );
		unset( $columnas['categories'] );

		$nuevas = array();

		foreach ( $columnas as $clave => $etiqueta ) {
			if ( 'title' === $clave ) {
				$nuevas['asdir_logo'] = __( 'Logo', 'asogravas-directorio' );
				$nuevas['title']      = __( 'Empresa', 'asogravas-directorio' );
				continue;
			}

			if ( 'date' === $clave ) {
				if ( $hay_categorias ) {
					$nuevas['categories'] = __( 'Categoría', 'asogravas-directorio' );
				}

				$nuevas['asdir_ubicacion']    = __( 'Ubicación', 'asogravas-directorio' );
				$nuevas['asdir_contacto']     = __( 'Contacto', 'asogravas-directorio' );
				$nuevas['asdir_destacada']    = __( 'Destacada', 'asogravas-directorio' );
				$nuevas['asdir_fecha_inicio'] = __( 'Fecha de inicio', 'asogravas-directorio' );
			}

			$nuevas[ $clave ] = $etiqueta;
		}

		return $nuevas;
	}

	public function contenido_columna( string $columna, int $post_id ): void {
		switch ( $columna ) {
			case 'asdir_logo':
				$this->columna_logo( $post_id );
				break;

			case 'asdir_ubicacion':
				echo esc_html( asdir_ubicacion( $post_id ) ?: '—' );
				break;

			case 'asdir_contacto':
				$this->columna_contacto( $post_id );
				break;

			case 'asdir_destacada':
				$this->columna_destacada( $post_id );
				break;

			case 'asdir_fecha_inicio':
				$fecha = asdir_meta( $post_id, '_asdir_fecha_inicio' );

				echo '' === $fecha
					? '—'
					: esc_html( date_i18n( get_option( 'date_format' ), (int) strtotime( $fecha ) ) );
				break;
		}
	}

	private function columna_logo( int $post_id ): void {
		if ( has_post_thumbnail( $post_id ) ) {
			echo get_the_post_thumbnail( $post_id, array( 50, 50 ), array( 'class' => 'asdir-col-logo' ) );

			return;
		}

		$marcador = asdir_inicial_y_color( get_the_title( $post_id ) );

		printf(
			'<span class="asdir-col-logo asdir-col-logo--vacio" style="background:%s" aria-hidden="true">%s</span>',
			esc_attr( $marcador['color'] ),
			esc_html( $marcador['inicial'] )
		);
	}

	private function columna_contacto( int $post_id ): void {
		$telefono = asdir_meta( $post_id, '_asdir_telefono' );
		$email    = asdir_meta( $post_id, '_asdir_email' );

		if ( '' === $telefono && '' === $email ) {
			echo '—';

			return;
		}

		if ( '' !== $telefono ) {
			$enlace = asdir_tel_link( $telefono );

			printf(
				'<div><a href="%s">%s</a></div>',
				esc_url( $enlace ),
				esc_html( $telefono )
			);
		}

		if ( '' !== $email ) {
			printf(
				'<div><a href="%s">%s</a></div>',
				esc_url( 'mailto:' . $email ),
				esc_html( $email )
			);
		}
	}

	private function columna_destacada( int $post_id ): void {
		$activa = asdir_es_destacada( $post_id );

		printf(
			'<button type="button" class="asdir-estrella %s" data-post="%d" aria-pressed="%s" title="%s"><span class="dashicons %s"></span></button>',
			$activa ? 'is-activa' : '',
			$post_id,
			$activa ? 'true' : 'false',
			esc_attr__( 'Destacar en la página principal', 'asogravas-directorio' ),
			$activa ? 'dashicons-star-filled' : 'dashicons-star-empty'
		);
	}

	public function columnas_ordenables( array $columnas ): array {
		$columnas['asdir_ubicacion']    = 'asdir_ciudad';
		$columnas['asdir_destacada']    = 'asdir_destacada';
		$columnas['asdir_fecha_inicio'] = 'asdir_fecha_inicio';

		return $columnas;
	}

	public function filtros( string $post_type ): void {
		if ( ASDIR_Post_Type::SLUG !== $post_type ) {
			return;
		}

		$this->filtro_categoria();
		$this->filtro_departamento();
		$this->filtro_destacada();
	}

	private function filtro_categoria(): void {
		$actual = isset( $_GET['category_name'] ) ? sanitize_title( wp_unslash( $_GET['category_name'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		wp_dropdown_categories(
			array(
				'taxonomy'          => ASDIR_Taxonomy::SLUG,
				'name'              => 'category_name',
				'value_field'       => 'slug',
				'selected'          => $actual,
				'hierarchical'      => true,
				'hide_empty'        => false,
				'orderby'           => 'name',
				'show_option_none'  => __( 'Todas las categorías', 'asogravas-directorio' ),
				'option_none_value' => '',
			)
		);
	}

	private function filtro_departamento(): void {
		$departamentos = $this->valores_distintos( '_asdir_departamento' );

		if ( ! $departamentos ) {
			return;
		}

		$actual = isset( $_GET['asdir_departamento'] ) ? sanitize_text_field( wp_unslash( $_GET['asdir_departamento'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		echo '<select name="asdir_departamento">';
		printf( '<option value="">%s</option>', esc_html__( 'Todos los departamentos', 'asogravas-directorio' ) );

		foreach ( $departamentos as $departamento ) {
			printf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $departamento ),
				selected( $actual, $departamento, false ),
				esc_html( $departamento )
			);
		}

		echo '</select>';
	}

	private function filtro_destacada(): void {
		$actual = isset( $_GET['asdir_destacada'] ) ? sanitize_text_field( wp_unslash( $_GET['asdir_destacada'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		echo '<select name="asdir_destacada">';
		printf( '<option value="">%s</option>', esc_html__( 'Destacadas: todas', 'asogravas-directorio' ) );
		printf( '<option value="1" %s>%s</option>', selected( $actual, '1', false ), esc_html__( 'Solo destacadas', 'asogravas-directorio' ) );
		printf( '<option value="0" %s>%s</option>', selected( $actual, '0', false ), esc_html__( 'Sin destacar', 'asogravas-directorio' ) );
		echo '</select>';
	}

	/**
	 * Valores únicos de un meta, para poblar un filtro con lo que realmente existe.
	 *
	 * @return string[]
	 */
	private function valores_distintos( string $meta_key ): array {
		global $wpdb;

		$valores = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT pm.meta_value
				 FROM {$wpdb->postmeta} pm
				 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
				 WHERE pm.meta_key = %s
				   AND pm.meta_value <> ''
				   AND p.post_type = %s
				   AND p.post_status NOT IN ('trash', 'auto-draft')
				 ORDER BY pm.meta_value ASC",
				$meta_key,
				ASDIR_Post_Type::SLUG
			)
		);

		return array_map( 'strval', (array) $valores );
	}

	public function aplicar_filtros( WP_Query $query ): void {
		if ( ! $this->es_listado_de_empresas( $query ) ) {
			return;
		}

		$meta_query = array();

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- filtros de lectura en la tabla del admin.
		$departamento = isset( $_GET['asdir_departamento'] ) ? sanitize_text_field( wp_unslash( $_GET['asdir_departamento'] ) ) : '';

		if ( '' !== $departamento ) {
			$meta_query[] = array(
				'key'   => '_asdir_departamento',
				'value' => $departamento,
			);
		}

		$destacada = isset( $_GET['asdir_destacada'] ) ? sanitize_text_field( wp_unslash( $_GET['asdir_destacada'] ) ) : '';

		if ( '1' === $destacada ) {
			$meta_query[] = array(
				'key'   => '_asdir_destacada',
				'value' => '1',
			);
		} elseif ( '0' === $destacada ) {
			$meta_query[] = array(
				'relation' => 'OR',
				array(
					'key'     => '_asdir_destacada',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'   => '_asdir_destacada',
					'value' => '0',
				),
			);
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		if ( $meta_query ) {
			$existente = $query->get( 'meta_query' );
			$query->set( 'meta_query', array_merge( is_array( $existente ) ? $existente : array(), $meta_query ) );
		}
	}

	/**
	 * Ordena por un meta con un LEFT JOIN propio.
	 *
	 * Con `meta_key` + `orderby` nativos, WordPress genera un INNER JOIN y las empresas que no
	 * tienen ese dato desaparecen de la tabla; además, sobrescribir `meta_query` aquí anularía
	 * los filtros aplicados en `aplicar_filtros()`.
	 */
	public function ordenar_por_meta( array $clauses, WP_Query $query ): array {
		global $wpdb;

		if ( ! $this->es_listado_de_empresas( $query ) ) {
			return $clauses;
		}

		$mapa = array(
			'asdir_ciudad'       => '_asdir_ciudad',
			'asdir_destacada'    => '_asdir_destacada',
			'asdir_fecha_inicio' => '_asdir_fecha_inicio',
		);

		$orderby = $query->get( 'orderby' );

		if ( ! is_string( $orderby ) || ! isset( $mapa[ $orderby ] ) ) {
			return $clauses;
		}

		$orden = 'DESC' === strtoupper( (string) $query->get( 'order' ) ) ? 'DESC' : 'ASC';

		$clauses['join'] .= $wpdb->prepare(
			" LEFT JOIN {$wpdb->postmeta} AS asdir_orden
			  ON ( asdir_orden.post_id = {$wpdb->posts}.ID AND asdir_orden.meta_key = %s )",
			$mapa[ $orderby ]
		);

		$clauses['orderby'] = "asdir_orden.meta_value {$orden}, {$wpdb->posts}.post_title ASC";

		return $clauses;
	}

	private function es_listado_de_empresas( WP_Query $query ): bool {
		return is_admin()
			&& $query->is_main_query()
			&& ASDIR_Post_Type::SLUG === $query->get( 'post_type' );
	}

	/**
	 * Hace que la búsqueda del admin encuentre también por ciudad, departamento y correo.
	 * El resultado se añade con OR dentro del bloque de búsqueda, sin tocar las condiciones
	 * de tipo de contenido ni de estado.
	 */
	public function ampliar_busqueda( string $search, WP_Query $query ): string {
		global $wpdb;

		if ( '' === $search || ! $this->es_listado_de_empresas( $query ) ) {
			return $search;
		}

		$termino = (string) $query->get( 's' );

		if ( '' === trim( $termino ) ) {
			return $search;
		}

		$like = '%' . $wpdb->esc_like( $termino ) . '%';

		$subconsulta = $wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta}
			 WHERE meta_key IN ('_asdir_ciudad', '_asdir_departamento', '_asdir_email')
			   AND meta_value LIKE %s",
			$like
		);

		$condiciones = preg_replace( '/^\s*AND\s*/i', '', $search );

		return " AND ( {$condiciones} OR {$wpdb->posts}.ID IN ({$subconsulta}) )";
	}

	public function ajax_toggle_destacada(): void {
		check_ajax_referer( 'asdir_admin', 'nonce' );

		$post_id = isset( $_POST['post'] ) ? absint( wp_unslash( $_POST['post'] ) ) : 0;

		if ( 0 === $post_id || ASDIR_Post_Type::SLUG !== get_post_type( $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Empresa no encontrada.', 'asogravas-directorio' ) ), 404 );
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Sin permisos para editar esta empresa.', 'asogravas-directorio' ) ), 403 );
		}

		$nueva = asdir_es_destacada( $post_id ) ? '0' : '1';

		update_post_meta( $post_id, '_asdir_destacada', $nueva );

		wp_send_json_success( array( 'destacada' => '1' === $nueva ) );
	}
}
