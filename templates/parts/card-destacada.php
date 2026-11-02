<?php
/**
 * Tarjeta grande de la franja de destacadas.
 *
 * @package Asogravas_Directorio
 *
 * @var int $post_id
 */

defined( 'ABSPATH' ) || exit;

$post_id   = isset( $post_id ) ? (int) $post_id : get_the_ID();
$categoria = asdir_categoria( $post_id );
$ubicacion = asdir_ubicacion( $post_id );
$resumen   = asdir_resumen( $post_id );
$portada   = asdir_portada_id( $post_id );
$permalink = get_permalink( $post_id );
?>
<article class="asdir-card-destacada">
	<a class="asdir-card-destacada__enlace" href="<?php echo esc_url( $permalink ); ?>">
		<div class="asdir-card-destacada__media">
			<span class="asdir-pill asdir-pill--destacada"><?php esc_html_e( 'Destacada', 'asogravas-directorio' ); ?></span>
			<?php
			if ( $portada ) {
				echo '<span class="asdir-card-destacada__foto-wrap">';
				echo wp_get_attachment_image(
					$portada,
					'asdir-portada',
					false,
					array(
						'class'    => 'asdir-card-destacada__foto',
						'alt'      => '',
						'loading'  => 'lazy',
						'decoding' => 'async',
					)
				);
				echo '</span>';
			}
			?>
		</div>
		<span class="asdir-card-destacada__logo">
			<?php echo asdir_logo_html( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</span>
		<div class="asdir-card-destacada__cuerpo">
			<?php if ( $categoria ) : ?>
				<p class="asdir-card__cat"><?php echo esc_html( $categoria->name ); ?></p>
			<?php endif; ?>
			<h3 class="asdir-card__nombre"><?php echo esc_html( get_the_title( $post_id ) ); ?></h3>
			<?php if ( $resumen ) : ?>
				<p class="asdir-card-destacada__resumen"><?php echo esc_html( $resumen ); ?></p>
			<?php endif; ?>
		</div>
		<div class="asdir-card-destacada__pie">
			<?php if ( $ubicacion ) : ?>
				<span class="asdir-card__ubi"><?php echo asdir_icono( 'pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php echo esc_html( $ubicacion ); ?></span>
			<?php else : ?>
				<span></span>
			<?php endif; ?>
			<span class="asdir-card__ver">
				<?php esc_html_e( 'Ver perfil', 'asogravas-directorio' ); ?>
				<?php echo asdir_icono( 'flecha' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</span>
		</div>
	</a>
</article>
