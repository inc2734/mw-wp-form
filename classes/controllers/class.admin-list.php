<?php
/**
 * Name       : MW WP Form Admin List Controller
 * Version    : 1.0.1
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : January 1, 2015
 * Modified   : February 8, 2015
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Admin_List_Controller {
	
	/**
	 * initialize
	 */
	public function initialize() {
		add_action( 'current_screen', array( $this , 'current_screen' ) );
	}

	/**
	 * current_screen
	 * @param WP_Screen $screen
	 */
	public function current_screen( $screen ) {
		if ( $screen->id === 'edit-' . MWF_Config::NAME ) {
			$View = new MW_WP_Form_Admin_List_View();
			add_filter( 'views_' . $screen->id , array( $View, 'donate_link' ) );
			add_action( 'admin_head'           , array( $this, 'add_columns' ) );
			add_action( 'admin_enqueue_scripts', array( $this , 'admin_enqueue_scripts' ) );
		}
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
		$columns['mwform_form_key'] = __( 'Form Key', MWF_Config::DOMAIN );
		$columns['date'] = $date;
		return $columns;
	}

	/**
	 * manage_posts_custom_column
	 * @param string $column_name
	 * @param int $post_id
	 */
	public function manage_posts_custom_column( $column_name, $post_id ) {
		$View = new MW_WP_Form_Admin_List_View();
		$View->set( 'post_id', get_the_ID() );
		if ( $column_name === 'mwform_form_key' ) {
			$View->form_key();
		}
	}
}