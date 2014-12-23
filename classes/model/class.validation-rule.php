<?php
/**
 * Name: MW Validation Rule
 * Description: バリデーションルールの抽象クラス
 * Version: 1.0.1
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : July 19, 2014
 * Modified: August 8, 2014
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
abstract class MW_Validation_Rule {

	/**
	 * バリデーションルール名を指定
	 */
	protected $name;

	/*
	 * MW_WP_Form_Data オブジェクト
	 */
	protected $Data;

	/**
	 * __construct
	 * @param string $key 識別子
	 */
	public function __construct( $key ) {
		if ( !$this->getName() )
			exit( 'MW_Validation_Rule::$name must override.' );
		$this->Data = MW_WP_Form_Data::getInstance( $key );
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
	 * rule
	 * @param string $key name属性
	 * @param array $option
	 * @return string エラーメッセージ
	 */
	abstract public function rule( $key, array $options = array() );

	/**
	 * admin
	 * @param numeric $key バリデーションルールセットの識別番号
	 * @param array $value バリデーションルールセットの内容
	 */
	abstract public function admin( $key, $value );
}