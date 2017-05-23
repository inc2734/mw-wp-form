<?php
/**
 * Name       : MW WP Form Contact Data Controller
 * Version    : 1.1.0
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : December 31, 2014
 * Modified   : March 27, 2015
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Contact_Data_Controller extends MW_WP_Form_Controller {

	public function __construct() {
		$screen = get_current_screen();
		if ( 'post' !== $screen->base ) {
			exit;
		}

		$contact_data_post_types = MW_WP_Form_Contact_Data_Setting::get_form_post_types();
		if ( ! in_array( $screen->id, $contact_data_post_types ) ) {
			exit;
		}

		$args = apply_filters( 'mwform_get_inquiry_data_args-' . $screen->post_type, array() );
		if ( empty( $args ) || ! is_array( $args ) ) {
			$args = array();
		}

		$args = array_merge( $args, array(
			'post_type'      => $screen->post_type,
			'posts_per_page' => 1,
			'post_status'    => 'any',
			'p'              => $_GET['post'],
		) );
		$permit_posts = get_posts( $args );
		if ( empty( $permit_posts ) ) {
			return;
		}

		add_action( 'add_meta_boxes'       , array( $this, '_add_meta_boxes' ) );
		add_action( 'admin_enqueue_scripts', array( $this, '_admin_enqueue_scripts' ) );
		add_action( 'admin_print_styles'   , array( $this, '_admin_print_styles' ) );
		add_action( 'edit_form_top'        , array( $this, '_edit_form_top' ) );
		add_action( 'save_post'            , array( $this, '_save_post' ) );
	}

	/**
	 * メタボックスを追加
	 */
	public function _add_meta_boxes() {
		$post_type = get_post_type();
		add_meta_box(
			substr( MWF_Config::CONTACT_DATA_NAME, 1 ) . '_custom_fields',
			__( 'Custom Fields', 'mw-wp-form' ),
			array( $this, '_detail' ),
			$post_type
		);
	}

	/**
	 * CSSの読み込み
	 */
	public function _admin_enqueue_scripts() {
		$url = plugins_url( MWF_Config::NAME );
		wp_enqueue_style( MWF_Config::NAME . '-admin-data', $url . '/css/admin-data.css' );
	}

	/**
	 * 詳細画面で新規追加のリンクを消す
	 */
	public function _admin_print_styles() {
		$this->_render( 'contact-data/admin-print-styles' );
	}

	/**
	 * 問い合わせデータ詳細画面で一覧に戻るリンクを表示
	 *
	 * @param object $post
	 */
	public function _edit_form_top( $post ) {
		$post_type = get_post_type();
		$link = admin_url( '/edit.php?post_type=' . $post_type );
		$this->_assign( 'link', $link );
		$this->_render( 'contact-data/returning-link' );
	}

	/**
	 * @param int $post_id
	 */
	public function save_post( $post_id ) {
		if ( ! isset( $_POST['post_type'] ) ) {
			return;
		}

		$contact_data_post_types = MW_WP_Form_Contact_Data_setting::get_form_post_types();
		if ( ! in_array( $_POST['post_type'], $contact_data_post_types ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST[ MWF_Config::NAME . '_nonce' ], MWF_Config::NAME ) ) {
			return;
		}

		if ( !current_user_can( MWF_Config::CAPABILITY ) ) {
			return;
		}

		$Contact_Data_Setting = new MW_WP_Form_Contact_Data_setting( $post_id );
		$permit_keys = $Contact_Data_Setting->get_permit_keys();
		$data = array();
		foreach ( $permit_keys as $key ) {
			if ( isset( $_POST[ MWF_Config::CONTACT_DATA_NAME ][ $key ] ) ) {
				$value = $_POST[ MWF_Config::CONTACT_DATA_NAME ][ $key ];
				if ( 'response_status' === $key ) {
					if ( ! array_key_exists( $value, $Contact_Data_Setting->get_response_statuses() ) ) {
						continue;
					}
				}
				$data[ $key ] = $value;
			}
		}
		$Contact_Data_Setting->sets( $data );
		$Contact_Data_Setting->save();
	}

	/**
	 * 詳細
	 */
	public function _detail( $post ) {
		$this->_assign( 'post', $post );
		$this->_assign( 'post_type', $post->post_type );
		$this->_assign( 'Contact_Data_Setting', new MW_WP_Form_Contact_Data_Setting( get_the_ID() ) );
		$this->_render( 'contact-data/detail' );
	}
}
