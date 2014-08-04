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
	 * バリデーションルール名を指定
	 */
	protected static $name;

	/*
	 * MW_WP_Form_Data オブジェクト
	 */
	protected $Data;

	/**
	 * __construct
	 * @param string $key 識別子
	 */
	public function __construct( $key ) {
		if ( !self::getName() )
			exit( 'MW_Validation_Rule::$name must override.' );
		$this->Data = MW_WP_Form_Data::getInstance( $key );
	}

	/**
	 * getName
	 * バリデーションルール名を返す
	 * @return string $this->name バリデーションルール名
	 */
	public static function getName() {
		$class = get_called_class();
		return $class::$name;
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
	public static function admin( $key, $value ) {
		exit( 'MW_Validation_Rule::admin must override.' );
	}
}