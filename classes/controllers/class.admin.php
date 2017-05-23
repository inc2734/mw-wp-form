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
	 * @var array
	 */
	protected $styles = array();

	public function __construct() {
		$this->styles = apply_filters( 'mwform_styles', $this->styles );
		$Admin        = new MW_WP_Form_Admin();

		$Form_Fields = MW_WP_Form_Form_Fields::instantiation();
		$form_fields = $Form_Fields->get_form_fields();
		foreach ( $form_fields as $form_field ) {
			$form_field->add_tag_generator();
		}

		add_action( 'add_meta_boxes'       , array( $this , '_add_meta_boxes' ) );
		add_filter( 'default_content'      , array( $this , '_default_content' ) );
		add_action( 'media_buttons'        , array( $this , '_tag_generator' ) );
		add_action( 'admin_enqueue_scripts', array( $this , '_admin_enqueue_scripts' ) );
		add_action( 'save_post'            , array( $Admin, 'save_post' ) );
	}

	/**
	 * カスタムフィールドを出力
	 */
	public function _add_meta_boxes() {
		// 完了画面内容
		add_meta_box(
			MWF_Config::NAME . '_complete_message_metabox',
			__( 'Complete Message', 'mw-wp-form' ),
			array( $this, '_complete_message' ),
			MWF_Config::NAME, 'normal'
		);

		// URL設定
		add_meta_box(
			MWF_Config::NAME . '_url',
			__( 'URL Options', 'mw-wp-form' ),
			array( $this, '_url' ),
			MWF_Config::NAME, 'normal'
		);

		// バリデーション
		add_meta_box(
			MWF_Config::NAME . '_validation',
			__( 'Validation Rule', 'mw-wp-form' ),
			array( $this, '_validation_rule' ),
			MWF_Config::NAME, 'normal'
		);

		// アドオン
		add_meta_box(
			MWF_Config::NAME . '_addon',
			__( 'Add-ons', 'mw-wp-form' ),
			array( $this, '_add_ons' ),
			MWF_Config::NAME, 'side'
		);

		// フォーム識別子
		add_meta_box(
			MWF_Config::NAME . '_formkey',
			__( 'Form Key', 'mw-wp-form' ),
			array( $this, '_form_key' ),
			MWF_Config::NAME, 'side'
		);

		// 自動返信メール設定
		add_meta_box(
			MWF_Config::NAME . '_mail',
			__( 'Automatic Reply Email Options', 'mw-wp-form' ),
			array( $this, '_mail_options' ),
			MWF_Config::NAME, 'side'
		);

		// 管理者メール設定
		add_meta_box(
			MWF_Config::NAME . '_admin_mail',
			__( 'Admin Email Options', 'mw-wp-form' ),
			array( $this, '_admin_mail_options' ),
			MWF_Config::NAME, 'side'
		);

		// 設定
		add_meta_box(
			MWF_Config::NAME . '_settings',
			__( 'settings', 'mw-wp-form' ),
			array( $this, '_settings' ),
			MWF_Config::NAME, 'side'
		);

		// スタイル
		if ( $this->styles ) {
			add_meta_box(
				MWF_Config::NAME . '_styles',
				__( 'Style setting', 'mw-wp-form' ),
				array( $this, '_style' ),
				MWF_Config::NAME, 'side'
			);
		}
	}

	/**
	 * 本文の初期値を設定
	 *
	 * @param string $content
	 * @return string
	 */
	public function _default_content( $content ) {
		return apply_filters( 'mwform_default_content', '' );
	}

	/**
	 * タグジェネレータを出力
	 *
	 * @param string $editor_id
	 */
	public function _tag_generator( $editor_id ) {
		$post_type = get_post_type();
		if ( MWF_Config::NAME !== $post_type ) {
			return;
		}

		if ( 'content' !== $editor_id ) {
			return;
		}

		$this->_render( 'admin/tag-generator' );
	}

	/**
	 * admin_enqueue_scripts
	 */
	public function _admin_enqueue_scripts() {
		$url = plugins_url( MWF_Config::NAME );

		wp_enqueue_style(
			MWF_Config::NAME . '-admin',
			$url . '/css/admin.css'
		);

		wp_enqueue_style(
			MWF_Config::NAME . '-admin-repeatable',
			$url . '/css/admin-repeatable.css'
		);

		wp_enqueue_script(
			MWF_Config::NAME . '-repeatable',
			$url . '/js/mw-wp-form-repeatable.js'
		);

		wp_enqueue_script(
			MWF_Config::NAME . '-admin',
			$url . '/js/admin.js',
			array( 'jquery-ui-dialog', 'jquery-ui-sortable' )
		);

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

	/**
	 * 完了画面内容
	 */
	public function _complete_message() {
		wp_editor(
			$this->_get_option( 'complete_message' ),
			MWF_Config::NAME . '_complete_message',
			array(
				'textarea_name' => MWF_Config::NAME . '[complete_message]',
				'textarea_rows' => 7,
			)
		);
	}

	/**
	 * URL設定
	 */
	public function _url() {
		$this->_assign( 'input_url'           , $this->_get_option( 'input_url' ) );
		$this->_assign( 'confirmation_url'    , $this->_get_option( 'confirmation_url' ) );
		$this->_assign( 'complete_url'        , $this->_get_option( 'complete_url' ) );
		$this->_assign( 'validation_error_url', $this->_get_option( 'validation_error_url' ) );
		$this->_render( 'admin/url' );
	}

	/**
	 * バリデーション
	 */
	public function _validation_rule() {
		$validation = $this->_get_option( 'validation' );
		if ( ! $validation ) {
			$validation = array();
		}

		$validation_keys = array(
			'target' => '',
		);

		$Validation_Rules = MW_WP_Form_Validation_Rules::instantiation();

		foreach ( $Validation_Rules->get_validation_rules() as $validation_rule => $instance ) {
			$validation_keys[ $instance->getName() ] = '';
		}

		// 空の隠れバリデーションフィールド（コピー元）を挿入
		array_unshift( $validation, $validation_keys );
		$this->_assign( 'validation'      , $validation );
		$this->_assign( 'validation_rules', $Validation_Rules->get_validation_rules() );
		$this->_assign( 'validation_keys' , $validation_keys );
		$this->_render( 'admin/validation-rule' );
	}

	/**
	 * アドオン
	 */
	public function _add_ons() {
		$this->_render( 'admin/add-ons' );
	}

	/**
	 * フォーム識別子
	 */
	public function _form_key() {
		$this->_assign( 'post_id', get_the_ID() );
		$this->_render( 'admin/form-key' );
	}

	/**
	 * 自動返信メール設定
	 */
	public function _mail_options() {
		$this->_assign( 'mail_subject'         , $this->_get_option( 'mail_subject' ) );
		$this->_assign( 'mail_sender'          , $this->_get_option( 'mail_sender' ) );
		$this->_assign( 'mail_from'            , $this->_get_option( 'mail_from' ) );
		$this->_assign( 'mail_content'         , $this->_get_option( 'mail_content' ) );
		$this->_assign( 'automatic_reply_email', $this->_get_option( 'automatic_reply_email' ) );
		$this->_render( 'admin/mail-options' );
	}

	/**
	 * 管理者メール設定
	 */
	public function _admin_mail_options() {
		$this->_assign( 'mail_to'               , $this->_get_option( 'mail_to' ) );
		$this->_assign( 'mail_cc'               , $this->_get_option( 'mail_cc' ) );
		$this->_assign( 'mail_bcc'              , $this->_get_option( 'mail_bcc' ) );
		$this->_assign( 'admin_mail_subject'    , $this->_get_option( 'admin_mail_subject' ) );
		$this->_assign( 'admin_mail_sender'     , $this->_get_option( 'admin_mail_sender' ) );
		$this->_assign( 'mail_return_path'      , $this->_get_option( 'mail_return_path' ) );
		$this->_assign( 'admin_mail_from'       , $this->_get_option( 'admin_mail_from' ) );
		$this->_assign( 'admin_mail_content'    , $this->_get_option( 'admin_mail_content' ) );
		$this->_render( 'admin/admin-mail-options' );
	}

	/**
	 * 設定
	 */
	public function _settings() {
		$this->_assign( 'querystring'         , $this->_get_option( 'querystring' ) );
		$this->_assign( 'usedb'               , $this->_get_option( 'usedb' ) );
		$this->_assign( 'scroll'              , $this->_get_option( 'scroll' ) );
		$this->_assign( 'akismet_author'      , $this->_get_option( 'akismet_author' ) );
		$this->_assign( 'akismet_author_email', $this->_get_option( 'akismet_author_email' ) );
		$this->_assign( 'akismet_author_url'  , $this->_get_option( 'akismet_author_url' ) );
		$this->_assign( 'tracking_number'     , $this->_get_option( MWF_Config::TRACKINGNUMBER ) );
		$this->_render( 'admin/settings' );
	}

	/**
	 * スタイル
	 */
	public function _style() {
		$this->_assign( 'styles', $this->styles );
		$this->_assign( 'style' , $this->_get_option( 'style' ) );
		$this->_render( 'admin/style' );
	}

	/**
	 * フォームの設定データを返す
	 *
	 * @param string $key 設定データのキー
	 * @return mixed 設定データ
	 */
	protected function _get_option( $key ) {
		global $post;
		$Setting = new MW_WP_Form_Setting( $post->ID );

		if ( MWF_Config::TRACKINGNUMBER === $key ) {
			$value = $Setting->get_tracking_number();
		} else {
			$value = $Setting->get( $key );
		}

		if ( ! empty( $value ) ) {
			return $value;
		}

		$date     = $post->post_date;
		$modified = $post->post_modified;
		if ( $date === $modified ){
			return apply_filters( 'mwform_default_settings', '', $key );
		}
	}
}
