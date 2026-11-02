<?php
/**
 * Barra lateral de filtros.
 *
 * @package Asogravas_Directorio
 *
 * @var array     $filtros
 * @var array     $atts
 * @var WP_Term[] $categorias
 * @var array     $contadores
 */

defined( 'ABSPATH' ) || exit;

$ubicacion   = $filtros['ubicacion'] ?? '';
$marcadas    = $filtros['categorias'] ?? array();
$hay_filtros = ASDIR_Query::hay_filtros( $filtros );
$action      = ASDIR_Settings::url_directorio();
$limpieza    = $action;
?>
<aside class="asdir-filtros-wrap">
	<details class="asdir-filtros-panel" open>
		<summary class="asdir-filtros-panel__resumen"><?php esc_html_e( 'Filtrar', 'asogravas-directorio' ); ?></summary>
		<form class="asdir-filtros" method="get" action="<?php echo esc_url( $action ); ?>" data-asdir-filtros>
			<div class="asdir-filtros__cabecera">
				<h2><?php esc_html_e( 'Filtrar', 'asogravas-directorio' ); ?></h2>
				<?php if ( $hay_filtros ) : ?>
					<a class="asdir-filtros__limpiar" href="<?php echo esc_url( $limpieza ); ?>"><?php esc_html_e( 'Limpiar filtros', 'asogravas-directorio' ); ?></a>
				<?php else : ?>
					<a class="asdir-filtros__limpiar" href="<?php echo esc_url( $limpieza ); ?>" hidden><?php esc_html_e( 'Limpiar filtros', 'asogravas-directorio' ); ?></a>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $filtros['q'] ) ) : ?>
				<input type="hidden" name="asdir_q" value="<?php echo esc_attr( $filtros['q'] ); ?>">
			<?php endif; ?>

			<fieldset class="asdir-filtros__grupo">
				<legend><?php esc_html_e( 'Ubicación', 'asogravas-directorio' ); ?></legend>
				<div class="asdir-pildoras" role="radiogroup" aria-label="<?php esc_attr_e( 'Ubicación', 'asogravas-directorio' ); ?>">
					<label class="asdir-pildora">
						<input type="radio" name="asdir_ubicacion" value="" <?php checked( $ubicacion, '' ); ?>>
						<span><?php esc_html_e( 'Todas', 'asogravas-directorio' ); ?></span>
					</label>
					<label class="asdir-pildora">
						<input type="radio" name="asdir_ubicacion" value="colombia" <?php checked( $ubicacion, 'colombia' ); ?>>
						<span><?php esc_html_e( 'Colombia', 'asogravas-directorio' ); ?></span>
					</label>
					<label class="asdir-pildora">
						<input type="radio" name="asdir_ubicacion" value="internacional" <?php checked( $ubicacion, 'internacional' ); ?>>
						<span><?php esc_html_e( 'Internacional', 'asogravas-directorio' ); ?></span>
					</label>
				</div>
			</fieldset>

			<fieldset class="asdir-filtros__grupo">
				<legend><?php esc_html_e( 'Categoría', 'asogravas-directorio' ); ?></legend>
				<ul class="asdir-cats">
					<?php foreach ( $categorias as $termino ) : ?>
						<?php
						$n        = (int) ( $contadores[ $termino->slug ] ?? 0 );
						$marcada  = in_array( $termino->slug, $marcadas, true );
						$disabled = 0 === $n && ! $marcada;
						?>
						<li class="<?php echo $disabled ? 'is-vacia' : ''; ?>">
							<label>
								<input class="asdir-check" type="checkbox" name="asdir_cat[]" value="<?php echo esc_attr( $termino->slug ); ?>" <?php checked( $marcada ); ?> <?php disabled( $disabled ); ?>>
								<span class="asdir-cats__nombre"><?php echo esc_html( $termino->name ); ?></span>
								<span class="asdir-cats__n" data-slug="<?php echo esc_attr( $termino->slug ); ?>"><?php echo esc_html( (string) $n ); ?></span>
							</label>
						</li>
					<?php endforeach; ?>
				</ul>
			</fieldset>

			<noscript>
				<button type="submit" class="asdir-btn asdir-btn--azul"><?php esc_html_e( 'Aplicar filtros', 'asogravas-directorio' ); ?></button>
			</noscript>
		</form>
	</details>
</aside>
