<?php
/**
 * Name       : MW WP Form Token Check
 * Version    : 1.0.0
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : December 31, 2014
 * Modified   :
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Token_Check {

	/**
	 * $Data
	 * @var MW_WP_Form_Data
	 */
	protected $Data;

	/**
	 * $token_name
	 * @var string
	 */
	protected $token_name = 'token';

	/**
	 * COMPLETE_TWICE
	 * 完了画面の判定用
	 * @var string
	 */
	const COMPLETE_TWICE = '__complete_twice_flg';

	/**
	 * $key
	 * nonceの名前
	 * @var string
	 */
	protected $key;

	/**
	 * __construct
	 * @param string $key nonceの名前
	 * @param MW_WP_Form_Data $Data
	 */
	public function __construct( $key, MW_WP_Form_Data $Data ) {
		$this->key  = $key;
		$this->Data = $Data;
	}

	/**
	 * initialize
	 */
	public function initialize() {
		add_filter( 'mwform_form_end_html', array( $this, 'mwform_form_end_html' ) );
	}

	/**
	 * mwform_form_end_html
	 * @param string $html
	 * @return string $html
	 */
	public function mwform_form_end_html( $html ) {
		$html .= wp_nonce_field( $this->key, $this->token_name, true, false );
		return $html;
	}

	/**
	 * get_token_name
	 * nonce用のキーを返す
	 * @return string
	 */
	public function get_token_name() {
		return $this->token_name;
	}

	/**
	 * check
	 * トークンチェック
	 * @return bool
	 */
	public function check() {
		if ( isset( $_POST[$this->token_name] ) ) {
			$request_token = $_POST[$this->token_name];
		}
		$values = $this->Data->gets();
		if ( isset( $request_token ) && wp_verify_nonce( $request_token, $this->key ) ) {
			$this->Data->set( self::COMPLETE_TWICE, true );
			return true;
		} elseif ( empty( $_POST ) && !empty( $values ) && $this->Data->get_raw( self::COMPLETE_TWICE ) ) {
			$this->Data->clear_value( self::COMPLETE_TWICE );
			return true;
		}
		return false;
	}
}