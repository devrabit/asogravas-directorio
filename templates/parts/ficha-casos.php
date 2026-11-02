<?php
/**
 * Casos de éxito: texto y/o PDF.
 *
 * @package Asogravas_Directorio
 *
 * @var int $post_id
 */

defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : get_the_ID();
$texto   = asdir_meta( $post_id, '_asdir_casos_exito' );
$pdf_id  = absint( asdir_meta( $post_id, '_asdir_casos_exito_pdf' ) );
$pdf_url = $pdf_id ? wp_get_attachment_url( $pdf_id ) : '';
$pdf_nom = $pdf_id ? basename( get_attached_file( $pdf_id ) ?: '' ) : '';

if ( '' === trim( wp_strip_all_tags( $texto ) ) && ! $pdf_url ) {
	return;
}
?>
<section class="asdir-ficha-casos" aria-labelledby="asdir-casos-titulo">
	<h2 id="asdir-casos-titulo"><?php esc_html_e( 'Casos de éxito', 'asogravas-directorio' ); ?></h2>
	<?php if ( '' !== trim( wp_strip_all_tags( $texto ) ) ) : ?>
		<div class="asdir-ficha-casos__texto">
			<?php echo wp_kses_post( $texto ); ?>
		</div>
	<?php endif; ?>
	<?php if ( $pdf_url ) : ?>
		<a class="asdir-ficha-pdf" href="<?php echo esc_url( $pdf_url ); ?>" download>
			<span class="asdir-ficha-pdf__icono"><?php echo asdir_icono( 'pdf' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<span class="asdir-ficha-pdf__nombre"><?php echo esc_html( $pdf_nom ?: __( 'Casos de éxito.pdf', 'asogravas-directorio' ) ); ?></span>
			<span class="asdir-ficha-pdf__accion"><?php esc_html_e( 'Descargar', 'asogravas-directorio' ); ?></span>
		</a>
	<?php endif; ?>
</section>
