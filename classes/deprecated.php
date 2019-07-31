<?php
class MWF_Deprecated {

	public function __construct() {
		add_action( 'mwform_after_exec_shortcode', array( $this, '_mwform_after_exec_shortcode2' ), 10000 );
	}

	public function _mwform_after_exec_shortcode2() {
		global $wp_filter;

		remove_action( 'mwform_after_exec_shortcode', array( $this, '_mwform_after_exec_shortcode2' ), 10000 );

		if ( has_action( 'mwform_after_exec_shortcode' ) ) {
			MWF_Functions::deprecated_message(
				'mwform_after_exec_shortcode',
				'mwform_start_main_process'
			);
		}
	}
}

new MWF_Deprecated();
