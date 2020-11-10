<?php
/**
 * @package mw-wp-form
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * MW_WP_Form_Main_Controller
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

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'nocache_headers', array( $this, '_nocache_headers' ), 1 );
		add_filter( 'nginxchampuru_caching_headers', array( $this, '_nginxchampuru_caching_headers' ) );

		add_action( 'parse_request', array( $this, '_remove_query_vars_from_post' ) );
		add_action( 'template_redirect', array( $this, '_send_headers' ), 10000 );
		add_action( 'template_redirect', array( $this, '_template_redirect' ), 10000 );
	}

	/**
	 * Delete the value of $_POST included in the request to WordPress.
	 *
	 * @param WP_Query $wp_query WP_Query object.
	 */
	public function _remove_query_vars_from_post( $wp_query ) {
		if ( isset( $_POST[ MWF_Config::TOKEN_NAME ] ) ) {
			$request_token = $_POST[ MWF_Config::TOKEN_NAME ];
		}

		if ( ! isset( $request_token ) ) {
			return;
		}

		foreach ( $_POST as $key => $value ) {
			if ( 'token' === $key ) {
				continue;
			}

			if ( isset( $wp_query->query_vars[ $key ] )
				&& $wp_query->query_vars[ $key ] === $value
				&& ! empty( $value ) ) {

				$wp_query->query_vars[ $key ] = '';
			}
		}
	}

	/**
	 * Cache control for Nginx Cache Controller plugin.
	 *
	 * @todo NOT WORKING
	 *
	 * @param array $headers HTTP headers.
	 */
	public function _nginxchampuru_caching_headers( $headers ) {
		$headers = $this->_nocache_headers( $headers );
		return $headers;
	}

	/**
	 * Customize request header for Nginx Cache Controller.
	 *
	 * @param array $headers HTTP headers.
	 * @return array
	 */
	public function _nocache_headers( $headers ) {
		$headers['X-Accel-Expires'] = 0;
		$headers['Cache-Control']   = 'private, no-store, no-cache, must-revalidate';
		return $headers;
	}

	/**
	 * Proxy cache measures.
	 *
	 * @todo NOT WORKING
	 */
	public function _send_headers() {
		$nocache = false;

		$post = get_post();
		if ( $post ) {
			if ( preg_match( '|\[mwform_formkey [^\]]+?\]|ms', $post->post_content ) ) {
				$nocache = true;
			}
		}

		$nocache = apply_filters( 'mwform_send_nocache_header', $nocache );

		if ( $nocache ) {
			nocache_headers();
		}
	}

	/**
	 * Main process for form displaying.
	 */
	public function _template_redirect() {
		/**
		 * - 送信時はバリデーションチェック、トークンチェックを行い、リダイレクト先を決定する
		 * - 決定したリダイレクト先にリダイレクトする
		 * - リダイレクト先が現在表示しようとしているページと同じ場合は無視する
		 */
		if ( ! empty( $_POST ) && ! empty( $_POST[ MWF_Config::NAME . '-form-id' ] ) ) {
			$form_id = $_POST[ MWF_Config::NAME . '-form-id' ];
			if ( MWF_Config::NAME !== get_post_type( $form_id ) ) {
				wp_safe_redirect( home_url() );
				exit;
			}

			$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );

			/**
			 * @deprecated since v4.0.0
			 * Because refactoring changed the timing to execute the shortcode
			 */
			do_action( 'mwform_after_exec_shortcode', $form_key );

			do_action( 'mwform_start_main_process', $form_key );

			$form_verify_token = $_POST[ MWF_Config::NAME . '-form-verify-token' ];
			$this->Setting     = new MW_WP_Form_Setting( (int) $form_id );
			$this->Data        = MW_WP_Form_Data::connect( $form_key, $_POST, $_FILES );
			$post_condition    = $this->Data->get_post_condition();
			$this->Validation  = new MW_WP_Form_Validation( $form_key );
			$Redirected        = new MW_WP_Form_Redirected( $form_key, $this->Setting, $this->Validation->is_valid(), $post_condition );

			if ( $this->Setting->generate_form_verify_token() !== $form_verify_token ) {
				wp_safe_redirect( $Redirected->redirect() );
				exit;
			}

			if ( in_array( $post_condition, array( 'confirm', 'complete' ), true ) ) {
				$this->_file_upload();
			}

			$view_flg = $Redirected->get_view_flg();
			$this->Data->set_view_flg( $view_flg );

			if ( 'complete' === $view_flg ) {
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

			/**
			 * If [mwform_formkey] in $post, enqueue assets here.
			 * If not in, enqueue in footer.
			 */
			$this->_mwform_enqueue_scripts();

		}
	}

	/**
	 * Add shortcode for [mwform_formkey].
	 *
	 * @example [mwform_formkey key="post_id"]
	 *
	 * @param array $attributes Attributes of [mwform_formkey].
	 * @return string
	 */
	public function _mwform_formkey( $attributes ) {
		$Exec_Shortcode = new MW_WP_Form_Exec_Shortcode();
		return $Exec_Shortcode->initialize( $attributes );
	}

	/**
	 * If [mwform_formkey] in $post, enqueue assets.
	 */
	protected function _mwform_enqueue_scripts() {
		global $post;

		if ( ! is_a( $post, 'WP_Post' ) || ! has_shortcode( $post->post_content, 'mwform_formkey' ) ) {
			return;
		}

		preg_match_all( '/' . get_shortcode_regex() . '/s', $post->post_content, $matches, PREG_SET_ORDER );
		if ( ! is_array( $matches ) || empty( $matches ) ) {
			return;
		}

		foreach ( $matches as $match ) {
			if ( ! isset( $match[2] ) || 'mwform_formkey' !== $match[2] ) {
				continue;
			}

			if ( ! preg_match( '/key=["\']?(\d+)["\']?/', $match[0], $reg ) ) {
				continue;
			}

			if ( is_array( $reg ) ) {
				MWF_Functions::mwform_enqueue_scripts( $reg[1] );
			}
		}
	}

	/**
	 * Send mail.
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

		// Send admin mail
		$is_admin_mail_sended = $Mail_Service->send_admin_mail();

		if ( ! $is_admin_mail_sended ) {
			error_log( 'Failed to send admin mail.' );
			return false;
		}

		// Send reply mail
		$automatic_reply_email = $this->Setting->get( 'automatic_reply_email' );
		if ( $automatic_reply_email ) {
			$automatic_reply_email   = $this->Data->get_post_value_by_key( $automatic_reply_email );
			$Validation_Rules        = MW_WP_Form_Validation_Rules::instantiation( $form_key );
			$validation_rules        = $Validation_Rules->get_validation_rules();
			$is_invalid_mail_address = $validation_rules['mail']->rule(
				$automatic_reply_email
			);

			if ( $automatic_reply_email && ! $is_invalid_mail_address ) {
				$is_reply_mail_sended = $Mail_Service->send_reply_mail();
				if ( ! $is_reply_mail_sended ) {
					error_log( 'Failed to send auto reply mail.' );
				}
			}
		}

		$Mail_Service->update_tracking_number();

		return true;
	}

	/**
	 * Return that generate an array for the attached file based on the transmitted data.
	 *
	 * @return array
	 */
	protected function _get_attachments() {
		$attachments      = array();
		$upload_file_keys = $this->Data->get_post_value_by_key( MWF_Config::UPLOAD_FILE_KEYS );

		if ( is_null( $upload_file_keys ) || ! is_array( $upload_file_keys ) ) {
			return array();
		}

		foreach ( $upload_file_keys as $key ) {
			$upload_file_url = $this->Data->get_post_value_by_key( $key );
			if ( ! $upload_file_url ) {
				continue;
			}

			$filepath = MWF_Functions::fileurl_to_path( $upload_file_url );
			if ( ! file_exists( $filepath ) ) {
				continue;
			}

			$form_key       = $this->Data->get_form_key();
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
	 * File upload processing.
	 * Regenerate form data according to the actual upload situation.
	 */
	protected function _file_upload() {
		$File         = new MW_WP_Form_File();
		$files        = array();
		$upload_files = $this->Data->get_post_value_by_key( MWF_Config::UPLOAD_FILES );
		if ( ! is_array( $upload_files ) ) {
			$upload_files = array();
		}
		foreach ( $upload_files as $key => $file ) {
			if ( $this->Validation->is_valid_field( $key ) ) {
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
