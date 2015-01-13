<?php
/**
 * Name       : MW WP Form Mail Service
 * Version    : 1.0.0
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : January 1, 2015
 * Modified   : 
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
	 * $Mail_admin
	 * @var MW_WP_Form_Mail
	 */
	protected $Mail_admin;
	
	/**
	 * $Mail_auto
	 * @var MW_WP_Form_Mail
	 */
	protected $Mail_auto;

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
			$this->Mail_admin = $this->parse_mail_object( $this->Mail_admin_raw );
			$this->Mail_admin = $this->set_admin_mail_reaquire_params( $this->Mail_admin );
			$this->Mail_admin = $this->apply_filters_mwform_mail( $this->Mail_admin );
			$this->Mail_admin = $this->apply_filters_mwform_admin_mail( $this->Mail_admin );

			$this->set_reply_mail_raw_params();
			$this->Mail_auto_raw = $this->apply_filters_mwform_auto_mail_raw( $this->Mail_auto_raw );
			$this->Mail_auto = $this->parse_mail_object( $this->Mail_auto_raw );
			$this->Mail_auto = $this->set_reply_mail_reaquire_params( $this->Mail_auto );
			$this->Mail_auto = $this->apply_filters_mwform_auto_mail( $this->Mail_auto );
		} else {
			$Mail = $this->apply_filters_mwform_mail( $Mail );
		}
	}

	/**
	 * get_Mail_raw
	 * @return MW_WP_Form_Mail
	 */
	public function get_Mail_raw() {
		return $this->Mail_raw;
	}

	/**
	 * get_Mail_admin_raw
	 * @return MW_WP_Form_Mail
	 */
	public function get_Mail_admin_raw() {
		return $this->Mail_admin_raw;
	}

	/**
	 * get_Mail_admin
	 * @return MW_WP_Form_Mail
	 */
	public function get_Mail_admin() {
		return $this->Mail_admin;
	}

	/**
	 * get_Mail_auto_raw
	 * @return MW_WP_Form_Mail
	 */
	public function get_Mail_auto_raw() {
		return $this->Mail_auto_raw;
	}

	/**
	 * get_Mail_auto
	 * @return MW_WP_Form_Mail
	 */
	public function get_Mail_auto() {
		return $this->Mail_auto;
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
			$this->Data->gets()
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
			$this->Data->gets()
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
			$this->Data->gets()
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
			$this->Data->gets()
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
			$this->Data->gets()
		);
	}

	/**
	 * parse_mail_object
	 * @param MW_WP_Form_Mail $_Mail
	 * @return MW_WP_Form_Mail $Mail
	 */
	protected function parse_mail_object( MW_WP_Form_Mail $_Mail ) {
		$Mail = clone $_Mail;
		$parsed_Mail_vars = get_object_vars( $Mail );
		foreach ( $parsed_Mail_vars as $key => $value ) {
			if ( is_array( $value ) || $key == 'to' || $key == 'cc' || $key == 'bcc' ) {
				continue;
			}
			$value = $this->parse_mail_content( $value );
			$Mail->$key = $value;
		}
		return $Mail;
	}

	/**
	 * parse_mail_content
	 * メール本文用に {name属性} を置換
	 * @param string $value
	 * @return string
	 */
	protected function parse_mail_content( $value ) {
		return preg_replace_callback(
			'/{(.+?)}/',
			array( $this, '_parse_mail_content' ),
			$value
		);
	}
	protected function _parse_mail_content( $matches ) {
		return $this->parse_mail_body( $matches, false );
	}

	/**
	 * save_mail_body
	 * DB保存用に {name属性} を置換、保存
	 */
	protected function save_mail_body( $value ) {
		return preg_replace_callback(
			'/{(.+?)}/',
			array( $this, '_save_mail_body' ),
			$value
		);
	}
	protected function _save_mail_body( $matches ) {
		return $this->parse_mail_body( $matches, true );
	}

	/**
	 * save_contact_data
	 * @param int $form_id
	 * @param MW_WP_Form_Mail $Mail
	 * @param array $files 保存するファイルパスの配列
	 */
	public function save_contact_data( MW_WP_Form_Mail $Mail, array $files = array() ) {
		$form_id = $this->Setting->get( 'post_id' );
		$insert_contact_data_id = wp_insert_post( array(
			'post_title'  => $Mail->subject,
			'post_status' => 'publish',
			'post_type'   => MWF_Config::DBDATA . $form_id,
		) );
		$this->insert_contact_data_id = $insert_contact_data_id;

		// メタデータを保存
		$this->save_mail_body( $Mail->body );

		// 添付ファイルをメディアに保存
		if ( !empty( $insert_contact_data_id ) ) {
			MWF_Functions::save_attachments_in_media(
				$insert_contact_data_id,
				$files,
				$form_id
			);
		}
	}

	/**
	 * parse_mail_body
	 * $this->create_mail_body(), $this->save_mail_body の本体
	 * 第2引数でDB保存するか判定
	 * @param array $matches
	 * @param bool $doUpdate
	 * @return string $value
	 */
	protected function parse_mail_body( $matches, $doUpdate = false ) {
		$match = $matches[1];
		// MWF_Config::TRACKINGNUMBER のときはお問い合せ番号を参照する
		if ( $match === MWF_Config::TRACKINGNUMBER ) {
			$form_id = $this->Setting->get( 'post_id' );
			if ( $form_id ) {
				$value = $this->Setting->get_tracking_number( $form_id );
			}
		} else {
			$value = $this->Data->get( $match );
		}
		if ( $value !== null && $doUpdate ) {
			update_post_meta( $this->insert_contact_data_id, $match, $value );
		}
		return $value;
	}
}