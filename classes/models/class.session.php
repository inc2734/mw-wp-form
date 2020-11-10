<?php
/**
 * @package mw-wp-form
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * MW_WP_Form_Session
 */
class MW_WP_Form_Session {

	/**
	 * Session name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $session_id;

	/**
	 * Transient's survival time.
	 *
	 * @var int
	 */
	protected $expiration = 1440;

	/**
	 * Constructor.
	 *
	 * @param string $name Session name.
	 */
	public function __construct( $name ) {
		$this->name = MWF_Config::NAME . '_session_' . $name;

		if ( isset( $_COOKIE[ $this->name ] ) ) {
			$session_id = $_COOKIE[ $this->name ];
		} else {
			$session_id = sha1( wp_create_nonce( $this->name ) . ip2long( $this->get_remote_addr() ) . uniqid() );
			$secure     = apply_filters( 'mwform_secure_cookie', is_ssl() );
			try {
				set_error_handler( array( 'MW_WP_Form_Session', 'error_handler' ) );
				setcookie( $this->name, $session_id, 0, COOKIEPATH, COOKIE_DOMAIN, $secure, true );
			} catch ( ErrorException $e ) {
				// No process...
			}
		}

		$this->session_id = $session_id;
	}

	/**
	 * Error handler.
	 *
	 * @param int    $errno   Contains the level of the error raised.
	 * @param string $errstr  Contains the error message.
	 * @param string $errfile Which contains the filename that the error was raised in.
	 * @param int    $errline Which contains the line number the error was raised at.
	 */
	public static function error_handler(
		// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$errno,
		$errstr,
		$errfile,
		$errline
		// phpcs:disable
	) {
	}

	/**
	 * Save values.
	 *
	 * @param array $data Saving session data.
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
	 * Save a value.
	 *
	 * @param string $key   Session value name.
	 * @param mixed  $value Session value.
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
	 * Push a value.
	 *
	 * @param string $key   Session value name.
	 * @param mixed  $value Session value.
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
				$transient_data[ $key ]   = array( $transient_data[ $key ] );
				$transient_data[ $key ][] = $value;
			}
		}
		set_transient( $this->session_id, $transient_data, $this->expiration );
	}

	/**
	 * Return a value.
	 *
	 * @param string $key Session value name.
	 * @return mixed
	 */
	public function get( $key ) {
		$transient_data = get_transient( $this->session_id );
		if ( is_array( $transient_data ) && isset( $transient_data[ $key ] ) ) {
			return $transient_data[ $key ];
		}
	}

	/**
	 * Return all values.
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
	 * Clear a value.
	 *
	 * @param string $key Session value name.
	 */
	public function clear_value( $key ) {
		$transient_data = get_transient( $this->session_id );
		if ( is_array( $transient_data ) && isset( $transient_data[ $key ] ) ) {
			unset( $transient_data[ $key ] );
			set_transient( $this->session_id, $transient_data, $this->expiration );
		}
	}

	/**
	 * Clear values.
	 */
	public function clear_values() {
		delete_transient( $this->session_id );
	}

	/**
	 * Return $_SERVER['REMOTE_ADDR'].
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
