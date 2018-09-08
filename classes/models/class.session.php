<?php
/**
 * Name       : MW WP Form Session
 * Version    : 3.0.0
 * Author     : Takashi Kitajima
 * Author URI : https://2inc.org
 * Created    : July 17, 2012
 * Modified   : June 1, 2017
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Session {

	/**
	 * Session name
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $session_id;

	/**
	 * Transient's survival time
	 * @var int
	 */
	protected $expiration = 1440;

	/**
	 * @param string $name
	 */
	public function __construct( $name ) {
		$this->name = MWF_Config::NAME . '_session_' . $name;

		if ( isset( $_COOKIE[ $this->name ] ) ) {
			$session_id = $_COOKIE[ $this->name ];
		} else {
			$session_id = sha1( wp_create_nonce( $this->name ) . ip2long( $this->get_remote_addr() ) . uniqid() );
			$secure = apply_filters( 'mwform_secure_cookie', is_ssl() );
			try {
				set_error_handler( array( 'MW_WP_Form_Session', 'error_handler' ) );
				setcookie( $this->name, $session_id, 0, COOKIEPATH, COOKIE_DOMAIN, $secure, true );
			} catch ( ErrorException $e ) {
			}
		}

		$this->session_id = $session_id;
	}

	public static function error_handler( $errno, $errstr, $errfile, $errline ) {
	}

	/**
	 * Save values
	 *
	 * @param array $data
	 * @return void
	 */
	public function save( array $data ) {
		$transient_data = get_transient( $this->session_id );
		if ( ! is_array( $transient_data ) ) {
			$transient_data = array();
		}

		foreach ( $data as $key => $value ) {
			$transient_data[ $key ] = $value;
		}
		set_transient( $this->session_id, $transient_data, $this->expiration );
	}

	/**
	 * Save a value
	 *
	 * @param string
	 * @param mixed
	 * @return void
	 */
	public function set( $key, $value ) {
		$transient_data = get_transient( $this->session_id );
		if ( ! is_array( $transient_data ) ) {
			$transient_data = array();
		}

		$transient_data[ $key ] = $value;
		set_transient( $this->session_id, $transient_data, $this->expiration );
	}

	/**
	 * Push a value
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function push( $key, $value ) {
		$transient_data = get_transient( $this->session_id );
		if ( ! is_array( $transient_data ) ) {
			$transient_data = array();
		}

		if ( ! isset( $transient_data[ $key ] ) ) {
			$transient_data[ $key ] = array( $value );
		} else {
			if ( is_array( $transient_data[ $key ] ) ) {
				$transient_data[ $key ][] = $value;
			} else {
				$transient_data[ $key ] = array( $transient_data[ $key ] );
				$transient_data[ $key ][] = $value;
			}
		}
		set_transient( $this->session_id, $transient_data, $this->expiration );
	}

	/**
	 * Return a value
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get( $key ) {
		$transient_data = get_transient( $this->session_id );
		if ( is_array( $transient_data ) && isset( $transient_data[ $key ] ) ) {
			return $transient_data[ $key ];
		}
	}

	/**
	 * Return all values
	 *
	 * @return array
	 */
	public function gets() {
		$transient_data = get_transient( $this->session_id );
		if ( is_array( $transient_data ) ) {
			return $transient_data;
		}
		return array();
	}

	/**
	 * Clear a value
	 *
	 * @param string $key
	 * @return void
	 */
	public function clear_value( $key ) {
		$transient_data = get_transient( $this->session_id );
		if ( is_array( $transient_data ) && isset( $transient_data[ $key ] ) ) {
			unset( $transient_data[ $key ] );
			set_transient( $this->session_id, $transient_data, $this->expiration );
		}
	}

	/**
	 * Clear values
	 *
	 * @return void
	 */
	public function clear_values() {
		delete_transient( $this->session_id );
	}

	/**
	 * Return $_SERVER['REMOTE_ADDR']
	 *
	 * @return string
	 */
	protected function get_remote_addr() {
		if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			return $_SERVER['REMOTE_ADDR'];
		}
		return '127.0.0.1';
	}
}
