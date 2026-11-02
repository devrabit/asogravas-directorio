<?php
/**
 * Carrusel de fotografías.
 *
 * @package Asogravas_Directorio
 *
 * @var int $post_id
 */

defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : get_the_ID();
$ids     = asdir_galeria_ids( $post_id );

if ( ! $ids ) {
	return;
}

$total   = count( $ids );
$multi   = $total > 1;
$fuentes = array();

foreach ( $ids as $attachment_id ) {
	$grande = wp_get_attachment_image_url( $attachment_id, 'large' );
	$thumb  = wp_get_attachment_image_url( $attachment_id, 'medium' );

	if ( ! $grande ) {
		continue;
	}

	$fuentes[] = array(
		'id'     => $attachment_id,
		'grande' => $grande,
		'thumb'  => $thumb ?: $grande,
		'alt'    => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ?: get_the_title( $post_id ),
	);
}

if ( ! $fuentes ) {
	return;
}

$total = count( $fuentes );
$multi = $total > 1;
?>
<section class="asdir-ficha-galeria" aria-labelledby="asdir-fotos-titulo">
	<div class="asdir-ficha-seccion__cabecera">
		<h2 id="asdir-fotos-titulo"><?php esc_html_e( 'Fotografías', 'asogravas-directorio' ); ?></h2>
		<?php if ( $multi ) : ?>
			<p class="asdir-ficha-galeria__contador" data-asdir-galeria-contador aria-live="polite">
				<?php
				printf(
					/* translators: 1: índice actual, 2: total */
					esc_html__( '%1$d / %2$d', 'asogravas-directorio' ),
					1,
					(int) $total
				);
				?>
			</p>
		<?php endif; ?>
	</div>

	<div class="asdir-ficha-galeria__escena" data-asdir-galeria tabindex="0">
		<figure class="asdir-ficha-galeria__principal">
			<img
				src="<?php echo esc_url( $fuentes[0]['grande'] ); ?>"
				alt="<?php echo esc_attr( $fuentes[0]['alt'] ); ?>"
				data-asdir-galeria-img
				width="1200"
				height="675"
				decoding="async"
			>
			<?php if ( $multi ) : ?>
				<button type="button" class="asdir-ficha-galeria__flecha asdir-ficha-galeria__flecha--prev" data-asdir-galeria-prev aria-label="<?php esc_attr_e( 'Fotografía anterior', 'asogravas-directorio' ); ?>">
					<?php echo asdir_icono( 'chevron-izq' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</button>
				<button type="button" class="asdir-ficha-galeria__flecha asdir-ficha-galeria__flecha--next" data-asdir-galeria-next aria-label="<?php esc_attr_e( 'Fotografía siguiente', 'asogravas-directorio' ); ?>">
					<?php echo asdir_icono( 'chevron-der' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</button>
			<?php endif; ?>
		</figure>

		<?php if ( $multi ) : ?>
			<ul class="asdir-ficha-galeria__thumbs" role="list">
				<?php foreach ( $fuentes as $i => $foto ) : ?>
					<li>
						<button
							type="button"
							class="asdir-ficha-galeria__thumb<?php echo 0 === $i ? ' is-activa' : ''; ?>"
							data-asdir-galeria-thumb
							data-src="<?php echo esc_url( $foto['grande'] ); ?>"
							data-alt="<?php echo esc_attr( $foto['alt'] ); ?>"
							aria-label="<?php echo esc_attr( sprintf( /* translators: 1: índice, 2: total */ __( 'Fotografía %1$d de %2$d', 'asogravas-directorio' ), $i + 1, $total ) ); ?>"
							aria-current="<?php echo 0 === $i ? 'true' : 'false'; ?>"
						>
							<img src="<?php echo esc_url( $foto['thumb'] ); ?>" alt="" loading="lazy" decoding="async" width="160" height="100">
						</button>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</section>
