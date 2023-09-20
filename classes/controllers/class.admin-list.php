<?php
/**
 * @package mw-wp-form
 * @author websoudan
 * @license GPL-2.0+
 */

/**
 * MW_WP_Form_Admin_List_Controller
 */
class MW_WP_Form_Admin_List_Controller extends MW_WP_Form_Controller {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$screen = get_current_screen();
		add_action( 'admin_head', array( $this, '_add_columns' ) );
		add_action( 'admin_enqueue_scripts', array( $this, '_admin_enqueue_scripts' ) );
	}

	/**
	 * Hooked for adding columns.
	 */
	public function _add_columns() {
		add_filter( 'manage_posts_columns', array( $this, '_manage_posts_columns' ) );
		add_action( 'manage_posts_custom_column', array( $this, '_manage_posts_custom_column' ) );
	}

	/**
	 * Enqueue assets.
	 */
	public function _admin_enqueue_scripts() {
		$url = plugins_url( MWF_Config::NAME );
		wp_enqueue_style( MWF_Config::NAME . '-admin-list', $url . '/css/admin-list.css' );
	}

	/**
	 * Add columns.
	 *
	 * @param array $columns An associative array of column headings.
	 * @return array
	 */
	public function _manage_posts_columns( $columns ) {
		$date = $columns['date'];
		unset( $columns['date'] );
		$columns['mwform_form_key'] = __( 'Form Key', 'mw-wp-form' );
		$columns['date']            = $date;
		return $columns;
	}

	/**
	 * Render column for form key.
	 *
	 * @param string $column_name An associative array of column headings.
	 */
	public function _manage_posts_custom_column( $column_name ) {
		if ( 'mwform_form_key' === $column_name ) {
			$this->_render(
				'admin-list/form-key',
				array(
					'post_id' => get_the_ID(),
				)
			);
		}
	}
}
