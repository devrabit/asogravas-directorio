<?php
/**
 * Bootstrap del plugin: autoload, instanciación de componentes, activación y desactivación.
 *
 * @package Asogravas_Directorio
 */

defined( 'ABSPATH' ) || exit;

final class ASDIR_Plugin {

	/**
	 * Se incrementa cuando cambian las capabilities, para volver a asignarlas en sitios
	 * donde el plugin ya estaba activo.
	 */
	const CAPS_VERSION = 2;

	private static ?ASDIR_Plugin $instance = null;

	public static function instance(): ASDIR_Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		spl_autoload_register( array( $this, 'autoload' ) );

		require_once ASDIR_PATH . 'includes/helpers.php';

		add_action( 'init', array( $this, 'cargar_traducciones' ) );
		add_action( 'init', array( $this, 'flush_si_hace_falta' ), 99 );
		add_action( 'plugins_loaded', array( $this, 'arrancar' ) );
		add_action( 'admin_init', array( $this, 'revisar_capabilities' ) );
	}

	/**
	 * ASDIR_Admin_List => includes/class-asdir-admin-list.php
	 */
	public function autoload( string $clase ): void {
		if ( ! str_starts_with( $clase, 'ASDIR_' ) ) {
			return;
		}

		$archivo = ASDIR_PATH . 'includes/class-' . strtolower( str_replace( '_', '-', $clase ) ) . '.php';

		if ( is_readable( $archivo ) ) {
			require_once $archivo;
		}
	}

	public function cargar_traducciones(): void {
		load_plugin_textdomain( 'asogravas-directorio', false, dirname( plugin_basename( ASDIR_FILE ) ) . '/languages' );
	}

	/**
	 * Un ZIP subido encima no dispara de nuevo el hook de activación; sin este flush,
	 * /empresas/ puede seguir en 404 hasta que el usuario vaya a Enlaces permanentes.
	 */
	public function flush_si_hace_falta(): void {
		if ( get_option( 'asdir_rewrite' ) === ASDIR_VERSION ) {
			return;
		}

		flush_rewrite_rules( false );
		update_option( 'asdir_rewrite', ASDIR_VERSION );
	}

	public function arrancar(): void {
		new ASDIR_Post_Type();
		new ASDIR_Taxonomy();
		new ASDIR_Shortcode();
		new ASDIR_Ajax();
		new ASDIR_Block();
		new ASDIR_Templates();
		new ASDIR_Schema();

		ASDIR_Query::boot();

		if ( is_admin() ) {
			new ASDIR_Metabox();
			new ASDIR_Media();
			new ASDIR_Admin_List();
			new ASDIR_Settings();
		}
	}

	public static function activar(): void {
		// El CPT y la taxonomía deben existir antes de generar las reglas de reescritura.
		( new ASDIR_Post_Type() )->registrar();
		( new ASDIR_Taxonomy() )->registrar();

		self::asignar_capabilities();

		flush_rewrite_rules();
	}

	public static function desactivar(): void {
		flush_rewrite_rules();
	}

	/**
	 * Vuelve a asignar las capabilities si cambiaron desde la última activación. Sin esto, una
	 * actualización del plugin dejaría a los administradores sin acceso al menú de empresas.
	 */
	public function revisar_capabilities(): void {
		if ( (int) get_option( 'asdir_caps_version' ) === self::CAPS_VERSION ) {
			return;
		}

		self::asignar_capabilities();
	}

	private static function asignar_capabilities(): void {
		$roles = array( 'administrator', 'editor' );

		foreach ( $roles as $nombre_rol ) {
			$rol = get_role( $nombre_rol );

			if ( ! $rol ) {
				continue;
			}

			foreach ( ASDIR_Post_Type::capabilities() as $cap ) {
				$rol->add_cap( $cap );
			}

			// Caps de la taxonomía propia que el plugin usaba antes de pasar a las categorías
			// nativas de WordPress. Se retiran para no dejar permisos huérfanos en los roles.
			foreach ( array( 'manage_asdir_categorias', 'edit_asdir_categorias', 'delete_asdir_categorias', 'assign_asdir_categorias' ) as $cap ) {
				$rol->remove_cap( $cap );
			}
		}

		update_option( 'asdir_caps_version', self::CAPS_VERSION );
	}
}
