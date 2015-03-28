<?php
class MW_WP_Form_Controller {

	protected $assign_data = array();
	
	protected function assign( $key, $value ) {
		$this->assign_data[$key] = $value;
	}

	protected function render( $template ) {
		extract( $this->assign_data );
		$template_dir  = plugin_dir_path( __FILE__ ) . '../../templates/';
		$template_path = $template_dir . $template . '.php';
		if ( file_exists( $template_path ) ) {
			include( $template_path );
			$this->assign_data = array();
		}
	}
}
