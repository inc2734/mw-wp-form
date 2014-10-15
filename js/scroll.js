jQuery( function( $ ) {
	var posy = $( '.mw_wp_form_input, .mw_wp_form_confirm, .mw_wp_form_complete' ).offset().top;
	posy = posy + parseInt( mwform_scroll.offset );
	$( window ).scrollTop( posy );
} );