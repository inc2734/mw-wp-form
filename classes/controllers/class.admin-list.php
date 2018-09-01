<?php
/**
 * Name       : MW WP Form Admin List Controller
 * Version    : 2.0.0
 * Author     : Takashi Kitajima
 * Author URI : https://2inc.org
 * Created    : January 1, 2015
 * Modified   : May 30, 2017
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Admin_List_Controller extends MW_WP_Form_Controller {

	public function __construct() {
		$screen = get_current_screen();
		add_filter( 'views_' . $screen->id , array( $this, '_donate_link' ) );
		add_action( 'admin_head'           , array( $this, '_add_columns' ) );
		add_action( 'admin_enqueue_scripts', array( $this, '_admin_enqueue_scripts' ) );
	}

	/**
	 * Return Donate link html
	 *
	 * @param array $views
	 * @return array
	 */
	public function _donate_link( $views ) {
		$donation = array(
			'donation' =>
				'<div class="donation"><p>' .
				__( 'Your contribution is needed for making this plugin better.', 'mw-wp-form' ) .
				' <a href="https://www.amazon.co.jp/registry/wishlist/39ANKRNSTNW40" class="button">' .
				__( 'Donate', 'mw-wp-form' ) . '</a></p></div>'
		);
		$views = array_merge( $donation, $views );
		return $views;
	}

	/**
	 * Hooked for adding columns
	 *
	 * @return void
	 */
	public function _add_columns() {
		add_filter( 'manage_posts_columns'      , array( $this, '_manage_posts_columns' ) );
		add_action( 'manage_posts_custom_column', array( $this, '_manage_posts_custom_column' ), 10, 2 );
	}

	/**
	 * Enqueue assets
	 *
	 * @return void
	 */
	public function _admin_enqueue_scripts() {
		$url = plugins_url( MWF_Config::NAME );
		wp_enqueue_style( MWF_Config::NAME . '-admin-list', $url . '/css/admin-list.css' );
	}

	/**
	 * Add columns
	 *
	 * @param array $columns
	 * @return array
	 */
	public function _manage_posts_columns( $columns ) {
		$date = $columns['date'];
		unset( $columns['date'] );
		$columns['mwform_form_key'] = __( 'Form Key', 'mw-wp-form' );
		$columns['date'] = $date;
		return $columns;
	}

	/**
	 * Render column for form key
	 *
	 * @param string $column_name
	 * @param int $post_id
	 */
	public function _manage_posts_custom_column( $column_name, $post_id ) {
		if ( 'mwform_form_key' === $column_name ) {
			$this->_render( 'admin-list/form-key', array(
				'post_id' => get_the_ID(),
			) );
		}
	}
}
