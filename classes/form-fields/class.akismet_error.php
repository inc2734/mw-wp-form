<?php
/**
 * @package mw-wp-form
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * MW_WP_Form_Field_Akismet_Error
 */
class MW_WP_Form_Field_Akismet_Error extends MW_WP_Form_Abstract_Form_Field {

	/**
	 * Types of form type.
	 * input|select|button|input_button|error|other.
	 *
	 * @var string
	 */
	public $type = 'error';

	/**
	 * Set shortcode_name and display_name.
	 * Overwrite required for each child class.
	 *
	 * @return array
	 */
	protected function set_names() {
		return array(
			'shortcode_name' => 'mwform_akismet_error',
			'display_name'   => __( 'Akismet Error', 'mw-wp-form' ),
		);
	}

	/**
	 * Set default attributes.
	 *
	 * @return array
	 */
	protected function set_defaults() {
		return array();
	}

	/**
	 * Callback of add shortcode for input page.
	 *
	 * @return string
	 */
	protected function input_page() {
		$error = $this->get_error( MWF_Config::AKISMET );
		if ( $error ) {
			return sprintf(
				'<span class="akismet_error">%s</span>',
				$this->get_error( MWF_Config::AKISMET )
			);
		}
	}

	/**
	 * Callback of add shortcode for confirm page.
	 */
	protected function confirm_page() {
	}
}
