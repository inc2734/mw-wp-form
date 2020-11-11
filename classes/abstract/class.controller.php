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
	 * @param string $form_key Form key.
	 */
	protected function _render( $template, array $args = array(), $form_key = null ) {
		// phpcs:disable WordPress.PHP.DontExtract.extract_extract
		extract( $args );
		// phpcs:enable

		$template_dir  = plugin_dir_path( __FILE__ ) . '../../templates/';
		$template_path = $template_dir . $template . '.php';

		if ( ! file_exists( $template_path ) ) {
			return;
		}

		ob_start();
		include( $template_path );
		$html = ob_get_clean();
		$html = apply_filters( 'mwform_template_render', $html, $template, $args );
		if ( $form_key ) {
			$html = apply_filters( 'mwform_template_render_' . $form_key, $html, $template, $args );
		}
		echo $html;
	}
}
