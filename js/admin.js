jQuery( function( $ ) {

	$( '#mw-wp-form_validation' ).mw_wp_form_repeatable();

	/**
	 * 完了ページの入力エリアからオリジナルボタンを消去
	 */
	$( window ).on( 'load', function() {
		$( '#mw-wp-form_complete_message_metabox input[id^="qt_mw-wp-form_complete_message_mwform_"]' ).remove();
	} );

	/**
	 * フォームタグジェネレータ
	 */
	function mwform_create_shortcode( dialog_id ) {
		var shortcode = [];
		var shortcode_name = dialog_id.replace( 'dialog-', '' );

		$( '#' + dialog_id + ':first' ).find( 'input, textarea' ).each( function( i, e ) {
			var val;
			var name = $( e ).attr( 'name' );

			if ( $( e )[0].tagName.toLowerCase() == 'textarea' ) {
				val = $( e ).val().split( /\r\n|\r|\n/ );
				val = val.join( ',' );
			} else if ( $( e ).attr( 'type' ) === 'checkbox' ) {
				if ( $( e ).prop( 'checked' ) ) {
					val = $( e ).closest( ':checked' ).val();
				}
			} else {
				val = $( e ).val();
			}

			if ( name == 'name' && !val ) {
				val = shortcode_name + '-' + Math.floor( Math.random() * 1000 )
			}
			if ( val ) {
				var attribute = name + '=\"' + val + '\"';
				shortcode.push( attribute );
			}
		} );
		shortcode = shortcode.join( ' ' );
		if ( shortcode ) {
			var shortcode2 = '[' + shortcode_name + ' ' + shortcode + ']';
		} else {
			var shortcode2 = '[' + shortcode_name + ']';
		}
		return shortcode2;
	}
	$( '.mwform-dialog' ).dialog( {
		bgiframe: true,
		autoOpen: false,
		resizable: true,
		width: 500,
		buttons: {
			'Insert': function() {
				send_to_editor( mwform_create_shortcode( $( this ).attr( 'id' ) ) );
				$( this ).dialog( 'close' );
			},
			'Cancel': function() {
				$( this ).dialog( 'close' );
			}
		},
		open: function() {
		}
	} );
	$( '.add-mwform-btn .button' ).click( function() {
		var select = $( '.add-mwform-btn select' ).val();
		$( '#dialog-' + select ).dialog( 'open' );
	} );

	/**
	 * sortable
	 */
	$( '#mw-wp-form_validation .repeatable-boxes' ).sortable( {
		items : '> .repeatable-box',
		handle: '.sortable-icon-handle'
	} );

	/**
	 * 問い合わせ番号フィールド
	 */
	$( 'input[name="open_tracking_number_field"]' ).click( function() {
		var tracking_number_field = $( '#tracking_number_field' );
		var is_open = $( this ).prop( 'checked' );
		if ( is_open ) {
			tracking_number_field.removeAttr( 'disabled' );
		} else {
			tracking_number_field.attr( 'disabled', 'disabled' );
		}
	} );
} );