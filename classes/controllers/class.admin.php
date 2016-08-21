<?php
/**
 * Name       : MW WP Form Admin Controller
 * Version    : 1.2.0
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : December 31, 2014
 * Modified   : August 22, 2016
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Admin_Controller extends MW_WP_Form_Controller {

	/**
	 * バリデーションルールの配列
	 * @var array
	 */
	protected $validation_rules = array();

	/**
	 * フォームスタイルの配列
	 */
	protected $styles = array();

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
		$this->styles = apply_filters( 'mwform_styles', $this->styles );
	}

	/**
	 * initialize
	 */
	public function initialize() {
		$Admin = new MW_WP_Form_Admin();
		add_action( 'add_meta_boxes'       , array( $this , 'add_meta_boxes' ) );
		add_filter( 'default_content'      , array( $this , 'default_content' ) );
		add_action( 'media_buttons'        , array( $this , 'tag_generator' ) );
		add_action( 'admin_enqueue_scripts', array( $this , 'admin_enqueue_scripts' ) );
		add_action( 'save_post'            , array( $Admin, 'save_post' ) );
	}

	/**
	 * カスタムフィールドを出力
	 */
	public function add_meta_boxes() {
		// 完了画面内容
		add_meta_box(
			MWF_Config::NAME . '_complete_message_metabox',
			__( 'Complete Message', 'mw-wp-form' ),
			array( $this, 'complete_message' ),
			MWF_Config::NAME, 'normal'
		);

		// URL設定
		add_meta_box(
			MWF_Config::NAME . '_url',
			__( 'URL Options', 'mw-wp-form' ),
			array( $this, 'url' ),
			MWF_Config::NAME, 'normal'
		);

		// バリデーション
		add_meta_box(
			MWF_Config::NAME . '_validation',
			__( 'Validation Rule', 'mw-wp-form' ),
			array( $this, 'validation_rule' ),
			MWF_Config::NAME, 'normal'
		);

		// アドオン
		add_meta_box(
			MWF_Config::NAME . '_addon',
			__( 'Add-ons', 'mw-wp-form' ),
			array( $this, 'add_ons' ),
			MWF_Config::NAME, 'side'
		);

		// フォーム識別子
		add_meta_box(
			MWF_Config::NAME . '_formkey',
			__( 'Form Key', 'mw-wp-form' ),
			array( $this, 'form_key' ),
			MWF_Config::NAME, 'side'
		);

		// 自動返信メール設定
		add_meta_box(
			MWF_Config::NAME . '_mail',
			__( 'Automatic Reply Email Options', 'mw-wp-form' ),
			array( $this, 'mail_options' ),
			MWF_Config::NAME, 'side'
		);

		// 管理者メール設定
		add_meta_box(
			MWF_Config::NAME . '_admin_mail',
			__( 'Admin Email Options', 'mw-wp-form' ),
			array( $this, 'admin_mail_options' ),
			MWF_Config::NAME, 'side'
		);

		// 設定
		add_meta_box(
			MWF_Config::NAME . '_settings',
			__( 'settings', 'mw-wp-form' ),
			array( $this, 'settings' ),
			MWF_Config::NAME, 'side'
		);

		// スタイル
		if ( $this->styles ) {
			add_meta_box(
				MWF_Config::NAME . '_styles',
				__( 'Style setting', 'mw-wp-form' ),
				array( $this, 'style' ),
				MWF_Config::NAME, 'side'
			);
		}
	}

	/**
	 * 完了画面内容
	 */
	public function complete_message() {
		wp_editor(
			$this->get_option( 'complete_message' ), MWF_Config::NAME . '_complete_message',
			array(
				'textarea_name' => MWF_Config::NAME . '[complete_message]',
				'textarea_rows' => 7,
			)
		);
	}

	/**
	 * URL設定
	 */
	public function url() {
		$this->assign( 'input_url'           , $this->get_option( 'input_url' ) );
		$this->assign( 'confirmation_url'    , $this->get_option( 'confirmation_url' ) );
		$this->assign( 'complete_url'        , $this->get_option( 'complete_url' ) );
		$this->assign( 'validation_error_url', $this->get_option( 'validation_error_url' ) );
		$this->render( 'admin/url' );
	}

	/**
	 * バリデーション
	 */
	public function validation_rule() {
		$validation = $this->get_option( 'validation' );
		if ( !$validation ) {
			$validation = array();
		}
		$validation_keys = array(
			'target' => '',
		);
		foreach ( $this->validation_rules as $validation_rule => $instance ) {
			$validation_keys[$instance->getName()] = '';
		}
		// 空の隠れバリデーションフィールド（コピー元）を挿入
		array_unshift( $validation, $validation_keys );
		$this->assign( 'validation'      , $validation );
		$this->assign( 'validation_rules', $this->validation_rules );
		$this->assign( 'validation_keys' , $validation_keys );
		$this->render( 'admin/validation-rule' );
	}

	/**
	 * アドオン
	 */
	public function add_ons() {
		$this->render( 'admin/add-ons' );
	}

	/**
	 * フォーム識別子
	 */
	public function form_key() {
		$this->assign( 'post_id', get_the_ID() );
		$this->render( 'admin/form-key' );
	}

	/**
	 * 自動返信メール設定
	 */
	public function mail_options() {
		$this->assign( 'mail_subject'         , $this->get_option( 'mail_subject' ) );
		$this->assign( 'mail_sender'          , $this->get_option( 'mail_sender' ) );
		$this->assign( 'mail_from'            , $this->get_option( 'mail_from' ) );
		$this->assign( 'mail_content'         , $this->get_option( 'mail_content' ) );
		$this->assign( 'automatic_reply_email', $this->get_option( 'automatic_reply_email' ) );
		$this->render( 'admin/mail-options' );
	}

	/**
	 * 管理者メール設定
	 */
	public function admin_mail_options() {
		$this->assign( 'mail_to'               , $this->get_option( 'mail_to' ) );
		$this->assign( 'mail_cc'               , $this->get_option( 'mail_cc' ) );
		$this->assign( 'mail_bcc'              , $this->get_option( 'mail_bcc' ) );
		$this->assign( 'admin_mail_subject'    , $this->get_option( 'admin_mail_subject' ) );
		$this->assign( 'admin_mail_sender'     , $this->get_option( 'admin_mail_sender' ) );
		$this->assign( 'mail_return_path'      , $this->get_option( 'mail_return_path' ) );
		$this->assign( 'admin_mail_from'       , $this->get_option( 'admin_mail_from' ) );
		$this->assign( 'admin_mail_content'    , $this->get_option( 'admin_mail_content' ) );
		$this->render( 'admin/admin-mail-options' );
	}

	/**
	 * 設定
	 */
	public function settings() {
		$this->assign( 'querystring'         , $this->get_option( 'querystring' ) );
		$this->assign( 'usedb'               , $this->get_option( 'usedb' ) );
		$this->assign( 'scroll'              , $this->get_option( 'scroll' ) );
		$this->assign( 'akismet_author'      , $this->get_option( 'akismet_author' ) );
		$this->assign( 'akismet_author_email', $this->get_option( 'akismet_author_email' ) );
		$this->assign( 'akismet_author_url'  , $this->get_option( 'akismet_author_url' ) );
		$this->assign( 'tracking_number'     , $this->get_option( MWF_Config::TRACKINGNUMBER ) );
		$this->render( 'admin/settings' );
	}

	/**
	 * スタイル
	 */
	public function style() {
		$this->assign( 'styles', $this->styles );
		$this->assign( 'style' , $this->get_option( 'style' ) );
		$this->render( 'admin/style' );
	}

	/**
	 * 本文の初期値を設定
	 *
	 * @param string $content
	 * @return string
	 */
	public function default_content( $content ) {
		return apply_filters( 'mwform_default_content', '' );
	}

	/**
	 * タグジェネレータを出力
	 *
	 * @param string $editor_id
	 */
	public function tag_generator( $editor_id ) {
		$post_type = get_post_type();
		if ( $post_type !== MWF_Config::NAME ) {
			return;
		}
		if ( $editor_id !== 'content' ) {
			return;
		}
		$this->render( 'admin/tag-generator' );
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

		if ( $key === MWF_Config::TRACKINGNUMBER ) {
			$value = $Setting->get_tracking_number();
		} else {
			$value = $Setting->get( $key );
		}

		if ( !empty( $value ) ) {
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
		$url = plugins_url( MWF_Config::NAME );
		wp_enqueue_style( MWF_Config::NAME . '-admin', $url . '/css/admin.css' );
		wp_enqueue_style( MWF_Config::NAME . '-admin-repeatable', $url . '/css/admin-repeatable.css' );
		wp_enqueue_script( MWF_Config::NAME . '-repeatable', $url . '/js/mw-wp-form-repeatable.js' );
		wp_enqueue_script( MWF_Config::NAME . '-admin', $url . '/js/admin.js', array( 'jquery-ui-dialog', 'jquery-ui-sortable' ) );
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
