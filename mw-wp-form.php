<?php
/**
 * Plugin Name: MW WP Form
 * Plugin URI: http://plugins.2inc.org/mw-wp-form/
 * Description: MW WP Form is shortcode base contact form plugin. This plugin have many feature. For example you can use many validation rules, contact data saving, and chart aggregation using saved contact data.
 * Version: 2.9.0
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : September 25, 2012
 * Modified: August 22, 2016
 * Text Domain: mw-wp-form
 * Domain Path: /languages/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
include_once( plugin_dir_path( __FILE__ ) . 'classes/functions.php' );
include_once( plugin_dir_path( __FILE__ ) . 'classes/config.php' );

class MW_WP_Form {

	/**
	 * フォームフィールドの配列
	 * @var array
	 */
	protected $form_fields = array();

	/**
	 * バリデーションルールの配列。順番を固定するために定義が必要
	 * @var array
	 */
	protected $validation_rules = array(
		'akismet_check' => '',
		'noempty'       => '',
		'required'      => '',
		'numeric'       => '',
		'alpha'         => '',
		'alphanumeric'  => '',
		'katakana'      => '',
		'hiragana'      => '',
		'kana'          => '',
		'zip'           => '',
		'tel'           => '',
		'mail'          => '',
		'date'          => '',
		'url'           => '',
		'eq'            => '',
		'between'       => '',
		'minlength'     => '',
		'filetype'      => '',
		'filesize'      => '',
	);

	/**
	 * 日本語の時のみ使用できるバリデーションルール
	 * @var array
	 */
	protected $validation_rules_only_jp = array(
		'MW_WP_Form_Validation_Rule_Zip',
		'MW_WP_Form_Validation_Rule_Tel',
	);

	/**
	 * 日本語の時のみ使用できるフォーム項目
	 * @var array
	 */
	protected $form_fields_only_jp = array(
		'MW_WP_Form_Field_Zip',
		'MW_WP_Form_Field_Tel',
	);

	/**
	 * __construct
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'load_initialize_files' ), 9 );
		add_action( 'plugins_loaded', array( $this, 'initialize' ), 11 );
		// 有効化した時の処理
		register_activation_hook( __FILE__, array( __CLASS__, 'activation' ) );
		// アンインストールした時の処理
		register_uninstall_hook( __FILE__, array( __CLASS__, 'uninstall' ) );
	}

	/**
	 * initialize に必要なファイルをロード
	 */
	public function load_initialize_files() {
		$plugin_dir_path = plugin_dir_path( __FILE__ );
		include_once( $plugin_dir_path . 'classes/controllers/class.controller.php' );
		include_once( $plugin_dir_path . 'classes/controllers/class.admin.php' );
		include_once( $plugin_dir_path . 'classes/controllers/class.admin-list.php' );
		include_once( $plugin_dir_path . 'classes/controllers/class.stores-inquiry-data-form-list.php' );
		include_once( $plugin_dir_path . 'classes/controllers/class.contact-data.php' );
		include_once( $plugin_dir_path . 'classes/controllers/class.contact-data-list.php' );
		include_once( $plugin_dir_path . 'classes/controllers/class.chart.php' );
		include_once( $plugin_dir_path . 'classes/controllers/class.main.php' );
		include_once( $plugin_dir_path . 'classes/models/class.abstract-validation-rule.php' );
		include_once( $plugin_dir_path . 'classes/models/class.csv.php' );
		include_once( $plugin_dir_path . 'classes/models/class.admin.php' );
		include_once( $plugin_dir_path . 'classes/models/class.akismet.php' );
		include_once( $plugin_dir_path . 'classes/models/class.contact-data.php' );
		include_once( $plugin_dir_path . 'classes/models/class.contact-data-setting.php' );
		include_once( $plugin_dir_path . 'classes/models/class.data.php' );
		include_once( $plugin_dir_path . 'classes/models/class.error.php' );
		include_once( $plugin_dir_path . 'classes/models/class.file.php' );
		include_once( $plugin_dir_path . 'classes/models/class.abstract-form-field.php' );
		include_once( $plugin_dir_path . 'classes/models/class.form.php' );
		include_once( $plugin_dir_path . 'classes/models/class.mail.php' );
		include_once( $plugin_dir_path . 'classes/models/class.session.php' );
		include_once( $plugin_dir_path . 'classes/models/class.setting.php' );
		include_once( $plugin_dir_path . 'classes/models/class.validation.php' );
		include_once( $plugin_dir_path . 'classes/models/class.json-parser.php' );
		include_once( $plugin_dir_path . 'classes/services/class.mail-parser.php' );
		include_once( $plugin_dir_path . 'classes/services/class.exec-shortcode.php' );
		include_once( $plugin_dir_path . 'classes/services/class.mail.php' );
		include_once( $plugin_dir_path . 'classes/services/class.redirected.php' );
	}

	/**
	 * initialize
	 */
	public function initialize() {
		load_plugin_textdomain( 'mw-wp-form', false, basename( dirname( __FILE__ ) ) . '/languages' );

		add_action( 'after_setup_theme', array( $this, 'after_setup_theme' ), 11 );
		add_action( 'init', array( $this, 'register_post_type' ) );
	}

	/**
	 * 各管理画面の初期化、もしくはフロント画面の初期化
	 */
	public function after_setup_theme() {
		// フォームフィールドの読み込み、インスタンス化
		$this->instantiate_form_fields();

		$plugin_dir_path = plugin_dir_path( __FILE__ );
		if ( current_user_can( MWF_Config::CAPABILITY ) && is_admin() ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
			add_action( 'admin_menu'           , array( $this, 'admin_menu_for_chart' ) );
			add_action( 'admin_menu'           , array( $this, 'admin_menu_for_contact_data_list' ) );
			add_action( 'admin_init'           , array( $this, 'register_setting' ) );
			add_action( 'current_screen'       , array( $this, 'current_screen' ) );
		} elseif ( !is_admin() ) {
			$validation_rules = $this->get_validation_rules();
			$Controller = new MW_WP_Form_Main_Controller( $validation_rules );
			$Controller->initialize();
		}
	}

	/**
	 * 共通CSSの読み込み
	 */
	public function admin_enqueue_scripts() {
		$url = plugins_url( MWF_Config::NAME );
		wp_enqueue_style( MWF_Config::NAME . '-admin-common', $url . '/css/admin-common.css' );
	}

	/**
	 * グラフページのメニューを追加
	 */
	public function admin_menu_for_chart() {
		$contact_data_post_types = MW_WP_Form_Contact_Data_Setting::get_posts();
		if ( empty( $contact_data_post_types ) ) {
			return;
		}

		add_submenu_page(
			'edit.php?post_type=' . MWF_Config::NAME,
			esc_html__( 'Chart', 'mw-wp-form' ),
			esc_html__( 'Chart', 'mw-wp-form' ),
			MWF_Config::CAPABILITY,
			MWF_Config::NAME . '-chart',
			array( $this, 'display_chart' )
		);
	}

	/**
	 * グラフページを表示
	 */
	public function display_chart() {
		// ここでは画面の呼び出しだけ。
		// JSの読み込みや画面の表示可否判定は current_screen() で行う（ここでは遅い）。
		$Controller = new MW_WP_Form_Chart_Controller();
		$Controller->index();
	}

	/**
	 * 問い合わせデータ閲覧ページのメニューを追加
	 */
	public function admin_menu_for_contact_data_list() {
		$contact_data_post_types = MW_WP_Form_Contact_Data_Setting::get_posts();
		if ( empty( $contact_data_post_types ) ) {
			return;
		}

		add_submenu_page(
			'edit.php?post_type=' . MWF_Config::NAME,
			__( 'Inquiry data', 'mw-wp-form' ),
			__( 'Inquiry data', 'mw-wp-form' ),
			MWF_Config::CAPABILITY,
			MWF_Config::NAME . '-save-data',
			array( $this, 'display_stores_inquiry_data_form_list' )
		);
	}

	/**
	 * 問い合わせデータ閲覧ページを表示
	 */
	public function display_stores_inquiry_data_form_list() {
		$Controller = new MW_WP_Form_Stores_Inquiry_Data_Form_List_Controller();
		$Controller->index();
	}

	/**
	 * グラフページ用の register_setting
	 */
	public function register_setting() {
		$formkey = ( !empty( $_GET['formkey'] ) ) ? $_GET['formkey'] : '';
		if ( !empty( $_POST[MWF_Config::NAME . '-formkey'] ) ) {
			$formkey = $_POST[MWF_Config::NAME . '-formkey'];
		}
		if ( !empty( $formkey ) ) {
			$option_group = MWF_Config::NAME . '-' . 'chart-group';
			register_setting(
				$option_group,
				MWF_Config::NAME . '-chart-' . $formkey,
				array( $this, 'sanitize' )
			);
		}
	}

	/**
	 * グラフページ設定データのサニタイズ
	 *
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
	 * 各画面のコントローラーの呼び出し
	 *
	 * @param WP_Screen $screen
	 */
	public function current_screen( $screen ) {
		$contact_data_post_types = MW_WP_Form_Contact_Data_Setting::get_posts();
		if ( $screen->id === MWF_Config::NAME ) {
			$validation_rules = $this->get_validation_rules();
			$Controller = new MW_WP_Form_Admin_Controller( $validation_rules );
			$Controller->initialize();
		}
		elseif ( $screen->id === 'edit-' . MWF_Config::NAME ) {
			$Controller = new MW_WP_Form_Admin_List_Controller();
			$Controller->initialize();
		}
		elseif ( MWF_Functions::is_contact_data_post_type( $screen->id ) ) {
			$Controller = new MW_WP_Form_Contact_Data_Controller();
			$Controller->initialize();
		}
		elseif ( preg_match( '/^edit-' . MWF_Config::DBDATA . '\d+$/', $screen->id ) ) {
			$Controller = new MW_WP_Form_Contact_Data_List_Controller();
			$Controller->initialize();
		}
		elseif ( $screen->id === MWF_Config::NAME . '_page_' . MWF_Config::NAME . '-chart' ) {
			$Controller = new MW_WP_Form_Chart_Controller();
			$Controller->initialize();
		}
	}

	/**
	 * 管理画面（カスタム投稿タイプ）の設定
	 */
	public function register_post_type() {
		if ( !current_user_can( MWF_Config::CAPABILITY ) && is_admin() ) {
			return;
		}

		// MW WP Form のフォーム設定を管理する投稿タイプ
		register_post_type( MWF_Config::NAME, array(
			'label'    => 'MW WP Form',
			'labels'   => array(
				'name' => 'MW WP Form',
				'singular_name'      => 'MW WP Form',
				'add_new_item'       => __( 'Add New Form', 'mw-wp-form' ),
				'edit_item'          => __( 'Edit Form', 'mw-wp-form' ),
				'new_item'           => __( 'New Form', 'mw-wp-form' ),
				'view_item'          => __( 'View Form', 'mw-wp-form' ),
				'search_items'       => __( 'Search Forms', 'mw-wp-form' ),
				'not_found'          => __( 'No Forms found', 'mw-wp-form' ),
				'not_found_in_trash' => __( 'No Forms found in Trash', 'mw-wp-form' ),
			),
			'capability_type' => 'page',
			'public'          => false,
			'show_ui'         => true,
		) );

		// MW WP Form のデータベースに保存される問い合わせデータを管理する投稿タイプ
		$Admin = new MW_WP_Form_Admin();
		$forms = $Admin->get_forms_using_database();
		foreach ( $forms as $form ) {
			$post_type = MWF_Functions::get_contact_data_post_type_from_form_id( $form->ID );
			register_post_type( $post_type, array(
				'label'  => $form->post_title,
				'labels' => array(
					'name'               => $form->post_title,
					'singular_name'      => $form->post_title,
					'edit_item'          => __( 'Edit ', 'mw-wp-form' ) . ':' . $form->post_title,
					'view_item'          => __( 'View', 'mw-wp-form' ) . ':' . $form->post_title,
					'search_items'       => __( 'Search', 'mw-wp-form' ) . ':' . $form->post_title,
					'not_found'          => __( 'No data found', 'mw-wp-form' ),
					'not_found_in_trash' => __( 'No data found in Trash', 'mw-wp-form' ),
				),
				'capability_type' => 'page',
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => false,
				'supports'        => array( 'title' ),
			) );
		}
	}

	/**
	 * 有効化した時の処理
	 */
	public static function activation() {
	}

	/**
	 * アンインストールした時の処理
	 */
	public static function uninstall() {
		$plugin_dir_path = plugin_dir_path( __FILE__ );
		include_once( $plugin_dir_path . 'classes/models/class.admin.php' );
		$Admin = new MW_WP_Form_Admin();
		$forms = $Admin->get_forms();

		$data_post_ids = array();
		foreach ( $forms as $form ) {
			$data_post_ids[] = $form->ID;
			wp_delete_post( $form->ID, true );
		}

		foreach ( $data_post_ids as $data_post_id ) {
			delete_option( MWF_Config::NAME . '-chart-' . $data_post_id );
			$data_posts = get_posts( array(
				'post_type'      => MWF_Functions::get_contact_data_post_type_from_form_id( $data_post_id ),
				'posts_per_page' => -1,
			) );
			if ( empty( $data_posts ) ) continue;
			foreach ( $data_posts as $data_post ) {
				wp_delete_post( $data_post->ID, true );
			}
		}

		include_once( plugin_dir_path( __FILE__ ) . 'classes/models/class.file.php' );
		$File = new MW_WP_Form_File();
		$File->remove_temp_dir();

		delete_option( MWF_Config::NAME );
	}

	/**
	 * フォームフィールドのインスタンス化。配列にはフックを通して格納する。
	 */
	protected function instantiate_form_fields() {
		$plugin_dir_path = plugin_dir_path( __FILE__ );
		foreach ( $this->form_fields_only_jp as $key => $value ) {
			$this->form_fields_only_jp[$key] = strtolower( $value );
		}
		foreach ( glob( $plugin_dir_path . './classes/form-fields/*.php' ) as $filename ) {
			include_once $filename;
			$class_name = $this->get_class_name_from_form_field_filename( $filename );
			if ( class_exists( $class_name ) ) {
				if ( get_locale() !== 'ja' && in_array( strtolower( $class_name ), $this->form_fields_only_jp ) ) {
					continue;
				}
				new $class_name();
			}
		}
		$this->form_fields = apply_filters( 'mwform_form_fields', $this->form_fields );
	}

	/**
	 * フォーム項目クラスのファイル名からクラス名を取得
	 *
	 * @param string $filename ファイル名
	 * @return string クラス名
	 */
	protected function get_class_name_from_form_field_filename( $filename ) {
		$class_name = preg_replace( '/^class\./', '', basename( $filename, '.php' ) );
		$class_name = str_replace( '-', '_', $class_name );
		$class_name = 'MW_WP_Form_Field_' . $class_name;
		return $class_name;
	}

	/**
	 * バリデーションルールのインスタンス化。配列にはフックを通して格納する。
	 *
	 * @param string $key フォーム識別子
	 * @return $validation_rules バリデーションルールオブジェクトの配列
	 */
	protected function get_validation_rules() {
		$validation_rules = array();
		$plugin_dir_path = plugin_dir_path( __FILE__ );
		foreach ( $this->validation_rules_only_jp as $key => $value ) {
			$this->validation_rules_only_jp[$key] = strtolower( $value );
		}
		foreach ( glob( $plugin_dir_path . './classes/validation-rules/*.php' ) as $filename ) {
			include_once $filename;
			$class_name = $this->get_class_name_from_validation_rule_filename( $filename );
			if ( class_exists( $class_name ) ) {
				if ( get_locale() !== 'ja' && in_array( strtolower( $class_name ), $this->validation_rules_only_jp ) ) {
					continue;
				}
				$instance = new $class_name();
				$this->validation_rules[$instance->getName()] = $instance;
			}
		}
		$this->validation_rules = apply_filters(
			'mwform_validation_rules',
			$this->validation_rules,
			null // 後方互換性のために残してるだけ
		);
		return $this->validation_rules;
	}

	/**
	 * バリデーションルールクラスのファイル名からクラス名を取得
	 *
	 * @param string $filename ファイル名
	 * @return string クラス名
	 */
	protected function get_class_name_from_validation_rule_filename( $filename ) {
		$class_name = preg_replace( '/^class\./', '', basename( $filename, '.php' ) );
		$class_name = str_replace( '-', '_', $class_name );
		$class_name = 'MW_WP_Form_Validation_Rule_' . $class_name;
		return $class_name;
	}
}
$MW_WP_Form = new MW_WP_Form();
