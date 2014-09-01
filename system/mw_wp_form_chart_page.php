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
	 * formkey
	 */
	private $formkey;

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
		add_action( 'admin_print_styles', array( $this, 'admin_print_styles' ) );
		$this->option_group = MWF_Config::NAME . '-' . 'chart-group';
		if ( !empty( $_GET['formkey'] ) ) {
			$this->formkey = $_GET['formkey'];
		}
	}

	/**
	 * admin_print_styles
	 */
	public function admin_print_styles() {
		?>
		<style>
		#menu-posts-mw-wp-form .wp-submenu li:last-child {
			display: none;
		}
		</style>
		<?php
	}

	/**
	 * add_menu
	 */
	public function add_menu() {
		$submenu_page = add_submenu_page(
			'edit.php?post_type=' . MWF_Config::NAME,
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
		global $wp_scripts;
		$ui = $wp_scripts->query( 'jquery-ui-core' );
		wp_enqueue_style( 'jquery.ui', '//ajax.googleapis.com/ajax/libs/jqueryui/' . $ui->ver . '/themes/smoothness/jquery-ui.min.css', array( 'jquery' ), $ui->ver );
		wp_enqueue_script( 'jquery-ui-sortable' );

		$url = plugin_dir_url( __FILE__ );

		wp_register_script( 'jsapi', 'https://www.google.com/jsapi' );
		wp_enqueue_script( 'jsapi' );

		wp_register_script(
			MWF_Config::NAME . '-repeatable',
			$url . '../js/mw-wp-form-repeatable.js',
			array( 'jquery' ),
			null,
			true
		);
		wp_enqueue_script( MWF_Config::NAME . '-repeatable' );

		wp_register_script(
			MWF_Config::NAME . '-google-chart',
			$url . '../js/mw-wp-form-google-chart.js',
			array( 'jquery' ),
			null,
			true
		);
		wp_enqueue_script( MWF_Config::NAME . '-google-chart' );

		wp_register_script(
			MWF_Config::NAME . '-admin-chart',
			$url . '../js/admin-chart.js',
			array( 'jquery', 'jquery-ui-sortable' ),
			null,
			true
		);
		wp_enqueue_script( MWF_Config::NAME . '-admin-chart' );
	}

	/**
	 * display
	 */
	public function display() {
		if ( !empty( $this->formkey ) ) {
			$this->formkey = $_GET['formkey'];
			$form_posts = get_posts( array(
				'post_type' => $this->formkey,
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
	<h2><?php esc_html_e( 'Chart', MWF_Config::DOMAIN ); ?> : <?php echo esc_html( get_the_title( preg_replace( '/^(.+_)(\d+)$/', '$2', $this->formkey ) ) ); ?></h2>
	<form method="post" action="options.php">
		<?php
		settings_fields( $this->option_group );
		do_settings_sections( $this->option_group );
		?>
		<div id="<?php echo esc_attr( MWF_Config::NAME . '_chart' ); ?>" class="postbox">
			<div class="inside">
				<b class="add-btn"><?php esc_html_e( 'Add Chart', MWF_Config::DOMAIN ); ?></b>
				<div class="repeatable-boxes">
					<?php foreach ( $postdata as $key => $value ) :  ?>
					<div class="repeatable-box"<?php if ( $key === 0 ) : ?> style="display:none"<?php endif; ?>>
						<div class="remove-btn"><b>×</b></div>
						<div class="open-btn"><span><?php echo esc_attr( $value['target'] ); ?></span><b>▼</b></div>
						<div class="repeatable-box-content">
							<?php esc_html_e( 'Item that create chart', MWF_Config::DOMAIN ); ?>
							<select class="targetKey" name="<?php echo esc_attr( sprintf( '%s-chart-%s[chart][%s][target]', MWF_Config::NAME, $this->formkey, $key ) ); ?>">
								<option value=""><?php esc_html_e( 'Select this.', MWF_Config::DOMAIN ); ?></option>
								<?php foreach ( $custom_keys as $custom_key_name => $custom_key_value ) : ?>
								<option value="<?php echo esc_attr( $custom_key_name ); ?>" <?php selected( $value['target'], $custom_key_name ); ?>><?php echo esc_html( $custom_key_name ); ?></option>
								<?php endforeach; ?>
							</select>
							<br />
							<?php esc_html_e( 'Chart type', MWF_Config::DOMAIN ); ?>
							<select name="<?php echo esc_attr( sprintf( '%s-chart-%s[chart][%s][chart]', MWF_Config::NAME, $this->formkey, $key ) ); ?>">
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
							<input type="text" name="<?php echo esc_attr( sprintf( '%s-chart-%s[chart][%s][separator]', MWF_Config::NAME, $this->formkey, $key ) ); ?>" value="<?php echo esc_attr( $value['separator'] ); ?>" size="5" />
						<!-- end .repeatable-box-content --></div>
					<!-- end .repeatable-box --></div>
					<?php endforeach; ?>
				<!-- end .repeatable-boxes --></div>
				<input type="hidden" name="<?php echo esc_attr( sprintf( '%s-formkey', MWF_Config::NAME ) ); ?>" value="<?php echo esc_attr( $this->formkey ); ?>" />
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
			'<h3>%s <span style="font-weight:normal;font-size:14px">( %s: %d )</span></h3><div class="%s" style="width: 100%%; max-width: 800px"></div>',
			esc_html( $chart['target'] ),
			esc_html__( 'The number of inquiries', MWF_Config::DOMAIN ),
			count( $form_posts ),
			esc_attr( MWF_Config::NAME . '-chart-div-' . $postdata_key )
		);
	}

	$chart_data = array();
	foreach ( $postdata as $postdata_key => $chart ) {
		$data = array();
		$raw_data = array();
		foreach ( $custom_keys[$chart['target']] as $item => $values ) {
			if ( $chart['separator'] && strstr( $item, $chart['separator'] ) ) {
				$item = explode( $chart['separator'] , $item );
			}
			if ( is_array( $item ) ) {
				foreach ( $item as $_item ) {
					if ( $_item === '' ) {
						$_item = '(Empty)';
					}
					if ( empty( $raw_data[$_item] ) ) {
						$raw_data[$_item] = count( $values );
					} else {
						$raw_data[$_item] += count( $values );
					}
				}
			} else {
				if ( $item === '' ) {
					$item = '(Empty)';
				}
				if ( empty( $raw_data[$item] ) ) {
					$raw_data[$item] = count( $values );
				} else {
					$raw_data[$item] += count( $values );
				}
			}
		}
		$data[] = array( '', '' );
		foreach ( $raw_data as $raw_data_key => $raw_data_value ) {
			if ( $chart['chart'] === 'bar' ) {
				$value = $raw_data_value / count( $form_posts );
			} else {
				$value = $raw_data_value;
			}
			$data[] = array(
				$raw_data_key,
				$value,
			);
		}
		$chart_data[$postdata_key] = array(
			'chart' => $chart['chart'],
			'data'  => $data,
		);
	}
	?>
	<script>
	google.load( 'visualization', 1, { packages:['corechart'] } );
	google.setOnLoadCallback( mwformDrawCharts );
	function mwformDrawCharts() {
		jQuery( function( $ ) {
			<?php foreach ( $chart_data as $postdata_key => $chart ) : ?>
			$( '.<?php echo esc_js( MWF_Config::NAME . '-chart-div-' . $postdata_key ); ?>' )
				.mw_wp_form_google_chart( {
					chart: <?php echo json_encode( $chart['chart'] ); ?>,
					data : <?php echo json_encode( $chart['data'] ); ?>
				} );
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
		if ( !empty( $this->formkey ) ) {
			$formkey = $this->formkey;
		} elseif ( !empty( $_POST[MWF_Config::NAME . '-formkey'] ) ) {
			$formkey = $_POST[MWF_Config::NAME . '-formkey'];
		}
		if ( !empty( $formkey ) ) {
			register_setting(
				$this->option_group,
				MWF_Config::NAME . '-chart-' . $formkey,
				array( $this, 'sanitize' )
			);
		}
	}

	/**
	 * sanitize
	 * @param array $input フォームから送信されたデータ
	 * @return array
	 */
	public function sanitize( $input ) {
		$new_input = array();
		if ( is_array( $input ) && isset( $input['chart'] ) && is_array( $input['chart'] ) ) {
			foreach ( $input['chart'] as $key => $value ) {
				if ( !empty( $value['target'] ) ) {
					$new_input['chart'][$key] = $value;
				}
			}
		}
		return $new_input;
	}

	/**
	 * get_option_data
	 * フォームの設定データを返す
	 * @param string $key 設定データのキー
	 * @return mixed 設定データ
	 */
	protected function get_option_data( $key ) {
		$option = get_option( MWF_Config::NAME . '-chart-' . $this->formkey );
		if ( is_array( $option ) && isset( $option[$key] ) ) {
			return $option[$key];
		}
	}
}