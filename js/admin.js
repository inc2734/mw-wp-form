jQuery( function( $ ) {

	var cnt = $( '.validation-box' ).length;

	/**
	 * 開閉ボタン
	 */
	$( '.validation-btn b' ).on( 'click', function() {
		$( this ).parent().siblings( '.validation-content' ).slideToggle( 'high' );
	} );

	/**
	 * 削除ボタン
	 */
	$( '.validation-remove b' ).on( 'click', function() {
		cnt++;
		$( this ).closest( '.validation-box' ).fadeOut( function() {
			$( this ).remove();
		} );
	} );

	/**
	 * 追加ボタン
	 */
	$( '#mw-wp-form_validation b' ).click( function() {
		cnt++;
		var clone = $( this ).parent().find( '.validation-box:first' ).clone( true );
		clone.find( 'input' ).each( function() {
			$( this ).attr( 'name', $( this ).attr( 'name' ).replace( /\[\d+\]/, '[' + cnt + ']' ) );
		} );
		clone.hide().find( '.validation-content' ).show();
		$( this ).siblings( '.validation-box:first' ).after( clone.fadeIn() );
	} );

	/**
	 * ターゲット名をラベルとして表示
	 */
	$( '.targetKey' ).on( 'keyup', function() {
		var val = $( this ).val();
		console.log( val );
		$( this ).parent().parent().find( '.validation-btn span' ).text( val );
	} );

	/**
	 * 完了ページの入力エリアからオリジナルボタンを消去
	 */
	$( window ).on( 'load', function() {
		$( '#mw-wp-form_complete_message_metabox input[id^="qt_mw-wp-form_complete_message_mwform_"]' ).remove();
	} );

} );

/**
 * フォームタグジェネレータ
 */
jQuery( function( $ ) {
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
			'Cansel': function() {
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
} );