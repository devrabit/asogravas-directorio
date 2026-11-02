<?php
/**
 * Tarjeta de descripción con foto.
 *
 * @package Asogravas_Directorio
 *
 * @var int $post_id
 */

defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : get_the_ID();
$foto_id = asdir_foto_descripcion_id( $post_id );
$contenido = get_post_field( 'post_content', $post_id );
$tiene_texto = '' !== trim( wp_strip_all_tags( (string) $contenido ) );

if ( ! $tiene_texto && ! $foto_id ) {
	return;
}
?>
<?php
$modificadores = array();
if ( ! $foto_id ) {
	$modificadores[] = 'asdir-ficha-desc__tarjeta--sin-foto';
}
if ( ! $tiene_texto ) {
	$modificadores[] = 'asdir-ficha-desc__tarjeta--sin-texto';
}
?>
<section class="asdir-ficha-desc">
	<div class="asdir-ficha-desc__tarjeta<?php echo $modificadores ? ' ' . esc_attr( implode( ' ', $modificadores ) ) : ''; ?>">
		<?php if ( $foto_id ) : ?>
			<figure class="asdir-ficha-desc__foto">
				<?php
				echo wp_get_attachment_image(
					$foto_id,
					'asdir-portada',
					false,
					array(
						'alt'      => '',
						'loading'  => 'eager',
						'decoding' => 'async',
					)
				);
				?>
			</figure>
		<?php endif; ?>
		<?php if ( $tiene_texto ) : ?>
			<div class="asdir-ficha-desc__cuerpo">
				<div class="asdir-ficha-desc__titulo">
					<h2><?php esc_html_e( 'Descripción', 'asogravas-directorio' ); ?></h2>
					<span class="asdir-ficha-desc__comillas"><?php echo asdir_icono( 'comillas' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				</div>
				<div class="asdir-ficha-desc__texto">
					<?php the_content(); ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</section>
