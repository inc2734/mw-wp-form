<?php
/**
 * Name: MW Validation Rule In
 * Description: 値が、配列で指定された中に含まれている
 * Version: 1.0.1
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : July 21, 2014
 * Modified: August 8, 2014
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_Validation_Rule_In extends MW_Validation_Rule {

	/**
	 * バリデーションルール名を指定
	 */
	protected $name = 'in';

	/**
	 * rule
	 * @param string $key name属性
	 * @param array $option
	 * @return string エラーメッセージ
	 */
	public function rule( $key, array $options = array() ) {
		$value = $this->Data->get( $key );
		if ( !is_null( $value ) && !MWF_Functions::is_empty( $value ) ) {
			$defaults = array(
				'options' => array(),
				'message' => __( 'This value is invalid.', MWF_Config::DOMAIN )
			);
			$options = array_merge( $defaults, $options );
			if ( !( is_array( $options['options'] ) && in_array( $value, $options['options'] ) ) ) {
				return $options['message'];
			}
		}
	}

	/**
	 * admin
	 * @param numeric $key バリデーションルールセットの識別番号
	 * @param array $value バリデーションルールセットの内容
	 */
	public function admin( $key, $value ) {
	}
}