<?php
/**
 * Banda final de llamado a la acción.
 *
 * @package Asogravas_Directorio
 */

defined( 'ABSPATH' ) || exit;

$url = asdir_ajuste( 'banda_url' );

if ( '' === $url ) {
	return;
}
?>
<section class="asdir-banda">
	<div class="asdir-banda__inner">
		<div>
			<h2><?php echo esc_html( asdir_ajuste( 'banda_titulo' ) ); ?></h2>
			<p><?php echo esc_html( asdir_ajuste( 'banda_parrafo' ) ); ?></p>
		</div>
		<a class="asdir-btn asdir-btn--verde" href="<?php echo esc_url( $url ); ?>">
			<?php echo esc_html( asdir_ajuste( 'banda_boton' ) ); ?>
		</a>
	</div>
</section>
