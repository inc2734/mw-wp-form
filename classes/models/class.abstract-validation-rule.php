<?php
/**
 * Name       : MW WP Form Abstract Validation Rule
 * Description: バリデーションルールの抽象クラス
 * Version    : 1.1.1
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : July 19, 2014
 * Modified   : March 26, 2015
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
abstract class MW_WP_Form_Abstract_Validation_Rule {

	/**
	 * バリデーションルール名を指定
	 * @var string
	 */
	protected $name;

	/**
	 * @var MW_WP_Form_Data
	 */
	protected $Data;

	/**
	 * __construct
	 */
	public function __construct() {
		if ( !$this->getName() ) {
			exit( 'MW_WP_Form_Abstract_Validation_Rule::$name must override.' );
		}
	}

	/**
	 * MW_WP_Form_Data を注入
	 *
	 * @param MW_WP_Form_Data $Data
	 */
	public function set_Data( MW_WP_Form_Data $Data ) {
		$this->Data = $Data;
	}

	/**
	 * バリデーションルール名を返す
	 *
	 * @return string $this->name バリデーションルール名
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * バリデーションチェック
	 *
	 * @param string $key name属性
	 * @param array $option
	 * @return string エラーメッセージ
	 */
	abstract public function rule( $key, array $options = array() );

	/**
	 * 設定パネルに追加
	 *
	 * @param numeric $key バリデーションルールセットの識別番号
	 * @param array $value バリデーションルールセットの内容
	 */
	abstract public function admin( $key, $value );
}
