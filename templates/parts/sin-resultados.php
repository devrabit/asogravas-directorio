<?php
/**
 * Estado vacío de la grilla.
 *
 * @package Asogravas_Directorio
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="asdir-vacio">
	<p><?php esc_html_e( 'No encontramos empresas con esos criterios.', 'asogravas-directorio' ); ?></p>
	<a class="asdir-btn asdir-btn--linea" href="<?php echo esc_url( ASDIR_Settings::url_directorio() ); ?>">
		<?php esc_html_e( 'Limpiar filtros', 'asogravas-directorio' ); ?>
	</a>
</div>
