<?php
/**
 * Name       : MW WP Form Admin List Controller
 * Version    : 1.1.0
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : January 1, 2015
 * Modified   : March 27, 2015
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Admin_List_Controller extends MW_WP_Form_Controller {
	
	/**
	 * initialize
	 */
	public function initialize() {
		$screen = get_current_screen();
		add_filter( 'views_' . $screen->id , array( $this, 'donate_link' ) );
		add_action( 'admin_head'           , array( $this, 'add_columns' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * 寄付リンクを出力
	 *
	 * @param array $views
	 * @return array
	 */
	public function donate_link( $views ) {
		$donation = array(
			'donation' =>
				'<div class="donation"><p>' .
				__( 'Your contribution is needed for making this plugin better.', 'mw-wp-form' ) .
				' <a href="http://www.amazon.co.jp/registry/wishlist/39ANKRNSTNW40" class="button">' .
				__( 'Donate', 'mw-wp-form' ) . '</a></p></div>'
		);
		$views = array_merge( $donation, $views );
		return $views;
	}

	/**
	 * admin_enqueue_scripts
	 */
	public function admin_enqueue_scripts() {
		$url = plugins_url( MWF_Config::NAME );
		wp_enqueue_style( MWF_Config::NAME . '-admin-list', $url . '/css/admin-list.css' );
	}

	/**
	 * add_columns
	 */
	public function add_columns() {
		add_filter( 'manage_posts_columns'      , array( $this, 'manage_posts_columns' ) );
		add_action( 'manage_posts_custom_column', array( $this, 'manage_posts_custom_column' ), 10, 2 );
	}

	/**
	 * manage_posts_columns
	 * @param array $columns
	 * @return array $columns
	 */
	public function manage_posts_columns( $columns ) {
		$date = $columns['date'];
		unset( $columns['date'] );
		$columns['mwform_form_key'] = __( 'Form Key', 'mw-wp-form' );
		$columns['date'] = $date;
		return $columns;
	}

	/**
	 * manage_posts_custom_column
	 * @param string $column_name
	 * @param int $post_id
	 */
	public function manage_posts_custom_column( $column_name, $post_id ) {
		$this->assign( 'post_id', get_the_ID() );
		if ( $column_name === 'mwform_form_key' ) {
			$this->render( 'admin-list/form-key' );
		}
	}
}
