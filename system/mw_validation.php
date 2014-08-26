<?php
/**
 * Name: MW Validation
 * Description: バリデーションクラス
 * Version: 1.7.1
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : July 20, 2012
 * Modified: August 26, 2014
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_Validation {

	/**
	 * フォーム識別子
	 */
	private $key;

	/**
	 * MW_Error オブジェクト
	 */
	protected $Error;

	/**
	 * バリデートをかける項目とかけるバリデーションの種類の一覧
	 */
	public $validate = array();

	/**
	 * バリデーションルールの一覧
	 */
	private $validation_rules = array();

	/**
	 * __construct
	 * @param string $key 識別子
	 */
	public function __construct( $key ) {
		$this->key = $key;
		$this->Error = new MW_Error();
	}

	/**
	 * add_validation_rule
	 * 各バリデーションルールクラスのインスタンスをセット
	 * @param string $rule_name
	 * @param MW_Validation_Rule $instance
	 */
	public function add_validation_rule( $rule_name, $instance ) {
		$this->validation_rules[$rule_name] = $instance;
	}

	/**
	 * Error
	 * エラーオブジェクトを返す
	 * @return MW_Error エラーオブジェクト
	 */
	public function Error() {
		return $this->Error;
	}

	/**
	 * isValid
	 * バリデートが通っているかチェック
	 * @return bool
	 */
	protected function isValid() {
		$errors = $this->Error->getErrors();
		if ( empty( $errors ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * setRule
	 * バリデートが通っているかチェック
	 * @param string キー
	 * @param string バリデーションルール名
	 * @param array オプション
	 * @return bool
	 */
	public function setRule( $key, $rule, array $options = array() ) {
		$rules = array(
			'rule' => strtolower( $rule ),
			'options' => $options
		);
		$this->validate[$key][] = $rules;
		return $this;
	}

	/**
	 * check
	 * validate実行
	 * @return bool エラーがなければ true
	 */
	public function check() {
		$Data = MW_WP_Form_Data::getInstance( $this->key );
		foreach ( $this->validate as $key => $rules ) {
			$this->_check( $key, $rules );
		}
		return $this->isValid();
	}

	/**
	 * singleCheck
	 * 特定の項目のvalidate実行
	 * @param string $key
	 * @return bool エラーがなければ true
	 */
	public function singleCheck( $key ) {
		$Data = MW_WP_Form_Data::getInstance( $this->key );
		$rules = array();
		if ( is_array( $this->validate ) && isset( $this->validate[$key] ) ) {
			$rules = $this->validate[$key];
			if ( $this->_check( $key, $rules ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * _check
	 * validate実行の実態
	 * @param string $key
	 * @param array $rules
	 * @return bool エラーがあれば true
	 */
	protected function _check( $key, array $rules ) {
		foreach ( $rules as $ruleSet ) {
			if ( isset( $ruleSet['rule'] ) ) {
				$rule = $ruleSet['rule'];
				$options = array();
				if ( isset( $ruleSet['options'] ) ) {
					$options = $ruleSet['options'];
				}
				if ( isset( $this->validation_rules[$rule] )
					 && is_callable( array( $this->validation_rules[$rule], 'rule' ) ) ) {

					$message = $this->validation_rules[$rule]->rule( $key, $options );
					if ( !empty( $message ) ) {
						$this->Error->setError( $key, $rule, $message );
						return true;
					}
				}
			}
		}
	}
}
