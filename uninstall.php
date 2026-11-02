<?php
/**
 * Desinstalación del plugin.
 *
 * Por defecto no se borra nada: desinstalar por error no debe costar el directorio completo.
 * El borrado solo ocurre si la opción `asdir_borrar_datos_al_desinstalar` está activa.
 *
 * @package Asogravas_Directorio
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$asdir_opciones = array(
	'asdir_caps_version',
	'asdir_borrar_datos_al_desinstalar',
	'asdir_ajustes',
	'asdir_rewrite',
);

if ( ! get_option( 'asdir_borrar_datos_al_desinstalar' ) ) {
	return;
}

$asdir_empresas = get_posts(
	array(
		'post_type'      => 'asdir_empresa',
		'post_status'    => 'any',
		'numberposts'    => -1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
	)
);

foreach ( $asdir_empresas as $asdir_post_id ) {
	wp_delete_post( (int) $asdir_post_id, true );
}

// Las categorías son las nativas de WordPress y las comparten las entradas del sitio:
// el plugin nunca las borra.

foreach ( $asdir_opciones as $asdir_opcion ) {
	delete_option( $asdir_opcion );
}
