<?php
/**
 * Name       : MW WP Form Controller
 * Version    : 1.0.0
 * Author     : Takashi Kitajima
 * Author URI : https://2inc.org
 * Created    : March 28, 2015
 * Modified   :
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Controller {

	/**
	 * Rendering template
	 *
	 * @param string $template {directory name}/{file name (no need extension)}
	 * @param array Array of data you want to assign
	 * @return void
	 */
	protected function _render( $template, array $args = array() ) {
		extract( $args );
		$template_dir  = plugin_dir_path( __FILE__ ) . '../../templates/';
		$template_path = $template_dir . $template . '.php';

		if ( ! file_exists( $template_path ) ) {
			return;
		}

		include( $template_path );
	}
}
