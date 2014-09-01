/**
 * mw_wp_form_google_chart
 * Created: August 31, 2014
 */
jQuery( function( $ ) {
	$.fn.mw_wp_form_google_chart = function( config ) {
		var defaults = {
			chart: 'pie',
			data : []
		};
		var config = $.extend( defaults, config );

		return this.each( function( i, e ) {
			var data = google.visualization.arrayToDataTable( config.data );
			var target = $( e ).get( 0 );
			if ( config.chart === 'pie' ) {
				var options = {
					colors: getColors(),
					backgroundColor: 'transparent',
					chartArea: {
						top   : 0,
						left  : 0,
						height: '100%'
					},
					legend: {
						alignment: 'center'
					},
					height: 260
				};
				var chart = new google.visualization.PieChart( target );
			} else if ( config.chart === 'bar' ) {
				data = new google.visualization.DataView( data );
				data.setColumns( [0, 1, {
					calc: 'stringify',
					sourceColumn: 1,
					type: 'string',
					role: 'annotation'
				}] );
				var height = ( config.data.length - 1 ) * 40;
				var options = {
					colors: getColors(),
					backgroundColor: 'transparent',
					chartArea: {
						top   : 0,
						left  : 10,
						height: height,
						width : '95%'
					},
					vAxis: {
						textStyle: {
							color: '#fff'
						},
						textPosition: 'in'
					},
					legend: {
						position: 'none'
					},
					height: height + 30
				};
				var chart = new google.visualization.BarChart( target );
			}
			chart.draw( data, options );
		} );

		function getColors() {
			var base_color = '2ea2cc';
			var red   = parseInt( base_color.substr( 0, 2 ), 16 );
			var green = parseInt( base_color.substr( 2, 2 ), 16 );
			var blue  = parseInt( base_color.substr( 4, 2 ), 16 );
			var count = config.data.length - 1;
			var colors = [];
			for ( i = 0; i <= count; i ++ ) {
				red += 15;
				if ( red > 240 ) {
					red = 240;
				}
				green += 10;
				if ( green > 240 ) {
					green = 240;
				}
				blue += 5;
				if ( blue > 240 ) {
					blue = 240;
				}
				var hred = red.toString( 16 );
				if ( hred.length < 2 ) {
					hred += hred;
				}
				var hgreen = green.toString( 16 );
				if ( hgreen.length < 2 ) {
					hgreen += hgreen;
				}
				var hblue = blue.toString( 16 );
				if ( hblue.length < 2 ) {
					hblue += hblue;
				}
				colors.push( '#' + hred + hgreen + hblue );
			}
			return colors;
		}
	}
} );