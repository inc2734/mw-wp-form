<?php
/**
 * Name       : MW WP Form Mail
 * Description: メールクラス
 * Version    : 2.0.0
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : July 20, 2012
 * Modified   : August 19, 2016
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Mail {

	/**
	 * 宛先
	 * @var string
	 */
	public $to;

	/**
	 * CC
	 * @var string
	 */
	public $cc;

	/**
	 * BCC
	 * @var string
	 */
	public $bcc;

	/**
	 * 送信元
	 * @var string
	 */
	public $from;

	/**
	 * Return-Path
	 * @var string
	 */
	public $return_path;

	/**
	 * 送信者
	 * @var string
	 */
	public $sender;

	/**
	 * 件名
	 * @var string
	 */
	public $subject;

	/**
	 * 本文
	 * @var string
	 */
	public $body;

	/**
	 * 添付
	 * @var array
	 */
	public $attachments = array();

	/**
	 * @var MW_WP_Form_Mail_Parser
	 */
	protected $Mail_Parser;

	/**
	 * メール送信
	 */
	public function send() {
		if ( !$this->to ) {
			return;
		}

		$sender  = $this->sender;
		$from    = $this->from;
		$subject = $this->subject;
		$body    = $this->body;

		add_action( 'phpmailer_init'   , array( $this, 'set_return_path' ) );
		add_filter( 'wp_mail_from'     , array( $this, 'set_mail_from' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'set_mail_from_name' ) );

		if ( defined( 'MWFORM_DEBUG' ) && MWFORM_DEBUG === true ) {
			$File     = new MW_WP_Form_File();
			$File->create_temp_dir();
			$temp_dir = $File->get_temp_dir();
			$temp_dir = trailingslashit( $temp_dir['dir'] );
			$temp_dir = apply_filters( 'mwform_log_directory', $temp_dir );
		}

		$headers = array();
		if ( $this->cc ) {
			$headers[] = 'Cc: ' . $this->cc;
		}
		if ( $this->bcc ) {
			$headers[] = 'Bcc: ' . $this->bcc;
		}
		$to = trim( $this->to );
		if ( !empty( $File ) ) {
			$contents = sprintf(
				"====================\n\nSend Date: %s\nTo: %s\nSender: %s\nFrom: %s\nSubject: %s\nheaders:%s\n-----\n%s\n-----\nattachments:\n%s\n\n",
				date( 'M j Y, H:i:s' ),
				$to,
				$sender,
				$from,
				$subject,
				implode( "\n", $headers ),
				$body,
				implode( "\n", $this->attachments )
			);
			file_put_contents( $temp_dir . '/mw-wp-form-debug.log', $contents, FILE_APPEND );
		} else {
			@wp_mail( $to, $subject, $body, $headers, $this->attachments );
		}

		remove_action( 'phpmailer_init'   , array( $this, 'set_return_path' ) );
		remove_filter( 'wp_mail_from'     , array( $this, 'set_mail_from' ) );
		remove_filter( 'wp_mail_from_name', array( $this, 'set_mail_from_name' ) );
	}

	/**
	 * 送信元を設定
	 *
	 * @param string $email fromメールアドレス
	 * @return string
	 */
	public function set_mail_from( $email ) {
		if ( filter_var( $this->from, FILTER_VALIDATE_EMAIL ) ) {
			return $this->from;
		}
		return $email;
	}

	/**
	 * 送信者名を設定
	 *
	 * @param string $sender 送信者名
	 * @return string
	 */
	public function set_mail_from_name( $sender ) {
		return $this->sender;
	}

	/**
	 * Return-Path を設定
	 *
	 * @param phpmailer $phpmailer
	 */
	public function set_return_path( $phpmailer ) {
		$phpmailer->Sender = $this->return_path;
	}

	/**
	 * 配列からbodyを生成
	 *
	 * @param array ( 見出し => 内容, … )
	 * @param array ( 'exclude' => array( 除外したいキー1, … ) )
	 * @return string メール本文
	 */
	public function createBody( array $array, array $options = array() ) {
		$_ret = '';
		$defaults = array(
			'exclude' => array()
		);
		$options = array_merge( $defaults, $options );
		foreach( $array as $key => $value ) {
			if ( in_array( $key, $options['exclude'] ) )
				continue;
			if ( is_array( $value ) && isset( $value['separator'], $value['data'] ) ) {
				$_value = '';
				if ( is_array( $value['data'] ) ) {
					foreach ( $value['data'] as $_val ) {
						if ( !( $_val === '' || $_val === null ) ) {
							$_value = implode( $value['separator'], $value['data'] );
							break;
						}
					}
				} else {
					$_value = $value['data'];
				}
				$value = $_value;
			}
			if ( $value ) {
				$_ret .= sprintf( "▼%s\n%s\n\n", esc_html( $key ), esc_html( $value ) );
			}
		}
		return $_ret;
	}

	/**
	 * 管理者メール用に初期値を設定
	 *
	 * @param MW_WP_Form_Setting $Setting
	 */
	public function set_admin_mail_raw_params( MW_WP_Form_Setting $Setting ) {
		// タイトルを指定
		$admin_mail_subject = $Setting->get( 'mail_subject' );
		if ( $Setting->get( 'admin_mail_subject' ) ) {
			$admin_mail_subject = $Setting->get( 'admin_mail_subject' );
		}
		$this->subject = $admin_mail_subject;

		// 本文を指定
		$admin_mail_content = $Setting->get( 'mail_content' );
		if ( $Setting->get( 'admin_mail_content' ) ) {
			$admin_mail_content = $Setting->get( 'admin_mail_content' );
		}
		$this->body = $admin_mail_content;

		// 送信先を指定
		$admin_mail_to = get_bloginfo( 'admin_email' );
		if ( $Setting->get( 'mail_to' ) ) {
			$admin_mail_to = $Setting->get( 'mail_to' );
		}
		$this->to = $admin_mail_to;

		// CCを指定
		$admin_mail_cc = '';
		if ( $Setting->get( 'mail_cc' ) ) {
			$admin_mail_cc = $Setting->get( 'mail_cc' );
		}
		$this->cc = $admin_mail_cc;

		// BCCを指定
		$admin_mail_bcc = '';
		if ( $Setting->get( 'mail_bcc' ) ) {
			$admin_mail_bcc = $Setting->get( 'mail_bcc' );
		}
		$this->bcc = $admin_mail_bcc;

		// 送信元を指定
		$admin_mail_from = get_bloginfo( 'admin_email' );
		if ( $Setting->get( 'mail_from' ) ) {
			$admin_mail_from = $Setting->get( 'mail_from' );
		}
		if ( $Setting->get( 'admin_mail_from' ) ) {
			$admin_mail_from = $Setting->get( 'admin_mail_from' );
		}
		$this->from = $admin_mail_from;

		// Return-Path を指定
		$mail_return_path = get_bloginfo( 'admin_email' );
		if ( $Setting->get( 'mail_return_path' ) ) {
			$mail_return_path = $Setting->get( 'mail_return_path' );
		}
		$this->return_path = $mail_return_path;

		// 送信者を指定
		$admin_mail_sender = get_bloginfo( 'name' );
		if ( $Setting->get( 'mail_sender' ) ) {
			$admin_mail_sender = $Setting->get( 'mail_sender' );
		}
		if ( $Setting->get( 'admin_mail_sender' ) ) {
			$admin_mail_sender = $Setting->get( 'admin_mail_sender' );
		}
		$this->sender = $admin_mail_sender;
	}

	/**
	 * 自動返信メール用に初期値を設定
	 *
	 * @param MW_WP_Form_Setting $Setting
	 */
	public function set_reply_mail_raw_params( MW_WP_Form_Setting $Setting ) {
		$this->to          = '';
		$this->cc          = '';
		$this->bcc         = '';
		$this->attachments = array();

		$Data = MW_WP_Form_Data::getInstance();
		$automatic_reply_email = $Setting->get( 'automatic_reply_email' );

		$form_id = $Setting->get( 'post_id' );
		if ( $form_id ) {
			$Validation = new MW_WP_Form_Validation_Rule_Mail();
			$Validation->set_Data( $Data );
			$is_invalid_mail_address = $Validation->rule(
				$automatic_reply_email
			);

			// 送信先を指定
			if ( $automatic_reply_email && !$is_invalid_mail_address ) {
				$this->to = $Data->get_post_value_by_key( $automatic_reply_email );
			}

			// Return-Path を指定
			$mail_return_path = get_bloginfo( 'admin_email' );
			if ( $Setting->get( 'mail_return_path' ) ) {
				$mail_return_path = $Setting->get( 'mail_return_path' );
			}
			$this->return_path = $mail_return_path;

			// 送信元を指定
			$reply_mail_from = get_bloginfo( 'admin_email' );
			if ( $Setting->get( 'mail_from' ) ) {
				$reply_mail_from = $Setting->get( 'mail_from' );
			}
			$this->from = $reply_mail_from;

			// 送信者を指定
			$reply_mail_sender = get_bloginfo( 'name' );
			if ( $Setting->get( 'mail_sender' ) ) {
				$reply_mail_sender = $Setting->get( 'mail_sender' );
			}
			$this->sender = $reply_mail_sender;

			// タイトルを指定
			$this->subject = $Setting->get( 'mail_subject' );

			// 本文を指定
			$this->body = $Setting->get( 'mail_content' );
		}
	}

	/**
	 * 管理者メールに必須の項目を設定
	 */
	public function set_admin_mail_reaquire_params() {
		$admin_mail_to     = get_bloginfo( 'admin_email' );
		$admin_mail_from   = get_bloginfo( 'admin_email' );
		$admin_mail_sender = get_bloginfo( 'name' );

		if ( !$this->to ) {
			$this->to = $admin_mail_to;
		}
		if ( !$this->from ) {
			$this->from = $admin_mail_from;
		}
		if ( !$this->sender ) {
			$this->sender = $admin_mail_sender;
		}
	}

	/**
	 * 自動返信メールに必須の項目を設定
	 */
	public function set_reply_mail_reaquire_params() {
		$reply_mail_from   = get_bloginfo( 'admin_email' );
		$reply_mail_sender = get_bloginfo( 'name' );

		if ( !$this->from ) {
			$this->from = $reply_mail_from;;
		}
		if ( !$this->sender ) {
			$this->sender = $reply_mail_sender;;
		}
	}

	/**
	 * メールを送信内容に置換
	 *
	 * @param MW_WP_Form_Setting $Setting
	 * @param bool $do_update
	 */
	public function parse( $Setting, $do_update = false ) {
		$Data = MW_WP_Form_Data::getInstance();

		$this->Mail_Parser = new MW_WP_Form_Mail_Parser( $this, $Setting );
		$Mail = $this->Mail_Parser->get_parsed_mail_object( $do_update );
		foreach ( get_object_vars( $Mail ) as $key => $value ) {
			$this->$key = $value;
		}
	}

	/**
	 * 保存した問い合わせデータの Post IDを取得する
	 *
	 * @return int
	 */
	public function get_saved_mail_id(){
		if ( $this->Mail_Parser ) {
			return $this->Mail_Parser->get_saved_mail_id();
		}
	}
}
