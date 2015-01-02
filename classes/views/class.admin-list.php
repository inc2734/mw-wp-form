<?php
/**
 * Name       : MW WP Form Admin List View
 * Version    : 1.0.0
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : January 2, 2015
 * Modified   : 
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Admin_List_View extends MW_WP_Form_View {
	
	/**
	 * form_key
	 */
	public function form_key() {
		$post_id = $this->get( 'post_id' );
		printf(
			'<span id="formkey_field">[mwform_formkey key="%d"]</span>',
			$post_id
		);
	}
}