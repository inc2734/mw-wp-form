<?php
/**
 * Name       : MW WP Form Error
 * Version    : 2.0.0
 * Author     : Takashi Kitajima
 * Author URI : https://2inc.org
 * Created    : July 17, 2012
 * Modified   : May 17, 2017
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Error {

	/**
	 * @var array of MW_WP_Form_Error
	 */
	protected static $Instances;

	/**
	 * @var MW_WP_Form_Sesion
	 */
	protected $Session;

	/**
 	 * @param string $form_key
	 */
	private function __construct( $form_key ) {
		$this->Session = new MW_WP_Form_Session( $form_key . '-errors' );
	}

	/**
	 * Instantiation MW_WP_Form_Error
	 *
 	 * @param string $form_key
	 */
	public static function connect( $form_key ) {
		if ( isset( self::$Instances[ $form_key ] ) ) {
			return self::$Instances[ $form_key ];
		}

		self::$Instances[ $form_key ] = new self( $form_key );
		return self::$Instances[ $form_key ];
	}

	/**
	 * Set the error message
	 *
	 * @param string $key name attribute
	 * @param string $rule
	 * @param string $message
	 */
	public function set_error( $key, $rule, $message ) {
		if ( ! is_string( $message ) ) {
			exit( 'The Validate error message must be string!' );
		}
		$errors = $this->Session->get( $key );
		if ( ! is_array( $errors ) ) {
			$errors = array();
		}
		$errors[ $rule ] = $message;
		$this->Session->set( $key, $errors );
	}

	/**
	 * エラーメッセージを返す
	 *
	 * @param string $key name attribute
	 * @return array
	 */
	public function get_error( $key ) {
		$errors = $this->Session->get( $key );
		if ( is_null( $errors ) ) {
			return array();
		}
		return $errors;
	}

	/**
	 * 全てのエラーメッセージを返す
	 *
	 * @return array
	 */
	public function get_errors() {
		$errors = $this->Session->gets();
		if ( ! is_array( $errors ) ) {
			return array();
		}
		return $errors;
	}

	/**
	 * Clear all values
	 */
	public function clear_errors() {
		$this->Session->clear_values();
	}
}
