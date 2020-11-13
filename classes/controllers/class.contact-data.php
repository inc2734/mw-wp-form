<?php
/**
 * @package mw-wp-form
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * MW_WP_Form_Contact_Data_Controller
 */
class MW_WP_Form_Contact_Data_Controller extends MW_WP_Form_Controller {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$screen = get_current_screen();
		if ( 'post' !== $screen->base ) {
			exit;
		}

		$contact_data_post_types = MW_WP_Form_Contact_Data_Setting::get_form_post_types();
		if ( ! in_array( $screen->id, $contact_data_post_types, true ) ) {
			exit;
		}

		$args = apply_filters( 'mwform_get_inquiry_data_args-' . $screen->post_type, array() );
		if ( empty( $args ) || ! is_array( $args ) ) {
			$args = array();
		}

		$_post_id = null;
		if ( isset( $_GET['post'] ) ) {
			$_post_id = $_GET['post'];
		} elseif ( $_POST['post_ID'] ) {
			$_post_id = $_POST['post_ID'];
		}

		$args         = array_merge(
			$args,
			array(
				'post_type'      => $screen->post_type,
				'posts_per_page' => 1,
				'post_status'    => 'any',
				'p'              => $_post_id,
			)
		);
		$permit_posts = get_posts( $args );
		if ( empty( $permit_posts ) ) {
			return;
		}

		add_action( 'add_meta_boxes', array( $this, '_add_meta_boxes' ) );
		add_action( 'admin_enqueue_scripts', array( $this, '_admin_enqueue_scripts' ) );
		add_action( 'admin_print_styles', array( $this, '_admin_print_styles' ) );
		add_action( 'edit_form_top', array( $this, '_edit_form_top' ) );
		add_action( 'save_post', array( $this, '_save_post' ) );
	}

	/**
	 * Add meta boxes.
	 */
	public function _add_meta_boxes() {
		$post_type = get_post_type();
		add_meta_box(
			substr( MWF_Config::INQUIRY_DATA_NAME, 1 ) . '_custom_fields',
			__( 'Custom Fields', 'mw-wp-form' ),
			array( $this, '_detail' ),
			$post_type
		);
	}

	/**
	 * Enqueue assets.
	 */
	public function _admin_enqueue_scripts() {
		$url = plugins_url( MWF_Config::NAME );
		wp_enqueue_style( MWF_Config::NAME . '-admin-data', $url . '/css/admin-data.css' );
	}

	/**
	 * Delete add new link.
	 */
	public function _admin_print_styles() {
		$this->_render( 'contact-data/admin-print-styles' );
	}

	/**
	 * Render back to list link.
	 */
	public function _edit_form_top() {
		$post_type = get_post_type();
		$link      = admin_url( '/edit.php?post_type=' . $post_type );
		$this->_render(
			'contact-data/returning-link',
			array(
				'link' => $link,
			)
		);
	}

	/**
	 * Save.
	 *
	 * @param int $post_id Post ID.
	 */
	public function _save_post( $post_id ) {
		if ( ! isset( $_POST['post_type'] ) ) {
			return;
		}

		$contact_data_post_types = MW_WP_Form_Contact_Data_setting::get_form_post_types();
		if ( ! in_array( $_POST['post_type'], $contact_data_post_types, true ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST[ MWF_Config::NAME . '_nonce' ], MWF_Config::NAME ) ) {
			return;
		}

		if ( ! current_user_can( MWF_Config::CAPABILITY ) ) {
			return;
		}

		$contact_data_setting = new MW_WP_Form_Contact_Data_setting( $post_id );
		$permit_keys          = $contact_data_setting->get_permit_keys();
		$data                 = array();
		foreach ( $permit_keys as $key ) {
			if ( isset( $_POST[ MWF_Config::INQUIRY_DATA_NAME ][ $key ] ) ) {
				$value = $_POST[ MWF_Config::INQUIRY_DATA_NAME ][ $key ];
				if ( 'response_status' === $key ) {
					if ( ! array_key_exists( $value, $contact_data_setting->get_response_statuses() ) ) {
						continue;
					}
				}
				$data[ $key ] = $value;
			}
		}
		$contact_data_setting->sets( $data );
		$contact_data_setting->save();
	}

	/**
	 * Render detail meta box.
	 *
	 * @param WP_Post $post WP_Post object.
	 */
	public function _detail( $post ) {
		$this->_render(
			'contact-data/detail',
			array(
				'post'                 => $post,
				'post_type'            => $post->post_type,
				'contact_data_setting' => new MW_WP_Form_Contact_Data_Setting( get_the_ID() ),
			)
		);
	}
}
