<?php
/**
 * Name       : MW WP Form Main Controller
 * Description: フロントエンドにおいて、適切な画面にリダイレクトさせる
 * Version    : 1.5.1
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : December 23, 2014
 * Modified   : April 28, 2017
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Main_Controller {

	/**
	 * @var MW_WP_Form_Data
	 */
	protected $Data;

	/**
	 * @var MW_WP_Form_Setting
	 */
	protected $Setting;

	/**
	 * @var MW_WP_Form_Validation
	 */
	protected $Validation;

	public function __construct() {
		add_action( 'parse_request'   , array( $this, '_remove_query_vars_from_post' ) );
		add_filter( 'nocache_headers' , array( $this, '_nocache_headers' ) , 1 );
		add_filter( 'template_include', array( $this, '_template_include' ), 10000 );
	}

	/**
	 * WordPressへのリクエストに含まれている、$_POSTの値を削除
	 */
	public function _remove_query_vars_from_post( $wp_query ) {
		if ( isset( $_POST[MWF_Config::TOKEN_NAME] ) ) {
			$request_token = $_POST[ MWF_Config::TOKEN_NAME ];
		}

		if ( ! isset( $request_token ) ) {
			return;
		}

		foreach ( $_POST as $key => $value ) {
			if ( 'token' === $key ) {
				continue;
			}

			if ( isset( $wp_query->query_vars[$key] )
				&& $wp_query->query_vars[$key] === $value
				&& ! empty( $value ) ) {

				$wp_query->query_vars[$key] = '';
			}
		}
	}

	/**
	 * Nginx Cache Controller 用に header をカスタマイズ
	 *
	 * @param array $headers
	 * @return array $headers
	 */
	public function _nocache_headers( $headers ) {
		$headers['X-Accel-Expires'] = 0;
		return $headers;
	}

	/**
	 * 表示画面でのプラグインの処理等
	 *
	 * @param string $template
	 * @return string $template
	 */
	public function _template_include( $template ) {
		global $post;

		/**
		 * - 送信時はバリデーションチェック、トークンチェックを行い、リダイレクト先を決定する
		 * - 決定したリダイレクト先にリダイレクトする
		 * - リダイレクト先が現在表示しようとしているページと同じ場合は無視する
		 */
		if ( ! empty( $_POST ) && ! empty( $_POST[ MWF_Config::NAME . '-form-id' ] ) ) {
			nocache_headers();

			$form_id           = $_POST[ MWF_Config::NAME . '-form-id' ];
			$form_verify_token = $_POST[ MWF_Config::NAME . '-form-verify-token' ];
			$this->Setting     = new MW_WP_Form_Setting( (int) $form_id );

			if ( MWF_Config::NAME !== get_post_type( $form_id ) ) {
				wp_safe_redirect( home_url() );
				exit;
			}

			if ( $this->Setting->generate_form_verify_token() !== $form_verify_token ) {
				wp_safe_redirect( home_url() );
				exit;
			}

			$form_key   = MWF_Functions::get_form_key_from_form_id( $form_id );
			$this->Data = MW_WP_Form_Data::connect( $form_key, $_POST, $_FILES );

			$this->Validation = new MW_WP_Form_Validation( $form_key );

			$Validation_Rules = MW_WP_Form_Validation_Rules::instantiation();
			$validation_rules = $Validation_Rules->get_validation_rules();
			foreach ( $validation_rules as $validation_name => $validation_rule ) {
				if ( is_callable( array( $validation_rule, 'set_Data' ) ) ) {
					$validation_rule->set_Data( $this->Data );
				}
			}

			$post_condition = $this->Data->get_post_condition();

			if ( in_array( $post_condition, array( 'confirm', 'complete' ) ) ) {
				$this->_file_upload();
			}

			$Redirected = new MW_WP_Form_Redirected( $form_key, $this->Setting, $this->Validation->check(), $post_condition );
			$view_flg   = $Redirected->get_view_flg();
			$this->Data->set_view_flg( $view_flg );

			if ( $view_flg === 'complete' ) {
				$is_mail_sended = $this->_send();
			}

			if ( isset( $is_mail_sended ) && false === $is_mail_sended ) {
				$this->Data->set_send_error();
			} elseif ( isset( $is_mail_sended ) && true === $is_mail_sended ) {
				do_action(
					'mwform_after_send_' . $form_key,
					clone $this->Data
				);
			}

			$Redirected->redirect();

		} else {

			/**
			 * [mwform], [mwform_formkey] の登録
			 * - 確認・完了画面に直接アクセスされた場合はエラーメッセージを表示しフォームを表示しない
			 * - スクロールスクリプトのロードには Setting ← Post ID が必要。そのため Exec_Shortcode 内で実行させる
			 * - mwform_add_shortcode と入力フィールドショートコードの実行も Exec_Shortcode 内で行う
			 */
			add_shortcode( 'mwform_formkey', array( $this, '_mwform_formkey' ) );

		}

		return $template;
	}

	/**
	 * Add shortcode for [mwform_formkey]
	 *
	 * @param array $attributes
	 * @return string html
	 * @example [mwform_formkey key="post_id"]
	 */
	public function _mwform_formkey( $attributes ) {
		$Exec_Shortcode = new MW_WP_Form_Exec_Shortcode();
		return $Exec_Shortcode->initialize( $attributes );
	}

	/**
	 * メール送信
	 *
	 * @return boolean
	 */
	protected function _send() {
		$Mail         = new MW_WP_Form_Mail();
		$form_key     = $this->Data->get_form_key();
		$attachments  = $this->_get_attachments();
		$Mail_Service = new MW_WP_Form_Mail_Service( $Mail, $form_key, $this->Setting, $attachments );

		// データベース非保存の場合はファイルも保存されないので、メールで URL が飛ばないように消す
		if ( ! $this->Setting->get( 'usedb' ) ) {
			foreach ( $attachments as $key => $attachment ) {
				$this->Data->clear_value( $key );
			}
		}

		$is_admin_mail_sended = $Mail_Service->send_admin_mail();

		if ( ! $is_admin_mail_sended ) {
			return false;
		}

		// 自動返信メールの送信
		$automatic_reply_email = $this->Setting->get( 'automatic_reply_email' );
		if ( $automatic_reply_email ) {
			$automatic_reply_email   = $this->Data->get_post_value_by_key( $automatic_reply_email );
			$Validation_Rules        = MW_WP_Form_Validation_Rules::instantiation();
			$validation_rules        = $Validation_Rules->get_validation_rules();
			$is_invalid_mail_address = $validation_rules['mail']->rule(
				$automatic_reply_email
			);

			if ( $automatic_reply_email && !$is_invalid_mail_address ) {
				$is_reply_mail_sended = $Mail_Service->send_reply_mail();
			}
		}

		// 問い合わせ番号を加算
		$Mail_Service->update_tracking_number();

		return true;
	}

	/**
	 * 送信されたデータをもとに添付ファイル用の配列を生成して返す
	 *
	 * @return array $attachments pathの配列
	 */
	protected function _get_attachments() {
		$attachments      = array();
		$upload_file_keys = $this->Data->get_post_value_by_key( MWF_Config::UPLOAD_FILE_KEYS );

		if ( is_null( $upload_file_keys ) || ! is_array( $upload_file_keys ) ) {
			return array();
		}

		$wp_upload_dir = wp_upload_dir();
		foreach ( $upload_file_keys as $key ) {
			$upload_file_url = $this->Data->get_post_value_by_key( $key );
			if ( ! $upload_file_url ) {
				continue;
			}

			$filepath = MWF_Functions::fileurl_to_path( $upload_file_url );
			if ( ! file_exists( $filepath ) ) {
				continue;
			}

			$form_key = $this->Data->get_form_key();
			$new_upload_dir = apply_filters(
				'mwform_upload_dir_' . $form_key,
				'',
				clone $this->Data,
				$key
			);

			$new_filename = apply_filters(
				'mwform_upload_filename_' . $form_key,
				'',
				clone $this->Data,
				$key
			);

			$filepath = MWF_Functions::move_temp_file_to_upload_dir(
				$filepath,
				$new_upload_dir,
				$new_filename
			);

			$new_upload_file_url = MWF_Functions::filepath_to_url( $filepath );
			$this->Data->set( $key, $new_upload_file_url );
			$attachments[ $key ] = $filepath;
		}

		return $attachments;
	}

	/**
	 * ファイルアップロード処理。実際のアップロード状況に合わせてフォームデータも再生成する。
	 */
	protected function _file_upload() {
		$File  = new MW_WP_Form_File();
		$files = array();
		$upload_files = $this->Data->get_post_value_by_key( MWF_Config::UPLOAD_FILES );
		if ( ! is_array( $upload_files ) ) {
			$upload_files = array();
		}
		foreach ( $upload_files as $key => $file ) {
			if ( $this->Validation->single_check( $key ) ) {
				$files[ $key ] = $file;
			} elseif ( isset( $files[ $key ] ) ) {
				unset( $files[ $key ] );
			}
		}
		$uploaded_files = $File->upload( $files );
		$this->Data->push_uploaded_file_keys( $uploaded_files );
		$this->Data->regenerate_upload_file_keys();
	}
}
