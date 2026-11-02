( function ( wp ) {
	if ( ! wp || ! wp.blocks ) {
		return;
	}

	var el = wp.element.createElement;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelBody = wp.components.PanelBody;
	var ToggleControl = wp.components.ToggleControl;
	var SelectControl = wp.components.SelectControl;
	var TextControl = wp.components.TextControl;
	var __ = wp.i18n.__;

	function siNo( valor ) {
		return 'si' === valor;
	}

	wp.blocks.registerBlockType( 'asogravas/directorio', {
		edit: function ( props ) {
			var atts = props.attributes;

			return el(
				'div',
				{ className: 'asdir-block-placeholder' },
				el(
					InspectorControls,
					{},
					el(
						PanelBody,
						{ title: __( 'Directorio', 'asogravas-directorio' ), initialOpen: true },
						el( ToggleControl, {
							label: __( 'Encabezado', 'asogravas-directorio' ),
							checked: siNo( atts.hero ),
							onChange: function ( v ) { props.setAttributes( { hero: v ? 'si' : 'no' } ); }
						} ),
						el( ToggleControl, {
							label: __( 'Empresas destacadas', 'asogravas-directorio' ),
							checked: siNo( atts.destacadas ),
							onChange: function ( v ) { props.setAttributes( { destacadas: v ? 'si' : 'no' } ); }
						} ),
						el( ToggleControl, {
							label: __( 'Filtros', 'asogravas-directorio' ),
							checked: siNo( atts.filtros ),
							onChange: function ( v ) { props.setAttributes( { filtros: v ? 'si' : 'no' } ); }
						} ),
						el( ToggleControl, {
							label: __( 'Banda final', 'asogravas-directorio' ),
							checked: siNo( atts.cta ),
							onChange: function ( v ) { props.setAttributes( { cta: v ? 'si' : 'no' } ); }
						} ),
						el( ToggleControl, {
							label: __( 'Solo destacadas', 'asogravas-directorio' ),
							checked: siNo( atts.solo_destacadas ),
							onChange: function ( v ) { props.setAttributes( { solo_destacadas: v ? 'si' : 'no' } ); }
						} ),
						el( TextControl, {
							label: __( 'Empresas por tanda', 'asogravas-directorio' ),
							type: 'number',
							value: atts.por_pagina,
							onChange: function ( v ) { props.setAttributes( { por_pagina: v } ); }
						} ),
						el( SelectControl, {
							label: __( 'Columnas', 'asogravas-directorio' ),
							value: atts.columnas,
							options: [
								{ label: '2', value: '2' },
								{ label: '3', value: '3' },
								{ label: '4', value: '4' }
							],
							onChange: function ( v ) { props.setAttributes( { columnas: v } ); }
						} ),
						el( SelectControl, {
							label: __( 'Orden', 'asogravas-directorio' ),
							value: atts.orden,
							options: [
								{ label: __( 'Destacadas primero', 'asogravas-directorio' ), value: 'destacadas' },
								{ label: __( 'A – Z', 'asogravas-directorio' ), value: 'alfabetico' },
								{ label: __( 'Más recientes', 'asogravas-directorio' ), value: 'reciente' }
							],
							onChange: function ( v ) { props.setAttributes( { orden: v } ); }
						} )
					)
				),
				el( 'p', { style: { padding: '24px', background: '#f3f4fe', borderRadius: '12px', color: '#1e2a7a', fontWeight: 700 } },
					__( 'Directorio de empresas. Ajusta las opciones en el panel lateral. El diseño se ve en la página publicada.', 'asogravas-directorio' )
				)
			);
		},
		save: function () {
			return null;
		}
	} );
}( window.wp ) );
