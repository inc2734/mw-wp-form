<?php
/**
 * Name       : MW WP Form Contact Data Controller
 * Version    : 1.0.0
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : December 31, 2014
 * Modified   : 
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Contact_Data_Controller {

	/**
	 * $contact_data_post_types
	 * @var array
	 */
	protected $contact_data_post_types = array();

	/**
	 * initialize
	 */
	public function initialize() {
		$this->contact_data_post_types = MW_WP_Form_Contact_Data_Setting::get_posts();
		$Contact_Data = new MW_WP_Form_Contact_Data();
		add_action( 'admin_menu'           , array( $this, 'admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'add_meta_boxes'       , array( $this, 'add_meta_boxes' ) );
		add_action( 'admin_print_styles'   , array( $this, 'admin_print_styles' ) );
		add_action( 'edit_form_top'        , array( $this, 'edit_form_top' ) );
		add_action( 'save_post'            , array( $Contact_Data, 'save_post' ) );
	}

	/**
	 * admin_menu
	 * 問い合わせデータ閲覧ページへのメニューを追加
	 */
	public function admin_menu() {
		$View = new MW_WP_Form_Contact_Data_View();
		$View->set( 'contact_data_post_types', $this->contact_data_post_types );
		add_submenu_page(
			'edit.php?post_type=' . MWF_Config::NAME,
			__( 'Inquiry data', MWF_Config::DOMAIN ),  // ページタイトル
			__( 'Inquiry data', MWF_Config::DOMAIN ),  // メニュー名
			MWF_Config::CAPABILITY, // 権限
			MWF_Config::NAME . '-save-data', // 画面のパス
			array( $View, 'index' ) // 表示用の関数
		);
	}

	/**
	 * admin_enqueue_scripts
	 * 本当は css, js のロードだけしたいけど、ここからしか post_id がとれないので渋々…
	 */
	public function admin_enqueue_scripts() {
		if ( !$this->is_contact_data_post_type() ) {
			return;
		}
		$url = plugin_dir_url( __FILE__ );
		wp_enqueue_style( MWF_Config::NAME . '-admin', $url . '../../css/admin.css' );
		wp_enqueue_script( MWF_Config::NAME . '-admin-data', $url . '../../js/admin-data.js' );
	}

	/**
	 * admin_print_styles
	 * 詳細画面で新規追加のリンクを消す
	 */
	public function admin_print_styles() {
		if ( !$this->is_contact_data_post_type() ) {
			return;
		}
		$View = new MW_WP_Form_Contact_Data_View();
		$View->admin_print_styles_for_detail();
	}

	/**
	 * add_meta_boxes
	 */
	public function add_meta_boxes() {
		if ( !$this->is_contact_data_post_type() ) {
			return;
		}
		$post_type = get_post_type();
		$View = new MW_WP_Form_Contact_Data_View();
		$View->set( 'post_type', $post_type );
		$View->set( 'Contact_Data_Setting', new MW_WP_Form_Contact_Data_Setting( get_the_ID() ) );
		add_meta_box(
			substr( MWF_Config::CONTACT_DATA_NAME, 1 ) . '_custom_fields',
			__( 'Custom Fields', MWF_Config::DOMAIN ),
			array( $View, 'detail' ),
			$post_type
		);
	}

	/**
	 * edit_form_top 
	 * 問い合わせデータ詳細画面で一覧に戻るリンクを表示
	 * @param object $post
	 */
	public function edit_form_top( $post ) {
		if ( !$this->is_contact_data_post_type() ) {
			return;
		}
		$post_type = get_post_type();
		$link = admin_url( '/edit.php?post_type=' . $post_type );
		$View = new MW_WP_Form_Contact_Data_View();
		$View->set( 'link', $link );
		$View->returning_link();
	}

	/**
	 * is_contact_data_post_type
	 */
	protected function is_contact_data_post_type() {
		$post_type = get_post_type();
		if ( is_array( $this->contact_data_post_types ) &&
			 in_array( $post_type, $this->contact_data_post_types ) ) {
			return true;
		}
		return false;
	}
}