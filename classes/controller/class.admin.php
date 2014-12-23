<?php
/**
 * Name: MW WP Form Admin Controller
 * Version: 1.0.0
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : December 23, 2014
 * Modified:
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Admin_Controller {

	/**
	 * __controller
	 */
	public function __construct() {
		$plugin_dir_path = plugin_dir_path( __FILE__ );
		include_once( $plugin_dir_path . '../service/class.admin.php' );
	}

	/**
	 * initialize
	 */
	public function initialize() {
		$Admin_Service = new MW_WP_Form_Admin_Service();

		$Data = new MW_WP_Form_Contact_Data_Page();
		$Data->initialize();
		$Chart = new MW_WP_Form_Chart_Page();
		$Chart->initialize();
		
		$Admin = new MW_WP_Form_Admin_Page();
		$Admin_Service->set_admin_page( $Admin );

		// フォームフィールドの読み込み、インスタンス化
		$Admin_Service->instantiate_form_fields();

		// バリデーションルールの読み込み、インスタンス化
		$Admin_Service->set_validation_rules();
		$Admin_Service->set_validation_rules_in_admin_page();
	}
}
