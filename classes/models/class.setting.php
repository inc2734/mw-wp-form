<?php
/**
 * Name       : MW WP Form Setting
 * Version    : 2.0.0
 * Author     : Takashi Kitajima
 * Author URI : https://2inc.org
 * Created    : December 31, 2014
 * Modified   : June 1, 2017
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Setting {

	/**
	 * Form ID
	 * @var int
	 */
	protected $post_id;

	/**
	 * Whether to enable URL querystring
	 * @var false|1
	 */
	protected $querystring = false;

	/**
	 * Whether to use database
	 * @var false|1
	 */
	protected $usedb = false;

	/**
	 * Reply mail subject
	 * @var string
	 */
	protected $mail_subject = '';

	/**
	 * Reply mail from
	 * @var string
	 */
	protected $mail_from = '';

	/**
	 * Reply mail sender
	 * @var string
	 */
	protected $mail_sender = '';

	/**
	 * Reply mail Reply-to
	 * @var string
	 */
	protected $mail_reply_to = '';

	/**
	 * Reply mail content
	 * @var string
	 */
	protected $mail_content = '';

	/**
	 * The name of the form field storing the destination of the automatic reply e-mail
	 * @var string
	 */
	protected $automatic_reply_email = '';

	/**
	 * Admin mail To
	 * @var string
	 */
	protected $mail_to = '';

	/**
	 * Admin mail CC
	 * @var string
	 */
	protected $mail_cc = '';

	/**
	 * Admin mail BCC
	 * @var string
	 */
	protected $mail_bcc = '';

	/**
	 * Admin mail Reply-to
	 * @var string
	 */
	protected $admin_mail_reply_to = '';

	/**
	 * Admin mail subject
	 * @var string
	 */
	protected $admin_mail_subject = '';

	/**
	 * Return-Path
	 * @var string
	 */
	protected $mail_return_path = '';

	/**
	 * Admin mail from
	 * @var string
	 */
	protected $admin_mail_from = '';

	/**
	 * Admin mail sender
	 * @var string
	 */
	protected $admin_mail_sender = '';

	/**
	 * Admin mail content
	 * @var string
	 */
	protected $admin_mail_content = '';

	/**
	 * Input field name that targeted akismet author
	 * @var string
	 */
	protected $akismet_author = '';

	/**
	 * Input field name that targeted akismet e-mail
	 * @var string
	 */
	protected $akismet_author_email = '';

	/**
	 * Input field name that targeted akismet URL
	 * @var string
	 */
	protected $akismet_author_url = '';

	/**
	 * Complete screen message
	 * @var string
	 */
	protected $complete_message = '';

	/**
	 * Input screen URL
	 * @var string
	 */
	protected $input_url = '';

	/**
	 * Confirm screen URL
	 * @var string
	 */
	protected $confirmation_url = '';

	/**
	 * Complete screen URL
	 * @var string
	 */
	protected $complete_url = '';

	/**
	 * Validation error screen url
	 * @var string
	 */
	protected $validation_error_url = '';

	/**
	 * Array of validation rules set in the form
	 * @var array
	 */
	protected $validation = array();

	/**
	 * Style set in the form
	 * @var string
	 */
	protected $style = '';

	/**
	 * Whether to scroll to the position of the form
	 * @var false|1
	 */
	protected $scroll = false;

	/**
	 * @param int $post_id
	 */
	public function __construct( $post_id ) {
		if ( MWF_Config::NAME !== get_post_type( $post_id ) ) {
			return;
		}

		$this->post_id = $post_id;
		$values = get_post_meta( $post_id, MWF_Config::NAME, true );
		if ( ! is_array( $values ) ) {
			return;
		}

		$this->sets( $values );
	}

	/**
	 * Return a attribute
	 *
	 * @param string $key
	 * @return mixed|null
	 */
	public function get( $key ) {
		if ( isset( $this->$key ) ) {
			return $this->$key;
		}
	}

	/**
	 * Set a attribute
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function set( $key, $value ) {
		if ( isset( $this->$key ) ) {
			$this->$key = $value;
		}
	}

	/**
	 * Set attributes
	 *
	 * @param array $values
	 */
	public function sets( array $values ) {
		foreach ( $values as $key => $value ) {
			$this->set( $key, $value );
		}
	}

	/**
	 * Update with retained data
	 *
	 * @return void
	 */
	public function save() {
		$values = get_object_vars( $this );
		$new_values = array();
		foreach ( $values as $key => $value ) {
			if ( 'post_id' == $key ) {
				continue;
			}
			$new_values[ $key ] = $value;
		}
		update_post_meta( $this->post_id, MWF_Config::NAME, $new_values );
		$form_key = MWF_Functions::get_form_key_from_form_id( $this->post_id );
		do_action( 'mwform_settings_save_' . $form_key, $this->post_id );
	}

	/**
	 * Return all forms
	 *
	 * @return array Array of WP_Post
	 */
	public function get_posts() {
		$Admin = new MW_WP_Form_Admin();
		return $Admin->get_forms();
	}

	/**
	 * Return tracking number
	 *
	 * @return int $tracking_number
	 */
	public function get_tracking_number() {
		$tracking_number = get_post_meta( $this->post_id, MWF_Config::TRACKINGNUMBER, true );
		if ( empty( $tracking_number ) ) {
			$tracking_number = 1;
		}
		return intval( $tracking_number );
	}

	/**
	 * Update traking number
	 *
	 * @param null|int $count Update to it if specified
	 */
	public function update_tracking_number( $count = null ) {
		$new_tracking_number = null;
		if ( is_null( $count ) ) {
			$tracking_number     = $this->get_tracking_number();
			$new_tracking_number = $tracking_number + 1;
		} elseif ( MWF_Functions::is_numeric( $count ) ) {
			$new_tracking_number = $count;
		}
		if ( ! is_null( $new_tracking_number ) ) {
			update_post_meta( $this->post_id, MWF_Config::TRACKINGNUMBER, $new_tracking_number );
		}
	}

	/**
	 * Generate verify token for form posts data correct checking
	 *
	 * @return string
	 */
	public function generate_form_verify_token() {
		$vars  = get_object_vars( $this );
		$token = serialize( $vars );
		$token = base64_encode( $token );
		$token = sha1( $token );
		return $token;
	}
}
