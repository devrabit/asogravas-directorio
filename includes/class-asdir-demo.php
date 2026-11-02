<?php
/**
 * Importa las 4 empresas de prueba desde data/empresas-demo.json.
 *
 * @package Asogravas_Directorio
 */

defined( 'ABSPATH' ) || exit;

class ASDIR_Demo {

	const ARCHIVO = 'data/empresas-demo.json';

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_post_asdir_importar_demo', array( $this, 'procesar' ) );
		add_action( 'admin_notices', array( $this, 'aviso' ) );
	}

	public function menu(): void {
		add_submenu_page(
			'edit.php?post_type=' . ASDIR_Post_Type::SLUG,
			__( 'Datos de prueba', 'asogravas-directorio' ),
			__( 'Datos de prueba', 'asogravas-directorio' ),
			'edit_asdir_empresas',
			'asdir-demo',
			array( $this, 'pantalla' )
		);
	}

	public function pantalla(): void {
		if ( ! current_user_can( 'edit_asdir_empresas' ) ) {
			return;
		}

		$datos = self::leer();
		$n     = isset( $datos['empresas'] ) ? count( $datos['empresas'] ) : 0;
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Datos de prueba', 'asogravas-directorio' ); ?></h1>
			<p><?php echo esc_html( sprintf( /* translators: %d: número de empresas */ __( 'Hay %d empresas de demostración listas para importar, cada una en una categoría distinta. Si ya existen (mismo slug), se omiten.', 'asogravas-directorio' ), $n ) ); ?></p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="asdir_importar_demo">
				<?php wp_nonce_field( 'asdir_importar_demo' ); ?>
				<?php submit_button( __( 'Importar 4 empresas de prueba', 'asogravas-directorio' ) ); ?>
			</form>
		</div>
		<?php
	}

	public function procesar(): void {
		if ( ! current_user_can( 'edit_asdir_empresas' ) ) {
			wp_die( esc_html__( 'Sin permisos.', 'asogravas-directorio' ) );
		}

		check_admin_referer( 'asdir_importar_demo' );

		$resultado = self::importar();

		set_transient(
			'asdir_demo_aviso_' . get_current_user_id(),
			$resultado,
			MINUTE_IN_SECONDS
		);

		wp_safe_redirect( admin_url( 'edit.php?post_type=' . ASDIR_Post_Type::SLUG ) );
		exit;
	}

	public function aviso(): void {
		$aviso = get_transient( 'asdir_demo_aviso_' . get_current_user_id() );

		if ( ! is_array( $aviso ) ) {
			return;
		}

		delete_transient( 'asdir_demo_aviso_' . get_current_user_id() );

		printf(
			'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
			esc_html(
				sprintf(
					/* translators: 1: creadas, 2: omitidas */
					__( 'Importación de prueba: %1$d empresas creadas, %2$d omitidas porque ya existían.', 'asogravas-directorio' ),
					(int) ( $aviso['creadas'] ?? 0 ),
					(int) ( $aviso['omitidas'] ?? 0 )
				)
			)
		);
	}

	/**
	 * @return array{creadas: int, omitidas: int}
	 */
	public static function importar(): array {
		$datos = self::leer();
		$creadas  = 0;
		$omitidas = 0;

		foreach ( $datos['categorias'] ?? array() as $nombre ) {
			self::asegurar_categoria( (string) $nombre );
		}

		foreach ( $datos['empresas'] ?? array() as $empresa ) {
			if ( ! is_array( $empresa ) || empty( $empresa['nombre'] ) ) {
				continue;
			}

			$slug = sanitize_title( (string) ( $empresa['slug'] ?? $empresa['nombre'] ) );
			$ya   = get_page_by_path( $slug, OBJECT, ASDIR_Post_Type::SLUG );

			if ( $ya instanceof WP_Post ) {
				++$omitidas;
				continue;
			}

			if ( self::crear( $empresa, $slug ) ) {
				++$creadas;
			}
		}

		return compact( 'creadas', 'omitidas' );
	}

	/**
	 * @return array<string, mixed>
	 */
	public static function leer(): array {
		$ruta = ASDIR_PATH . self::ARCHIVO;

		if ( ! is_readable( $ruta ) ) {
			return array();
		}

		$json = json_decode( (string) file_get_contents( $ruta ), true );

		return is_array( $json ) ? $json : array();
	}

	private static function asegurar_categoria( string $nombre ): int {
		$existe = term_exists( $nombre, ASDIR_Taxonomy::SLUG );

		if ( is_array( $existe ) ) {
			return (int) $existe['term_id'];
		}

		$creado = wp_insert_term( $nombre, ASDIR_Taxonomy::SLUG );

		return is_wp_error( $creado ) ? 0 : (int) $creado['term_id'];
	}

	/**
	 * @param array<string, mixed> $empresa
	 */
	private static function crear( array $empresa, string $slug ): bool {
		$id = wp_insert_post(
			array(
				'post_type'    => ASDIR_Post_Type::SLUG,
				'post_status'  => 'publish' === ( $empresa['estado'] ?? '' ) ? 'publish' : 'draft',
				'post_title'   => sanitize_text_field( (string) $empresa['nombre'] ),
				'post_name'    => $slug,
				'post_content' => wp_kses_post( (string) ( $empresa['descripcion'] ?? '' ) ),
				'post_excerpt' => mb_substr( sanitize_textarea_field( (string) ( $empresa['resumen'] ?? '' ) ), 0, 160 ),
			),
			true
		);

		if ( is_wp_error( $id ) || ! $id ) {
			return false;
		}

		$termino = self::asegurar_categoria( (string) ( $empresa['categoria'] ?? '' ) );

		if ( $termino ) {
			wp_set_object_terms( (int) $id, array( $termino ), ASDIR_Taxonomy::SLUG );
		}

		$mapa = array(
			'ciudad'        => '_asdir_ciudad',
			'departamento'  => '_asdir_departamento',
			'pais'          => '_asdir_pais',
			'telefono'      => '_asdir_telefono',
			'email'         => '_asdir_email',
			'whatsapp'      => '_asdir_whatsapp',
			'sitio_web'     => '_asdir_sitio_web',
			'fecha_inicio'  => '_asdir_fecha_inicio',
			'productos'     => '_asdir_productos',
			'servicios'     => '_asdir_servicios',
			'casos_exito'   => '_asdir_casos_exito',
			'datos_extra'   => '_asdir_datos_extra',
			'video'         => '_asdir_video',
			'facebook'      => '_asdir_facebook',
			'x'             => '_asdir_x',
			'linkedin'      => '_asdir_linkedin',
			'instagram'     => '_asdir_instagram',
			'youtube'       => '_asdir_youtube',
			'tiktok'        => '_asdir_tiktok',
		);

		foreach ( $mapa as $json_key => $meta_key ) {
			$campo = ASDIR_Fields::get( $meta_key );
			$crudo = $empresa[ $json_key ] ?? '';

			if ( ! $campo ) {
				continue;
			}

			$resultado = ASDIR_Sanitize::field( $crudo, $campo );

			if ( '' !== $resultado['error'] || '' === $resultado['value'] ) {
				continue;
			}

			update_post_meta( (int) $id, $meta_key, $resultado['value'] );

			if ( 'youtube' === $campo['type'] ) {
				$video_id = asdir_youtube_id( (string) $resultado['value'] );
				if ( '' !== $video_id ) {
					update_post_meta( (int) $id, '_asdir_video_id', $video_id );
				}
			}
		}

		update_post_meta( (int) $id, '_asdir_destacada', ! empty( $empresa['destacada'] ) ? '1' : '0' );

		return true;
	}
}
