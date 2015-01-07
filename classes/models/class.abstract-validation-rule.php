<?php
/**
 * Name       : MW WP Form Abstract Validation Rule
 * Description: バリデーションルールの抽象クラス
 * Version    : 1.1.0
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : July 19, 2014
 * Modified   : December 31, 2014
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
abstract class MW_WP_Form_Abstract_Validation_Rule extends MW_Validation_Rule {
}
abstract class MW_Validation_Rule {

	/**
	 * $name
	 * バリデーションルール名を指定
	 * @var string
	 */
	protected $name;

	/**
	 * $Data
	 * @var MW_WP_Form_Data
	 */
	protected $Data;

	/**
	 * __construct
	 */
	public function __construct() {
		$parent_class = get_parent_class( $this );
		$class        = get_class( $this );
		if ( is_admin() && in_array( 'MW_Validation_Rule', array( $parent_class, $class ) ) ) {
			MWF_Functions::deprecated_message(
				'MW_Validation_Rule',
				'MW_WP_Form_Abstract_Validation_Rule'
			);
		}
		if ( !$this->getName() ) {
			exit( 'MW_WP_Form_Abstract_Validation_Rule::$name must override.' );
		}
	}

	/**
	 * set_Data
	 * @param MW_WP_Form_Data $Data
	 */
	public function set_Data( MW_WP_Form_Data $Data ) {
		$this->Data = $Data;
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
