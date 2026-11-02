<?php
/**
 * Banda azul de la ficha.
 *
 * @package Asogravas_Directorio
 *
 * @var int $post_id
 */

defined( 'ABSPATH' ) || exit;

$post_id   = isset( $post_id ) ? (int) $post_id : get_the_ID();
$ubicacion = asdir_ubicacion( $post_id );
$whatsapp  = asdir_meta( $post_id, '_asdir_whatsapp' );
$web       = asdir_meta( $post_id, '_asdir_sitio_web' );
$nombre    = get_the_title( $post_id );
?>
<section class="asdir-ficha-banda">
	<div class="asdir-ficha-banda__inner">
		<div class="asdir-ficha-banda__identidad">
			<span class="asdir-ficha-banda__logo">
				<?php echo asdir_logo_html( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</span>
			<div class="asdir-ficha-banda__textos">
				<h1 class="asdir-ficha-banda__nombre"><?php echo esc_html( $nombre ); ?></h1>
				<div class="asdir-ficha-banda__meta">
					<?php if ( $ubicacion ) : ?>
						<span class="asdir-ficha-banda__ubi">
							<?php echo asdir_icono( 'pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php echo esc_html( $ubicacion ); ?>
						</span>
					<?php endif; ?>
					<?php if ( asdir_es_destacada( $post_id ) ) : ?>
						<span class="asdir-pill asdir-pill--destacada">
							<?php echo asdir_icono( 'estrella' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php esc_html_e( 'Empresa destacada', 'asogravas-directorio' ); ?>
						</span>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="asdir-ficha-banda__acciones">
			<a class="asdir-btn asdir-btn--verde" href="#asdir-cotizacion">
				<?php esc_html_e( 'Solicitar cotización', 'asogravas-directorio' ); ?>
			</a>
			<?php if ( $whatsapp ) : ?>
					<a
						class="asdir-btn asdir-btn--linea-clara"
						href="<?php echo esc_url( $whatsapp ); ?>"
						target="_blank"
						rel="noopener"
						aria-label="<?php echo esc_attr( sprintf( /* translators: %s: empresa */ __( 'WhatsApp de %s', 'asogravas-directorio' ), $nombre ) ); ?>"
					>
						<?php echo asdir_icono( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php esc_html_e( 'WhatsApp', 'asogravas-directorio' ); ?>
					</a>
				<?php endif; ?>
				<?php if ( $web ) : ?>
					<a
						class="asdir-btn asdir-btn--linea-clara"
						href="<?php echo esc_url( $web ); ?>"
						target="_blank"
						rel="noopener nofollow"
						aria-label="<?php echo esc_attr( sprintf( /* translators: %s: empresa */ __( 'Sitio web de %s', 'asogravas-directorio' ), $nombre ) ); ?>"
					>
						<?php echo asdir_icono( 'web' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php esc_html_e( 'Visitar sitio web', 'asogravas-directorio' ); ?>
					</a>
				<?php endif; ?>
		</div>
	</div>
</section>
