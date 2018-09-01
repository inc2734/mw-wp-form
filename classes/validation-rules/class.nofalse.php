<?php
/**
 * Name       : MW WP Form Validation Rule noFalse
 * Version    : 2.0.0
 * Author     : Takashi Kitajima
 * Author URI : https://2inc.org
 * Created    : July 21, 2014
 * Modified   : May 30, 2017
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Validation_Rule_noFalse extends MW_WP_Form_Abstract_Validation_Rule {

	/**
	 * Validation rule name
	 * @var string
	 */
	protected $name = 'nofalse';

	/**
	 * Validation process
	 *
	 * @param string $name
	 * @param array $option
	 * @return string Error message
	 */
	public function rule( $name, array $options = array() ) {
		$value = $this->Data->get( $name );

		if ( is_null( $value ) || ! empty( $value ) ) {
			return;
		}

		$defaults = array(
			'message' => __( 'Please enter.', 'mw-wp-form' )
		);
		$options = array_merge( $defaults, $options );
		return $options['message'];
	}

	/**
	 * Add setting field to validation rule setting panel
	 *
	 * @param numeric $key ID of validation rule
	 * @param array $value Content of validation rule
	 * @return void
	 */
	public function admin( $key, $value ) {
	}
}
