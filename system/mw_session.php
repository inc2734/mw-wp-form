<?php
/**
 * Name: MW Session
 * URI: http://2inc.org
 * Description: セッションクラス
 * Version: 2.0.0
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : July 17, 2012
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
class MW_Session {

	private static $name; // セッション名
	private $session_id; // セッションID
	private $expiration = 1440; // Transient の生存時間

	private function __construct( $name ) {
		$this->setSessionName( $name );
	}

	/**
	 * start
	 * インスタンス化
	 * @param string $name 識別子
	 * @return Session Sessionオブジェクト
	 */
	public static function start( $name ) {
		self::$name = MWF_Config::NAME . '_session_' . $name;
		if ( isset( $_COOKIE[self::$name] ) ) {
			$session_id = $_COOKIE[self::$name];
		} else {
			$session_id = sha1( wp_create_nonce( self::$name ) . ip2long( $_SERVER['REMOTE_ADDR'] ) . uniqid() );
			$secure = apply_filters( 'mwform_secure_cookie', is_ssl() );
			setcookie( self::$name, $session_id, 0, COOKIEPATH, COOKIE_DOMAIN, $secure, true );
		}
		$Session = new MW_Session( $session_id );
		return $Session;
	}

	/**
	 * setSessionName
	 * セッション名を設定
	 * @param string $session_id
	 */
	private function setSessionName( $session_id ) {
		$this->session_id = $session_id;
	}

	/**
	 * save
	 * セッション変数にセット
	 * @param array $data
	 */
	public function save( Array $data ) {
		$transient_data = get_transient( $this->session_id );
		if ( is_array( $transient_data ) ) {
			foreach ( $data as $key => $value ) {
				$transient_data[$key] = $value;
			}
		} else {
			$transient_data = $data;
		}
		set_transient( $this->session_id, $transient_data, $this->expiration );
	}

	/**
	 * setValue
	 * セッション変数にセット
	 * @param string $key キー
	 * @param mixed $value 値
	 */
	public function setValue( $key, $value ) {
		$transient_data = get_transient( $this->session_id );
		if ( is_array( $transient_data ) ) {
			$transient_data[$key] = $value;
		} else {
			$transient_data = array( $key => $value );
		}
		set_transient( $this->session_id, $transient_data, $this->expiration );
	}

	/**
	 * pushValue
	 * セッション変数にセット
	 * @param string $key キー
	 * @param mixed $value 値
	 */
	public function pushValue( $key, $value ) {
		$transient_data = get_transient( $this->session_id );
		if ( is_array( $transient_data ) ) {
			$transient_data[$key][] = $value;
		} else {
			$transient_data = array( $key => array( $value ) );
		}
		set_transient( $this->session_id, $transient_data, $this->expiration );
	}

	/**
	 * getValue
	 * セッション変数から取得
	 * @param string $key キー
	 * @return mixed セッション値
	 */
	public function getValue( $key ) {
		$transient_data = get_transient( $this->session_id );
		if ( is_array( $transient_data ) && isset( $transient_data[$key] ) ) {
			return $transient_data[$key];
		}
		return null;
	}

	/**
	 * getValues
	 * セッション変数から取得
	 * @return array セッション値
	 */
	public function getValues() {
		$transient_data = get_transient( $this->session_id );
		if ( is_array( $transient_data ) ) {
			return $transient_data;
		}
		return array();
	}

	/**
	 * clearValue
	 * セッション変数を空に
	 * @param string $key キー
	 */
	public function clearValue( $key ) {
		$transient_data = get_transient( $this->session_id );
		if ( is_array( $transient_data ) && isset( $transient_data[$key] ) ) {
			unset( $transient_data[$key] );
			set_transient( $this->session_id, $transient_data, $this->expiration );
		}
	}

	/**
	 * clearValues
	 * セッション変数を空に
	 */
	public function clearValues() {
		delete_transient( $this->session_id );
	}
}