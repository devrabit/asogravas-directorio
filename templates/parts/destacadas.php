<?php
/**
 * Franja de empresas destacadas.
 *
 * @package Asogravas_Directorio
 *
 * @var WP_Query $query
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="asdir-destacadas" aria-labelledby="asdir-destacadas-titulo">
	<h2 id="asdir-destacadas-titulo" class="asdir-destacadas__titulo">
		<span class="asdir-destacadas__icono"><?php echo asdir_icono( 'estrella' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		<?php esc_html_e( 'Empresas destacadas', 'asogravas-directorio' ); ?>
	</h2>
	<div class="asdir-destacadas__lista">
		<?php
		while ( $query->have_posts() ) :
			$query->the_post();
			asdir_plantilla( 'parts/card-destacada.php', array( 'post_id' => get_the_ID() ) );
		endwhile;
		?>
	</div>
</section>
