jQuery( function( $ ) {
	$( '#mw-wp-form_chart' ).mw_wp_form_repeatable( {
		add_position: 'last'
	} );

	$( '#mw-wp-form_chart .repeatable-boxes' ).sortable( {
		items : '> .repeatable-box',
		handle: '.sortable-icon-handle'
	} );
} );