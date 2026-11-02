<?php
/**
 * Encabezado del directorio.
 *
 * @package Asogravas_Directorio
 *
 * @var array $filtros
 */

defined( 'ABSPATH' ) || exit;

$cta_url = asdir_ajuste( 'hero_cta_url' );
$action  = ASDIR_Settings::url_directorio();
?>
<section class="asdir-hero">
	<div class="asdir-hero__inner">
		<div class="asdir-hero__principal">
			<?php if ( asdir_ajuste( 'hero_antetitulo' ) ) : ?>
				<p class="asdir-hero__kicker"><?php echo esc_html( asdir_ajuste( 'hero_antetitulo' ) ); ?></p>
			<?php endif; ?>
			<h1 class="asdir-hero__titulo"><?php echo esc_html( asdir_ajuste( 'hero_titulo' ) ); ?></h1>
			<?php if ( asdir_ajuste( 'hero_parrafo' ) ) : ?>
				<p class="asdir-hero__intro"><?php echo esc_html( asdir_ajuste( 'hero_parrafo' ) ); ?></p>
			<?php endif; ?>

			<form class="asdir-buscador" method="get" action="<?php echo esc_url( $action ); ?>" role="search">
				<label class="screen-reader-text" for="asdir_q"><?php esc_html_e( 'Buscar empresas', 'asogravas-directorio' ); ?></label>
				<span class="asdir-buscador__icono"><?php echo asdir_icono( 'lupa' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<input type="search" id="asdir_q" name="asdir_q" value="<?php echo esc_attr( $filtros['q'] ?? '' ); ?>" placeholder="<?php echo esc_attr( asdir_ajuste( 'hero_placeholder' ) ); ?>">
				<button type="submit" class="asdir-btn asdir-btn--verde"><?php esc_html_e( 'Buscar', 'asogravas-directorio' ); ?></button>
			</form>
		</div>

		<aside class="asdir-hero__cta">
			<h2><?php echo esc_html( asdir_ajuste( 'hero_cta_titulo' ) ); ?></h2>
			<p><?php echo esc_html( asdir_ajuste( 'hero_cta_parrafo' ) ); ?></p>
			<?php if ( $cta_url ) : ?>
				<a class="asdir-btn asdir-btn--azul" href="<?php echo esc_url( $cta_url ); ?>">
					<?php echo esc_html( asdir_ajuste( 'hero_cta_boton' ) ); ?>
				</a>
			<?php endif; ?>
		</aside>
	</div>
</section>
