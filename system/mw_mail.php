<?php
/**
 * Name: MW Mail
 * Description: メールクラス
 * Version: 1.4.1
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created: July 20, 2012
 * Modified: July 24, 2014
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_Mail {

	/**
	 * 宛先
	 */
	public $to;

	/**
	 * CC
	 */
	public $cc;

	/**
	 * BCC
	 */
	public $bcc;

	/**
	 * 送信元
	 */
	public $from;

	/**
	 * 送信者
	 */
	public $sender;

	/**
	 * 件名
	 */
	public $subject;

	/**
	 * 本文
	 */
	public $body;

	/**
	 * 添付
	 */
	public $attachments;

	/**
	 * send
	 * メール送信
	 */
	public function send() {
		if ( !$this->to ) return;
		$subject = $this->subject;
		$body = $this->body;

		add_action( 'phpmailer_init', array( $this, 'set_return_path' ) );
		add_filter( 'wp_mail_from', array( $this, 'set_mail_from' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'set_mail_from_name' ) );
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
			wp_mail( $to, $subject, $body, $headers, $this->attachments );
		}
		remove_action( 'phpmailer_init', array( $this, 'set_return_path' ) );
		remove_filter( 'wp_mail_from', array( $this, 'set_mail_from' ) );
		remove_filter( 'wp_mail_from_name', array( $this, 'set_mail_from_name' ) );
	}
	public function set_mail_from( $email ) {
		return $this->from;
	}
	public function set_mail_from_name( $email_from ) {
		return $this->sender;
	}
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
			if ( $value )
				$_ret .= sprintf( "▼%s\n%s\n\n", esc_html( $key ), esc_html( $value ) );
		}
		return $_ret;
	}
}
