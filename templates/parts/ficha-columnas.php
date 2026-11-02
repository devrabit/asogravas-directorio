<?php
/**
 * Productos, servicios y contacto.
 *
 * @package Asogravas_Directorio
 *
 * @var int $post_id
 */

defined( 'ABSPATH' ) || exit;

$post_id   = isset( $post_id ) ? (int) $post_id : get_the_ID();
$productos = asdir_items_lista( asdir_meta( $post_id, '_asdir_productos' ) );
$servicios = asdir_items_lista( asdir_meta( $post_id, '_asdir_servicios' ) );
if ( 'vacio' === $productos['modo'] && 'vacio' === $servicios['modo'] ) {
	return;
}

$columnas = (int) ( 'vacio' !== $productos['modo'] ) + (int) ( 'vacio' !== $servicios['modo'] );
?>
<section class="asdir-ficha-cols asdir-ficha-cols--<?php echo esc_attr( (string) $columnas ); ?>">
	<?php if ( 'vacio' !== $productos['modo'] ) : ?>
		<article class="asdir-ficha-card">
			<h2 class="asdir-ficha-card__titulo">
				<span class="asdir-ficha-card__icono asdir-ficha-card__icono--azul"><?php echo asdir_icono( 'caja' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<?php esc_html_e( 'Productos', 'asogravas-directorio' ); ?>
			</h2>
			<?php if ( 'lista' === $productos['modo'] ) : ?>
				<ul class="asdir-ficha-lista asdir-ficha-lista--gris">
					<?php foreach ( $productos['items'] as $item ) : ?>
						<li>
							<span class="asdir-ficha-lista__check"><?php echo asdir_icono( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<span><?php echo wp_kses_post( $item ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<div class="asdir-ficha-card__html"><?php echo $productos['html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			<?php endif; ?>
		</article>
	<?php endif; ?>

	<?php if ( 'vacio' !== $servicios['modo'] ) : ?>
		<article class="asdir-ficha-card">
			<h2 class="asdir-ficha-card__titulo">
				<span class="asdir-ficha-card__icono asdir-ficha-card__icono--rosa"><?php echo asdir_icono( 'portapapeles' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<?php esc_html_e( 'Servicios', 'asogravas-directorio' ); ?>
			</h2>
			<?php if ( 'lista' === $servicios['modo'] ) : ?>
				<ul class="asdir-ficha-lista asdir-ficha-lista--rosa">
					<?php foreach ( $servicios['items'] as $item ) : ?>
						<li>
							<span class="asdir-ficha-lista__check"><?php echo asdir_icono( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<span><?php echo wp_kses_post( $item ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<div class="asdir-ficha-card__html"><?php echo $servicios['html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			<?php endif; ?>
		</article>
	<?php endif; ?>
</section>
