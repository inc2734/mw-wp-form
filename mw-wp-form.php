<?php
/**
 * Plugin Name: MW WP Form
 * Plugin URI: http://plugins.2inc.org/mw-wp-form/
 * Description: MW WP Form is shortcode base contact form plugin. This plugin have many feature. For example you can use many validation rules, contact data saving, and chart aggregation using saved contact data.
 * Version: 2.2.1
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : September 25, 2012
 * Modified: January 14, 2015
 * Text Domain: mw-wp-form
 * Domain Path: /languages/
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
include_once( plugin_dir_path( __FILE__ ) . 'classes/functions.php' );
include_once( plugin_dir_path( __FILE__ ) . 'classes/config.php' );

class MW_WP_Form {

	/**
	 * form_fields
	 * フォームフィールドの配列
	 * @var array
	 */
	protected $form_fields = array();

	/**
	 * $validation_rules
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
	 * $validation_rules_only_jp
	 * 日本語の時のみ使用できるバリデーションルール
	 * @var array
	 */
	protected $validation_rules_only_jp = array(
		'MW_WP_Form_Validation_Rule_Zip',
		'MW_WP_Form_Validation_Rule_Tel',
	);

	/**
	 * $form_fields_only_jp
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
	 * load_initialize_files
	 * initialize に必要なファイルをロード
	 */
	public function load_initialize_files() {
		$plugin_dir_path = plugin_dir_path( __FILE__ );
		include_once( $plugin_dir_path . 'classes/controllers/class.admin.php' );
		include_once( $plugin_dir_path . 'classes/controllers/class.admin-list.php' );
		include_once( $plugin_dir_path . 'classes/controllers/class.contact-data.php' );
		include_once( $plugin_dir_path . 'classes/controllers/class.contact-data-list.php' );
		include_once( $plugin_dir_path . 'classes/controllers/class.chart.php' );
		include_once( $plugin_dir_path . 'classes/controllers/class.main.php' );
		include_once( $plugin_dir_path . 'classes/models/class.abstract-validation-rule.php' );
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
		include_once( $plugin_dir_path . 'classes/services/class.exec-shortcode.php' );
		include_once( $plugin_dir_path . 'classes/services/class.mail.php' );
		include_once( $plugin_dir_path . 'classes/services/class.redirected.php' );
		include_once( $plugin_dir_path . 'classes/views/class.view.php' );
		include_once( $plugin_dir_path . 'classes/views/class.admin.php' );
		include_once( $plugin_dir_path . 'classes/views/class.admin-list.php' );
		include_once( $plugin_dir_path . 'classes/views/class.chart.php' );
		include_once( $plugin_dir_path . 'classes/views/class.main.php' );
		include_once( $plugin_dir_path . 'classes/views/class.contact-data.php' );
		include_once( $plugin_dir_path . 'classes/views/class.contact-data-list.php' );
	}

	/**
	 * initialize
	 */
	public function initialize() {
		load_plugin_textdomain( MWF_Config::DOMAIN, false, basename( dirname( __FILE__ ) ) . '/languages' );

		add_action( 'init', array( $this, 'register_post_type' ) );

		// フォームフィールドの読み込み、インスタンス化
		$this->instantiate_form_fields();

		// バリデーションルールの読み込み、インスタンス化
		$validation_rules = $this->get_validation_rules();

		$plugin_dir_path = plugin_dir_path( __FILE__ );
		if ( is_admin() ) {
			$Controller = new MW_WP_Form_Admin_Controller( $validation_rules );
			$Controller->initialize();

			$Controller = new MW_WP_Form_Admin_List_Controller();
			$Controller->initialize();

			$Controller = new MW_WP_Form_Contact_Data_Controller();
			$Controller->initialize();

			$Controller = new MW_WP_Form_Contact_Data_List_Controller();
			$Controller->initialize();

			$Controller = new MW_WP_Form_Chart_Controller();
			$Controller->initialize();
		} else {
			$Controller = new MW_WP_Form_Main_Controller( $validation_rules );
			$Controller->initialize();
		}
	}

	/**
	 * register_post_type
	 * 管理画面（カスタム投稿タイプ）の設定
	 */
	public function register_post_type() {
		// MW WP Form のフォーム設定を管理する投稿タイプ
		register_post_type( MWF_Config::NAME, array(
			'label'    => 'MW WP Form',
			'labels'   => array(
				'name' => 'MW WP Form',
				'singular_name'      => 'MW WP Form',
				'add_new_item'       => __( 'Add New Form', MWF_Config::DOMAIN ),
				'edit_item'          => __( 'Edit Form', MWF_Config::DOMAIN ),
				'new_item'           => __( 'New Form', MWF_Config::DOMAIN ),
				'view_item'          => __( 'View Form', MWF_Config::DOMAIN ),
				'search_items'       => __( 'Search Forms', MWF_Config::DOMAIN ),
				'not_found'          => __( 'No Forms found', MWF_Config::DOMAIN ),
				'not_found_in_trash' => __( 'No Forms found in Trash', MWF_Config::DOMAIN ),
			),
			'capability_type' => 'page',
			'public'          => false,
			'show_ui'         => true,
		) );

		// MW WP Form のデータベースに保存される問い合わせデータを管理する投稿タイプ
		$Admin = new MW_WP_Form_Admin();
		$forms = $Admin->get_forms();
		foreach ( $forms as $form ) {
			$Setting = new MW_WP_Form_Setting( $form->ID );
			if ( !$Setting->get( 'usedb' ) ) {
				continue;
			}

			$post_type = MWF_Config::DBDATA . $form->ID;
			register_post_type( $post_type, array(
				'label'  => $form->post_title,
				'labels' => array(
					'name'               => $form->post_title,
					'singular_name'      => $form->post_title,
					'edit_item'          => __( 'Edit ', MWF_Config::DOMAIN ) . ':' . $form->post_title,
					'view_item'          => __( 'View', MWF_Config::DOMAIN ) . ':' . $form->post_title,
					'search_items'       => __( 'Search', MWF_Config::DOMAIN ) . ':' . $form->post_title,
					'not_found'          => __( 'No data found', MWF_Config::DOMAIN ),
					'not_found_in_trash' => __( 'No data found in Trash', MWF_Config::DOMAIN ),
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
	 * activation
	 * 有効化した時の処理
	 */
	public static function activation() {
	}

	/**
	 * uninstall
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
				'post_type'      => MWF_Config::DBDATA . $data_post_id,
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
	 * instantiate_form_fields
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
	 * get_class_name_from_form_field_filename
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
	 * get_validation_rules
	 * バリデーションルールのインスタンス化。配列にはフックを通して格納する。
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
	 * get_class_name_from_validation_rule_filename
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
