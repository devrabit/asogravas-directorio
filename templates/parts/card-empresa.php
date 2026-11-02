<?php
/**
 * Tarjeta compacta de la grilla.
 *
 * @package Asogravas_Directorio
 *
 * @var int $post_id
 */

defined( 'ABSPATH' ) || exit;

$post_id   = isset( $post_id ) ? (int) $post_id : get_the_ID();
$categoria = asdir_categoria( $post_id );
$ubicacion = asdir_ubicacion( $post_id );
$permalink = get_permalink( $post_id );
?>
<article class="asdir-card">
	<a class="asdir-card__enlace" href="<?php echo esc_url( $permalink ); ?>">
		<div class="asdir-card__media">
			<?php if ( asdir_es_destacada( $post_id ) ) : ?>
				<span class="asdir-pill asdir-pill--destacada asdir-pill--esquina">
					<?php echo asdir_icono( 'estrella' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php esc_html_e( 'Destacada', 'asogravas-directorio' ); ?>
				</span>
			<?php endif; ?>
			<span class="asdir-card__marca">
				<?php echo asdir_logo_html( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</span>
		</div>
		<div class="asdir-card__cuerpo">
			<?php if ( $categoria ) : ?>
				<p class="asdir-card__cat"><?php echo esc_html( $categoria->name ); ?></p>
			<?php endif; ?>
			<h3 class="asdir-card__nombre"><?php echo esc_html( get_the_title( $post_id ) ); ?></h3>
			<?php if ( $ubicacion ) : ?>
				<p class="asdir-card__ubi"><?php echo asdir_icono( 'pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php echo esc_html( $ubicacion ); ?></p>
			<?php endif; ?>
			<span class="asdir-card__ver">
				<?php esc_html_e( 'Ver perfil', 'asogravas-directorio' ); ?>
				<?php echo asdir_icono( 'flecha' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</span>
		</div>
	</a>
</article>
