<?php
/**
 * @package mw-wp-form
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * MW_WP_Form_Stores_Inquiry_Data_Form_List_Controller
 */
class MW_WP_Form_Stores_Inquiry_Data_Form_List_Controller extends MW_WP_Form_Controller {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$screen = get_current_screen();
		add_action( $screen->id, array( $this, '_index' ) );
	}

	/**
	 * Render the page.
	 */
	public function _index() {
		$contact_data_post_types = MW_WP_Form_Contact_Data_Setting::get_form_post_types();
		$form_list               = array();
		foreach ( $contact_data_post_types as $post_type ) {
			$post_type_object        = get_post_type_object( $post_type );
			$form_list[ $post_type ] = array(
				'title'             => $post_type_object->labels->singular_name,
				'count'             => $this->_get_count( $post_type ),
				'modified_datetime' => $this->_get_modified_datetime( $post_type ),
				'created_datetime'  => $this->_get_created_datetime( $post_type ),
			);
		}

		$this->_render(
			'stores-inquiry-data-form-list/index',
			array(
				'form_list' => $form_list,
			)
		);
	}

	/**
	 * Return data count.
	 *
	 * @param string $post_type Post type.
	 * @return int
	 */
	protected function _get_count( $post_type ) {
		$args = apply_filters( 'mwform_get_inquiry_data_args-' . $post_type, array() );
		if ( empty( $args ) || ! is_array( $args ) ) {
			$args = array();
		}
		$args  = array_merge(
			$args,
			array(
				'post_type'      => $post_type,
				'posts_per_page' => 1,
				'post_status'    => 'any',
			)
		);
		$query = new WP_Query( $args );
		return $query->found_posts;
	}

	/**
	 * Return the latest saved date of saved inquiry data.
	 *
	 * @param string $post_type Post type.
	 * @return string
	 */
	protected function _get_modified_datetime( $post_type ) {
		global $post;
		$inquiry_posts = get_posts(
			array(
				'post_type'      => $post_type,
				'posts_per_page' => 1,
				'orderby'        => 'modified',
			)
		);

		$modified_datetime = '';
		foreach ( $inquiry_posts as $post ) {
			setup_postdata( $post );
			$modified_datetime = get_the_modified_date();
			break;
		}
		wp_reset_postdata();
		return $modified_datetime;
	}

	/**
	 * Return date of creating form.
	 *
	 * @param string $post_type Post type.
	 * @return string
	 */
	protected function _get_created_datetime( $post_type ) {
		$post_id   = preg_replace( '/^mwf_(.+?)$/', '$1', $post_type );
		$post_date = get_the_date( get_option( 'date_format' ), $post_id );
		return $post_date;
	}
}
