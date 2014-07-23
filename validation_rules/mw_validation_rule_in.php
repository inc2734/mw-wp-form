<?php
/**
 * Name: MW Validation Rule In
 * URI: http://2inc.org
 * Description: 値が、配列で指定された中に含まれている
 * Version: 1.0.0
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : July 21, 2014
 * Modified:
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_Validation_Rule_In extends mw_validation_rule {

	/**
	 * バリデーションルール名を指定
	 */
	protected $name = 'in';

	/**
	 * rule
	 * @param mw_wp_form_data $Data
	 * @param string $key name属性
	 * @param array $option
	 * @return string エラーメッセージ
	 */
	public function rule( mw_wp_form_data $Data, $key, $options = array() ) {
		$value = $Data->get( $key );
		if ( !is_null( $value ) && !$this->isEmpty( $value ) ) {
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