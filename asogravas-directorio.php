<?php
/**
 * Plugin Name:       Asogravas Directorio de Empresas
 * Description:       Registra empresas desde el administrador y publícalas en un directorio con ficha individual.
 * Version:           1.2.2
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Asogravas
 * Text Domain:       asogravas-directorio
 * Domain Path:       /languages
 * License:           GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

define( 'ASDIR_VERSION', '1.2.2' );

if ( ! function_exists( 'str_starts_with' ) ) {
	/**
	 * PHP 8. El resto del plugin puede llamarla también en 7.4.
	 */
	function str_starts_with( string $haystack, string $needle ): bool {
		if ( '' === $needle ) {
			return true;
		}

		return 0 === strpos( $haystack, $needle );
	}
}
define( 'ASDIR_FILE', __FILE__ );
define( 'ASDIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'ASDIR_URL', plugin_dir_url( __FILE__ ) );

/**
 * El plugin no se carga si el entorno no cumple los mínimos: en lugar de provocar un error
 * fatal, muestra un aviso y se queda inactivo.
 */
function asdir_requisitos_incumplidos(): string {
	if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
		return sprintf(
			/* translators: %s: versión de PHP instalada. */
			__( 'Asogravas Directorio requiere PHP 7.4 o superior. Este servidor usa PHP %s.', 'asogravas-directorio' ),
			PHP_VERSION
		);
	}

	if ( version_compare( get_bloginfo( 'version' ), '6.0', '<' ) ) {
		return sprintf(
			/* translators: %s: versión de WordPress instalada. */
			__( 'Asogravas Directorio requiere WordPress 6.0 o superior. Este sitio usa la versión %s.', 'asogravas-directorio' ),
			get_bloginfo( 'version' )
		);
	}

	return '';
}

$asdir_error = asdir_requisitos_incumplidos();

if ( '' !== $asdir_error ) {
	add_action(
		'admin_notices',
		static function () use ( $asdir_error ) {
			printf( '<div class="notice notice-error"><p>%s</p></div>', esc_html( $asdir_error ) );
		}
	);

	return;
}

require_once ASDIR_PATH . 'includes/class-asdir-plugin.php';

ASDIR_Plugin::instance();

register_activation_hook( __FILE__, array( 'ASDIR_Plugin', 'activar' ) );
register_deactivation_hook( __FILE__, array( 'ASDIR_Plugin', 'desactivar' ) );
