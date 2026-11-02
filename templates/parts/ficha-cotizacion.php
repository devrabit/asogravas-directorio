<?php
/**
 * Formulario de cotización. Maqueta: no envía ni guarda datos.
 *
 * @package Asogravas_Directorio
 *
 * @var int $post_id
 */

defined( 'ABSPATH' ) || exit;

$post_id   = isset( $post_id ) ? (int) $post_id : get_the_ID();
$nombre    = get_the_title( $post_id );
$opciones  = array();

foreach ( array( '_asdir_productos', '_asdir_servicios' ) as $meta ) {
	$lista = asdir_items_lista( asdir_meta( $post_id, $meta ) );

	if ( 'lista' !== ( $lista['modo'] ?? '' ) ) {
		continue;
	}

	foreach ( $lista['items'] as $item ) {
		$texto = trim( wp_strip_all_tags( $item ) );

		if ( '' !== $texto ) {
			$opciones[] = $texto;
		}
	}
}

$opciones = array_values( array_unique( $opciones ) );
?>
<section class="asdir-ficha-cotizacion" id="asdir-cotizacion" aria-labelledby="asdir-cotizacion-titulo">
	<h2 id="asdir-cotizacion-titulo"><?php esc_html_e( 'Solicitud de cotización', 'asogravas-directorio' ); ?></h2>
	<p class="asdir-ficha-cotizacion__intro">
		<?php
		printf(
			/* translators: %s: nombre de la empresa */
			esc_html__( 'Cuéntale a %s qué necesitas y te contactarán con una propuesta.', 'asogravas-directorio' ),
			esc_html( $nombre )
		);
		?>
	</p>

	<form class="asdir-ficha-cotizacion__form" data-asdir-cotizacion novalidate>
		<div class="asdir-ficha-cotizacion__grid">
			<label>
				<span><?php esc_html_e( 'Nombre completo', 'asogravas-directorio' ); ?></span>
				<input type="text" name="nombre" required autocomplete="name">
			</label>
			<label>
				<span><?php esc_html_e( 'Empresa', 'asogravas-directorio' ); ?></span>
				<input type="text" name="empresa" autocomplete="organization">
			</label>
			<label>
				<span><?php esc_html_e( 'Correo electrónico', 'asogravas-directorio' ); ?></span>
				<input type="email" name="correo" required autocomplete="email">
			</label>
			<label>
				<span><?php esc_html_e( 'Teléfono / WhatsApp', 'asogravas-directorio' ); ?></span>
				<input type="tel" name="telefono" autocomplete="tel">
			</label>
		</div>
		<label class="asdir-ficha-cotizacion__ancho">
			<span><?php esc_html_e( 'Producto o servicio de interés', 'asogravas-directorio' ); ?></span>
			<select name="interes">
				<option value=""><?php esc_html_e( 'Selecciona una opción', 'asogravas-directorio' ); ?></option>
				<?php foreach ( $opciones as $opcion ) : ?>
					<option value="<?php echo esc_attr( $opcion ); ?>"><?php echo esc_html( $opcion ); ?></option>
				<?php endforeach; ?>
				<option value="otro"><?php esc_html_e( 'Otro', 'asogravas-directorio' ); ?></option>
			</select>
		</label>
		<label class="asdir-ficha-cotizacion__ancho">
			<span><?php esc_html_e( 'Describe tu necesidad', 'asogravas-directorio' ); ?></span>
			<textarea name="mensaje" rows="5" required></textarea>
		</label>
		<label class="asdir-ficha-cotizacion__check">
			<input type="checkbox" name="acepta" required>
			<span><?php esc_html_e( 'Acepto el tratamiento de mis datos para esta solicitud.', 'asogravas-directorio' ); ?></span>
		</label>
		<button type="submit" class="asdir-btn asdir-btn--azul"><?php esc_html_e( 'Enviar solicitud', 'asogravas-directorio' ); ?></button>
	</form>

	<p class="asdir-ficha-cotizacion__ok" data-asdir-cotizacion-ok hidden>
		<?php esc_html_e( 'Recibimos tu solicitud. Te contactaremos pronto.', 'asogravas-directorio' ); ?>
	</p>
</section>
