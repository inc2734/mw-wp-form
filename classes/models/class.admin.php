<?php
/**
 * @package mw-wp-form
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * MW_WP_Form_Admin
 */
class MW_WP_Form_Admin {

	/**
	 * Return all forms.
	 *
	 * @return array
	 */
	public function get_forms() {
		return get_posts(
			array(
				'post_type'      => MWF_Config::NAME,
				'posts_per_page' => -1,
			)
		);
	}

	/**
	 * Return forms that using database.
	 *
	 * @return array
	 */
	public function get_forms_using_database() {
		$forms_using_database = array();
		$forms                = $this->get_forms();
		foreach ( $forms as $form ) {
			$Setting = new MW_WP_Form_Setting( $form->ID );
			if ( ! $Setting->get( 'usedb' ) ) {
				continue;
			}
			$forms_using_database[ $form->ID ] = $form;
		}
		return $forms_using_database;
	}
}
