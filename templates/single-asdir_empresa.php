<?php
/**
 * Ficha pública de una empresa.
 *
 * @package Asogravas_Directorio
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$post_id = get_the_ID();
	?>
	<main class="asdir-directorio asdir-ficha" id="asdir-ficha">
		<?php asdir_plantilla( 'parts/ficha-banda.php', array( 'post_id' => $post_id ) ); ?>
		<div class="asdir-ficha-contenedor">
			<?php asdir_plantilla( 'parts/ficha-descripcion.php', array( 'post_id' => $post_id ) ); ?>
			<div class="asdir-ficha-cuerpo">
				<div class="asdir-ficha-main">
					<?php
					asdir_plantilla( 'parts/ficha-columnas.php', array( 'post_id' => $post_id ) );
					asdir_plantilla( 'parts/ficha-galeria.php', array( 'post_id' => $post_id ) );
					asdir_plantilla( 'parts/ficha-media.php', array( 'post_id' => $post_id ) );
					asdir_plantilla( 'parts/ficha-casos.php', array( 'post_id' => $post_id ) );
					asdir_plantilla( 'parts/ficha-datos-extra.php', array( 'post_id' => $post_id ) );
					asdir_plantilla( 'parts/ficha-cotizacion.php', array( 'post_id' => $post_id ) );
					?>
				</div>
				<?php asdir_plantilla( 'parts/ficha-sidebar.php', array( 'post_id' => $post_id ) ); ?>
			</div>
			<?php asdir_plantilla( 'parts/ficha-relacionadas.php', array( 'post_id' => $post_id ) ); ?>
		</div>
	</main>
	<?php
endwhile;

get_footer();
