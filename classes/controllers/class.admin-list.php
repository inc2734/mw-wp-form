<?php
/**
 * Name       : MW WP Form Admin List Controller
 * Version    : 1.0.0
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : January 1, 2015
 * Modified   : 
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Admin_List_Controller {
	
	/**
	 * initialize
	 */
	public function initialize() {
		add_action( 'admin_head', array( $this, 'add_columns' ) );
	}

	/**
	 * add_columns
	 */
	public function add_columns() {
		$post_type = get_post_type();
		if ( $post_type !== MWF_Config::NAME ) {
			return;
		}
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