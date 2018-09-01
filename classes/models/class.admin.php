<?php
/**
 * Name       : MW WP Form Admin
 * Version    : 3.0.0
 * Author     : Takashi Kitajima
 * Author URI : https://2inc.org
 * Created    : February 21, 2013
 * Modified   : June 1, 2017
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Admin {

	/**
	 * Return all forms
	 *
	 * @return array Array of WP_Post
	 */
	public function get_forms() {
		return get_posts( array(
			'post_type'      => MWF_Config::NAME,
			'posts_per_page' => -1,
		) );
	}

	/**
	 * Return forms that using database
	 *
	 * @return array Array of WP_Post
	 */
	public function get_forms_using_database() {
		$forms_using_database = array();
		$forms = $this->get_forms();
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
