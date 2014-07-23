<?php
/**
 * Name: MW Validation Rule
 * URI: http://2inc.org
 * Description: バリデーションルールの抽象クラス
 * Version: 1.0.0
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : July 19, 2014
 * Modified:
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
abstract class mw_validation_rule {
	private $key;
	protected $Data;
	protected $ENCODE = 'utf-8';

	/**
	 * バリデーションルール名を指定
	 */
	protected $name;

	public function __construct() {
		if ( !$this->name )
			exit;
	}

	public function get_name() {
		return $this->name;
	}

	public function set_data(  ) {
		$this->Data = $Data;
	}

	/**
	 * isEmpty
	 * 値が空（0は許可）
	 * @param	Mixed
	 * @return	Boolean
	 */
	protected function isEmpty( $value ) {
		if ( $value === array() || $value === '' || $value === null ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * rule
	 * @param mw_wp_form_data $Data
	 * @param string $key name属性
	 * @param array $option
	 * @return string エラーメッセージ
	 */
	abstract public function rule( mw_wp_form_data $Data, $key, $options = array() );

	/**
	 * admin
	 * @param numeric $key バリデーションルールセットの識別番号
	 * @param array $value バリデーションルールセットの内容
	 */
	abstract public function admin( $key, $value );
}