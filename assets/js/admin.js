/* global jQuery, wp, asdirAdmin */
( function ( $ ) {
	'use strict';

	/**
	 * Campo de archivo único: imagen o PDF.
	 */
	function iniciarArchivos() {
		$( document ).on( 'click', '.asdir-archivo__elegir', function () {
			var $campo = $( this ).closest( '.asdir-archivo' );
			var esPdf = 'pdf' === $campo.data( 'tipo' );
			var textos = esPdf ? asdirAdmin.pdf : asdirAdmin.imagen;

			var marco = wp.media( {
				title: textos.titulo,
				button: { text: textos.boton },
				library: { type: esPdf ? 'application/pdf' : 'image' },
				multiple: false
			} );

			marco.on( 'select', function () {
				var adjunto = marco.state().get( 'selection' ).first().toJSON();

				$campo.find( '.asdir-archivo__valor' ).val( adjunto.id );
				$campo.find( '.asdir-archivo__previa' ).html( previaArchivo( adjunto, esPdf ) );
				$campo.find( '.asdir-archivo__quitar' ).removeAttr( 'hidden' );
			} );

			marco.open();
		} );

		$( document ).on( 'click', '.asdir-archivo__quitar', function () {
			var $campo = $( this ).closest( '.asdir-archivo' );

			$campo.find( '.asdir-archivo__valor' ).val( '' );
			$campo.find( '.asdir-archivo__previa' ).empty();
			$( this ).attr( 'hidden', 'hidden' );
		} );
	}

	function previaArchivo( adjunto, esPdf ) {
		if ( esPdf ) {
			return $( '<span class="asdir-archivo__pdf"></span>' )
				.append( '<span class="dashicons dashicons-pdf"></span> ' )
				.append( $( '<a target="_blank" rel="noopener"></a>' ).attr( 'href', adjunto.url ).text( adjunto.title || adjunto.filename ) );
		}

		var origen = ( adjunto.sizes && adjunto.sizes.thumbnail ) ? adjunto.sizes.thumbnail.url : adjunto.url;

		return $( '<img class="asdir-archivo__img" alt="">' ).attr( 'src', origen );
	}

	/**
	 * Galería: selección múltiple, reordenamiento por arrastre y borrado individual.
	 */
	function iniciarGaleria() {
		$( '.asdir-galeria__lista' ).sortable( {
			items: '> li',
			update: function () {
				sincronizarGaleria( $( this ).closest( '.asdir-galeria' ) );
			}
		} );

		$( document ).on( 'click', '.asdir-galeria__agregar', function () {
			var $campo = $( this ).closest( '.asdir-galeria' );

			var marco = wp.media( {
				title: asdirAdmin.imagenes.titulo,
				button: { text: asdirAdmin.imagenes.boton },
				library: { type: 'image' },
				multiple: 'add'
			} );

			marco.on( 'select', function () {
				var $lista = $campo.find( '.asdir-galeria__lista' );

				marco.state().get( 'selection' ).each( function ( adjunto ) {
					var datos = adjunto.toJSON();

					// No duplicar una imagen que ya está en la galería.
					if ( $lista.find( '[data-id="' + datos.id + '"]' ).length ) {
						return;
					}

					$lista.append( itemGaleria( datos ) );
				} );

				sincronizarGaleria( $campo );
			} );

			marco.open();
		} );

		$( document ).on( 'click', '.asdir-galeria__quitar', function () {
			var $campo = $( this ).closest( '.asdir-galeria' );

			$( this ).closest( '.asdir-galeria__item' ).remove();
			sincronizarGaleria( $campo );
		} );
	}

	function itemGaleria( adjunto ) {
		var origen = ( adjunto.sizes && adjunto.sizes.thumbnail ) ? adjunto.sizes.thumbnail.url : adjunto.url;

		return $( '<li class="asdir-galeria__item"></li>' )
			.attr( 'data-id', adjunto.id )
			.append( $( '<img alt="">' ).attr( 'src', origen ) )
			.append( $( '<button type="button" class="asdir-galeria__quitar">&times;</button>' ).attr( 'aria-label', asdirAdmin.quitar ) );
	}

	function sincronizarGaleria( $campo ) {
		var ids = $campo.find( '.asdir-galeria__item' ).map( function () {
			return $( this ).data( 'id' );
		} ).get();

		$campo.find( '.asdir-galeria__valor' ).val( ids.join( ',' ) );
	}

	/**
	 * Vista previa del video al pegar el enlace.
	 */
	function iniciarVideo() {
		$( document ).on( 'input change paste', '.asdir-video__url', function () {
			var $campo = $( this ).closest( '.asdir-video' );
			var input = this;

			// El evento paste se dispara antes de que el valor esté en el input.
			window.setTimeout( function () {
				var id = idDeYoutube( input.value );
				var $previa = $campo.find( '.asdir-video__previa' );

				if ( ! id ) {
					$previa.empty();
					return;
				}

				$previa.html(
					$( '<img alt="" width="200" height="150">' ).attr( 'src', 'https://i.ytimg.com/vi/' + id + '/mqdefault.jpg' )
				);
			}, 0 );
		} );
	}

	function idDeYoutube( url ) {
		var patrones = [
			/youtube\.com\/watch\?(?:.*&)?v=([A-Za-z0-9_-]{11})/i,
			/youtu\.be\/([A-Za-z0-9_-]{11})/i,
			/youtube\.com\/embed\/([A-Za-z0-9_-]{11})/i,
			/youtube\.com\/shorts\/([A-Za-z0-9_-]{11})/i
		];

		for ( var i = 0; i < patrones.length; i++ ) {
			var coincidencia = url.match( patrones[ i ] );

			if ( coincidencia ) {
				return coincidencia[ 1 ];
			}
		}

		return '';
	}

	/**
	 * Estrella de destacado en la tabla de empresas.
	 */
	function iniciarEstrella() {
		$( document ).on( 'click', '.asdir-estrella', function () {
			var $boton = $( this );

			if ( $boton.hasClass( 'is-cargando' ) ) {
				return;
			}

			$boton.addClass( 'is-cargando' );

			$.post( asdirAdmin.ajaxUrl, {
				action: 'asdir_toggle_destacada',
				nonce: asdirAdmin.nonce,
				post: $boton.data( 'post' )
			} ).done( function ( respuesta ) {
				if ( ! respuesta || ! respuesta.success ) {
					return;
				}

				var activa = respuesta.data.destacada;

				$boton.toggleClass( 'is-activa', activa ).attr( 'aria-pressed', activa ? 'true' : 'false' );
				$boton.find( '.dashicons' )
					.toggleClass( 'dashicons-star-filled', activa )
					.toggleClass( 'dashicons-star-empty', ! activa );
			} ).always( function () {
				$boton.removeClass( 'is-cargando' );
			} );
		} );
	}

	function iniciarResumen() {
		var $ex = $( '#postexcerpt #excerpt, #excerpt' ).first();

		if ( ! $ex.length ) {
			return;
		}

		var max = asdirAdmin.maxExcerpt || 160;
		var $contador = $( '<p class="asdir-contador"></p>' );

		$ex.attr( 'maxlength', max ).after( $contador );

		function actualizar() {
			var n = $ex.val().length;
			$contador.text( asdirAdmin.excerptTpl.replace( '%1$s', n ).replace( '%2$s', max ) );
			$contador.toggleClass( 'is-exceso', n >= max );
		}

		$ex.on( 'input', actualizar );
		actualizar();
	}

	$( function () {
		iniciarArchivos();
		iniciarGaleria();
		iniciarVideo();
		iniciarEstrella();
		iniciarResumen();
	} );
}( jQuery ) );
