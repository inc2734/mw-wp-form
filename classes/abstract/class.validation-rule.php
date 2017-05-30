<?php
/**
 * Name       : MW WP Form Abstract Validation Rule
 * Version    : 2.0.0
 * Author     : Takashi Kitajima
 * Author URI : https://2inc.org
 * Created    : July 19, 2014
 * Modified   : May 30, 2017
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
abstract class MW_WP_Form_Abstract_Validation_Rule {

	/**
	 * Validation rule name
	 * @var string
	 */
	protected $name;

	/**
	 * @var MW_WP_Form_Data
	 */
	protected $Data;

	public function __construct() {
		if ( ! $this->getName() ) {
			exit( 'MW_WP_Form_Abstract_Validation_Rule::$name must override.' );
		}

		add_filter( 'mwform_validation_rules', array( $this, '_mwform_validation_rules' ) );
	}

	/**
	 * Generate array of validation rules
	 *
	 * @param array $validation_rules rray of MW_WP_Form_Abstract_Validation_Rule
	 * @return array
	 */
	public function _mwform_validation_rules( array $validation_rules ) {
		$validation_rules[ $this->getName() ] = $this;
		return $validation_rules;
	}

	/**
	 * Inject MW_WP_Form_Data
	 *
	 * @todo なくしてコンストラクターインジェクションにしたい
	 * @param MW_WP_Form_Data $Data
	 * @return void
	 */
	public function set_Data( MW_WP_Form_Data $Data ) {
		$this->Data = $Data;
	}

	/**
	 * Return validation rule name
	 *
	 * @return string Validation rule name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Validation process
	 *
	 * @param string $name
	 * @param array $option
	 * @return string Error message
	 */
	abstract public function rule( $name, array $options = array() );

	/**
	 * Add setting field to validation rule setting panel
	 *
	 * @param numeric $key ID of validation rule
	 * @param array $value Content of validation rule
	 * @return void
	 */
	abstract public function admin( $key, $value );
}
