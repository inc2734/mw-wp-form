<?php
/**
 * Name: MW Session
 * Description: セッションクラス
 * Version: 2.0.1
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : July 17, 2012
 * Modified: June 23, 2014
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_Session {

	/**
	 * セッション名
	 */
	private $name;

	/**
	 * セッションID
	 */
	private $session_id;

	/**
	 * Transient の生存時間
	 */
	private $expiration = 1440;

	/**
	 * __construct
	 * @param string $name 識別子
	 */
	public function __construct( $name ) {
		$this->name = MWF_Config::NAME . '_session_' . $name;
		if ( isset( $_COOKIE[$this->name] ) ) {
			$session_id = $_COOKIE[$this->name];
		} else {
			$session_id = sha1( wp_create_nonce( $this->name ) . ip2long( $_SERVER['REMOTE_ADDR'] ) . uniqid() );
			$secure = apply_filters( 'mwform_secure_cookie', is_ssl() );
			setcookie( $this->name, $session_id, 0, COOKIEPATH, COOKIE_DOMAIN, $secure, true );
		}
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