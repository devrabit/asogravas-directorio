<?php
/**
 * Sidebar: contacto, redes, catálogo y volver.
 *
 * @package Asogravas_Directorio
 *
 * @var int $post_id
 */

defined( 'ABSPATH' ) || exit;

$post_id      = isset( $post_id ) ? (int) $post_id : get_the_ID();
$nombre       = get_the_title( $post_id );
$telefono     = asdir_meta( $post_id, '_asdir_telefono' );
$email        = asdir_meta( $post_id, '_asdir_email' );
$whatsapp     = asdir_meta( $post_id, '_asdir_whatsapp' );
$redes        = asdir_redes_sociales( $post_id );
$catalogo     = absint( asdir_meta( $post_id, '_asdir_catalogo_pdf' ) );
$catalogo_url = $catalogo ? wp_get_attachment_url( $catalogo ) : '';
$volver       = asdir_url_volver_directorio();
?>
<aside class="asdir-ficha-sidebar">
	<?php if ( asdir_tiene_contacto( $post_id ) ) : ?>
		<div class="asdir-ficha-contacto">
			<h2 class="asdir-ficha-card__titulo"><?php esc_html_e( 'Contacto', 'asogravas-directorio' ); ?></h2>
			<ul class="asdir-ficha-contacto__lista">
				<?php if ( $telefono ) : ?>
					<li>
						<a href="<?php echo esc_url( asdir_tel_link( $telefono ) ); ?>">
							<?php echo asdir_icono( 'telefono' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<span><?php echo esc_html( $telefono ); ?></span>
						</a>
					</li>
				<?php endif; ?>
				<?php if ( $email ) : ?>
					<li>
						<a href="<?php echo esc_url( 'mailto:' . $email ); ?>">
							<?php echo asdir_icono( 'correo' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<span><?php echo esc_html( $email ); ?></span>
						</a>
					</li>
				<?php endif; ?>
				<?php if ( $whatsapp ) : ?>
					<li>
						<a href="<?php echo esc_url( $whatsapp ); ?>" target="_blank" rel="noopener">
							<?php echo asdir_icono( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<span><?php esc_html_e( 'WhatsApp', 'asogravas-directorio' ); ?></span>
						</a>
					</li>
				<?php endif; ?>
			</ul>
			<a class="asdir-btn asdir-btn--azul asdir-ficha-contacto__cta" href="#asdir-cotizacion">
				<?php esc_html_e( 'Contactar empresa', 'asogravas-directorio' ); ?>
			</a>
			<?php if ( $whatsapp ) : ?>
				<a class="asdir-btn asdir-btn--verde asdir-ficha-contacto__cta" href="<?php echo esc_url( $whatsapp ); ?>" target="_blank" rel="noopener">
					<?php echo asdir_icono( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php esc_html_e( 'Escribir por WhatsApp', 'asogravas-directorio' ); ?>
				</a>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( $redes ) : ?>
		<div class="asdir-ficha-redes">
			<h2><?php esc_html_e( 'Redes sociales', 'asogravas-directorio' ); ?></h2>
			<ul class="asdir-ficha-redes__lista">
				<?php foreach ( $redes as $slug => $red ) : ?>
					<li>
						<a href="<?php echo esc_url( $red['url'] ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( $red['label'] . ' — ' . $nombre ); ?>">
							<?php echo asdir_icono( $slug ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

	<?php if ( $catalogo_url ) : ?>
		<a class="asdir-ficha-catalogo" href="<?php echo esc_url( $catalogo_url ); ?>" download>
			<span class="asdir-ficha-catalogo__icono"><?php echo asdir_icono( 'pdf' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<span class="asdir-ficha-catalogo__textos">
				<strong><?php esc_html_e( 'Catálogo de productos y servicios', 'asogravas-directorio' ); ?></strong>
				<span><?php esc_html_e( 'Descargar catálogo →', 'asogravas-directorio' ); ?></span>
			</span>
		</a>
	<?php endif; ?>

	<a class="asdir-ficha-volver" href="<?php echo esc_url( $volver ); ?>">
		<?php esc_html_e( 'Volver al directorio →', 'asogravas-directorio' ); ?>
	</a>
</aside>
