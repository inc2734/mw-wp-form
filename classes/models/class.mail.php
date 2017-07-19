<?php
/**
 * Name       : MW WP Form Mail
 * Version    : 3.0.0
 * Author     : Takashi Kitajima
 * Author URI : https://2inc.org
 * Created    : July 20, 2012
 * Modified   : May 31, 2017
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Mail {

	/**
	 * @var string
	 */
	public $to;

	/**
	 * @var string
	 */
	public $cc;

	/**
	 * @var string
	 */
	public $bcc;

	/**
	 * @var string
	 */
	public $reply_to;

	/**
	 * @var string
	 */
	public $from;

	/**
	 * @var string
	 */
	public $return_path;

	/**
	 * @var string
	 */
	public $sender;

	/**
	 * @var string
	 */
	public $subject;

	/**
	 * @var string
	 */
	public $body;

	/**
	 * @var array
	 */
	public $attachments = array();

	/**
	 * @var MW_WP_Form_Mail_Parser
	 */
	protected $Mail_Parser;

	/**
	 * Send mail
	 *
	 * @return boolean
	 */
	public function send() {
		$this->to       = trim( $this->to );
		$this->cc       = trim( $this->cc );
		$this->bcc      = trim( $this->bcc );
		$this->reply_to = trim( $this->reply_to );

		if ( ! $this->to ) {
			return apply_filters( 'mwform_is_mail_sended', false );
		}

		add_action( 'phpmailer_init'   , array( $this, '_set_return_path' ) );
		add_filter( 'wp_mail_from'     , array( $this, '_set_mail_from' ) );
		add_filter( 'wp_mail_from_name', array( $this, '_set_mail_from_name' ) );

		$headers = array();

		if ( $this->cc ) {
			$headers[] = 'Cc: ' . $this->cc;
		}

		if ( $this->bcc ) {
			$headers[] = 'Bcc: ' . $this->bcc;
		}

		if ( $this->reply_to ) {
			$headers[] = 'Reply-To: ' . $this->reply_to;
		}

		if ( ! $this->body ) {
			// wp_mail do not send mail when message is empty.
			$this->body = ' ';
		}

		if ( defined( 'MWFORM_DEBUG' ) && true === MWFORM_DEBUG ) {
			$is_mail_sended = $this->_put_mail_log( $headers );
		} else {
			$is_mail_sended = wp_mail( $this->to, $this->subject, $this->body, $headers, $this->attachments );
		}

		remove_action( 'phpmailer_init'   , array( $this, '_set_return_path' ) );
		remove_filter( 'wp_mail_from'     , array( $this, '_set_mail_from' ) );
		remove_filter( 'wp_mail_from_name', array( $this, '_set_mail_from_name' ) );

		return apply_filters( 'mwform_is_mail_sended', $is_mail_sended );
	}

	/**
	 * Set mail from
	 *
	 * @param string $email
	 * @return string
	 */
	public function _set_mail_from( $email ) {
		if ( filter_var( $this->from, FILTER_VALIDATE_EMAIL ) ) {
			return $this->from;
		}
		$this->from = $email;
		return $email;
	}

	/**
	 * Set sender (from name)
	 *
	 * @param string $sender
	 * @return string
	 */
	public function _set_mail_from_name( $sender ) {
		if ( $this->sender ) {
			return $this->sender;
		}
		$this->sender = $sender;
		return $sender;
	}

	/**
	 * Set Return-Path
	 *
	 * @param phpmailer $phpmailer
	 * @return void
	 */
	public function _set_return_path( $phpmailer ) {
		if ( $this->return_path ) {
			if ( filter_var( $this->return_path, FILTER_VALIDATE_EMAIL ) ) {
				$phpmailer->Sender = $this->return_path;
			}
		}
	}

	/**
	 * Create mail content from array
	 *
	 * @param array
	 * @param array
	 * @return string
	 */
	public function createBody( array $array, array $options = array() ) {
		$_ret = '';
		$defaults = array(
			'exclude' => array()
		);
		$options = array_merge( $defaults, $options );
		foreach( $array as $key => $value ) {
			if ( in_array( $key, $options['exclude'] ) )
				continue;
			if ( is_array( $value ) && isset( $value['separator'], $value['data'] ) ) {
				$_value = '';
				if ( is_array( $value['data'] ) ) {
					foreach ( $value['data'] as $_val ) {
						if ( '' !== $_val && ! is_null ( $_val ) ) {
							$_value = implode( $value['separator'], $value['data'] );
							break;
						}
					}
				} else {
					$_value = $value['data'];
				}
				$value = $_value;
			}
			if ( $value ) {
				$_ret .= sprintf( "▼%s\n%s\n\n", esc_html( $key ), esc_html( $value ) );
			}
		}
		return $_ret;
	}

	/**
	 * Set defaults setting for admin mail
	 *
	 * @param MW_WP_Form_Setting $Setting
	 * @return void
	 */
	public function set_admin_mail_raw_params( MW_WP_Form_Setting $Setting ) {
		$this->subject     = $Setting->get( 'admin_mail_subject' );
		$this->body        = $Setting->get( 'admin_mail_content' );
		$this->to          = $Setting->get( 'mail_to' );
		$this->cc          = $Setting->get( 'mail_cc' );
		$this->bcc         = $Setting->get( 'mail_bcc' );
		$this->reply_to    = $Setting->get( 'admin_mail_reply_to' );
		$this->from        = $Setting->get( 'admin_mail_from' );
		$this->return_path = $Setting->get( 'mail_return_path' );
		$this->sender      = $Setting->get( 'admin_mail_sender' );
	}

	/**
 	 * Set defaults setting for reply mail
	 *
	 * @param MW_WP_Form_Setting $Setting
	 * @return void
	 */
	public function set_reply_mail_raw_params( MW_WP_Form_Setting $Setting ) {
		$this->to          = '';
		$this->cc          = '';
		$this->bcc         = '';
		$this->attachments = array();

		$form_id  = $Setting->get( 'post_id' );
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Data     = MW_WP_Form_Data::connect( $form_key );
		$automatic_reply_email = $Setting->get( 'automatic_reply_email' );

		if ( ! $form_id ) {
			return;
		}

		$Validation = new MW_WP_Form_Validation_Rule_Mail( $Data );
		$is_invalid_mail_address = $Validation->rule(
			$automatic_reply_email
		);

		if ( $automatic_reply_email && ! $is_invalid_mail_address ) {
			$this->to = $Data->get_post_value_by_key( $automatic_reply_email );
		}

		$this->reply_to    = $Setting->get( 'mail_reply_to' );
		$this->return_path = $Setting->get( 'mail_return_path' );
		$this->from        = $Setting->get( 'mail_from' );
		$this->sender      = $Setting->get( 'mail_sender' );
		$this->subject     = $Setting->get( 'mail_subject' );
		$this->body        = $Setting->get( 'mail_content' );
	}

	/**
	 * Replace {name} to content in mail content
	 *
	 * @param MW_WP_Form_Setting $Setting
	 * @return void
	 */
	public function parse( $Setting ) {
		$this->Mail_Parser = new MW_WP_Form_Mail_Parser( $this, $Setting );
		$Mail = $this->Mail_Parser->get_parsed_mail_object();
		foreach ( get_object_vars( $Mail ) as $key => $value ) {
			$this->$key = $value;
		}
	}

	/**
	 * Save to database
	 *
	 * @param MW_WP_Form_Setting $Setting
	 * @return int
	 */
	public function save( $Setting ) {
		$this->Mail_Parser = new MW_WP_Form_Mail_Parser( $this, $Setting );
		$this->Mail_Parser->save();
		return $this->get_saved_mail_id();
	}

	/**
	 * Return saved mail ID
	 *
	 * @return int
	 */
	public function get_saved_mail_id(){
		if ( $this->Mail_Parser ) {
			return $this->Mail_Parser->get_saved_mail_id();
		}
	}

	/**
	 * Logging that MW WP Form sending mail
	 *
	 * @return bool
	 */
	protected function _put_mail_log( $headers ) {
		// Update properties
		wp_mail( '', $this->subject, '', $headers, array() );

		$File = new MW_WP_Form_File();
		$File->create_temp_dir();

		$temp_dir = $File->get_temp_dir();
		$temp_dir = trailingslashit( $temp_dir['dir'] );
		$temp_dir = apply_filters( 'mwform_log_directory', $temp_dir );

		$contents  = "====================";
		$contents .= "\n\n";
		$contents .= 'Send Date: %1$s';
		$contents .= "\n";
		$contents .= 'To: %2$s';
		$contents .= "\n";
		$contents .= 'Sender: %3$s';
		$contents .= "\n";
		$contents .= 'Reply-to: %4$s';
		$contents .= "\n";
		$contents .= 'From: %5$s';
		$contents .= "\n";
		$contents .= 'Return-Path: %6$s';
		$contents .= "\n";
		$contents .= 'Subject: %7$s';
		$contents .= "\n";
		$contents .= 'headers:%8$s';
		$contents .= "\n";
		$contents .= '-----';
		$contents .= "\n";
		$contents .= '%9$s';
		$contents .= "\n";
		$contents .= '-----';
		$contents .= "\n";
		$contents .= 'attachments:';
		$contents .= "\n";
		$contents .= '%10$s';
		$contents .= "\n\n";

		$contents = sprintf(
			$contents,
			date( 'M j Y, H:i:s' ),
			$this->to,
			$this->sender,
			$this->reply_to,
			$this->from,
			$this->return_path,
			$this->subject,
			implode( "\n", $headers ),
			$this->body,
			implode( "\n", $this->attachments )
		);

		$is_mail_sended = file_put_contents( $temp_dir . '/mw-wp-form-debug.log', $contents, FILE_APPEND );

		if ( false === $is_mail_sended) {
			return false;
		}

		return true;
	}
}
