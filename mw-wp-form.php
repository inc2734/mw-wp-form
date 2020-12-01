<?php
/**
 * Plugin Name: MW WP Form
 * Plugin URI: https://plugins.2inc.org/mw-wp-form/
 * Description: MW WP Form is shortcode base contact form plugin. This plugin have many features. For example you can use many validation rules, inquiry data saving, and chart aggregation using saved inquiry data.
 * Version: 4.4.0
 * Author: inc2734
 * Author URI: https://2inc.org
 * Text Domain: mw-wp-form
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package mw-wp-form
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * Include files.
 */
include_once( plugin_dir_path( __FILE__ ) . 'classes/functions.php' );
include_once( plugin_dir_path( __FILE__ ) . 'classes/config.php' );
include_once( plugin_dir_path( __FILE__ ) . 'classes/deprecated.php' );

class MW_WP_Form {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, '_load_initialize_files' ), 9 );
		add_action( 'plugins_loaded', array( $this, '_initialize' ), 11 );

		register_uninstall_hook( __FILE__, array( __CLASS__, '_uninstall' ) );
	}

	/**
	 * Load classes.
	 */
	public function _load_initialize_files() {
		$plugin_dir_path = plugin_dir_path( __FILE__ );
		$includes        = array(
			'/classes/abstract',
			'/classes/controllers',
			'/classes/models',
			'/classes/services',
			'/classes/validation-rules',
			'/classes/form-fields',
		);
		foreach ( $includes as $include ) {
			foreach ( glob( $plugin_dir_path . $include . '/*.php' ) as $file ) {
				require_once( $file );
			}
		}
	}

	/**
	 * Load text domain, The starting point of the process.
	 */
	public function _initialize() {
		load_plugin_textdomain( 'mw-wp-form' );

		add_action( 'after_setup_theme', array( $this, '_after_setup_theme' ), 11 );
		add_action( 'init', array( $this, '_register_post_type' ) );
	}

	/**
	 * Initialize each screens.
	 */
	public function _after_setup_theme() {
		if ( current_user_can( MWF_Config::CAPABILITY ) && is_admin() ) {
			add_action( 'admin_enqueue_scripts', array( $this, '_admin_enqueue_scripts' ) );
			add_action( 'admin_menu', array( $this, '_admin_menu_for_chart' ) );
			add_action( 'admin_menu', array( $this, '_admin_menu_for_inquiry_data_list' ) );
			add_action( 'current_screen', array( $this, '_current_screen' ) );
		} elseif ( ! is_admin() ) {
			new MW_WP_Form_Main_Controller();
		}
	}

	/**
	 * Enqueue assets.
	 */
	public function _admin_enqueue_scripts() {
		$url = plugins_url( MWF_Config::NAME );
		wp_enqueue_style( MWF_Config::NAME . '-admin-common', $url . '/css/admin-common.css' );
	}

	/**
	 * Add admin menu for chart.
	 */
	public function _admin_menu_for_chart() {
		$contact_data_post_types = MW_WP_Form_Contact_Data_Setting::get_form_post_types();
		if ( empty( $contact_data_post_types ) ) {
			return;
		}

		add_submenu_page(
			'edit.php?post_type=' . MWF_Config::NAME,
			esc_html__( 'Chart', 'mw-wp-form' ),
			esc_html__( 'Chart', 'mw-wp-form' ),
			MWF_Config::CAPABILITY,
			MWF_Config::NAME . '-chart',
			'__return_false'
		);
	}

	/**
	 * Add admin menu for saved inquiry data.
	 */
	public function _admin_menu_for_inquiry_data_list() {
		$contact_data_post_types = MW_WP_Form_Contact_Data_Setting::get_form_post_types();
		if ( empty( $contact_data_post_types ) ) {
			return;
		}

		add_submenu_page(
			'edit.php?post_type=' . MWF_Config::NAME,
			__( 'Inquiry data', 'mw-wp-form' ),
			__( 'Inquiry data', 'mw-wp-form' ),
			MWF_Config::CAPABILITY,
			MWF_Config::NAME . '-save-data',
			'__return_false'
		);
	}

	/**
	 * Front controller.
	 *
	 * @param WP_Screen $screen WP_Screen object.
	 */
	public function _current_screen( $screen ) {
		if ( MWF_Config::NAME === $screen->id ) {
			new MW_WP_Form_Admin_Controller();
		} elseif ( 'edit-' . MWF_Config::NAME === $screen->id ) {
			new MW_WP_Form_Admin_List_Controller();
		} elseif ( MWF_Functions::is_contact_data_post_type( $screen->id ) ) {
			new MW_WP_Form_Contact_Data_Controller();
		} elseif ( preg_match( '/^edit-' . MWF_Config::DBDATA . '\d+$/', $screen->id ) ) {
			new MW_WP_Form_Contact_Data_List_Controller();
		} elseif ( MWF_Config::NAME . '_page_' . MWF_Config::NAME . '-chart' === $screen->id ) {
			new MW_WP_Form_Chart_Controller();
		} elseif ( MWF_Config::NAME . '_page_' . MWF_Config::NAME . '-save-data' === $screen->id ) {
			new MW_WP_Form_Stores_Inquiry_Data_Form_List_Controller();
		}
	}

	/**
	 * Register post types for MW WP Form and inquiry data.
	 */
	public function _register_post_type() {
		if ( ! current_user_can( MWF_Config::CAPABILITY ) && is_admin() ) {
			return;
		}

		// MW WP Form のフォーム設定を管理する投稿タイプ
		register_post_type(
			MWF_Config::NAME,
			array(
				'label'           => 'MW WP Form',
				'labels'          => array(
					'name'               => 'MW WP Form',
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
			)
		);

		$admin = new MW_WP_Form_Admin();
		$forms = $admin->get_forms_using_database();
		foreach ( $forms as $form ) {
			$post_type = MWF_Functions::get_contact_data_post_type_from_form_id( $form->ID );
			register_post_type(
				$post_type,
				array(
					'label'           => $form->post_title,
					'labels'          => array(
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
				)
			);
		}
	}

	/**
	 * Uninstall processes.
	 */
	public static function _uninstall() {
		$plugin_dir_path = plugin_dir_path( __FILE__ );
		include_once( $plugin_dir_path . 'classes/models/class.admin.php' );
		include_once( $plugin_dir_path . 'classes/models/class.file.php' );

		$admin = new MW_WP_Form_Admin();
		$forms = $admin->get_forms();

		$data_post_ids = array();
		foreach ( $forms as $form ) {
			$data_post_ids[] = $form->ID;
			wp_delete_post( $form->ID, true );
		}

		foreach ( $data_post_ids as $data_post_id ) {
			delete_option( MWF_Config::NAME . '-chart-' . $data_post_id );

			$data_posts = get_posts(
				array(
					'post_type'      => MWF_Functions::get_contact_data_post_type_from_form_id( $data_post_id ),
					'posts_per_page' => -1,
				)
			);
			if ( empty( $data_posts ) ) {
				continue;
			}

			foreach ( $data_posts as $data_post ) {
				wp_delete_post( $data_post->ID, true );
			}
		}

		$file = new MW_WP_Form_File();
		$file->remove_temp_dir();

		delete_option( MWF_Config::NAME );
	}
}

$mw_wp_form = new MW_WP_Form();
