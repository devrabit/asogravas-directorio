/* global asdirDir */
( function () {
	'use strict';

	var root = document.querySelector( '.asdir-directorio' );

	if ( ! root || typeof asdirDir === 'undefined' ) {
		return;
	}

	var filtrosForm = root.querySelector( '[data-asdir-filtros]' );
	var resultados = root.querySelector( '[data-asdir-resultados]' );
	var grilla = root.querySelector( '[data-asdir-grilla]' );
	var conteoEl = root.querySelector( '.asdir-resultados__conteo' );
	var masWrap = root.querySelector( '[data-asdir-mas]' );
	var masBtn = root.querySelector( '[data-asdir-cargar]' );
	var ordenSelect = root.querySelector( '#asdir_orden' );
	var buscador = root.querySelector( '.asdir-buscador' );
	var limpiar = root.querySelector( '.asdir-filtros__limpiar' );
	var pagina = 1;
	var ocupado = false;

	root.classList.add( 'is-js' );

	if ( window.matchMedia( '(max-width: 959px)' ).matches ) {
		var panel = root.querySelector( '.asdir-filtros-panel' );
		if ( panel ) {
			panel.removeAttribute( 'open' );
		}
	}

	function estado() {
		var cats = [];

		if ( filtrosForm ) {
			filtrosForm.querySelectorAll( 'input[name="asdir_cat[]"]:checked' ).forEach( function ( el ) {
				cats.push( el.value );
			} );
		}

		var ubi = '';
		var ubiEl = filtrosForm && filtrosForm.querySelector( 'input[name="asdir_ubicacion"]:checked' );
		if ( ubiEl ) {
			ubi = ubiEl.value;
		}

		var q = '';
		var qEl = root.querySelector( '#asdir_q' );
		if ( qEl ) {
			q = qEl.value;
		}

		return {
			asdir_q: q,
			asdir_ubicacion: ubi,
			asdir_cat: cats.join( ',' ),
			asdir_orden: ordenSelect ? ordenSelect.value : 'destacadas',
			asdir_pag: pagina,
			por_pagina: resultados ? resultados.getAttribute( 'data-por-pagina' ) : '9',
			columnas: resultados ? resultados.getAttribute( 'data-columnas' ) : '3',
			categoria: resultados ? resultados.getAttribute( 'data-categoria' ) : '',
			solo_destacadas: resultados ? resultados.getAttribute( 'data-solo-destacadas' ) : 'no'
		};
	}

	function hayFiltros( data ) {
		return !!( data.asdir_q || data.asdir_ubicacion || data.asdir_cat );
	}

	function pedir( anexar ) {
		if ( ocupado ) {
			return;
		}

		ocupado = true;
		if ( resultados ) {
			resultados.classList.add( 'is-cargando' );
		}

		var data = estado();
		var cuerpo = new URLSearchParams();

		cuerpo.set( 'action', 'asdir_filtrar' );
		cuerpo.set( 'nonce', asdirDir.nonce );
		cuerpo.set( 'anexar', anexar ? '1' : '' );

		Object.keys( data ).forEach( function ( clave ) {
			cuerpo.set( clave, data[ clave ] );
		} );

		fetch( asdirDir.ajax, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: cuerpo.toString()
		} ).then( function ( r ) {
			return r.json();
		} ).then( function ( json ) {
			if ( ! json || ! json.success ) {
				throw new Error( 'bad' );
			}

			pintar( json.data, anexar );
		} ).catch( function () {
			if ( ! anexar ) {
				window.location.href = construirUrl();
			} else {
				window.alert( asdirDir.i18n.error );
			}
		} ).finally( function () {
			ocupado = false;
			if ( resultados ) {
				resultados.classList.remove( 'is-cargando' );
			}
		} );
	}

	function pintar( data, anexar ) {
		if ( ! grilla ) {
			return;
		}

		if ( anexar ) {
			grilla.insertAdjacentHTML( 'beforeend', data.html );
		} else if ( data.vacio ) {
			grilla.innerHTML = data.vacio_html;
		} else {
			grilla.innerHTML = data.html;
		}

		if ( conteoEl ) {
			conteoEl.textContent = data.conteo_txt;
		}

		if ( masWrap ) {
			if ( data.hay_mas ) {
				masWrap.removeAttribute( 'hidden' );
			} else {
				masWrap.setAttribute( 'hidden', 'hidden' );
			}
		}

		if ( resultados ) {
			resultados.setAttribute( 'data-pagina', String( data.pagina ) );
		}

		actualizarContadores( data.contadores || {} );
		actualizarLimpiar();

		if ( data.url && window.history && window.history.pushState ) {
			window.history.pushState( estado(), '', data.url );
		}

		if ( ! anexar && resultados ) {
			resultados.scrollIntoView( { behavior: 'smooth', block: 'start' } );
		}
	}

	function actualizarContadores( contadores ) {
		root.querySelectorAll( '.asdir-cats li' ).forEach( function ( li ) {
			var input = li.querySelector( 'input' );
			var nEl = li.querySelector( '.asdir-cats__n' );

			if ( ! input || ! nEl ) {
				return;
			}

			var n = Object.prototype.hasOwnProperty.call( contadores, input.value )
				? parseInt( contadores[ input.value ], 10 ) || 0
				: 0;

			nEl.textContent = String( n );

			if ( 0 === n && ! input.checked ) {
				li.classList.add( 'is-vacia' );
				input.disabled = true;
			} else {
				li.classList.remove( 'is-vacia' );
				input.disabled = false;
			}
		} );
	}

	function actualizarLimpiar() {
		if ( ! limpiar ) {
			return;
		}

		if ( hayFiltros( estado() ) ) {
			limpiar.removeAttribute( 'hidden' );
		} else {
			limpiar.setAttribute( 'hidden', 'hidden' );
		}
	}

	function construirUrl() {
		var data = estado();
		var url = new URL( window.location.href );

		[ 'asdir_q', 'asdir_ubicacion', 'asdir_cat', 'asdir_orden', 'asdir_pag' ].forEach( function ( k ) {
			url.searchParams.delete( k );
		} );

		if ( data.asdir_q ) {
			url.searchParams.set( 'asdir_q', data.asdir_q );
		}
		if ( data.asdir_ubicacion ) {
			url.searchParams.set( 'asdir_ubicacion', data.asdir_ubicacion );
		}
		if ( data.asdir_cat ) {
			url.searchParams.set( 'asdir_cat', data.asdir_cat );
		}
		if ( data.asdir_orden && 'destacadas' !== data.asdir_orden ) {
			url.searchParams.set( 'asdir_orden', data.asdir_orden );
		}

		return url.toString();
	}

	function resetYFiltrar() {
		pagina = 1;
		pedir( false );
	}

	if ( filtrosForm ) {
		filtrosForm.addEventListener( 'change', function ( ev ) {
			if ( ev.target && ( ev.target.name === 'asdir_cat[]' || ev.target.name === 'asdir_ubicacion' ) ) {
				resetYFiltrar();
			}
		} );

		filtrosForm.addEventListener( 'submit', function ( ev ) {
			ev.preventDefault();
			resetYFiltrar();
		} );
	}

	if ( buscador ) {
		buscador.addEventListener( 'submit', function ( ev ) {
			ev.preventDefault();
			resetYFiltrar();
		} );
	}

	if ( ordenSelect ) {
		ordenSelect.addEventListener( 'change', resetYFiltrar );
	}

	if ( masBtn ) {
		masBtn.addEventListener( 'click', function () {
			pagina += 1;
			pedir( true );
		} );
	}

	window.addEventListener( 'popstate', function () {
		window.location.reload();
	} );

	actualizarLimpiar();
}() );
