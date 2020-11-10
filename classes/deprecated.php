<?php
/**
 * @package mw-wp-form
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * MWF_Deprecated
 */
class MWF_Deprecated {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'mwform_after_exec_shortcode', array( $this, '_mwform_after_exec_shortcode2' ), 10000 );
	}

	/**
	 * Deprecated message for mwform_after_exec_shortcode.
	 */
	public function _mwform_after_exec_shortcode2() {
		remove_action(
			'mwform_after_exec_shortcode',
			array( $this, '_mwform_after_exec_shortcode2' ),
			10000
		);

		if ( has_action( 'mwform_after_exec_shortcode' ) ) {
			MWF_Functions::deprecated_message(
				'mwform_after_exec_shortcode',
				'mwform_start_main_process'
			);
		}
	}
}

new MWF_Deprecated();
