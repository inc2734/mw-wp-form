<?php
/**
 * Name       : MW WP Form Validation
 * Description: 与えられたデータに対してバリデーションエラーがあるかチェックする
 * Version    : 1.8.5
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : July 20, 2012
 * Modified   : April 15, 2015
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Validation {

	/**
	 * @var MW_WP_Form_Error
	 */
	protected $Error;

	/**
	 * バリデートをかける項目（name属性）と、それにかけるバリデーションの配列
	 * @var array
	 */
	protected $validate = array();

	/**
	 * バリデーションルールの配列
	 * @var array
	 */
	protected $validation_rules = array();

	/**
	 * __construct
	 *
	 * @param MW_WP_Form_Error $Error
	 */
	public function __construct( MW_WP_Form_Error $Error ) {
		$this->Error = $Error;
	}

	/**
	 * 各バリデーションルールクラスのインスタンスをセット
	 *
	 * @param array $validation_rules
	 */
	public function set_validation_rules( array $validation_rules ) {
		foreach ( $validation_rules as $validation_name => $instance ) {
			if ( is_callable( array( $instance, 'rule' ) ) ) {
				$this->validation_rules[$instance->getName()] = $instance;
			}
		}
	}

	/**
	 * セットされたバリデーションルールクラスを取得
	 *
	 * @return array
	 */
	public function get_validation_rules() {
		return $this->validation_rules;
	}

	/**
	 * バリデートが通っているかチェック
	 *
	 * @return bool
	 */
	protected function is_valid() {
		$errors = $this->Error->get_errors();
		if ( empty( $errors ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * set_rules
	 *
	 * @param MW_WP_Form_Setting $Setting
	 */
	public function set_rules( MW_WP_Form_Setting $Setting ) {
		$Data = MW_WP_Form_Data::getInstance();

		$rules = array();
		$validations = $Setting->get('validation' );
		if ( $validations ) {
			foreach ( $validations as $validation ) {
				foreach ( $validation as $rule => $options ) {
					if ( $rule == 'target' ) {
						continue;
					}
					if ( !is_array( $options ) ) {
						$options = array();
					}
					$this->set_rule( $validation['target'], $rule, $options );
				}
			}
		}
		$Akismet = new MW_WP_Form_Akismet();
		$akismet_check = $Akismet->check(
			$Setting->get( 'akismet_author' ),
			$Setting->get( 'akismet_author_email' ),
			$Setting->get( 'akismet_author_url' ),
			$Data
		);
		if ( $akismet_check ) {
			$this->set_rule( MWF_Config::AKISMET, 'akismet_check' );
		}
	}

	/**
	 * set_rule
	 *
	 * @param string ターゲットのname属性
	 * @param string バリデーションルール名
	 * @param array オプション
	 * @return MW_WP_Form_Validation
	 */
	public function set_rule( $key, $rule, array $options = array() ) {
		$rules = array(
			'rule'    => strtolower( $rule ),
			'options' => $options
		);
		$this->validate[$key][] = $rules;
		return $this;
	}

	/**
	 * validate実行
	 *
	 * @return bool エラーがなければ true
	 */
	public function check() {
		foreach ( $this->validate as $key => $rules ) {
			$this->_check( $key, $rules );
		}
		return $this->is_valid();
	}

	/**
	 * 特定の項目のvalidate実行
	 *
	 * @param string $key
	 * @return bool エラーがなければ true
	 */
	public function single_check( $key ) {
		$rules = array();
		if ( is_array( $this->validate ) && isset( $this->validate[$key] ) ) {
			$rules = $this->validate[$key];
			$this->_check( $key, $rules );
		}
		if ( $this->Error->get_error( $key ) ) {
			return false;
		}
		return true;
	}

	/**
	 * validate実行の実体
	 *
	 * @param string $key
	 * @param array $rules
	 */
	protected function _check( $key, array $rules ) {
		foreach ( $rules as $rule_set ) {
			if ( !isset( $rule_set['rule'] ) ) {
				continue;
			}
			$rule = $rule_set['rule'];
			if ( !isset( $this->validation_rules[$rule] ) ) {
				continue;
			}
			$options = array();
			if ( isset( $rule_set['options'] ) ) {
				$options = $rule_set['options'];
			}
			$validation_rule = $this->validation_rules[$rule];
			if ( is_callable( array( $validation_rule, 'rule' ) ) ) {
				$message = $validation_rule->rule( $key, $options );
				if ( !empty( $message ) ) {
					$this->Error->set_error( $key, $rule, $message );
				}
			}
		}
	}
}
