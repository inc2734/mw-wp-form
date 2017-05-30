<?php
/**
 * Name       : MW WP Form Chart Controller
 * Version    : 2.0.0
 * Author     : Takashi Kitajima
 * Author URI : https://2inc.org
 * Created    : January 1, 2015
 * Modified   : May 30, 2017
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Chart_Controller extends MW_WP_Form_Controller {

	/**
	 * Post type of saved inquiry data to display in this chart
	 * @var string
	 */
	protected $formkey;

	/**
	 * Settings of the form
	 * @var array
	 */
	protected $postdata = array();

	public function __construct() {
		if ( ! empty( $_GET['formkey'] ) ) {
			$this->formkey = $_GET['formkey'];
		}

		$contact_data_post_types = MW_WP_Form_Contact_Data_Setting::get_form_post_types();
		if ( ! in_array( $this->formkey, $contact_data_post_types ) ) {
			exit;
		}
		add_action( 'admin_enqueue_scripts', array( $this, '_admin_enqueue_scripts') );

		$screen = get_current_screen();
		add_action( 'load-' . $screen->id, array( $this, '_save' ) );
		add_action( $screen->id          , array( $this, '_index' ) );
	}

	/**
	 * Enqueue assets
	 *
	 * @return void
	 */
	public function _admin_enqueue_scripts() {
		global $wp_scripts;

		$ui = $wp_scripts->query( 'jquery-ui-core' );
		wp_enqueue_style(
			'jquery.ui',
			'//ajax.googleapis.com/ajax/libs/jqueryui/' . $ui->ver . '/themes/smoothness/jquery-ui.min.css',
			array( 'jquery' ),
			$ui->ver
		);

		wp_enqueue_script( 'jquery-ui-sortable' );

		$url = plugins_url( MWF_Config::NAME );

		wp_enqueue_style(
			MWF_Config::NAME . '-admin-repeatable',
			$url . '/css/admin-repeatable.css'
		);

		wp_enqueue_script(
			'jsapi',
			'https://www.google.com/jsapi'
		);

		wp_enqueue_script(
			MWF_Config::NAME . '-repeatable',
			$url . '/js/mw-wp-form-repeatable.js',
			array( 'jquery' ),
			null,
			true
		);

		wp_enqueue_script(
			MWF_Config::NAME . '-google-chart',
			$url . '/js/mw-wp-form-google-chart.js',
			array( 'jquery' ),
			null,
			true
		);

		wp_enqueue_script(
			MWF_Config::NAME . '-admin-chart',
			$url . '/js/admin-chart.js',
			array( 'jquery', 'jquery-ui-sortable' ),
			null,
			true
		);
	}

	/**
	 * Save
	 *
	 * @return void
	 */
	public function _save() {
		if ( ! isset( $_POST[ MWF_Config::NAME . '-chart-nonce-field' ] ) ) {
			return;
		}

		if ( empty( $_POST[ MWF_Config::NAME . '-chart-nonce-field' ] ) ) {
			return;
		}

		if ( ! check_admin_referer( MWF_Config::NAME . '-chart-action', MWF_Config::NAME . '-chart-nonce-field' ) ) {
			return;
		}

		if ( ! $this->formkey ) {
			return;
		}

		$option_name = MWF_Config::NAME . '-chart-' . $this->formkey;
		$sanitized_values = $this->_sanitize( $_POST[ $option_name ] );
		update_option( $option_name, $sanitized_values );
		wp_redirect(
			admin_url(
				'edit.php?post_type=' . MWF_Config::NAME . '&page=' . MWF_Config::NAME . '-chart&formkey=' . $this->formkey
			)
		);
		exit;
	}

	/**
	 * Display chart page
	 *
	 * @return void
	 */
	public function _index() {
		$post_type = $this->formkey;

		$args = apply_filters( 'mwform_get_inquiry_data_args-' . $post_type, array() );
		if ( empty( $args ) || ! is_array( $args ) ) {
			$args = array();
		}
		$args = array_merge( $args, array(
			'posts_per_page' => -1,
			'post_type'      => $post_type,
		) );

		$form_posts = get_posts( $args );

		// custom_keys
		$custom_keys = array();
		foreach ( $form_posts as $post ) {
			$post_custom_keys = get_post_custom_keys( $post->ID );
			if ( is_array( $post_custom_keys ) ) {
				foreach ( $post_custom_keys as $post_custom_key ) {
					if ( preg_match( '/^_/', $post_custom_key ) ) {
						continue;
					}
					$post_meta = get_post_meta( $post->ID, $post_custom_key, true );
					$custom_keys[ $post_custom_key ][ $post_meta ][] = $post->ID;
				}
			}
		}

		// postdata
		$postdata = array();
		$option   = get_option( MWF_Config::NAME . '-chart-' . $post_type );
		if ( is_array( $option ) && isset( $option['chart'] ) && is_array( $option['chart'] ) ) {
			$postdata = $option['chart'];
		}
		$default_keys = array(
			'target'    => '',
			'separator' => '',
			'chart'     => '',
		);
		// 空の隠れフィールド（コピー元）を挿入
		array_unshift( $postdata, $default_keys );

		$this->_render( 'chart/index', array(
			'post_type'   => $post_type,
			'form_posts'  => $form_posts,
			'custom_keys' => $custom_keys,
			'postdata'    => $postdata,
		) );
	}

	/**
	 * Sanitize for settings
	 *
	 * @param array $input Posted data from chart settings page
	 * @return array
	 */
	public function _sanitize( $input ) {
		if ( ! is_array( $input ) || ! isset( $input['chart'] ) || ! is_array( $input['chart'] ) ) {
			return array();
		}

		$new_input = array();

		foreach ( $input['chart'] as $key => $value ) {
			if ( empty( $value['target'] ) ) {
				continue;
			}

			$new_input['chart'][ $key ] = $value;
		}

		return $new_input;
	}
}
