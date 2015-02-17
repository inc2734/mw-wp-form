<?php
/**
 * Name       : MW WP Form Mail
 * Description: メールクラス
 * Version    : 1.5.0
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : July 20, 2012
 * Modified   : January 1, 2015
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Mail {

	/**
	 * $to
	 * 宛先
	 * @var string
	 */
	public $to;

	/**
	 * $cc
	 * CC
	 * @var string
	 */
	public $cc;

	/**
	 * $bcc
	 * BCC
	 * @var string
	 */
	public $bcc;

	/**
	 * $from
	 * 送信元
	 * @var string
	 */
	public $from;

	/**
	 * $sender
	 * 送信者
	 * @var string
	 */
	public $sender;

	/**
	 * $subject
	 * 件名
	 * @var string
	 */
	public $subject;

	/**
	 * $body
	 * 本文
	 * @var string
	 */
	public $body;

	/**
	 * $attachments
	 * 添付
	 * @var array
	 */
	public $attachments = array();

	/**
	 * send
	 * メール送信
	 */
	public function send() {
		if ( !$this->to ) {
			return;
		}

		$subject = $this->subject;
		$body    = $this->body;

		add_action( 'phpmailer_init'   , array( $this, 'set_return_path' ) );
		add_filter( 'wp_mail_from'     , array( $this, 'set_mail_from' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'set_mail_from_name' ) );

		if ( defined( 'MWFORM_DEBUG' ) && MWFORM_DEBUG === true ) {
			$File     = new MW_WP_Form_File();
			$temp_dir = $File->get_temp_dir();
			$temp_dir = trailingslashit( $temp_dir['dir'] );
			$temp_dir = apply_filters( 'mwform_log_directory', $temp_dir );
		}

		$tos = explode( ',', $this->to );
		foreach ( $tos as $to ) {
			$headers = array();
			if ( $this->cc ) {
				$headers[] = 'Cc: ' . $this->cc;
			}
			if ( $this->bcc ) {
				$headers[] = 'Bcc: ' . $this->bcc;
			}
			$to = trim( $to );
			if ( !empty( $File ) ) {
				$contents = sprintf(
					"====================\n\nSend Date: %s\nTo: %s\nSubject: %s\nheaders:%s\n-----\n%s\n-----\nattachments:\n%s\n\n",
					date( 'M j Y, H:i:s' ),
					$to,
					$subject,
					implode( "\n", $headers ),
					$body,
					implode( "\n", $this->attachments )
				);
				file_put_contents( $temp_dir . '/mw-wp-form-debug.log', $contents, FILE_APPEND );
			} else {
				@wp_mail( $to, $subject, $body, $headers, $this->attachments );
			}
		}

		remove_action( 'phpmailer_init'   , array( $this, 'set_return_path' ) );
		remove_filter( 'wp_mail_from'     , array( $this, 'set_mail_from' ) );
		remove_filter( 'wp_mail_from_name', array( $this, 'set_mail_from_name' ) );
	}

	/**
	 * set_mail_from
	 * @param string $email fromメールアドレス
	 * @return string
	 */
	public function set_mail_from( $email ) {
		return $this->from;
	}

	/**
	 * set_mail_from_name
	 * @param string $sender 送信者名
	 * @return string
	 */
	public function set_mail_from_name( $sender ) {
		return $this->sender;
	}

	/**
	 * set_return_path
	 * @param phpmailer $phpmailer
	 */
	public function set_return_path( $phpmailer ) {
		$phpmailer->Sender = $this->from;
	}

	/**
	 * createBody
	 * 配列からbodyを生成
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
}
