<?php
/**
 * Otras empresas que te pueden interesar.
 *
 * @package Asogravas_Directorio
 *
 * @var int $post_id
 */

defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : get_the_ID();
$ids     = ASDIR_Query::relacionadas( $post_id, 3 );

if ( ! $ids ) {
	return;
}

$categoria = asdir_categoria( $post_id );
$ver_todas = ASDIR_Settings::url_directorio();

if ( $categoria instanceof WP_Term ) {
	$ver_todas = add_query_arg( 'asdir_cat', $categoria->slug, $ver_todas );
}
?>
<section class="asdir-ficha-rel" aria-labelledby="asdir-rel-titulo">
	<div class="asdir-ficha-seccion__cabecera">
		<h2 id="asdir-rel-titulo"><?php esc_html_e( 'Otras empresas que te pueden interesar', 'asogravas-directorio' ); ?></h2>
		<a class="asdir-ficha-rel__todas" href="<?php echo esc_url( $ver_todas ); ?>">
			<?php esc_html_e( 'Ver todas en el directorio →', 'asogravas-directorio' ); ?>
		</a>
	</div>
	<div class="asdir-ficha-rel__lista">
		<?php
		foreach ( $ids as $id ) {
			asdir_plantilla( 'parts/card-relacionada.php', array( 'post_id' => $id ) );
		}
		?>
	</div>
</section>
