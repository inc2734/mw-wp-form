<?php
/**
 * @package mw-wp-form
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * MW_WP_Form_Abstract_Validation_Rule
 */
abstract class MW_WP_Form_Abstract_Validation_Rule {

	/**
	 * Validation rule name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * @var MW_WP_Form_Data
	 */
	protected $Data;

	/**
	 * Constructor.
	 *
	 * @param MW_WP_Form_Data $Data MW_WP_Form_Data object.
	 */
	public function __construct( MW_WP_Form_Data $Data = null ) {
		if ( ! $this->get_name() ) {
			exit( 'MW_WP_Form_Abstract_Validation_Rule::$name must override.' );
		}

		if ( ! is_null( $Data ) ) {
			$this->Data = $Data;
		}

		add_filter( 'mwform_validation_rules', array( $this, '_mwform_validation_rules' ) );
	}

	/**
	 * Generate array of validation rules.
	 *
	 * @param array $validation_rules Array of MW_WP_Form_Abstract_Validation_Rule.
	 * @return array
	 */
	public function _mwform_validation_rules( array $validation_rules ) {
		$validation_rules[ $this->get_name() ] = $this;
		return $validation_rules;
	}

	/**
	 * Inject MW_WP_Form_Data.
	 *
	 * @deprecated
	 *
	 * @param MW_WP_Form_Data $Data MW_WP_Form_Data object.
	 */
	public function set_Data( MW_WP_Form_Data $Data ) {
		$this->Data = $Data;
	}

	/**
	 * Return true when set $this->Data.
	 *
	 * @return boolean
	 */
	public function is_set_Data() {
		return ( is_a( $this->Data, 'MW_WP_Form_Data' ) );
	}

	/**
	 * Return validation rule name.
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Return validation rule name.
	 *
	 * @deprecated
	 *
	 * @return string
	 */
	public function getName() {
		MWF_Functions::deprecated_message(
			get_class() . '::getName()',
			get_class() . '::get_name()'
		);
		return $this->get_name();
	}

	/**
	 * Validation process.
	 *
	 * @param string $name    Validation name.
	 * @param array  $options Validation options.
	 * @return string
	 */
	abstract public function rule( $name, array $options = array() );

	/**
	 * Add setting field to validation rule setting panel.
	 *
	 * @param int   $key   ID of validation rule.
	 * @param array $value Content of validation rule.
	 */
	abstract public function admin( $key, $value );
}
