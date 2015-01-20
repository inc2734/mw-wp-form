<?php
/**
 * Name       : MW WP Form Admin Controller
 * Version    : 1.0.1
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : December 31, 2014
 * Modified   : January 20, 2015
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Admin_Controller {

	/**
	 * $validation_rules
	 * バリデーションルールの配列
	 * @var array
	 */
	protected $validation_rules = array();

	/**
	 * __construct
	 * @param array $validation_rules
	 */
	public function __construct( array $validation_rules ) {
		foreach ( $validation_rules as $validation_name => $instance ) {
			if ( is_callable( array( $instance, 'admin' ) ) ) {
				$this->validation_rules[$instance->getName()] = $instance;
			}
		}
	}

	/**
	 * initialize
	 */
	public function initialize() {
		$View  = new MW_WP_Form_Admin_View();
		$Admin = new MW_WP_Form_Admin();
		add_action( 'add_meta_boxes'            , array( $this , 'add_meta_boxes' ) );
		add_action( 'current_screen'            , array( $this , 'current_screen' ) );
		add_filter( 'default_content'           , array( $this , 'default_content' ) );
		add_action( 'media_buttons'             , array( $View , 'tag_generator' ) );
		add_action( 'admin_enqueue_scripts'     , array( $this , 'admin_enqueue_scripts' ) );
		add_action( 'admin_print_footer_scripts', array( $View , 'quicktag' ) );
		add_action( 'save_post'                 , array( $Admin, 'save_post' ) );
	}

	/**
	 * add_meta_boxes
	 */
	public function add_meta_boxes() {
		$post_type = get_post_type();
		if ( MWF_Config::NAME !== $post_type ) {
			return;
		}

		$View = new MW_WP_Form_Admin_View();

		// 完了画面内容
		$View->set( 'complete_message', $this->get_option( 'complete_message' ) );
		add_meta_box(
			MWF_Config::NAME . '_complete_message_metabox',
			__( 'Complete Message', MWF_Config::DOMAIN ),
			array( $View, 'complete_message' ),
			MWF_Config::NAME, 'normal'
		);

		// 入力画面URL
		$View->set( 'input_url'           , $this->get_option( 'input_url' ) );
		$View->set( 'confirmation_url'    , $this->get_option( 'confirmation_url' ) );
		$View->set( 'complete_url'        , $this->get_option( 'complete_url' ) );
		$View->set( 'validation_error_url', $this->get_option( 'validation_error_url' ) );
		add_meta_box(
			MWF_Config::NAME . '_url',
			__( 'URL Options', MWF_Config::DOMAIN ),
			array( $View, 'url' ),
			MWF_Config::NAME, 'normal'
		);

		// バリデーション
		$View->set( 'validation'      , $this->get_option( 'validation' ) );
		$View->set( 'validation_rules', $this->validation_rules );
		add_meta_box(
			MWF_Config::NAME . '_validation',
			__( 'Validation Rule', MWF_Config::DOMAIN ),
			array( $View, 'validation_rule' ),
			MWF_Config::NAME, 'normal'
		);

		// アドオン
		add_meta_box(
			MWF_Config::NAME . '_addon',
			__( 'Add-ons', MWF_Config::DOMAIN ),
			array( $View, 'add_ons' ),
			MWF_Config::NAME, 'side'
		);

		// フォーム識別子
		$View->set( 'post_id', get_the_ID() );
		add_meta_box(
			MWF_Config::NAME . '_formkey',
			__( 'Form Key', MWF_Config::DOMAIN ),
			array( $View, 'form_key' ),
			MWF_Config::NAME, 'side'
		);

		// 自動返信メール設定
		$View->set( 'mail_subject'         , $this->get_option( 'mail_subject' ) );
		$View->set( 'mail_sender'          , $this->get_option( 'mail_sender' ) );
		$View->set( 'mail_from'            , $this->get_option( 'mail_from' ) );
		$View->set( 'mail_content'         , $this->get_option( 'mail_content' ) );
		$View->set( 'automatic_reply_email', $this->get_option( 'automatic_reply_email' ) );
		add_meta_box(
			MWF_Config::NAME . '_mail',
			__( 'Automatic Reply Email Options', MWF_Config::DOMAIN ),
			array( $View, 'mail_options' ),
			MWF_Config::NAME, 'side'
		);

		// 管理者メール設定
		$View->set( 'mail_to'           , $this->get_option( 'mail_to' ) );
		$View->set( 'mail_cc'           , $this->get_option( 'mail_cc' ) );
		$View->set( 'mail_bcc'          , $this->get_option( 'mail_bcc' ) );
		$View->set( 'admin_mail_subject', $this->get_option( 'admin_mail_subject' ) );
		$View->set( 'admin_mail_sender' , $this->get_option( 'admin_mail_sender' ) );
		$View->set( 'admin_mail_from'   , $this->get_option( 'admin_mail_from' ) );
		$View->set( 'admin_mail_content', $this->get_option( 'admin_mail_content' ) );
		add_meta_box(
			MWF_Config::NAME . '_admin_mail',
			__( 'Admin Email Options', MWF_Config::DOMAIN ),
			array( $View, 'admin_mail_options' ),
			MWF_Config::NAME, 'side'
		);

		// 設定
		$View->set( 'querystring'         , $this->get_option( 'querystring' ) );
		$View->set( 'usedb'               , $this->get_option( 'usedb' ) );
		$View->set( 'scroll'              , $this->get_option( 'scroll' ) );
		$View->set( 'akismet_author'      , $this->get_option( 'akismet_author' ) );
		$View->set( 'akismet_author_email', $this->get_option( 'akismet_author_email' ) );
		$View->set( 'akismet_author_url'  , $this->get_option( 'akismet_author_url' ) );
		add_meta_box(
			MWF_Config::NAME . '_settings',
			__( 'settings', MWF_Config::DOMAIN ),
			array( $View, 'settings' ),
			MWF_Config::NAME, 'side'
		);

		// CSS
		$styles = apply_filters( 'mwform_styles', array() );
		if ( $styles ) {
			$View->set( 'styles', $styles );
			$View->set( 'style' , $this->get_option( 'style' ) );
			add_meta_box(
				MWF_Config::NAME . '_styles',
				__( 'Style setting', MWF_Config::DOMAIN ),
				array( $View, 'style' ),
				MWF_Config::NAME, 'side'
			);
		}
	}

	/**
	 * current_screen
	 * 寄付リンクを表示
	 * @param WP_Screen $screen
	 */
	public function current_screen( $screen ) {
		$View = new MW_WP_Form_Admin_View();
		if ( $screen->id === 'edit-' . MWF_Config::NAME ) {
			add_filter( 'views_' . $screen->id, array( $View, 'donate_link' ) );
		}
	}

	/**
	 * default_content
	 * 本文の初期値を設定
	 * @param string $content
	 * @return string
	 */
	public function default_content( $content ) {
		global $typenow;
		if ( $typenow === MWF_Config::NAME ) {
			return apply_filters( 'mwform_default_content', '' );
		}
	}

	/**
	 * get_option
	 * フォームの設定データを返す
	 * @param string $key 設定データのキー
	 * @return mixed 設定データ
	 */
	protected function get_option( $key ) {
		global $post;
		$Setting = new MW_WP_Form_Setting( $post->ID );
		$value = $Setting->get( $key );
		if ( !is_null( $value ) ) {
			return $value;
		} else {
			$date     = $post->post_date;
			$modified = $post->post_modified;
			if ( $date === $modified ){
				return apply_filters( 'mwform_default_settings', '', $key );
			}
		}
	}

	/**
	 * admin_enqueue_scripts
	 */
	public function admin_enqueue_scripts() {
		$post_type = get_post_type();
		$url = plugin_dir_url( __FILE__ );
		if ( isset( $_GET['post_type'] ) && MWF_Config::NAME === $_GET['post_type'] ||
			 MWF_Config::NAME == $post_type ) {
			wp_enqueue_style( MWF_Config::NAME . '-admin', $url . '../../css/admin.css' );
		}
		if ( MWF_Config::NAME === $post_type ) {
			wp_enqueue_script( MWF_Config::NAME . '-repeatable', $url . '../../js/mw-wp-form-repeatable.js' );
			wp_enqueue_script( MWF_Config::NAME . '-admin', $url . '../../js/admin.js' );
			wp_enqueue_script( 'jquery-ui-dialog' );
			wp_enqueue_script( 'jquery-ui-sortable' );

			global $wp_scripts;
			$ui = $wp_scripts->query( 'jquery-ui-core' );
			wp_enqueue_style(
				'jquery.ui',
				'//ajax.googleapis.com/ajax/libs/jqueryui/' . $ui->ver . '/themes/smoothness/jquery-ui.min.css',
				array(),
				$ui->ver
			);
		}
	}
}
