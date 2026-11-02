<?php
/**
 * Mini tarjeta horizontal para «Otras empresas».
 *
 * @package Asogravas_Directorio
 *
 * @var int $post_id
 */

defined( 'ABSPATH' ) || exit;

$post_id   = isset( $post_id ) ? (int) $post_id : get_the_ID();
$categoria = asdir_categoria( $post_id );
$permalink = get_permalink( $post_id );
?>
<article class="asdir-card-rel">
	<a class="asdir-card-rel__enlace" href="<?php echo esc_url( $permalink ); ?>">
		<span class="asdir-card-rel__logo">
			<?php echo asdir_logo_html( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</span>
		<span class="asdir-card-rel__textos">
			<?php if ( $categoria ) : ?>
				<span class="asdir-card__cat"><?php echo esc_html( $categoria->name ); ?></span>
			<?php endif; ?>
			<span class="asdir-card-rel__nombre"><?php echo esc_html( get_the_title( $post_id ) ); ?></span>
		</span>
	</a>
</article>
