<?php
/**
 * Name       : MW WP Form Contact Data List Controller
 * Version    : 2.0.1
 * Author     : Takashi Kitajima
 * Author URI : https://2inc.org
 * Created    : January 1, 2015
 * Modified   : June 26, 2018
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Contact_Data_List_Controller extends MW_WP_Form_Controller {

	/**
	 * @var string
	 */
	protected $post_type;

	public function __construct() {
		$contact_data_post_types = MW_WP_Form_Contact_Data_Setting::get_form_post_types();
		if ( ! isset( $_GET['post_type'] ) ) {
			exit;
		}

		$this->post_type = $_GET['post_type'];
		if ( ! in_array( $this->post_type, $contact_data_post_types ) ) {
			exit;
		}

		if ( ! empty( $_POST ) ) {
			$CSV = new MW_WP_Form_CSV( $this->post_type );
			$CSV->download();
		}

		add_action( 'pre_get_posts'        , array( $this, '_pre_get_posts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, '_admin_enqueue_scripts' ) );
		add_action( 'admin_print_styles'   , array( $this, '_admin_print_styles' ) );
		add_action( 'in_admin_footer'      , array( $this, '_add_csv_download_button' ) );
		add_filter( 'wp_count_posts'       , array( $this, '_wp_count_posts' ), 10, 2 );

		add_filter(
			'manage_' . $this->post_type . '_posts_columns',
			array( $this, '_add_form_columns_name' )
		);

		add_action(
			'manage_' . $this->post_type . '_posts_custom_column',
			array( $this, '_add_form_columns' ),
			10,
			2
		);
	}

	/**
	 * Change if there is a necessity of change in the inquiry data displayed by hook
	 *
	 * @param WP_Query $wp_query
	 * @return void
	 */
	public function _pre_get_posts( $wp_query ) {
		if ( ! $wp_query->is_main_query() ) {
			return;
		}

		$post_type   = $wp_query->get( 'post_type' );
		$post_status = $wp_query->get( 'post_status' );

		$args = apply_filters( 'mwform_get_inquiry_data_args-' . $post_type, array() );
		if ( empty( $args ) || ! is_array( $args ) ) {
			$args = array();
		}
		$args = array_merge( $args, array(
			'post_type'   => $post_type,
			'post_status' => $post_status,
		) );

		foreach ( $args as $key => $value ) {
			$wp_query->set( $key, $value );
		}
	}

	/**
	 * Enqueue assets
	 *
	 * @return void
	 */
	public function _admin_enqueue_scripts() {
		$url = plugins_url( MWF_Config::NAME );
		wp_enqueue_style( MWF_Config::NAME . '-admin-data-list', $url . '/css/admin-data-list.css' );
		wp_enqueue_script( MWF_Config::NAME . '-admin-data-list', $url . '/js/admin-data-list.js' );
	}

	/**
	 * Delete add new link
	 *
	 * @return void
	 */
	public function _admin_print_styles() {
		$this->_render( 'contact-data-list/admin-print-styles' );
	}

	/**
	 * Render csv download button
	 *
	 * @return void
	 */
	public function _add_csv_download_button() {
		if ( true !== apply_filters( 'mwform_csv_button_' . $this->post_type, true ) ) {
			return;
		}
		$page = ( basename( $_SERVER['PHP_SELF'] ) );
		if ( 'edit.php' !== $page  ) {
			return;
		}
		$action = $_SERVER['REQUEST_URI'];
		$this->_render( 'contact-data-list/csv-button', array(
			'action' => $action
		) );
	}

	/**
	 * Edit wp count posts
	 *
	 * @param object $counts
	 * @param string $type Post type
	 * @return object
	 */
	public function _wp_count_posts( $counts, $type ) {
		$args = apply_filters( 'mwform_get_inquiry_data_args-' . $type, array() );
		if ( empty( $args ) || ! is_array( $args ) ) {
			$args = array();
		}

		$args = array_merge( $args, array(
			'post_type'      => $type,
			'posts_per_page' => 1,
			'post_status'    => 'any',
		) );

		foreach ( $counts as $key => $count ) {
			$query = new WP_Query( array_merge( $args, array( 'post_status' => $key ) ) );
			$counts->$key = $query->found_posts;
		}

		return $counts;
	}

	/**
	 * Set displayed columns name
	 *
	 * @param array $columns
	 * @return array
	 */
	public function _add_form_columns_name( $columns ) {
		global $posts;

		unset( $columns['date'] );
		$columns['post_date']       = __( 'Registed Date', 'mw-wp-form' );
		$columns['admin_mail_to']   = __( 'Admin Email To', 'mw-wp-form' );
		$columns['response_status'] = __( 'Response Status', 'mw-wp-form' );
		$_columns = array();

		foreach ( $posts as $post ) {
			$post_custom_keys = get_post_custom_keys( $post->ID );
			if ( empty( $post_custom_keys ) || ! is_array( $post_custom_keys ) ) {
				continue;
			}

			foreach ( $post_custom_keys as $key ) {
				if ( preg_match( '/^_/', $key ) ) {
					continue;
				}

				if ( MWF_Config::TRACKINGNUMBER === $key ) {
					$_columns[ $key ] = MWF_Functions::get_tracking_number_title( $this->post_type );
					continue;
				}

				$_columns[ $key ] = $key;
			}
		}

		ksort( $_columns );
		$_columns = apply_filters( 'mwform_inquiry_data_columns-' . $this->post_type, $_columns );
		$columns  = array_merge( $columns, $_columns );
		return $columns;
	}

	/**
	 * Render each columns
	 *
	 * @param string $column Column name
	 * @param int void
	 */
	public function _add_form_columns( $column, $post_id ) {
		$post                 = get_post( $post_id );
		$post_custom_keys     = get_post_custom_keys( $post_id );
		$Contact_Data_Setting = new MW_WP_Form_Contact_Data_Setting( $post_id );

		if ( 'post_date' === $column ) {
			$value = esc_html( $post->post_date );
		} elseif ( 'response_status' === $column ) {
			$response_statuses = $Contact_Data_Setting->get_response_statuses();
			$response_status   = $Contact_Data_Setting->get( 'response_status' );
			$value = $response_statuses[ $response_status ];
		} elseif ( 'admin_mail_to' === $column ) {
			$value = $Contact_Data_Setting->get( 'admin_mail_to' );
		} elseif ( is_array( $post_custom_keys ) && in_array( $column, $post_custom_keys ) ) {
			$post_meta = get_post_meta( $post_id, $column, true );
			if ( $Contact_Data_Setting->is_upload_file_key( $column ) ) {
				// 過去バージョンでの不具合でメタデータが空になっていることがあるのでその場合は代替処理
				if ( '' === $post_meta ) {
					$post_meta = MWF_Functions::get_multimedia_id__fallback( $post, $column );
				}
				$value = MWF_Functions::get_multimedia_data( $post_meta );
			} elseif ( '' === $post_meta || null === $post_meta || false === $post_meta ) {
				$value = '&nbsp;';
			} else {
				$value = esc_html( $post_meta );
			}
		} else {
			$value = '&nbsp;';
		}

		$this->_render( 'contact-data-list/column', array(
			'column' => $value,
		) );
	}
}
