<?php
/**
 * Name       : MW WP Form Main Controller
 * Description: フロントエンドにおいて、適切な画面にリダイレクトさせる
 * Version    : 1.3.1
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : December 23, 2014
 * Modified   : April 4, 2016
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Main_Controller {

	/**
	 * @var MW_WP_Form_Data
	 */
	protected $Data;

	/**
	 * @var MW_WP_Form_Exec_Shortcode
	 */
	protected $ExecShortcode;

	/**
	 * @var MW_WP_Form_Redrected
	 */
	protected $Redirected;

	/**
	 * @var MW_WP_Form_Setting
	 */
	protected $Setting;

	/**
	 * @var array
	 */
	protected $validation_rules = array();

	/**
	 * @var string
	 */
	protected $token_name = 'mw_wp_form_token';

	/**
	 * リダイレクトされてからの complete であれば true
	 * @var bool
	 */
	protected $complete_twice = false;

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
		add_filter( 'nocache_headers'     , array( $this, 'nocache_headers' ) , 1 );
		add_action( 'parse_request'       , array( $this, 'remove_query_vars_from_post' ) );
		add_filter( 'template_include'    , array( $this, 'template_include' ), 10000 );
		add_filter( 'mwform_form_end_html', array( $this, 'mwform_form_end_html' ) );
	}

	/**
	 * WordPressへのリクエストに含まれている、$_POSTの値を削除
	 */
	public function remove_query_vars_from_post( $wp_query ) {
		if ( isset( $_POST[$this->token_name] ) ) {
			$request_token = $_POST[$this->token_name];
		}
		if ( isset( $request_token ) ) {
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
	 * 表示画面でのプラグインの処理等
	 *
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
		$this->Validation->set_rules( $this->Setting );
		$this->Validation = apply_filters(
			'mwform_validation_' . $form_key,
			$this->Validation,
			$this->Data->gets(),
			clone $this->Data
		);

		$token_check    = $this->token_check();
		$post_condition = $this->Data->get_post_condition( $token_check );
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
			if ( !$this->is_complete_twice() ) {
				$this->send();

				do_action(
					'mwform_after_send_' . $form_key,
					$this->Data
				);
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
			if ( $post_condition !== 'input' ) {
				add_action( 'wp_enqueue_scripts', array( $this, 'scroll_script' ) );
			}
		}

		// 画面表示用のショートコードを登録
		do_action(
			'mwform_add_shortcode',
			new MW_WP_Form_Form(),
			$view_flg,
			$Error,
			$form_key,
			$this->Data
		);

		$Form = new MW_WP_Form_Form();
		$this->ExecShortcode->add_shortcode( $view_flg, $this->Setting, $Form );

		add_action( 'wp_footer'         , array( $this->Data, 'clear_values' ) );
		add_action( 'wp_enqueue_scripts', array( $this      , 'wp_enqueue_scripts' ) );

		return $template;
	}

	/**
	 * 現在のURLと引数で渡されたリダイレクトURLが同じであればリダイレクトしない
	 *
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

		do_action( 'mwform_enqueue_scripts_' . $this->ExecShortcode->get( 'key' ) );
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
	 * Nginx Cache Controller 用に header をカスタマイズ
	 *
	 * @param array $headers
	 * @return array $headers
	 */
	public function nocache_headers( $headers ) {
		$headers['X-Accel-Expires'] = 0;
		return $headers;
	}

	/**
	 * メール送信
	 */
	protected function send() {
		$Mail         = new MW_WP_Form_Mail();
		$form_key     = $this->Data->get_form_key();
		$attachments  = $this->get_attachments();
		$Mail_Service = new MW_WP_Form_Mail_Service( $Mail, $form_key, $this->Setting, $attachments );

		// 管理画面で作成した場合だけ自動で送信
		if ( $this->ExecShortcode->is_generated_by_formkey() ) {
			// データベース非保存の場合はファイルも保存されないので、メールで URL が飛ばないように消す
			if ( !$this->Setting->get( 'usedb' ) ) {
				foreach ( $attachments as $key => $attachment ) {
					$this->Data->clear_value( $key );
				}
			}

			$Mail_Service->send_admin_mail();

			// 自動返信メールの送信
			$automatic_reply_email = $this->Setting->get( 'automatic_reply_email' );
			if ( $automatic_reply_email ) {
				$automatic_reply_email = $this->Data->get_post_value_by_key( $automatic_reply_email );
				$is_invalid_mail_address = $this->validation_rules['mail']->rule(
					$automatic_reply_email
				);
				if ( $automatic_reply_email && !$is_invalid_mail_address ) {
					$Mail_Service->send_reply_mail();
				}
			}

			// 問い合わせ番号を加算
			$Mail_Service->update_tracking_number();
		}
	}

	/**
	 * 送信されたデータをもとに添付ファイル用の配列を生成して返す
	 *
	 * @return array $attachments pathの配列
	 */
	protected function get_attachments() {
		$attachments = array();
		$upload_file_keys = $this->Data->get_post_value_by_key( MWF_Config::UPLOAD_FILE_KEYS );
		if ( $upload_file_keys !== null && is_array( $upload_file_keys ) ) {
			$wp_upload_dir = wp_upload_dir();
			foreach ( $upload_file_keys as $key ) {
				$upload_file_url = $this->Data->get_post_value_by_key( $key );
				if ( !$upload_file_url ) {
					continue;
				}
				$filepath = MWF_Functions::fileurl_to_path( $upload_file_url );
				if ( file_exists( $filepath ) ) {
					$form_key = $this->Data->get_form_key();
					$new_upload_dir = apply_filters(
						'mwform_upload_dir_' . $form_key,
						'',
						$this->Data,
						$key
					);
					$new_filename = apply_filters(
						'mwform_upload_filename_' . $form_key,
						'',
						$this->Data,
						$key
					);
					$filepath = MWF_Functions::move_temp_file_to_upload_dir(
						$filepath,
						$new_upload_dir,
						$new_filename
					);
					$new_upload_file_url = MWF_Functions::filepath_to_url( $filepath );
					$this->Data->set( $key, $new_upload_file_url );
					$attachments[$key]   = $filepath;
				}
			}
		}
		return $attachments;
	}

	/**
	 * ファイルアップロード処理。実際のアップロード状況に合わせてフォームデータも再生成する。
	 */
	protected function file_upload() {
		$File  = new MW_WP_Form_File();
		$files = array();
		$upload_files = $this->Data->get_post_value_by_key( MWF_Config::UPLOAD_FILES );
		if ( !is_array( $upload_files ) ) {
			$upload_files = array();
		}
		foreach ( $upload_files as $key => $file ) {
			if ( $this->Validation->single_check( $key ) ) {
				$files[$key] = $file;
			} elseif ( isset( $files[$key] ) ) {
				unset( $files[$key] );
			}
		}
		$uploaded_files = $File->upload( $files );
		$this->Data->push_uploaded_file_keys( $uploaded_files );
		$this->Data->regenerate_upload_file_keys();
	}

	/**
	 * トークンチェック
	 *
	 * @return bool
	 */
	protected function token_check() {
		if ( isset( $_POST[$this->token_name] ) ) {
			$request_token = $_POST[$this->token_name];
		}
		$values   = $this->Data->gets();
		$form_key = $this->Data->get_form_key();
		if ( isset( $request_token ) && wp_verify_nonce( $request_token, $form_key ) ) {
			return true;
		} elseif ( empty( $_POST ) && $values ) {
			$this->complete_twice = true;
			return true;
		}
		return false;
	}

	/**
	 * トークンを挿入
	 *
	 * @param string $html
	 * @return string
	 */
	public function mwform_form_end_html( $html ) {
		if ( is_a( $this->ExecShortcode, 'MW_WP_Form_Exec_Shortcode' ) ) {
			$form_key = $this->Data->get_form_key();
			$html .= wp_nonce_field( $form_key, $this->token_name, true, false );
			return $html;
		}
	}

	/**
	 * リダイレクト後の complete かチェック
	 *
	 * @return bool
	 */
	protected function is_complete_twice() {
		if ( $this->complete_twice === true ) {
			return true;
		}
		return false;
	}
}
