<?php
/**
 * Plugin Name: MW WP Form
 * Plugin URI: http://plugins.2inc.org/mw-wp-form/
 * Description: MW WP Form can create mail form with a confirmation screen.
 * Version: 2.1.4
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : September 25, 2012
 * Modified: December 6, 2014
 * Text Domain: mw-wp-form
 * Domain Path: /languages/
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
include_once( plugin_dir_path( __FILE__ ) . 'classes/functions.php' );
include_once( plugin_dir_path( __FILE__ ) . 'classes/config.php' );
$MW_WP_Form = new MW_WP_Form();
class MW_WP_Form {

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
		include_once( $plugin_dir_path . 'classes/service/class.service.php' );
		include_once( $plugin_dir_path . 'classes/model/class.form-fields.php' );
		include_once( $plugin_dir_path . 'classes/model/class.session.php' );
		include_once( $plugin_dir_path . 'classes/model/class.validation-rule.php' );
		include_once( $plugin_dir_path . 'classes/model/class.admin.php' );
		include_once( $plugin_dir_path . 'classes/model/class.chart.php' );
		include_once( $plugin_dir_path . 'classes/model/class.contact-data.php' );
		include_once( $plugin_dir_path . 'classes/model/class.data.php' );
		include_once( $plugin_dir_path . 'classes/model/class.akismet.php' );
		include_once( $plugin_dir_path . 'classes/model/class.error.php' );
		include_once( $plugin_dir_path . 'classes/model/class.form.php' );
		include_once( $plugin_dir_path . 'classes/model/class.mail.php' );
		include_once( $plugin_dir_path . 'classes/model/class.validation.php' );
		include_once( $plugin_dir_path . 'classes/model/class.file.php' );
	}

	/**
	 * initialize
	 */
	public function initialize() {
		load_plugin_textdomain( MWF_Config::DOMAIN, false, basename( dirname( __FILE__ ) ) . '/languages' );

		add_action( 'init', array( $this, 'register_post_type' ) );

		$plugin_dir_path = plugin_dir_path( __FILE__ );
		if ( is_admin() ) {
			include_once( $plugin_dir_path . 'classes/controller/class.admin.php' );
			$Controller = new MW_WP_Form_Admin_Controller();
		} else {
			include_once( $plugin_dir_path . 'classes/controller/class.main.php' );
			$Controller = new MW_WP_Form_Main_Controller();
		}
		$Controller->initialize();
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
		$Admin = new MW_WP_Form_Admin_page();
		$forms = $Admin->get_forms();
		foreach ( $forms as $form ) {
			$post_meta = $Admin->get_settings( $form->ID );
			if ( empty( $post_meta['usedb'] ) ) {
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
		$Admin = new MW_WP_Form_Admin_page();
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

		include_once( plugin_dir_path( __FILE__ ) . 'classes/model/mw_wp_form_file.php' );
		$File = new MW_WP_Form_File();
		$File->removeTempDir();

		delete_option( MWF_Config::NAME );
	}
}
