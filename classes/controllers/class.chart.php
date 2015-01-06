<?php
/**
 * Name       : MW WP Form Chart Controller
 * Version    : 1.0.0
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : January 1, 2015
 * Modified   :
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Chart_Controller {

	/**
	 * $formkey
	 * URL引数で渡される、そのグラフに使う投稿タイプ名
	 * @var string
	 */
	protected $formkey;

	/**
	 * $postdata
	 * フォームの設定データ
	 * @var array
	 */
	protected $postdata = array();

	/**
	 * $option_group
	 * Settings API グループ名
	 * @var string
	 */
	protected $option_group;

	/**
	 * __construct
	 */
	public function __construct() {
		$this->option_group = MWF_Config::NAME . '-' . 'chart-group';
		if ( !empty( $_GET['formkey'] ) ) {
			$this->formkey = $_GET['formkey'];
		}
	}

	/**
	 * initialize
	 */
	public function initialize() {
		add_action( 'admin_menu'           , array( $this, 'admin_menu' ) );
		add_action( 'admin_init'           , array( $this, 'register_setting' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts') );
		add_action( 'admin_print_styles'   , array( $this, 'admin_print_styles' ) );
	}

	/**
	 * admin_menu
	 */
	public function admin_menu() {
		$View = new MW_WP_Form_Chart_View();
		$View->set( 'is_chart' , $this->is_chart() );
		$View->set( 'post_type', $this->formkey );
		$View->set( 'option_group', $this->option_group );
		add_submenu_page(
			'edit.php?post_type=' . MWF_Config::NAME,
			esc_html__( 'Chart', MWF_Config::DOMAIN ),
			esc_html__( 'Chart', MWF_Config::DOMAIN ),
			MWF_Config::CAPABILITY,
			MWF_Config::NAME . '-chart',
			array( $View, 'index' )
		);
	}

	/**
	 * admin_print_styles
	 */
	public function admin_print_styles() {
		$View = new MW_WP_Form_Chart_View();
		$View->admin_print_styles();
	}

	/**
	 * admin_enqueue_scripts
	 */
	public function admin_enqueue_scripts() {
		if ( !$this->is_chart() ) {
			return;
		}
		global $wp_scripts;
		$ui = $wp_scripts->query( 'jquery-ui-core' );
		wp_enqueue_style(
			'jquery.ui',
			'//ajax.googleapis.com/ajax/libs/jqueryui/' . $ui->ver . '/themes/smoothness/jquery-ui.min.css',
			array( 'jquery' ),
			$ui->ver
		);
		wp_enqueue_script( 'jquery-ui-sortable' );

		$url = plugin_dir_url( __FILE__ );

		wp_register_script( 'jsapi', 'https://www.google.com/jsapi' );
		wp_enqueue_script( 'jsapi' );

		wp_register_script(
			MWF_Config::NAME . '-repeatable',
			$url . '../../js/mw-wp-form-repeatable.js',
			array( 'jquery' ),
			null,
			true
		);
		wp_enqueue_script( MWF_Config::NAME . '-repeatable' );

		wp_register_script(
			MWF_Config::NAME . '-google-chart',
			$url . '../../js/mw-wp-form-google-chart.js',
			array( 'jquery' ),
			null,
			true
		);
		wp_enqueue_script( MWF_Config::NAME . '-google-chart' );

		wp_register_script(
			MWF_Config::NAME . '-admin-chart',
			$url . '../../js/admin-chart.js',
			array( 'jquery', 'jquery-ui-sortable' ),
			null,
			true
		);
		wp_enqueue_script( MWF_Config::NAME . '-admin-chart' );
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
	 * is_chart
	 * @return bool
	 */
	protected function is_chart() {
		if ( isset( $_GET['page'] ) && $_GET['page'] === 'mw-wp-form-chart' && $this->formkey ) {
			return true;
		}
		return false;
	}
}