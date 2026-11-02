<?php
/**
 * Datos extra al final de la ficha.
 *
 * @package Asogravas_Directorio
 *
 * @var int $post_id
 */

defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : get_the_ID();
$extra   = asdir_meta( $post_id, '_asdir_datos_extra' );

if ( '' === trim( wp_strip_all_tags( $extra ) ) ) {
	return;
}
?>
<section class="asdir-ficha-extra" aria-labelledby="asdir-extra-titulo">
	<h2 id="asdir-extra-titulo"><?php esc_html_e( 'Información adicional', 'asogravas-directorio' ); ?></h2>
	<div class="asdir-ficha-extra__texto">
		<?php echo wp_kses_post( $extra ); ?>
	</div>
</section>
