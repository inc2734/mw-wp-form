<?php
/**
 * Name: MW Mail
 * URI: http://2inc.org
 * Description: メールクラス
 * Version: 1.4.0
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created: July 20, 2012
 * Modified: June 13, 2014
 * License: GPL2
 *
 * Copyright 2014 Takashi Kitajima (email : inc@2inc.org)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
class MW_Mail {

	public $to;				// 宛先
	public $cc;				// CC
	public $bcc;			// BCC
	public $from;			// 送信元
	public $sender;			// 送信者
	public $subject;		// 題名
	public $body;			// 本文
	public $attachments;	// 添付
	private $ENCODE = 'utf-8';

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
	 * @param	Array ( 見出し => 内容, … )
	 * 			Array ( 'exclude' => array( 除外したいキー1, … ) )
	 */
	public function createBody( Array $array, Array $options = array() ) {
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
?>