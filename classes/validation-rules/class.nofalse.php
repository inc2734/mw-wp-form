<?php
/**
 * @package mw-wp-form
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * MW_WP_Form_Validation_Rule_noFalse
 */
class MW_WP_Form_Validation_Rule_noFalse extends MW_WP_Form_Abstract_Validation_Rule {

	/**
	 * Validation rule name.
	 *
	 * @var string
	 */
	protected $name = 'nofalse';

	/**
	 * Validation process.
	 *
	 * @param string $name    Validation name.
	 * @param array  $options Validation options.
	 * @return string
	 */
	public function rule( $name, array $options = array() ) {
		$value = $this->Data->get( $name );

		if ( is_null( $value ) || ! empty( $value ) ) {
			return;
		}

		$defaults = array(
			'message' => __( 'Please enter.', 'mw-wp-form' ),
		);
		$options  = array_merge( $defaults, $options );
		return $options['message'];
	}

	/**
	 * Add setting field to validation rule setting panel.
	 *
	 * @param numeric $key ID of validation rule.
	 * @param array   $value Content of validation rule.
	 * @return void
	 */
	public function admin(
		// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$key,
		$value
		// phpcs:enable
	) {
	}
}
