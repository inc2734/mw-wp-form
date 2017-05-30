<?php
/**
 * Name       : MW WP Form Mail Parser
 * Version    : 2.0.0
 * Author     : Takashi Kitajima
 * Author URI : https://2inc.org
 * Created    : April 14, 2015
 * Modified   : May 30, 2017
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Mail_Parser {

	/**
	 * Saved mail ID
	 * @var int
	 */
	protected $saved_mail_id;

	/**
	 * @var MW_WP_Form_Mail
	 */
	protected $Mail;

	/**
	 * @var MW_WP_Form_Data
	 */
	protected $Data;

	/**
	 * @var MW_WP_Form_Setting
	 */
	protected $Setting;

	/**
	 * @param MW_WP_Form_Mail $Mail
	 * @param MW_WP_Form_Setting $Setting
	 */
	public function __construct( MW_WP_Form_Mail $Mail, MW_WP_Form_Setting $Setting ) {
		$this->Mail    = $Mail;
		$this->Setting = $Setting;
		$form_id       = $Setting->get( 'post_id' );
		$form_key      = MWF_Functions::get_form_key_from_form_id( $form_id );
		$this->Data    = MW_WP_Form_Data::connect( $form_key );
	}

	/**
	 * Return parsed Mail object
	 *
	 * @return MW_WP_Form_Mail
	 */
	public function get_parsed_mail_object() {
		return $this->_parse_mail_object();
	}

	/**
	 * Return saved mail ID
	 *
	 * @return int
	 */
	public function get_saved_mail_id(){
		return $this->saved_mail_id;
	}

	/**
	 * Convert each properties of Mail object
	 *
	 * @return MW_WP_Form_Mail $Mail
	 */
	protected function _parse_mail_object() {
		$parsed_Mail_vars = get_object_vars( $this->Mail );
		foreach ( $parsed_Mail_vars as $key => $value ) {
			if ( is_array( $value ) ) {
				continue;
			}

			if ( 'to' === $key || 'cc' === $key || 'bcc' === $key ) {
				$this->Mail->$key = $this->_parse_mail_destination( $value );
				continue;
			}
			$this->Mail->$key = $this->_parse_mail_content( $value );
		}
		return $this->Mail;
	}

	/**
	 * Replace {name} for mail content. It doesn't get from Data
	 *
	 * @param string $value
	 * @return string
	 */
	protected function _parse_mail_destination( $value ) {
		return preg_replace_callback(
			'/{(.+?)}/',
			array( $this, '_parse_mail_destination_callback' ),
			$value
		);
	}
	protected function _parse_mail_destination_callback( $matches ) {
		$match    = $matches[1];
		$form_id  = $this->Setting->get( 'post_id' );
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$value    = $this->_apply_filters_mwform_custom_mail_tag( $form_key, null, $match );

		// Return blank when custom mail tag isn't use(= null)
		if ( ! is_null( $value ) ) {
			return $value;
		}
		return '';
	}

	/**
	 * Replace {name} for mail content
	 *
	 * @param string $value
	 * @return string
	 */
	protected function _parse_mail_content( $value ) {
		return preg_replace_callback(
			'/{(.+?)}/',
			array( $this, '_parse_mail_content_callback' ),
			$value
		);
	}
	protected function _parse_mail_content_callback( $matches ) {
		$match = $matches[1];
		return $this->_parse( $match );
	}

	/**
	 * Save Mail content and attachment files
	 * Set property of saved mail ID
	 */
	public function save() {
		$form_id = $this->Setting->get( 'post_id' );
		$saved_mail_id = wp_insert_post( array(
			'post_title'  => $this->_parse_mail_content( $this->Mail->subject ),
			'post_status' => 'publish',
			'post_type'   => MWF_Functions::get_contact_data_post_type_from_form_id( $form_id ),
		) );

		// 添付ファイルをメディアに保存
		// save_mail_body 内のフックで添付ファイルの情報を使えるように、
		// save_mail_body より前にこのブロックを実行する
		// ここでポストメタとしてURLではなくファイルのIDを保存
		if ( ! empty( $saved_mail_id ) ) {
			MWF_Functions::save_attachments_in_media(
				$saved_mail_id,
				$this->Mail->attachments,
				$form_id
			);
		}

		$this->saved_mail_id = $saved_mail_id;

		$parsed_Mail_vars = get_object_vars( $this->Mail );
		foreach ( $parsed_Mail_vars as $key => $value ) {
			if ( is_array( $value ) ) {
				continue;
			}

			if ( $key == 'body' ) {
				$this->_save( $value );
			}
		}
	}

	/**
	 * Search {name} and save value to database
	 * Save value even if it is null (e.g. posting which checkbox isn't check)
	 *
	 * @param string $value
	 */
	protected function _save( $value ) {
		preg_match_all(
			'/{(.+?)}/',
			$value,
			$matches
		);

		if ( ! isset( $matches[1] ) ) {
			return;
		}

		$form_id = $this->Setting->get( 'post_id' );
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );

		$data = array();

		foreach ( $matches[1] as $name ) {
			$value = $this->_parse( $name );

			$ignore_keys = apply_filters( 'mwform_no_save_keys_' . $form_key, array() );
			if ( in_array( $name, $ignore_keys ) ) {
				continue;
			}

			// ファイルは MWF_Functions::save_attachments_in_media() で ID が保存されるため
			// ここで送信された値（URL）は保存しない
			if ( array_key_exists( $name, $this->Mail->attachments ) ) {
				continue;
			}

			$data[ $name ] = ( is_null( $value ) ) ? '' : $value;
		}

		$Contact_Data_Setting = new MW_WP_Form_Contact_Data_Setting( $this->saved_mail_id );
		$Contact_Data_Setting->sets( $data );
		$Contact_Data_Setting->save();
	}

	/**
	 * そのキーについて送信された値を返す
	 *
	 * @param string $name
	 * @return string
	 */
	protected function _parse( $name ) {
		$form_id = $this->Setting->get( 'post_id' );
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		// MWF_Config::TRACKINGNUMBER のときはお問い合せ番号を参照する
		if ( $name === MWF_Config::TRACKINGNUMBER ) {
			if ( $form_id ) {
				$value = $this->Setting->get_tracking_number( $form_id );
			}
		} else {
			$value = $this->Data->get( $name );
			$value = $this->_apply_filters_mwform_custom_mail_tag( $form_key, $value, $name );
		}
		return $value;
	}

	/**
	 * Apply mwform_custom_mail_tag filter hook
	 *
	 * @param string $form_key
	 * @param string|null $value
	 * @param string $match
	 * @return string
	 */
	protected function _apply_filters_mwform_custom_mail_tag( $form_key, $value, $match ) {
		$value = apply_filters(
			'mwform_custom_mail_tag',
			$value,
			$match,
			$this->saved_mail_id
		);

		$value = apply_filters(
			'mwform_custom_mail_tag_' . $form_key,
			$value,
			$match,
			$this->saved_mail_id
		);

		return $value;
	}
}
