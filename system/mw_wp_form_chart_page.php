<?php
/**
 * Name: MW WP Form Chart Page
 * Description: グラフ画面クラス
 * Version: 1.0.0
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : August 30, 2014
 * Modified:
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Chart_Page {

	/**
	 * フォームの設定データ
	 */
	private $postdata = array();

	/**
	 * Settings API グループ名
	 */
	private $option_group;

	/**
	 * __construct
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_init', array( $this, 'register_setting' ) );
		$this->option_group = MWF_Config::NAME . '-' . 'chart-group';
	}

	/**
	 * add_menu
	 */
	public function add_menu() {
		$submenu_page = add_submenu_page(
			'edit.php?post_type=mw-wp-form',
			esc_html__( 'Chart', MWF_Config::DOMAIN ),
			esc_html__( 'Chart', MWF_Config::DOMAIN ),
			MWF_Config::CAPABILITY,
			MWF_Config::NAME . '-chart',
			array( $this, 'display' )
		);
		add_action( 'load-' . $submenu_page, array( $this, 'load' ) );
	}
	public function load() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts') );
	}

	/**
	 * admin_enqueue_scripts
	 */
	public function admin_enqueue_scripts() {
		$url = plugin_dir_url( __FILE__ );
		wp_register_script( 'jsapi', 'https://www.google.com/jsapi' );
		wp_enqueue_script( 'jsapi' );
		wp_register_script( MWF_Config::NAME . '-repeatable', $url . '../js/mw-wp-form-repeatable.js' );
		wp_enqueue_script( MWF_Config::NAME . '-repeatable' );
		wp_register_script( MWF_Config::NAME . '-admin-chart', $url . '../js/admin-chart.js' );
		wp_enqueue_script( MWF_Config::NAME . '-admin-chart' );
		wp_enqueue_script( 'jquery-ui-dialog' );

		global $wp_scripts;
		$ui = $wp_scripts->query( 'jquery-ui-core' );
	}

	/**
	 * display
	 */
	public function display() {
		if ( isset( $_GET['formkey'] ) ) {
			$formkey = $_GET['formkey'];
			$form_posts = get_posts( array(
				'post_type' => $formkey,
				'posts_per_page' => -1,
			) );
		} else {
			exit;
		}

		$custom_keys = array();
		foreach ( $form_posts as $post ) {
			setup_postdata( $post );
			$post_custom_keys = get_post_custom_keys( $post->ID );
			if ( !empty( $post_custom_keys ) && is_array( $post_custom_keys ) ) {
				foreach ( $post_custom_keys as $post_custom_key ) {
					if ( preg_match( '/^_/', $post_custom_key ) )
						continue;
					$custom_keys[$post_custom_key][get_post_meta( $post->ID, $post_custom_key, true )][] = $post->ID;
				}
			}
		}
		wp_reset_postdata();

		if ( ! $postdata = $this->get_option_data( 'chart' ) ) {
			$postdata = array();
		}
		$default_keys = array(
			'target' => '',
			'separator' => '',
			'chart' => '',
		);
		// 空の隠れフィールド（コピー元）を挿入
		array_unshift( $postdata, $default_keys );
		?>
<div class="wrap">
	<h2><?php esc_html_e( 'Chart', MWF_Config::DOMAIN ); ?></h2>
	<form method="post" action="options.php">
		<?php
		settings_fields( $this->option_group );
		do_settings_sections( $this->option_group );
		?>
		<div id="<?php echo esc_attr( MWF_Config::NAME . '_chart' ); ?>" class="postbox">
			<div class="inside">
				<b class="add-btn">グラフを追加</b>
				<?php foreach ( $postdata as $key => $value ) :  ?>
				<div class="repeatable-box"<?php if ( $key === 0 ) : ?> style="display:none"<?php endif; ?>>
					<div class="remove-btn"><b>×</b></div>
					<div class="open-btn"><span><?php echo esc_attr( $value['target'] ); ?></span><b>▼</b></div>
					<div class="repeatable-box-content">
						<?php esc_html_e( 'Item that create chart', MWF_Config::DOMAIN ); ?>
						<select class="targetKey" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[chart][<?php echo $key; ?>][target]">
							<option value=""><?php esc_html_e( 'Select this.', MWF_Config::DOMAIN ); ?></option>
							<?php foreach ( $custom_keys as $custom_key_name => $custom_key_value ) : ?>
							<option value="<?php echo esc_attr( $custom_key_name ); ?>" <?php selected( $value['target'], $custom_key_name ); ?>><?php echo esc_html( $custom_key_name ); ?></option>
							<?php endforeach; ?>
						</select>
						<br />
						<?php esc_html_e( 'Chart type', MWF_Config::DOMAIN ); ?>
						<select name="<?php echo esc_attr( MWF_Config::NAME ); ?>[chart][<?php echo $key; ?>][chart]">
							<?php
							$chart_options = array(
								'pie' => esc_html__( 'Pie chart', MWF_Config::DOMAIN ),
								'bar' => esc_html__( 'Bar chart', MWF_Config::DOMAIN ),
							);
							foreach ( $chart_options as $chart_option_key => $chart_option ) {
								printf(
									'<option value="%s" %s>%s</option>',
									esc_attr( $chart_option_key ),
									selected( $value['chart'], $chart_option_key, false ),
									esc_html( $chart_option )
								);
							}
							?>
						</select>
						<br />
						<?php esc_html_e( 'Separator string (If the check box. If the separator attribute is not set to ",")', MWF_Config::DOMAIN ); ?>
						<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[chart][<?php echo $key; ?>][separator]" value="<?php echo esc_attr( $value['separator'] ); ?>" size="5" />
					<!-- end .repeatable-box-content --></div>
				<!-- end .repeatable-box --></div>
				<?php endforeach; ?>
				<?php submit_button(); ?>
			<!-- end .inside --></div>
		<!-- end #mw-wp-form_chart --></div>
	</form>

	<?php
	foreach ( $postdata as $postdata_key => $chart ) {
		if ( !isset( $custom_keys[$chart['target']] ) ) {
			unset( $postdata[$postdata_key] );
			continue;
		}
		printf(
			'<h3>%s</h3><div class="%s" style="width: 100%%;"></div>',
			esc_html( $chart['target'] ),
			esc_attr( MWF_Config::NAME . '-chart-div-' . $postdata_key )
		);
	}

	$chart_data = array();
	foreach ( $postdata as $postdata_key => $chart ) {
		$data = array();
		$raw_data = array();
		foreach ( $custom_keys[$chart['target']] as $item => $values ) {
			if ( $chart['separator'] ) {
				$item = explode( $chart['separator'] , $item );
			}
			if ( is_array( $item ) ) {
				foreach ( $item as $_item ) {
					if ( $_item === '' ) {
						$_item = '(Empty)';
					}
					if ( empty( $raw_data[$_item] ) ) {
						$raw_data[$_item] = 1;
					} else {
						$raw_data[$_item] += 1;
					}
				}
			} else {
				if ( $item === '' ) {
					$item = '(Empty)';
				}
				if ( empty( $raw_data[$_item] ) ) {
					$raw_data[$item] = 1;
				} else {
					$raw_data[$item] += 1;
				}
			}
		}
		$data[] = array( '', '' );
		foreach ( $raw_data as $raw_data_key => $raw_data_value ) {
			$data[] = array(
				$raw_data_key,
				$raw_data_value,
			);
		}
		$chart_data[$postdata_key] = array(
			'chart' => $chart['chart'],
			'count' => count( $raw_data ),
			'data'  => json_encode( $data ),
		);
	}
	?>

	<script>
	google.load( 'visualization', 1, { packages:['corechart'] } );
	google.setOnLoadCallback( mwformDrawCharts );
	function mwformDrawCharts() {
		jQuery( function( $ ) {
			<?php foreach ( $chart_data as $postdata_key => $chart ) : ?>
			var data = google.visualization.arrayToDataTable( <?php echo $chart['data']; ?> );
			var target = $( '.<?php echo esc_js( MWF_Config::NAME . "-chart-div-" . $postdata_key ); ?>' ).get( 0 );
			<?php if ( $chart['chart'] === 'pie' ) : ?>
			var options = {
				colors: <?php echo $this->getColors( $chart['count'] ); ?>,
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
			<?php elseif ( $chart['chart'] === 'bar' ) : ?>
			data = new google.visualization.DataView( data );
			data.setColumns( [0, 1, {
				calc: 'stringify',
				sourceColumn: 1,
				type: 'string',
				role: 'annotation'
			}] );
			var options = {
				colors: <?php echo $this->getColors( $chart['count'] ); ?>,
				backgroundColor: 'transparent',
				chartArea: {
					top   : 0,
					height: '100%'
				},
				legend: {
					position: 'none'
				},
				height: <?php echo esc_js( $chart['count'] * 50 ); ?>
			};
			var chart = new google.visualization.BarChart( target );
			<?php endif; ?>
			chart.draw( data, options );
			<?php endforeach; ?>
		} );
	}
	</script>
<!-- end .wrap --></div>
		<?php
	}

	/**
	 * register_setting
	 */
	public function register_setting() {
		register_setting(
			$this->option_group,
			MWF_Config::NAME,
			array( $this, 'sanitize' )
		);
	}

	/**
	 * sanitize
	 * @param array $input フォームから送信されたデータ
	 * @return array
	 */
	public function sanitize( $input ) {
		$new_input = array();
		if ( is_array( $input ) && isset( $input['chart'] ) ) {
			if ( is_array( $input['chart'] ) ) {
				foreach ( $input['chart'] as $key => $chart_setting ) {
					if ( !empty( $chart_setting['target'] ) ) {
						$new_input['chart'][$key] = $chart_setting;
					}
				}
			}
		}
		return $new_input;
	}

	/**
	 * getColors
	 * 色の配列を返す
	 * @param int $count 要素数
	 * @return json
	 */
	protected function getColors( $count ) {
		$color_code = '2ea2cc';
		$colors = array();
		$red = hexdec( substr( $color_code, 0, 2 ) );
		$green = hexdec( substr( $color_code, 2, 2 ) );
		$blue = hexdec( substr( $color_code, 4, 2 ) );
		for ( $i = 0; $i <= $count; $i ++ ) {
			$red += 15;
			if ( $red > 240 ) {
				$red = 240;
			}
			$green += 10;
			if ( $green > 240 ) {
				$green = 240;
			}
			$blue += 5;
			if ( $blue > 240 ) {
				$blue = 240;
			}
			$hred = dechex( $red );
			if ( strlen( $hred ) < 2 ) {
				$hred .= $hred;
			}
			$hgreen = dechex( $green );
			if ( strlen( $hgreen ) < 2 ) {
				$hgreen .= $hgreen;
			}
			$hblue = dechex( $blue );
			if ( strlen( $hblue ) < 2 ) {
				$hblue .= $hblue;
			}
			$colors[] = '#' . $hred . $hgreen . $hblue;
		}
		return json_encode( $colors );
	}

	/**
	 * get_option_data
	 * フォームの設定データを返す
	 * @param string $key 設定データのキー
	 * @return mixed 設定データ
	 */
	protected function get_option_data( $key ) {
		$option = get_option( MWF_Config::NAME );
		if ( is_array( $option ) && isset( $option[$key] ) ) {
			return $option[$key];
		}
	}
}