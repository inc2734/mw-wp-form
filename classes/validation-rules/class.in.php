<?php
/**
 * Name       : MW WP Form Validation Rule In
 * Description: 値が、配列で指定された中に含まれている
 * Version    : 1.1.1
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : July 21, 2014
 * Modified   : January 17, 2014
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Validation_Rule_In extends MW_WP_Form_Abstract_Validation_Rule {

	/**
	 * $name
	 * バリデーションルール名を指定
	 * @var string
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
		$value = ( string ) $value;
		if ( !is_null( $value ) && !MWF_Functions::is_empty( $value ) ) {
			$defaults = array(
				'options' => array(),
				'message' => __( 'This value is invalid.', MWF_Config::DOMAIN )
			);
			$options = array_merge( $defaults, $options );
			if ( is_array( $options['options'] ) ) {
				foreach ( $options['options'] as $option ) {
					$option = ( string ) $option;
					if ( $value === $option ) {
						return;
					}
				}
			}
			return $options['message'];
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