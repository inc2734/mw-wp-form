/**
 * mw_wp_form_repeatable
 * Created: August 31, 2014
 */
jQuery( function( $ ) {

	$.fn.mw_wp_form_repeatable = function( config ) {
		var defaults = {
			label_field : '.open-btn span',
			open_btn    : '.open-btn b',
			remove_btn  : '.remove-btn b',
			add_btn     : '.add-btn',
			box         : '.repeatable-box',
			box_content : '.repeatable-box-content',
			add_position: 'first' // or last
		};
		var config = $.extend( defaults, config );
		var cnt = $( config.box ).length;

		return this.each( function( i, e ) {
			/**
			 * 開閉ボタン
			 */
			$( e ).find( config.open_btn ).on( 'click', function() {
				$( this ).parent().siblings( config.box_content ).slideToggle( 100 );
			} );

			/**
			 * 削除ボタン
			 */
			$( e ).find( config.remove_btn ).on( 'click', function() {
				cnt ++;
				$( this ).closest( config.box ).fadeOut( function() {
					$( this ).remove();
				} );
			} );

			/**
			 * 追加ボタン
			 */
			$( e ).find( config.add_btn ).click( function() {
				cnt ++;
				var clone = $( this ).parent().find( config.box ).first().clone( true );
				clone.find( 'input, select' ).each( function() {
					$( this ).attr( 'name',
						$( this ).attr( 'name' ).replace( /\[\d+\]/, '[' + cnt + ']' )
					);
				} );
				clone.hide().find( config.box_content ).show();
				if ( config.add_position === 'first' ) {
					$( this ).parent().find( config.box ).first().after( clone.fadeIn() );
				} else {
					$( this ).parent().find( config.box ).last().after( clone.fadeIn() );
				}
			} );

			/**
			 * ターゲット名をラベルとして表示
			 */
			$( e ).find( '.targetKey' ).on( 'change keyup', function() {
				var val = $( this ).val();
				$( this ).parents( config.box ).find( config.label_field ).text( val );
			} );
		} );
	}

} );