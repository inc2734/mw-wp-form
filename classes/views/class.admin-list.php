<?php
/**
 * Name       : MW WP Form Admin List View
 * Version    : 1.0.1
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : January 2, 2015
 * Modified   : February 8, 2015
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

	/**
	 * donate_link
	 * @param array $views
	 * @return array
	 */
	public function donate_link( $views ) {
		$donation = array( 'donation' => '<div class="donation"><p>' . __( 'Your contribution is needed for making this plugin better.', MWF_Config::DOMAIN ) . ' <a href="http://www.amazon.co.jp/registry/wishlist/39ANKRNSTNW40" class="button">' . __( 'Donate', MWF_Config::DOMAIN ) . '</a></p></div>' );
		$views = array_merge( $donation, $views );
		return $views;
	}
}