<?php
/**
 * @package mw-wp-form
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * MW_WP_Form_Json_Parser
 */
class MW_WP_Form_Json_Parser {

	/**
	 * Json from shortcode
	 *
	 * @var string
	 */
	protected $maybe_json;

	/**
	 * Waiting for single quotation
	 *
	 * @var bool
	 */
	protected $s_quote_stay = false;

	/**
	 * Waiting for double quotation
	 *
	 * @var bool
	 */
	protected $d_quote_stay = false;

	/**
	 * Waiting for colon
	 *
	 * @var bool
	 */
	protected $colon_stay = true;

	/**
	 * Array index to generate correct json
	 *
	 * @var int
	 */
	protected $index = 0;

	/**
	 * A flag that identifies whether the character to be set is key or value
	 *
	 * @var string key|value
	 */
	protected $key = 'key';

	/**
	 * Array of generating json
	 *
	 * @var array
	 */
	protected $temp = array();

	/**
	 * Constructor.

	 * @param string $maybe_json json.
	 */
	public function __construct( $maybe_json ) {
		$this->maybe_json = $maybe_json;
		$this->set_default_params();
	}

	/**
	 * Set initial value of each property.
	 */
	protected function set_default_params() {
		$this->s_quote_stay = false;
		$this->d_quote_stay = false;
		$this->colon_stay   = true;
		$this->index        = 0;
		$this->key          = 'key';
		$this->temp         = array();
	}

	/**
	 * Add a character to an array for generating json.
	 *
	 * @param string $character Character.
	 */
	public function push_character( $character ) {
		if ( ! isset( $this->temp[ $this->index ][ $this->key ] ) ) {
			$this->temp[ $this->index ][ $this->key ] = '';
		}
		$this->temp[ $this->index ][ $this->key ] .= $character;
	}

	/**
	 * Proccess for single quotation.
	 */
	protected function proccess_single_quote() {
		if ( ! $this->d_quote_stay ) {
			if ( ! $this->s_quote_stay ) {
				$this->s_quote_stay = true;
			} else {
				$this->s_quote_stay = false;
			}
		} else {
			$this->push_character( "'" );
		}
	}

	/**
	 * Proccess for double quotation.
	 */
	protected function proccess_double_quote() {
		if ( ! $this->s_quote_stay ) {
			if ( ! $this->d_quote_stay ) {
				$this->d_quote_stay = true;
			} else {
				$this->d_quote_stay = false;
			}
		} else {
			$this->push_character( '"' );
		}
	}

	/**
	 * Proccess for connma.
	 */
	protected function proccess_comma() {
		if ( ! $this->s_quote_stay || ! $this->d_quote_stay ) {
			$this->index ++;
			$this->colon_stay = true;
			$this->key        = 'key';
		} else {
			$this->push_character( ':' );
		}
	}

	/**
	 * Proccess for colon.
	 */
	protected function proccess_colon() {
		if ( $this->colon_stay ) {
			$this->colon_stay = false;
			$this->key        = 'value';
		} else {
			$this->push_character( ':' );
		}
	}

	/**
	 * Return json based on the array for generating json.
	 *
	 * @return json
	 */
	public function json_encode() {
		$js = array();
		foreach ( $this->temp as $param ) {
			if ( ! isset( $param['key'] ) ) {
				continue;
			}
			$key   = $param['key'];
			$value = '';
			if ( isset( $param['value'] ) ) {
				$value = $param['value'];
			}
			$value = trim( $value );
			if ( preg_match( '/^[\-\+]?[\d]+$/', $value ) ) {
				$value = (int) $value;
			} elseif ( mb_strtolower( 'true' ) === $value ) {
				$value = true;
			} elseif ( mb_strtolower( 'false' ) === $value ) {
				$value = false;
			} elseif ( mb_strtolower( 'null' ) === $value ) {
				$value = null;
			}
			$js[ trim( $key ) ] = $value;
		}
		return json_encode( $js );
	}

	/**
	 * Return json based on the array for generating json.
	 *
	 * @return json
	 */
	public function create_json() {
		$_js = trim( $this->maybe_json, '{}' );
		$_js = preg_split( '//u', $_js, -1, PREG_SPLIT_NO_EMPTY );

		foreach ( $_js as $character ) {
			if ( "'" === $character ) {
				// シングルクォーテーション
				$this->proccess_single_quote();
			} elseif ( '"' === $character ) {
				// ダブルクォーテーション
				$this->proccess_double_quote();
			} elseif ( ',' === $character ) {
				// カンマ
				$this->proccess_comma();
			} elseif ( ':' === $character ) {
				// コロン
				$this->proccess_colon();
			} else {
				// その他の文字
				$this->push_character( $character );
			}
		}

		$json = $this->json_encode();
		$this->set_default_params();
		return $json;
	}
}
