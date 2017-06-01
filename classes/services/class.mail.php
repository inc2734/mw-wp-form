<?php
/**
 * Name       : MW WP Form Mail Service
 * Version    : 2.0.0
 * Author     : Takashi Kitajima
 * Author URI : https://2inc.org
 * Created    : January 1, 2015
 * Modified   : May 30, 2017
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Mail_Service {

	/**
	 * @var MW_WP_Form_Mail
	 */
	protected $Mail_raw;

	/**
	 * @var MW_WP_Form_Mail
	 */
	protected $Mail_admin_raw;

	/**
	 * @var MW_WP_Form_Mail
	 */
	protected $Mail_auto_raw;

	/**
	 * @var MW_WP_Form_Data
	 */
	protected $Data;

	/**
	 * @var string
	 */
	protected $form_key;

	/**
	 * @var array
	 */
	protected $attachments = array();

	/**
	 * @var MW_WP_Form_Setting
	 */
	protected $Setting;

	/**
	 * @param MW_WP_Form_Mail $Mail
	 * @param strign $form_key
	 * @param MW_WP_Form_Setting $Setting
	 * @param array $attachments
	 */
	public function __construct( MW_WP_Form_Mail $Mail, $form_key, MW_WP_Form_Setting $Setting, array $attachments = array() ) {
		$this->form_key       = $form_key;
		$this->Data           = MW_WP_Form_Data::connect( $form_key );
		$this->Mail_raw       = $Mail;
		$this->Mail_admin_raw = clone $Mail;
		$this->Mail_auto_raw  = clone $Mail;
		$this->attachments    = $attachments;
		$this->Setting        = $Setting;

		if ( $this->Setting->get( 'post_id' ) ) {
			$this->_set_admin_mail_raw_params();
			// Attach attachment only to e-mail addressed to administrator
			$this->_set_attachments_to( $this->Mail_admin_raw );
			$this->Mail_admin_raw = $this->_apply_filters_mwform_admin_mail_raw( $this->Mail_admin_raw );

			$this->_set_reply_mail_raw_params();
			$this->Mail_auto_raw = $this->_apply_filters_mwform_auto_mail_raw( $this->Mail_auto_raw );
		} else {
			$Mail = $this->_apply_filters_mwform_mail( $Mail );
		}
	}

	/**
	 * Send admin mail and save to database
	 *
	 * @return boolean
	 */
	public function send_admin_mail() {
		$Mail_admin = $this->_get_parsed_mail_object( $this->Mail_admin_raw );
		if ( $this->Setting->get( 'usedb' ) ) {
			$Mail_admin_for_save = clone $this->Mail_admin_raw;
		}

		$Mail_admin = $this->_apply_filters_mwform_mail( $Mail_admin );
		$Mail_admin = $this->_apply_filters_mwform_admin_mail( $Mail_admin );
		do_action(
			'mwform_before_send_admin_mail_' . $this->form_key,
			clone $Mail_admin,
			clone $this->Data
		);
		$is_admin_mail_sended = $Mail_admin->send();

		// to が false の場合は意図的に送信していない（例えばDB保存だけおこないたい等）ということなので
		// 送信エラー画面が表示されるのはおかしい。そのためここでは true を返す
		if ( ! $Mail_admin->to && $this->Setting->get( 'usedb' ) ) {
			$is_admin_mail_sended = true;
		}

		if ( isset( $Mail_admin_for_save ) && $is_admin_mail_sended ) {
			$saved_mail_id = $this->_save( $Mail_admin_for_save );
		}

		// If not usedb, remove files after sending admin mail
		if ( ! $this->Setting->get( 'usedb' ) ) {
			$this->_delete_files();
		}

		return $is_admin_mail_sended;
	}

	/**
	 * Return parsed Mail object and save to database
	 *
	 * @param MW_WP_Form_Mail $_Mail
	 * @return MW_WP_Form_Mail
	 */
	protected function _get_parsed_mail_object( MW_WP_Form_Mail $_Mail ) {
		$Mail = clone $_Mail;
		$Mail->parse( $this->Setting );
		return $Mail;
	}

	/**
	 * Save to database and return saved mail ID
	 *
	 * @param MW_WP_Form_Mail $Mail
	 * @return int
	 */
	protected function _save( MW_WP_Form_Mail $Mail ) {
		return $Mail->save( $this->Setting );
	}

	/**
	 * Send reply mail
	 *
	 * @return boolean
	 */
	public function send_reply_mail() {
		$Mail_auto = $this->_get_parsed_mail_object( $this->Mail_auto_raw );
		$Mail_auto = $this->_apply_filters_mwform_auto_mail( $Mail_auto );
		do_action(
			'mwform_before_send_reply_mail_' . $this->form_key,
			clone $Mail_auto,
			clone $this->Data
		);
		$is_reply_mail_sended = $Mail_auto->send();
		return $is_reply_mail_sended;
	}

	/**
	 * Set attachment files to Mail object
	 *
	 * @param MW_WP_Form_Mail $Mail
	 * @return void
	 */
	protected function _set_attachments_to( MW_WP_Form_Mail $Mail ) {
		$Mail->attachments = $this->attachments;
	}

	/**
	 * Set admin mail params
	 *
	 * @return void
	 */
	protected function _set_admin_mail_raw_params() {
		$this->Mail_admin_raw->set_admin_mail_raw_params( $this->Setting );
	}

	/**
	 * Set reply mail params
	 *
	 * @return void
	 */
	private function _set_reply_mail_raw_params() {
		$this->Mail_auto_raw->set_reply_mail_raw_params( $this->Setting );
	}

	/**
	 * Apply mwform_admin_mail_raw filter hook
	 *
	 * @param MW_WP_Form_Mail $Mail
	 * @return MW_WP_Form_Mail $Mail
	 */
	protected function _apply_filters_mwform_admin_mail_raw( MW_WP_Form_Mail $Mail ) {
		return apply_filters(
			'mwform_admin_mail_raw_' . $this->form_key,
			$Mail,
			$this->Data->gets(),
			clone $this->Data
		);
	}

	/**
	 * Apply mwform_mail filter hook
	 *
	 * @param MW_WP_Form_Mail $Mail
	 * @return MW_WP_Form_Mail $Mail
	 */
	protected function _apply_filters_mwform_mail( MW_WP_Form_Mail $Mail ) {
		return apply_filters(
			'mwform_mail_' . $this->form_key,
			$Mail,
			$this->Data->gets(),
			clone $this->Data
		);
	}

	/**
	 * Apply mwform_admin_mail filter hook
	 *
	 * @param MW_WP_Form_Mail $Mail
	 * @return MW_WP_Form_Mail $Mail
	 */
	protected function _apply_filters_mwform_admin_mail( MW_WP_Form_Mail $Mail ) {
		return apply_filters(
			'mwform_admin_mail_' . $this->form_key,
			$Mail,
			$this->Data->gets(),
			clone $this->Data
		);
	}

	/**
	 * Apply mwform_auto_mail_raw filter hook
	 *
	 * @param MW_WP_Form_Mail $Mail
	 * @return MW_WP_Form_Mail $Mail
	 */
	protected function _apply_filters_mwform_auto_mail_raw( MW_WP_Form_Mail $Mail ) {
		return apply_filters(
			'mwform_auto_mail_raw_' . $this->form_key,
			$Mail,
			$this->Data->gets(),
			clone $this->Data
		);
	}

	/**
	 * Apply mwform_auto_mail filter hook
	 *
	 * @param MW_WP_Form_Mail $Mail
	 * @return MW_WP_Form_Mail $Mail
	 */
	protected function _apply_filters_mwform_auto_mail( MW_WP_Form_Mail $Mail ) {
		return apply_filters(
			'mwform_auto_mail_' . $this->form_key,
			$Mail,
			$this->Data->gets(),
			clone $this->Data
		);
	}

	/**
	 * Delete attachment files
	 *
	 * @return void
	 */
	protected function _delete_files() {
		foreach ( $this->attachments as $file ) {
			if ( file_exists( $file ) ) {
				unlink( $file );
			}
		}
	}

	/**
	 * Update tracking number
	 *
	 * @return void
	 */
	public function update_tracking_number() {
		if ( preg_match( '{' . MWF_Config::TRACKINGNUMBER . '}', $this->Mail_admin_raw->body ) ) {
			$this->Setting->update_tracking_number();
		}
	}
}
