<?php
/**
 * Name       : MW WP Form Main Controller
 * Description: フロントエンドにおいて、適切な画面にリダイレクトさせる
 * Version    : 1.0.2
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : December 23, 2014
 * Modified   : January 22, 2015
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Main_Controller {

	/**
	 * $Data
	 * @var MW_WP_Form_Data
	 */
	protected $Data;

	/**
	 * $ExecShortcode
	 * @var MW_WP_Form_Exec_Shortcode
	 */
	protected $ExecShortcode;

	/**
	 * $Redirected
	 * @var MW_WP_Form_Redrected
	 */
	protected $Redirected;

	/**
	 * $Setting
	 * @var MW_WP_Form_Setting
	 */
	protected $Setting;

	/**
	 * $validation_rules
	 * @var array
	 */
	protected $validation_rules = array();

	/**
	 * __construct
	 * @param array $validation_rules
	 */
	public function __construct( array $validation_rules ) {
		$this->validation_rules = $validation_rules;
	}

	/**
	 * initialize
	 */
	public function initialize() {
		add_filter( 'nocache_headers' , array( $this, 'nocache_headers' ) , 1 );
		add_action( 'parse_request'   , array( $this, 'remove_query_vars_from_post' ) );
		add_filter( 'template_include', array( $this, 'template_include' ), 10000 );
	}

	/**
	 * remove_query_vars_from_post
	 * WordPressへのリクエストに含まれている、$_POSTの値を削除
	 */
	public function remove_query_vars_from_post( $wp_query ) {
		if ( strtolower( $_SERVER['REQUEST_METHOD'] ) === 'post' && isset( $_POST['token'] ) ) {
			foreach ( $_POST as $key => $value ) {
				if ( $key == 'token' ) {
					continue;
				}
				if ( isset( $wp_query->query_vars[$key] ) &&
					 $wp_query->query_vars[$key] === $value &&
					 !empty( $value ) ) {

					$wp_query->query_vars[$key] = '';
				}
			}
		}
	}

	/**
	 * template_include
	 * 表示画面でのプラグインの処理等
	 * @param string $template
	 * @return string $template
	 */
	public function template_include( $template ) {
		global $post;

		$this->ExecShortcode = new MW_WP_Form_Exec_Shortcode( $post, $template );
		$has_shortcode = $this->ExecShortcode->has_shortcode();
		if ( !$has_shortcode ) {
			return $template;
		}

		$form_key      = $this->ExecShortcode->get( 'key' );
		$form_id       = $this->ExecShortcode->get_form_id();
		$this->Setting = new MW_WP_Form_Setting( $form_id );
		$this->Data    = MW_WP_Form_Data::getInstance( $form_key, $_POST, $_FILES );

		foreach ( $this->validation_rules as $validation_name => $validation_rule ) {
			if ( is_callable( array( $validation_rule, 'set_Data' ) ) ) {
				$validation_rule->set_Data( $this->Data );
			}
		}

		nocache_headers();

		$Error = new MW_WP_Form_Error();
		$this->Validation = new MW_WP_Form_Validation( $Error );
		$this->Validation->set_validation_rules( $this->validation_rules );
		$this->Validation->set_rules( $this->Setting, $this->Data );
		$this->Validation = apply_filters(
			'mwform_validation_' . $form_key,
			$this->Validation,
			$this->Data->gets(),
			clone $this->Data
		);

		$post_condition = $this->Data->get_post_condition();
		$is_valid = $this->Validation->check();
		$this->Redirected = new MW_WP_Form_Redirected(
			$this->ExecShortcode->get( 'input_url' ),
			$this->ExecShortcode->get( 'confirmation_url' ),
			$this->ExecShortcode->get( 'complete_url' ),
			$this->ExecShortcode->get( 'validation_error_url' ),
			$is_valid,
			$post_condition,
			$this->Setting->get( 'querystring' )
		);
		$url      = $this->Redirected->get_url();
		$view_flg = $this->Redirected->get_view_flg();

		// confirm もしくは complete のとき
		if ( in_array( $post_condition, array( 'confirm', 'complete' ) ) ) {
			$this->file_upload();
		}
		// complete のとき
		if ( $view_flg === 'complete' ) {
			if ( !$this->Data->is_complete_twice() ) {
				$this->send();
			}
			// 手動フォームの場合は完了画面に ExecShortcode が無く footer の clear_values が
			// 効かないためここで消す
			if ( !$form_id ) {
				$this->Data->clear_values();
			}
		}
		$this->redirect( $url );

		// スクロール用スクリプトのロード
		if ( $this->Setting->get( 'scroll' ) ) {
			if ( in_array( $view_flg, array( 'confirm', 'complete' ) ) || !$is_valid ) {
				add_action( 'wp_enqueue_scripts', array( $this, 'scroll_script' ) );
			}
		}

		// 画面表示用のショートコードを登録
		$Form = new MW_WP_Form_Form( $this->Data );
		$View = new MW_WP_Form_Main_View();
		$View->set( 'Form', $Form );
		$View->set( 'Error', $Error );
		$View->set( 'Setting', $this->Setting );
		$View->set( 'form_key', $form_key );
		$View->set( 'view_flg', $view_flg );
		$View->add_shortcode_that_display_content();

		add_action( 'wp_footer'         , array( $this->Data, 'clear_values' ) );
		add_action( 'wp_enqueue_scripts', array( $this      , 'wp_enqueue_scripts' ) );

		return $template;
	}

	/**
	 * redirect
	 * 現在のURLと引数で渡されたリダイレクトURLが同じであればリダイレクトしない
	 * @param string リダイレクトURL
	 */
	private function redirect( $url ) {
		$redirect = ( empty( $url ) ) ? $this->Redirected->get_request_uri() : $url;
		$REQUEST_URI = $this->Redirected->get_request_uri();
		if ( !empty( $_POST ) || $redirect != $REQUEST_URI ) {
			$redirect = wp_sanitize_redirect( $redirect );
			$redirect = wp_validate_redirect( $redirect, home_url() );
			wp_redirect( $redirect );
			exit();
		}
	}

	/**
	 * wp_enqueue_scripts
	 */
	public function wp_enqueue_scripts() {
		global $post;

		$url = plugin_dir_url( __FILE__ );
		wp_enqueue_style( MWF_Config::NAME, $url . '../../css/style.css' );

		$style  = $this->Setting->get( 'style' );
		$styles = apply_filters( 'mwform_styles', array() );
		if ( is_array( $styles ) && isset( $styles[$style] ) ) {
			$css = $styles[$style];
			wp_enqueue_style( MWF_Config::NAME . '_style', $css );
		}
		wp_enqueue_script( MWF_Config::NAME, $url . '../../js/form.js', array( 'jquery' ), false, true );
	}

	/**
	 * scroll_script
	 */
	public function scroll_script() {
		$url = plugin_dir_url( __FILE__ );
		wp_register_script(
			MWF_Config::NAME . '-scroll',
			$url . '../../js/scroll.js',
			array( 'jquery' ),
			false,
			true
		);
		wp_localize_script( MWF_Config::NAME . '-scroll', 'mwform_scroll', array(
			'offset' => apply_filters( 'mwform_scroll_offset_' . $this->ExecShortcode->get( 'key' ), 0 ),
		) );
		wp_enqueue_script( MWF_Config::NAME . '-scroll' );
	}
	
	/**
	 * nocache_headers
	 * Nginx Cache Controller用
	 * @param array $headers
	 * @return array $headers
	 */
	public function nocache_headers( $headers ) {
		$headers['X-Accel-Expires'] = 0;
		return $headers;
	}

	/**
	 * send
	 * メール送信
	 */
	protected function send() {
		$Mail         = new MW_WP_Form_Mail();
		$form_key     = $this->ExecShortcode->get( 'key' );
		$attachments  = $this->get_attachments();
		$Mail_Service = new MW_WP_Form_Mail_Service( $Mail, $this->Data, $form_key, $this->validation_rules, $this->Setting, $attachments );

		// 管理画面で作成した場合だけ自動で送信
		if ( $this->ExecShortcode->is_generated_by_formkey() ) {
			$Mail_admin_raw = $Mail_Service->get_Mail_admin_raw();

			// save_mail_body で登録されないように
			foreach ( $attachments as $key => $attachment ) {
				$this->Data->clear_value( $key );
			}

			// メール送信前にファイルのリネームをしないと、tempファイル名をメールで送信してしまう。
			if ( $this->Setting->get( 'usedb' ) ) {
				$Mail_Service->save_contact_data( $Mail_admin_raw, $attachments );
			}

			$Mail_admin = $Mail_Service->get_Mail_admin();
			$Mail_admin->send();

			// DB非保存時は管理者メール送信後、ファイルを削除
			if ( !$this->Setting->get( 'usedb' ) ) {
				$File = new MW_WP_Form_File();
				$File->delete_files( $attachments );
			}

			// 自動返信メールの送信
			$automatic_reply_email = $this->Setting->get( 'automatic_reply_email' );
			if ( $automatic_reply_email ) {
				$automatic_reply_email = $this->Data->get_raw( $automatic_reply_email );
				$is_invalid_mail_address = $this->validation_rules['mail']->rule(
					$automatic_reply_email
				);
				if ( $automatic_reply_email && !$is_invalid_mail_address ) {
					$Mail_auto = $Mail_Service->get_Mail_auto();
					$Mail_auto->send();
				}
			}

			// 問い合わせ番号を加算
			if ( preg_match( '{' . MWF_Config::TRACKINGNUMBER . '}', $Mail_admin_raw->body ) ) {
				$this->Setting->update_tracking_number();
			}
		}
	}

	/**
	 * get_attachments
	 * @return array $attachments pathの配列
	 */
	protected function get_attachments() {
		$attachments = array();
		$upload_file_keys = $this->Data->get_raw( MWF_Config::UPLOAD_FILE_KEYS );
		if ( $upload_file_keys !== null && is_array( $upload_file_keys ) ) {
			$wp_upload_dir = wp_upload_dir();
			foreach ( $upload_file_keys as $key ) {
				$upload_file_url = $this->Data->get_raw( $key );
				if ( !$upload_file_url ) {
					continue;
				}
				$filepath = MWF_Functions::fileurl_to_path( $upload_file_url );
				if ( file_exists( $filepath ) ) {
					$filepath            = MWF_Functions::move_temp_file_to_upload_dir( $filepath );
					$new_upload_file_url = MWF_Functions::filepath_to_url( $filepath );
					$this->Data->set( $key, $new_upload_file_url );
					$attachments[$key]   = $filepath;
				}
			}
		}
		return $attachments;
	}

	/**
	 * file_upload
	 * ファイルアップロード処理。実際のアップロード状況に合わせてフォームデータも再生成する。
	 */
	protected function file_upload() {
		$File  = new MW_WP_Form_File();
		$files = array();
		$upload_files = $this->Data->get_raw( MWF_Config::UPLOAD_FILES );
		if ( !is_array( $upload_files ) ) {
			$upload_files = array();
		}
		foreach ( $upload_files as $key => $file ) {
			if ( $this->Validation->single_check( $key ) ) {
				$files[$key] = $file;
			}
		}
		$uploaded_files = $File->upload( $files );
		$this->Data->set_upload_file_keys();
		$this->Data->push_uploaded_file_keys( $uploaded_files );
	}
}
