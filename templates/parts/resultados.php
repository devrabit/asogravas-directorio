<?php
/**
 * Encabezado de resultados, grilla y carga progresiva.
 *
 * @package Asogravas_Directorio
 *
 * @var array    $filtros
 * @var array    $atts
 * @var WP_Query $query
 * @var bool     $hay_mas
 */

defined( 'ABSPATH' ) || exit;

$columnas = (int) ( $filtros['columnas'] ?? 3 );
$orden    = $filtros['orden'] ?? 'destacadas';
$action   = ASDIR_Settings::url_directorio();
$conteo   = (int) $query->found_posts;
?>
<div class="asdir-resultados" data-asdir-resultados
	data-por-pagina="<?php echo esc_attr( (string) $filtros['por_pagina'] ); ?>"
	data-columnas="<?php echo esc_attr( (string) $columnas ); ?>"
	data-categoria="<?php echo esc_attr( implode( ',', $filtros['categorias_base'] ?? array() ) ); ?>"
	data-solo-destacadas="<?php echo esc_attr( ! empty( $filtros['solo_destacadas'] ) ? 'si' : 'no' ); ?>"
	data-pagina="<?php echo esc_attr( (string) $filtros['pagina'] ); ?>">

	<div class="asdir-resultados__cabecera">
		<p class="asdir-resultados__conteo" aria-live="polite"><?php echo esc_html( ASDIR_Ajax::texto_conteo( $conteo ) ); ?></p>
		<form class="asdir-orden" method="get" action="<?php echo esc_url( $action ); ?>">
			<?php if ( ! empty( $filtros['q'] ) ) : ?>
				<input type="hidden" name="asdir_q" value="<?php echo esc_attr( $filtros['q'] ); ?>">
			<?php endif; ?>
			<?php if ( ! empty( $filtros['ubicacion'] ) ) : ?>
				<input type="hidden" name="asdir_ubicacion" value="<?php echo esc_attr( $filtros['ubicacion'] ); ?>">
			<?php endif; ?>
			<?php foreach ( $filtros['categorias'] ?? array() as $slug ) : ?>
				<input type="hidden" name="asdir_cat[]" value="<?php echo esc_attr( $slug ); ?>">
			<?php endforeach; ?>
			<label for="asdir_orden"><?php esc_html_e( 'Ordenar', 'asogravas-directorio' ); ?></label>
			<select id="asdir_orden" name="asdir_orden">
				<option value="destacadas" <?php selected( $orden, 'destacadas' ); ?>><?php esc_html_e( 'Destacadas primero', 'asogravas-directorio' ); ?></option>
				<option value="alfabetico" <?php selected( $orden, 'alfabetico' ); ?>><?php esc_html_e( 'A – Z', 'asogravas-directorio' ); ?></option>
				<option value="reciente" <?php selected( $orden, 'reciente' ); ?>><?php esc_html_e( 'Más recientes', 'asogravas-directorio' ); ?></option>
			</select>
			<noscript><button type="submit"><?php esc_html_e( 'Ordenar', 'asogravas-directorio' ); ?></button></noscript>
		</form>
	</div>

	<div class="asdir-grilla asdir-grilla--cols-<?php echo esc_attr( (string) $columnas ); ?>" data-asdir-grilla>
		<?php if ( $query->have_posts() ) : ?>
			<?php
			while ( $query->have_posts() ) :
				$query->the_post();
				asdir_plantilla( 'parts/card-empresa.php', array( 'post_id' => get_the_ID() ) );
			endwhile;
			?>
		<?php else : ?>
			<?php asdir_plantilla( 'parts/sin-resultados.php' ); ?>
		<?php endif; ?>
	</div>

	<div class="asdir-mas" data-asdir-mas <?php echo $hay_mas && $query->have_posts() ? '' : 'hidden'; ?>>
		<button type="button" class="asdir-btn asdir-btn--linea" data-asdir-cargar>
			<?php esc_html_e( 'Cargar más empresas', 'asogravas-directorio' ); ?>
		</button>
	</div>

	<?php if ( $query->max_num_pages > 1 ) : ?>
		<noscript>
			<nav class="asdir-paginacion" aria-label="<?php esc_attr_e( 'Paginación del directorio', 'asogravas-directorio' ); ?>">
				<?php
				$base = ASDIR_Query::url( array_merge( $filtros, array( 'pagina' => 1 ) ) );
				echo wp_kses_post(
					paginate_links(
						array(
							'total'     => (int) $query->max_num_pages,
							'current'   => (int) $filtros['pagina'],
							'format'    => '?asdir_pag=%#%',
							'base'      => esc_url_raw( add_query_arg( 'asdir_pag', '%#%', $base ) ),
							'prev_text' => __( 'Anterior', 'asogravas-directorio' ),
							'next_text' => __( 'Siguiente', 'asogravas-directorio' ),
						)
					)
				);
				?>
			</nav>
		</noscript>
	<?php endif; ?>
</div>
