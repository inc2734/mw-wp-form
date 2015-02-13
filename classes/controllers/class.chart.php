<?php
/**
 * Name       : MW WP Form Chart Controller
 * Version    : 1.0.1
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : January 1, 2015
 * Modified   : February 7, 2015
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
		add_action( 'admin_menu'    , array( $this, 'admin_menu' ) );
		add_action( 'admin_init'    , array( $this, 'register_setting' ) );
		add_action( 'current_screen', array( $this, 'current_screen' ) );
	}

	/**
	 * current_screen
	 * @param WP_Screen $screen
	 */
	public function current_screen( $screen ) {
		if ( $screen->id === MWF_Config::NAME . '_page_' . MWF_Config::NAME . '-chart' ) {
			$contact_data_post_types = MW_WP_Form_Contact_Data_Setting::get_posts();
			if ( !in_array( $this->formkey, $contact_data_post_types ) ) {
				exit;
			}
		
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts') );
		}
	}

	/**
	 * admin_menu
	 */
	public function admin_menu() {
		$View = new MW_WP_Form_Chart_View();
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
	 * admin_enqueue_scripts
	 */
	public function admin_enqueue_scripts() {
		global $wp_scripts;
		$ui = $wp_scripts->query( 'jquery-ui-core' );
		wp_enqueue_style(
			'jquery.ui',
			'//ajax.googleapis.com/ajax/libs/jqueryui/' . $ui->ver . '/themes/smoothness/jquery-ui.min.css',
			array( 'jquery' ),
			$ui->ver
		);
		wp_enqueue_script( 'jquery-ui-sortable' );

		$url = plugins_url( MWF_Config::NAME );
		wp_enqueue_style( MWF_Config::NAME . '-admin-repeatable', $url . '/css/admin-repeatable.css' );
		wp_enqueue_script( 'jsapi', 'https://www.google.com/jsapi' );
		wp_enqueue_script(
			MWF_Config::NAME . '-repeatable',
			$url . '/js/mw-wp-form-repeatable.js',
			array( 'jquery' ),
			null,
			true
		);
		wp_enqueue_script(
			MWF_Config::NAME . '-google-chart',
			$url . '/js/mw-wp-form-google-chart.js',
			array( 'jquery' ),
			null,
			true
		);
		wp_enqueue_script(
			MWF_Config::NAME . '-admin-chart',
			$url . '/js/admin-chart.js',
			array( 'jquery', 'jquery-ui-sortable' ),
			null,
			true
		);
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
}