<?php
/**
 * Name       : MW WP Form Admin
 * Description: 管理画面クラス
 * Version    : 2.1.1
 * Author     : Takashi Kitajima
 * Author URI : https://2inc.org
 * Created    : February 21, 2013
 * Modified   : March 25, 2017
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Admin {

	/**
	 * get_forms
	 * @return array フォーム（WP_Post）の配列
	 */
	public function get_forms() {
		$forms = get_posts( array(
			'post_type'      => MWF_Config::NAME,
			'posts_per_page' => -1,
		) );
		return $forms;
	}

	/**
	 * get_forms_using_database
	 * @return array データベースに保存が有効なフォーム（WP_Post）の配列
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
