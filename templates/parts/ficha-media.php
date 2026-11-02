<?php
/**
 * Video de la ficha.
 *
 * @package Asogravas_Directorio
 *
 * @var int $post_id
 */

defined( 'ABSPATH' ) || exit;

$post_id  = isset( $post_id ) ? (int) $post_id : get_the_ID();
$video_id = asdir_meta( $post_id, '_asdir_video_id' );

if ( ! $video_id ) {
	return;
}

$nombre     = get_the_title( $post_id );
$poster_id  = asdir_foto_descripcion_id( $post_id );
$poster_url = $poster_id ? wp_get_attachment_image_url( $poster_id, 'large' ) : '';
?>
<section class="asdir-ficha-video">
	<h2><?php esc_html_e( 'Video', 'asogravas-directorio' ); ?></h2>
	<div class="asdir-ficha-video__fachada" data-asdir-video data-youtube-id="<?php echo esc_attr( $video_id ); ?>">
		<?php if ( $poster_url ) : ?>
			<img src="<?php echo esc_url( $poster_url ); ?>" alt="" loading="lazy" decoding="async" width="640" height="360">
		<?php else : ?>
			<span class="asdir-ficha-video__fondo" aria-hidden="true"></span>
		<?php endif; ?>
		<button type="button" class="asdir-ficha-video__play" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: empresa */ __( 'Reproducir video de %s', 'asogravas-directorio' ), $nombre ) ); ?>">
			<span class="asdir-ficha-video__play-circulo"><?php echo asdir_icono( 'play' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		</button>
	</div>
</section>
