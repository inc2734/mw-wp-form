<?php
/**
 * @package mw-wp-form
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * MW_WP_Form_Controller
 */
class MW_WP_Form_Controller {

	/**
	 * Rendering template
	 *
	 * @param string $template {directory name}/{file name (no need extension)}.
	 * @param array  $args     Array of data you want to assign.
	 */
	protected function _render( $template, array $args = array() ) {
		// phpcs:disable WordPress.PHP.DontExtract.extract_extract
		extract( $args );
		// phpcs:enable

		$template_dir  = plugin_dir_path( __FILE__ ) . '../../templates/';
		$template_path = $template_dir . $template . '.php';

		if ( ! file_exists( $template_path ) ) {
			return;
		}

		include( $template_path );
	}
}
