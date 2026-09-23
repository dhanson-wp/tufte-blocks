/**
 * Project details sidebar panel.
 *
 * Three parts: Source (identifiers the sync runs on), Synced values
 * (read-only, with Refresh now), Overrides (collapsed, placeholders show
 * the synced value). Vanilla wp.element, no build step.
 *
 * @package Tufte_Blocks
 */
( function ( wp ) {
	'use strict';

	var el = wp.element.createElement;
	var useState = wp.element.useState;
	var Fragment = wp.element.Fragment;
	var registerPlugin = wp.plugins.registerPlugin;
	var PluginDocumentSettingPanel = wp.editor.PluginDocumentSettingPanel;
	var TextControl = wp.components.TextControl;
	var Button = wp.components.Button;
	var Spinner = wp.components.Spinner;
	var Notice = wp.components.Notice;
	var useSelect = wp.data.useSelect;
	var useEntityProp = wp.coreData.useEntityProp;
	var apiFetch = wp.apiFetch;
	var __ = wp.i18n.__;
	var config = window.tufteProjectFields || { fields: [], restBase: 'tufte-blocks/v1/projects' };

	var GROUP_LABELS = {
		source: __( 'Source', 'tufte-blocks' ),
		editorial: __( 'Editorial', 'tufte-blocks' ),
		release: __( 'Release', 'tufte-blocks' ),
		requirements: __( 'Requirements', 'tufte-blocks' ),
		links: __( 'Links', 'tufte-blocks' )
	};

	function byGroup( fields ) {
		var out = {};
		fields.forEach( function ( f ) {
			( out[ f.group ] = out[ f.group ] || [] ).push( f );
		} );
		return out;
	}

	function heading( text ) {
		return el( 'p', { style: { margin: '16px 0 4px', fontWeight: 600, textTransform: 'uppercase', fontSize: '11px', letterSpacing: '0.05em' } }, text );
	}

	function ManualField( props ) {
		return el( TextControl, {
			__nextHasNoMarginBottom: true,
			__next40pxDefaultSize: true,
			label: props.field.label,
			type: props.field.type === 'url' ? 'url' : 'text',
			value: props.meta[ props.field.metaKey ] || '',
			onChange: function ( value ) {
				var next = {};
				next[ props.field.metaKey ] = value;
				props.setMeta( Object.assign( {}, props.meta, next ) );
			}
		} );
	}

	function Panel() {
		var postType = useSelect( function ( select ) {
			return select( 'core/editor' ).getCurrentPostType();
		}, [] );
		var postId = useSelect( function ( select ) {
			return select( 'core/editor' ).getCurrentPostId();
		}, [] );
		var initialSynced = useSelect( function ( select ) {
			return select( 'core/editor' ).getCurrentPostAttribute( 'project_synced' );
		}, [] );

		// Hooks must run unconditionally; bail after them.
		var metaPair = useEntityProp( 'postType', 'project', 'meta' );
		var syncedState = useState( null );
		var busyState = useState( false );
		var errorState = useState( '' );
		var showOverridesState = useState( false );

		if ( postType !== 'project' ) {
			return null;
		}

		var meta = metaPair[ 0 ] || {};
		var setMeta = metaPair[ 1 ];
		var synced = syncedState[ 0 ] || initialSynced || { resolved: {}, errors: {}, fetched_at: '' };
		var setSynced = syncedState[ 1 ];

		var groups = byGroup( config.fields );
		var derived = config.fields.filter( function ( f ) { return ! f.manual; } );

		function refresh() {
			busyState[ 1 ]( true );
			errorState[ 1 ]( '' );
			apiFetch( { path: '/' + config.restBase + '/' + postId + '/sync', method: 'POST' } )
				.then( function ( data ) { setSynced( data ); } )
				.catch( function ( err ) { errorState[ 1 ]( ( err && err.message ) || __( 'Refresh failed.', 'tufte-blocks' ) ); } )
				.finally( function () { busyState[ 1 ]( false ); } );
		}

		var sourceErrors = Object.keys( synced.errors || {} ).map( function ( source ) {
			return el( Notice, { key: source, status: 'warning', isDismissible: false }, source + ': ' + synced.errors[ source ] );
		} );

		return el( PluginDocumentSettingPanel, { name: 'tufte-project-details', title: __( 'Project details', 'tufte-blocks' ) },
			// 1. Source and editorial (manual fields).
			[ 'source', 'editorial' ].map( function ( group ) {
				return el( Fragment, { key: group },
					heading( GROUP_LABELS[ group ] ),
					( groups[ group ] || [] ).filter( function ( f ) { return f.manual; } ).map( function ( f ) {
						return el( ManualField, { key: f.key, field: f, meta: meta, setMeta: setMeta } );
					} )
				);
			} ),
			// 2. Synced values.
			heading( __( 'Synced values', 'tufte-blocks' ) ),
			el( 'p', { style: { fontSize: '12px', color: '#757575', margin: '0 0 8px' } },
				synced.fetched_at
					? __( 'Last fetched: ', 'tufte-blocks' ) + new Date( synced.fetched_at ).toLocaleString()
					: __( 'Never fetched. Save the identifiers above, then refresh.', 'tufte-blocks' )
			),
			sourceErrors,
			errorState[ 0 ] ? el( Notice, { status: 'error', isDismissible: false }, errorState[ 0 ] ) : null,
			el( 'dl', { style: { display: 'grid', gridTemplateColumns: 'auto 1fr', gap: '4px 12px', fontSize: '12px', margin: '0 0 8px' } },
				derived.map( function ( f ) {
					var value = ( synced.resolved || {} )[ f.key ] || '';
					return el( Fragment, { key: f.key },
						el( 'dt', { style: { color: '#757575' } }, f.label ),
						el( 'dd', { style: { margin: 0, wordBreak: 'break-all' } }, value || '—' )
					);
				} )
			),
			el( Button, { variant: 'secondary', onClick: refresh, disabled: busyState[ 0 ] || ! postId },
				busyState[ 0 ] ? el( Spinner ) : __( 'Refresh now', 'tufte-blocks' )
			),
			// 3. Overrides, collapsed.
			heading( __( 'Overrides', 'tufte-blocks' ) ),
			el( Button, { variant: 'link', onClick: function () { showOverridesState[ 1 ]( ! showOverridesState[ 0 ] ); } },
				showOverridesState[ 0 ] ? __( 'Hide overrides', 'tufte-blocks' ) : __( 'Show overrides', 'tufte-blocks' )
			),
			showOverridesState[ 0 ] ? derived.map( function ( f ) {
				var placeholder = ( synced.resolved || {} )[ f.key ] || '';
				return el( TextControl, {
					key: f.key,
					__nextHasNoMarginBottom: true,
					__next40pxDefaultSize: true,
					label: f.label,
					type: f.type === 'url' ? 'url' : 'text',
					value: meta[ f.metaKey ] || '',
					placeholder: placeholder,
					help: placeholder ? __( 'Clear to use the synced value.', 'tufte-blocks' ) : '',
					onChange: function ( value ) {
						var next = {};
						next[ f.metaKey ] = value;
						setMeta( Object.assign( {}, meta, next ) );
					}
				} );
			} ) : null
		);
	}

	registerPlugin( 'tufte-blocks-project-details', { render: Panel, icon: null } );
} )( window.wp );
