<?php
/**
 * Name       : MW WP Form Mail Service
 * Version    : 1.1.0
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : January 1, 2015
 * Modified   : February 13, 2015
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Mail_Service {

	/**
	 * $insert_contact_data_id
	 * 保存した問い合わせデータの Post ID
	 * @var int
	 */
	protected $insert_contact_data_id;

	/**
	 * $Mail_raw
	 * @var MW_WP_Form_Mail
	 */
	protected $Mail_raw;

	/**
	 * $Mail_admin_raw
	 * @var MW_WP_Form_Mail
	 */
	protected $Mail_admin_raw;

	/**
	 * $Mail_auto_raw
	 * @var MW_WP_Form_Mail
	 */
	protected $Mail_auto_raw;

	/**
	 * $Data
	 * @var MW_WP_Form_Data
	 */
	protected $Data;

	/**
	 * $form_key
	 * フォーム識別子
	 * @var string
	 */
	protected $form_key;

	/**
	 * $validation_rules
	 * @var array
	 */
	protected $validation_rules = array();

	/**
	 * $attachments
	 * @var array
	 */
	protected $attachments = array();

	/**
	 * $Setting
	 * @var MW_WP_Form_Setting
	 */
	protected $Setting;

	/**
	 * __construct
	 * @param MW_WP_Form_Mail $Mail
	 * @param MW_WP_Form_Data $Data
	 * @param strign $form_key
	 * @param array $validation_rules
	 * @param MW_WP_Form_Setting $Setting
	 * @param array $attachments
	 */
	public function __construct( MW_WP_Form_Mail $Mail, MW_WP_Form_Data $Data, $form_key, array $validation_rules, MW_WP_Form_Setting $Setting, array $attachments = array() ) {
		$this->form_key         = $form_key;
		$this->Data             = $Data;
		$this->validation_rules = $validation_rules;
		$this->Mail_raw         = $Mail;
		$this->Mail_admin_raw   = clone $Mail;
		$this->Mail_auto_raw    = clone $Mail;
		$this->attachments      = $attachments;
		$this->Setting          = $Setting;

		if ( $this->Setting->get( 'post_id' ) ) {
			$this->set_admin_mail_raw_params();
			$this->set_attachments( $this->Mail_admin_raw );
			$this->Mail_admin_raw = $this->apply_filters_mwform_admin_mail_raw( $this->Mail_admin_raw );

			$this->set_reply_mail_raw_params();
			$this->Mail_auto_raw = $this->apply_filters_mwform_auto_mail_raw( $this->Mail_auto_raw );
		} else {
			$Mail = $this->apply_filters_mwform_mail( $Mail );
		}
	}

	/**
	 * send_admin_mail
	 * 管理者メールの送信とデータベースへの保存
	 */
	public function send_admin_mail() {
		// save_mail_body でファイルURLではなくファイルのIDが保存されるように
		foreach ( $this->attachments as $key => $attachment ) {
			$this->Data->clear_value( $key );
		}

		if ( $this->Setting->get( 'usedb' ) ) {
			$parsed_mail_object = $this->get_parsed_mail_object( $this->Mail_admin_raw, true );
		} else {
			$parsed_mail_object = $this->get_parsed_mail_object( $this->Mail_admin_raw );
		}

		$Mail_admin = $this->set_admin_mail_reaquire_params( $parsed_mail_object );
		$Mail_admin = $this->apply_filters_mwform_mail( $Mail_admin );
		$Mail_admin = $this->apply_filters_mwform_admin_mail( $Mail_admin );
		$Mail_admin->send();

		// DB非保存時は管理者メール送信後、ファイルを削除
		if ( !$this->Setting->get( 'usedb' ) ) {
			$File = new MW_WP_Form_File();
			$File->delete_files( $this->attachments );
		}
	}

	/**
	 * get_parsed_mail_object
	 * パースしたMailオブジェクトの取得とデータベースへの保存
	 * @param MW_WP_Form_Mail $_Mail
	 * @param bool $do_update
	 * @return MW_WP_Form_Mail
	 */
	protected function get_parsed_mail_object( MW_WP_Form_Mail $_Mail, $do_update = false ) {
		$Mail = clone $_Mail;
		if ( $do_update ) {
			$form_id = $this->Setting->get( 'post_id' );
			$insert_contact_data_id = wp_insert_post( array(
				'post_title'  => $this->parse_mail_content( $Mail->subject ),
				'post_status' => 'publish',
				'post_type'   => MWF_Config::DBDATA . $form_id,
			) );

			// 添付ファイルをメディアに保存
			// save_mail_body 内のフックで添付ファイルの情報を使えるように、
			// save_mail_body より前にこのブロックを実行する
			if ( !empty( $insert_contact_data_id ) ) {
				MWF_Functions::save_attachments_in_media(
					$insert_contact_data_id,
					$this->attachments,
					$form_id
				);
			}
			$this->insert_contact_data_id = $insert_contact_data_id;
		}
		return $this->parse_mail_object( $Mail, $do_update );
	}

	/**
	 * parse_mail_object
	 * @param MW_WP_Form_Mail $_Mail
	 * @param bool $do_update
	 * @return MW_WP_Form_Mail $Mail
	 */
	protected function parse_mail_object( MW_WP_Form_Mail $_Mail, $do_update = false ) {
		$Mail = clone $_Mail;
		$parsed_Mail_vars = get_object_vars( $Mail );
		foreach ( $parsed_Mail_vars as $key => $value ) {
			if ( is_array( $value ) || $key == 'to' || $key == 'cc' || $key == 'bcc' ) {
				continue;
			}
			if ( $key == 'body' && $do_update ) {
				$value = $this->parse_mail_content( $value, true );
			} else {
				$value = $this->parse_mail_content( $value );
			}
			$Mail->$key = $value;
		}
		return $Mail;
	}

	/**
	 * parse_mail_content
	 * メール本文用に {name属性} を置換
	 * @param string $value
	 * @param bool $do_update
	 * @return string
	 */
	protected function parse_mail_content( $value, $do_update = false ) {
		if ( $do_update ) {
			$callback = '_save_mail_content';
		} else {
			$callback = '_parse_mail_content';
		}
		return preg_replace_callback(
			'/{(.+?)}/',
			array( $this, $callback ),
			$value
		);
	}
	protected function _parse_mail_content( $matches ) {
		return $this->parse( $matches, false );
	}
	protected function _save_mail_content( $matches ) {
		return $this->parse( $matches, true );
	}

	/**
	 * parse
	 * $this->_parse_mail_content(), $this->_save_mail_content の本体
	 * 第2引数でDB保存するか判定
	 * @param array $matches
	 * @param bool $do_update
	 * @return string $value
	 */
	protected function parse( $matches, $do_update = false ) {
		$match = $matches[1];
		// MWF_Config::TRACKINGNUMBER のときはお問い合せ番号を参照する
		if ( $match === MWF_Config::TRACKINGNUMBER ) {
			$form_id = $this->Setting->get( 'post_id' );
			if ( $form_id ) {
				$value = $this->Setting->get_tracking_number( $form_id );
			}
		} else {
			$value = $this->Data->get( $match );
			$value = apply_filters(
				'mwform_custom_mail_tag_' . $this->form_key,
				$value,
				$match,
				$this->insert_contact_data_id
			);
		}
		if ( $value !== null && $do_update ) {
			update_post_meta( $this->insert_contact_data_id, $match, $value );
		}
		return $value;
	}

	/**
	 * send_reply_mail
	 * 自動返信メールの送信
	 */
	public function send_reply_mail() {
		$Mail_auto = $this->parse_mail_object( $this->Mail_auto_raw );
		$Mail_auto = $this->set_reply_mail_reaquire_params( $Mail_auto );
		$Mail_auto = $this->apply_filters_mwform_auto_mail( $Mail_auto );
		$Mail_auto->send();
	}

	/**
	 * set_attachments
	 * @param MW_WP_Form_Mail $Mail
	 */
	protected function set_attachments( MW_WP_Form_Mail $Mail ) {
		$Mail->attachments = $this->attachments;
	}

	/**
	 * set_admin_mail_raw_params
	 * 管理者メールに項目を設定
	 */
	protected function set_admin_mail_raw_params() {
		// タイトルを指定
		$admin_mail_subject = $this->Setting->get( 'mail_subject' );
		if ( $this->Setting->get( 'admin_mail_subject' ) ) {
			$admin_mail_subject = $this->Setting->get( 'admin_mail_subject' );
		}
		$this->Mail_admin_raw->subject = $admin_mail_subject;

		// 本文を指定
		$admin_mail_content = $this->Setting->get( 'mail_content' );
		if ( $this->Setting->get( 'admin_mail_content' ) ) {
			$admin_mail_content = $this->Setting->get( 'admin_mail_content' );
		}
		$this->Mail_admin_raw->body = $admin_mail_content;

		// 送信先を指定
		$admin_mail_to = get_bloginfo( 'admin_email' );
		if ( $this->Setting->get( 'mail_to' ) ) {
			$admin_mail_to = $this->Setting->get( 'mail_to' );
		}
		$this->Mail_admin_raw->to = $admin_mail_to;

		// CCを指定
		$admin_mail_cc = '';
		if ( $this->Setting->get( 'mail_cc' ) ) {
			$admin_mail_cc = $this->Setting->get( 'mail_cc' );
		}
		$this->Mail_admin_raw->cc = $admin_mail_cc;

		// BCCを指定
		$admin_mail_bcc = '';
		if ( $this->Setting->get( 'mail_bcc' ) ) {
			$admin_mail_bcc = $this->Setting->get( 'mail_bcc' );
		}
		$this->Mail_admin_raw->bcc = $admin_mail_bcc;

		// 送信元を指定
		$admin_mail_from = get_bloginfo( 'admin_email' );
		if ( $this->Setting->get( 'admin_mail_from' ) ) {
			$admin_mail_from = $this->Setting->get( 'admin_mail_from' );
		}
		$this->Mail_admin_raw->from = $admin_mail_from;

		// 送信者を指定
		$admin_mail_sender = get_bloginfo( 'name' );
		if ( $this->Setting->get( 'admin_mail_sender' ) ) {
			$admin_mail_sender = $this->Setting->get( 'admin_mail_sender' );
		}
		$this->Mail_admin_raw->sender = $admin_mail_sender;
	}

	/**
	 * set_reply_mail_raw_params
	 * 自動返信メールに項目を設定
	 */
	private function set_reply_mail_raw_params() {
		$this->Mail_auto_raw->to  = '';
		$this->Mail_auto_raw->cc  = '';
		$this->Mail_auto_raw->bcc = '';
		// 自動返信メールからは添付ファイルを削除
		$this->Mail_auto_raw->attachments = array();
		$form_id = $this->Setting->get( 'post_id' );
		if ( $form_id ) {
			$automatic_reply_email = $this->Setting->get( 'automatic_reply_email' );
			$automatic_reply_email = $this->Data->get_raw( $automatic_reply_email );
			$is_invalid_mail_address = $this->validation_rules['mail']->rule(
				$automatic_reply_email
			);

			// 送信先を指定
			if ( $automatic_reply_email && !$is_invalid_mail_address ) {
				$this->Mail_auto_raw->to = $automatic_reply_email;
			}

			// 送信元を指定
			$reply_mail_from = get_bloginfo( 'admin_email' );
			if ( $this->Setting->get( 'mail_from' ) ) {
				$reply_mail_from = $this->Setting->get( 'mail_from' );
			}
			$this->Mail_auto_raw->from = $reply_mail_from;

			// 送信者を指定
			$reply_mail_sender = get_bloginfo( 'name' );
			if ( $this->Setting->get( 'mail_sender' ) ) {
				$reply_mail_sender = $this->Setting->get( 'mail_sender' );
			}
			$this->Mail_auto_raw->sender = $reply_mail_sender;

			// タイトルを指定
			$this->Mail_auto_raw->subject = $this->Setting->get( 'mail_subject' );

			// 本文を指定
			$this->Mail_auto_raw->body = $this->Setting->get( 'mail_content' );
		}
	}

	/**
	 * set_admin_mail_reaquire_params
	 * 管理者メールに必須の項目を設定
	 * @param MW_WP_Form_Mail $Mail
	 * @return MW_WP_Form_Mail $Mail
	 */
	private function set_admin_mail_reaquire_params( MW_WP_Form_Mail $Mail ) {
		$admin_mail_to     = get_bloginfo( 'admin_email' );
		$admin_mail_from   = get_bloginfo( 'admin_email' );
		$admin_mail_sender = get_bloginfo( 'name' );

		if ( !$Mail->to ) {
			$Mail->to = $admin_mail_to;
		}
		if ( !$Mail->from ) {
			$Mail->from = $admin_mail_from;;
		}
		if ( !$Mail->sender ) {
			$Mail->sender = $admin_mail_sender;;
		}
		return $Mail;
	}

	/**
	 * set_reply_mail_reaquire_params
	 * 自動返信メールに必須の項目を設定
	 * @param MW_WP_Form_Mail $Mail
	 * @return MW_WP_Form_Mail $Mail
	 */
	private function set_reply_mail_reaquire_params( MW_WP_Form_Mail $Mail ) {
		$reply_mail_from   = get_bloginfo( 'admin_email' );
		$reply_mail_sender = get_bloginfo( 'name' );

		if ( !$Mail->from ) {
			$Mail->from = $reply_mail_from;;
		}
		if ( !$Mail->sender ) {
			$Mail->sender = $reply_mail_sender;;
		}
		return $Mail;
	}

	/**
	 * apply_filters_mwform_admin_mail_raw
	 * @param MW_WP_Form_Mail $Mail
	 * @return MW_WP_Form_Mail $Mail
	 */
	protected function apply_filters_mwform_admin_mail_raw( MW_WP_Form_Mail $Mail ) {
		return apply_filters(
			'mwform_admin_mail_raw_' . $this->form_key,
			$Mail,
			$this->Data->gets(),
			clone $this->Data
		);
	}

	/**
	 * apply_filters_mwform_mail
	 * @param MW_WP_Form_Mail $Mail
	 * @return MW_WP_Form_Mail $Mail
	 */
	protected function apply_filters_mwform_mail( MW_WP_Form_Mail $Mail ) {
		return apply_filters(
			'mwform_mail_' . $this->form_key,
			$Mail,
			$this->Data->gets(),
			clone $this->Data
		);
	}

	/**
	 * apply_filters_mwform_admin_mail
	 * @param MW_WP_Form_Mail $Mail
	 * @return MW_WP_Form_Mail $Mail
	 */
	protected function apply_filters_mwform_admin_mail( MW_WP_Form_Mail $Mail ) {
		return apply_filters(
			'mwform_admin_mail_' . $this->form_key,
			$Mail,
			$this->Data->gets(),
			clone $this->Data
		);
	}

	/**
	 * apply_filters_mwform_auto_mail_raw
	 * @param MW_WP_Form_Mail $Mail
	 * @return MW_WP_Form_Mail $Mail
	 */
	protected function apply_filters_mwform_auto_mail_raw( MW_WP_Form_Mail $Mail ) {
		return apply_filters(
			'mwform_auto_mail_raw_' . $this->form_key,
			$Mail,
			$this->Data->gets(),
			clone $this->Data
		);
	}

	/**
	 * apply_filters_mwform_auto_mail
	 * @param MW_WP_Form_Mail $Mail
	 * @return MW_WP_Form_Mail $Mail
	 */
	protected function apply_filters_mwform_auto_mail( MW_WP_Form_Mail $Mail ) {
		return apply_filters(
			'mwform_auto_mail_' . $this->form_key,
			$Mail,
			$this->Data->gets(),
			clone $this->Data
		);
	}
}