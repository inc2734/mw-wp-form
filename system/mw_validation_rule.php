<?php
/**
 * Name: MW Validation Rule
 * Description: バリデーションルールの抽象クラス
 * Version: 1.0.0
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : July 19, 2014
 * Modified:
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
abstract class MW_Validation_Rule {

	/**
	 * 文字コード
	 */
	protected $ENCODE = 'utf-8';

	/**
	 * バリデーションルール名を指定
	 */
	protected $name;

	/**
	 * __construct
	 */
	public function __construct() {
		if ( !$this->name )
			exit;
	}

	/**
	 * getName
	 * バリデーションルール名を返す
	 * @return string $this->name バリデーションルール名
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * isEmpty
	 * 値が空（0は許可）
	 * @param mixed
	 * @return bool
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
	 * @param MW_WP_Form_Data $Data
	 * @param string $key name属性
	 * @param array $option
	 * @return string エラーメッセージ
	 */
	abstract public function rule( MW_WP_Form_Data $Data, $key, $options = array() );

	/**
	 * admin
	 * @param numeric $key バリデーションルールセットの識別番号
	 * @param array $value バリデーションルールセットの内容
	 */
	abstract public function admin( $key, $value );
}