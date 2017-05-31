<?php
class MWF_Deprecated {

	public function __construct() {
		add_action( 'mwform_after_exec_shortcode', array( $this, '_mwform_after_exec_shortcode' ), 10000 );
	}

	public function _mwform_after_exec_shortcode() {
		if ( has_filter( 'mwform_after_exec_shortcode' ) ) {
			MWF_Functions::deprecated_message(
				'mwform_after_exec_shortcode',
				'mwform_start_main_process'
			);
		}
	}
}
new MWF_Deprecated();
