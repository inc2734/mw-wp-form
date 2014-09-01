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
						top   : 10,
						left  : 0,
						height: '90%'
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
					calc: function ( dt, row ) {
						var val = dt.getValue( row, 1 );
						return {
							v: ( val * 100 ).toFixed( 1 ) + ' %',
							f: ( val * 100 ).toFixed( 1 ) + ' %'
						};
					},
					type: 'string',
					sourceColumn: 1,
					role: 'annotation'
				}] );
				var height = ( config.data.length - 1 ) * 40;
				var options = {
					colors: getColors(),
					backgroundColor: 'transparent',
					chartArea: {
						top   : 0,
						left  : 160,
						height: height,
						width : '95%'
					},
					annotations: {
						format: '#,#%',
						textStyle: {
							fontSize: 12,
						}
					},
					hAxis: {
						format: '#,#%',
						textStyle: {
							color: '#999',
							fontSize: 13
						}
					},
					vAxis: {
						textStyle: {
							color: '#444',
							fontSize: 13
						}
					},
					tooltip: {
						trigger: 'none'
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
			var base_color = '1e8cbe';
			var red   = parseInt( base_color.substr( 0, 2 ), 16 );
			var green = parseInt( base_color.substr( 2, 2 ), 16 );
			var blue  = parseInt( base_color.substr( 4, 2 ), 16 );
			var count = config.data.length - 1;
			var colors = [];
			for ( i = 0; i <= count; i ++ ) {
				red += 15;
				if ( red > 209 ) {
					red = 209;
				}
				green += 10;
				if ( green > 223 ) {
					green = 223;
				}
				blue += 5;
				if ( blue > 229 ) {
					blue = 229;
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