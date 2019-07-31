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
	 * @return int|null
	 */
	public function get_saved_mail_id(){
		return $this->Data->get_saved_mail_id();
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

			// To, CC, BCC, Return-Path can not use {name}. But they can use {custom_mail_tag}
			if ( 'to' === $key || 'cc' === $key || 'bcc' === $key || 'return_path' === $key ) {
				$Parser = new MW_WP_Form_Parser( $this->Setting );
				$this->Mail->$key = $Parser->replace_for_mail_destination( $value );
				continue;
			}

			$Parser = new MW_WP_Form_Parser( $this->Setting );
			$this->Mail->$key = $Parser->replace_for_mail_content( $value );
		}
		return $this->Mail;
	}

	/**
	 * Save Mail content and attachment files
	 * Set property of saved mail ID
	 */
	public function save() {
		$form_id = $this->Setting->get( 'post_id' );
		$Parser  = new MW_WP_Form_Parser( $this->Setting );
		$saved_mail_id = wp_insert_post( array(
			'post_title'  => $Parser->replace_for_mail_content( $this->Mail->subject ),
			'post_status' => 'publish',
			'post_type'   => MWF_Functions::get_contact_data_post_type_from_form_id( $form_id ),
		) );

		if ( ! empty( $saved_mail_id ) ) {
			$this->Data->set_saved_mail_id( $saved_mail_id );

			// 添付ファイルをメディアに保存
			// save_mail_body 内のフックで添付ファイルの情報を使えるように、
			// save_mail_body より前にこのブロックを実行する
			// ここでポストメタとしてURLではなくファイルのIDを保存
			MWF_Functions::save_attachments_in_media(
				$saved_mail_id,
				$this->Mail->attachments,
				$form_id
			);
		}

		$parsed_Mail_vars = get_object_vars( $this->Mail );
		foreach ( $parsed_Mail_vars as $key => $value ) {
			if ( is_array( $value ) ) {
				continue;
			}

			if ( 'body' === $key ) {
				$this->_save( $value );
			}
		}
	}

	/**
	 * Search {name} and save value to database
	 * Save value even if it is null (e.g. posting which checkbox isn't check)
	 *
	 * @param string $value
	 * @return void
	 */
	protected function _save( $value ) {
		$Parser  = new MW_WP_Form_Parser( $this->Setting );
		$matches = MW_WP_Form_Parser::search( $value );

		if ( ! isset( $matches[1] ) ) {
			return;
		}

		$form_id = $this->Setting->get( 'post_id' );
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );

		$data = array();

		foreach ( $matches[1] as $name ) {
			$value = $Parser->parse( $name );
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

		$data = array_merge(
			array(
				'admin_mail_to' => $this->Mail->to, // admin_mail_to = The property of MW_WP_Form_Contact_Data_Setting
			),
			$data
		);

		$Contact_Data_Setting = new MW_WP_Form_Contact_Data_Setting( $this->Data->get_saved_mail_id() );
		$Contact_Data_Setting->sets( $data );
		$Contact_Data_Setting->save();
	}
}
