<?php
/**
 * Textos configurables del directorio público. Empresas › Ajustes.
 *
 * @package Asogravas_Directorio
 */

defined( 'ABSPATH' ) || exit;

class ASDIR_Settings {

	const OPTION = 'asdir_ajustes';
	const PAGE   = 'asdir-ajustes';

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_init', array( $this, 'registrar' ) );
	}

	/**
	 * @return array<string, string>
	 */
	public static function defaults(): array {
		return array(
			'hero_antetitulo'     => __( 'Cadena de valor de los agregados', 'asogravas-directorio' ),
			'hero_titulo'         => __( 'Directorio de empresas', 'asogravas-directorio' ),
			'hero_parrafo'        => __( 'Encuentra productores, proveedores de maquinaria, laboratorios, consultores y demás empresas aliadas de la industria de agregados pétreos en Colombia y la región.', 'asogravas-directorio' ),
			'hero_placeholder'    => __( 'Buscar por nombre, producto, servicio o ciudad…', 'asogravas-directorio' ),
			'hero_cta_titulo'     => __( '¿Tu empresa hace parte de la industria?', 'asogravas-directorio' ),
			'hero_cta_parrafo'    => __( 'Publica tu perfil con productos, servicios, catálogo y contacto directo.', 'asogravas-directorio' ),
			'hero_cta_boton'      => __( 'Quiero aparecer en el directorio', 'asogravas-directorio' ),
			'hero_cta_url'        => '',
			'banda_titulo'        => __( 'Haz visible tu empresa ante toda la cadena de agregados', 'asogravas-directorio' ),
			'banda_parrafo'       => __( 'Perfil con logo, galería, productos, servicios, catálogo, video y solicitud de cotización. Escríbenos para conocer los planes de afiliación.', 'asogravas-directorio' ),
			'banda_boton'         => __( 'Registrar mi empresa', 'asogravas-directorio' ),
			'banda_url'           => '',
			'pagina_directorio'   => '',
		);
	}

	public static function get( string $clave ): string {
		$guardado = get_option( self::OPTION, array() );
		$defaults = self::defaults();

		if ( ! is_array( $guardado ) ) {
			$guardado = array();
		}

		$valor = isset( $guardado[ $clave ] ) && is_scalar( $guardado[ $clave ] )
			? (string) $guardado[ $clave ]
			: '';

		$crudos = array( 'hero_cta_url', 'banda_url', 'pagina_directorio' );

		if ( in_array( $clave, $crudos, true ) ) {
			return $valor;
		}

		if ( '' === trim( $valor ) ) {
			return $defaults[ $clave ] ?? '';
		}

		return $valor;
	}

	/**
	 * @return array<string, string>
	 */
	public static function all(): array {
		$salida = self::defaults();
		$guardado = get_option( self::OPTION, array() );

		if ( ! is_array( $guardado ) ) {
			return $salida;
		}

		foreach ( $salida as $clave => $default ) {
			if ( isset( $guardado[ $clave ] ) && is_scalar( $guardado[ $clave ] ) ) {
				$salida[ $clave ] = (string) $guardado[ $clave ];
			}
		}

		return $salida;
	}

	public static function url_directorio(): string {
		$pagina = absint( self::get( 'pagina_directorio' ) );

		if ( $pagina > 0 ) {
			$url = get_permalink( $pagina );

			if ( $url ) {
				return $url;
			}
		}

		return get_post_type_archive_link( ASDIR_Post_Type::SLUG ) ?: home_url( '/' );
	}

	public function menu(): void {
		add_submenu_page(
			'edit.php?post_type=' . ASDIR_Post_Type::SLUG,
			__( 'Ajustes del directorio', 'asogravas-directorio' ),
			__( 'Ajustes', 'asogravas-directorio' ),
			'manage_options',
			self::PAGE,
			array( $this, 'render' )
		);
	}

	public function registrar(): void {
		register_setting(
			'asdir_ajustes_grupo',
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitizar' ),
				'default'           => self::defaults(),
			)
		);
	}

	/**
	 * @param mixed $entrada
	 * @return array<string, string>
	 */
	public function sanitizar( $entrada ): array {
		$salida   = self::defaults();
		$entrada  = is_array( $entrada ) ? $entrada : array();
		$textos   = array( 'hero_antetitulo', 'hero_titulo', 'hero_parrafo', 'hero_placeholder', 'hero_cta_titulo', 'hero_cta_parrafo', 'hero_cta_boton', 'banda_titulo', 'banda_parrafo', 'banda_boton' );
		$urls     = array( 'hero_cta_url', 'banda_url' );

		foreach ( $textos as $clave ) {
			$salida[ $clave ] = isset( $entrada[ $clave ] ) ? sanitize_text_field( (string) $entrada[ $clave ] ) : $salida[ $clave ];
		}

		$salida['hero_parrafo']  = isset( $entrada['hero_parrafo'] ) ? sanitize_textarea_field( (string) $entrada['hero_parrafo'] ) : $salida['hero_parrafo'];
		$salida['banda_parrafo'] = isset( $entrada['banda_parrafo'] ) ? sanitize_textarea_field( (string) $entrada['banda_parrafo'] ) : $salida['banda_parrafo'];
		$salida['hero_cta_parrafo'] = isset( $entrada['hero_cta_parrafo'] ) ? sanitize_textarea_field( (string) $entrada['hero_cta_parrafo'] ) : $salida['hero_cta_parrafo'];

		foreach ( $urls as $clave ) {
			$url = isset( $entrada[ $clave ] ) ? trim( (string) $entrada[ $clave ] ) : '';
			$salida[ $clave ] = '' === $url ? '' : esc_url_raw( $url );
		}

		$salida['pagina_directorio'] = isset( $entrada['pagina_directorio'] ) ? (string) absint( $entrada['pagina_directorio'] ) : '';

		return $salida;
	}

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$valores = self::all();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Ajustes del directorio', 'asogravas-directorio' ); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'asdir_ajustes_grupo' ); ?>

				<h2><?php esc_html_e( 'Encabezado', 'asogravas-directorio' ); ?></h2>
				<table class="form-table" role="presentation">
					<?php
					$this->fila_texto( 'hero_antetitulo', __( 'Antetítulo', 'asogravas-directorio' ), $valores );
					$this->fila_texto( 'hero_titulo', __( 'Título', 'asogravas-directorio' ), $valores );
					$this->fila_area( 'hero_parrafo', __( 'Párrafo', 'asogravas-directorio' ), $valores );
					$this->fila_texto( 'hero_placeholder', __( 'Placeholder del buscador', 'asogravas-directorio' ), $valores );
					?>
				</table>

				<h2><?php esc_html_e( 'Tarjeta lateral del encabezado', 'asogravas-directorio' ); ?></h2>
				<table class="form-table" role="presentation">
					<?php
					$this->fila_texto( 'hero_cta_titulo', __( 'Título', 'asogravas-directorio' ), $valores );
					$this->fila_area( 'hero_cta_parrafo', __( 'Párrafo', 'asogravas-directorio' ), $valores );
					$this->fila_texto( 'hero_cta_boton', __( 'Texto del botón', 'asogravas-directorio' ), $valores );
					$this->fila_texto( 'hero_cta_url', __( 'Enlace del botón', 'asogravas-directorio' ), $valores, 'url' );
					?>
				</table>

				<h2><?php esc_html_e( 'Banda final', 'asogravas-directorio' ); ?></h2>
				<table class="form-table" role="presentation">
					<?php
					$this->fila_texto( 'banda_titulo', __( 'Título', 'asogravas-directorio' ), $valores );
					$this->fila_area( 'banda_parrafo', __( 'Párrafo', 'asogravas-directorio' ), $valores );
					$this->fila_texto( 'banda_boton', __( 'Texto del botón', 'asogravas-directorio' ), $valores );
					$this->fila_texto( 'banda_url', __( 'Enlace del botón', 'asogravas-directorio' ), $valores, 'url' );
					?>
				</table>

				<h2><?php esc_html_e( 'Página', 'asogravas-directorio' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="asdir_pagina_directorio"><?php esc_html_e( 'Página del directorio', 'asogravas-directorio' ); ?></label>
						</th>
						<td>
							<?php
							wp_dropdown_pages(
								array(
									'name'              => self::OPTION . '[pagina_directorio]',
									'id'                => 'asdir_pagina_directorio',
									'selected'          => absint( $valores['pagina_directorio'] ),
									'show_option_none'  => __( '— Archivo del tipo Empresas —', 'asogravas-directorio' ),
									'option_none_value' => '0',
								)
							);
							?>
							<p class="description"><?php esc_html_e( 'Se usa como destino de «Volver al directorio» y como URL base de los filtros.', 'asogravas-directorio' ); ?></p>
						</td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * @param array<string, string> $valores
	 */
	private function fila_texto( string $clave, string $label, array $valores, string $tipo = 'text' ): void {
		printf(
			'<tr><th scope="row"><label for="asdir_%1$s">%2$s</label></th><td><input type="%5$s" class="regular-text" id="asdir_%1$s" name="%3$s[%1$s]" value="%4$s"></td></tr>',
			esc_attr( $clave ),
			esc_html( $label ),
			esc_attr( self::OPTION ),
			esc_attr( $valores[ $clave ] ?? '' ),
			esc_attr( $tipo )
		);
	}

	/**
	 * @param array<string, string> $valores
	 */
	private function fila_area( string $clave, string $label, array $valores ): void {
		printf(
			'<tr><th scope="row"><label for="asdir_%1$s">%2$s</label></th><td><textarea class="large-text" rows="3" id="asdir_%1$s" name="%3$s[%1$s]">%4$s</textarea></td></tr>',
			esc_attr( $clave ),
			esc_html( $label ),
			esc_attr( self::OPTION ),
			esc_textarea( $valores[ $clave ] ?? '' )
		);
	}
}
