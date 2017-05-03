<?php
/**
 * Name       : MW WP Form Mail Service
 * Version    : 1.4.1
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : January 1, 2015
 * Modified   : May 4, 2017
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
	 * フォーム識別子
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
	 * __construct
	 *
	 * @param MW_WP_Form_Mail $Mail
	 * @param strign $form_key
	 * @param MW_WP_Form_Setting $Setting
	 * @param array $attachments
	 */
	public function __construct( MW_WP_Form_Mail $Mail, $form_key, MW_WP_Form_Setting $Setting, array $attachments = array() ) {
		$this->form_key       = $form_key;
		$this->Data           = MW_WP_Form_Data::getInstance();
		$this->Mail_raw       = $Mail;
		$this->Mail_admin_raw = clone $Mail;
		$this->Mail_auto_raw  = clone $Mail;
		$this->attachments    = $attachments;
		$this->Setting        = $Setting;

		if ( $this->Setting->get( 'post_id' ) ) {
			$this->set_admin_mail_raw_params();
			// 管理者宛メールにだけ添付ファイルを添付
			$this->set_attachments( $this->Mail_admin_raw );
			$this->Mail_admin_raw = $this->apply_filters_mwform_admin_mail_raw( $this->Mail_admin_raw );

			$this->set_reply_mail_raw_params();
			$this->Mail_auto_raw = $this->apply_filters_mwform_auto_mail_raw( $this->Mail_auto_raw );
		} else {
			$Mail = $this->apply_filters_mwform_mail( $Mail );
		}
	}

	/**
	 * 管理者メールの送信とデータベースへの保存
	 *
	 * @return boolean
	 */
	public function send_admin_mail() {
		$Mail_admin = $this->get_parsed_mail_object( $this->Mail_admin_raw );
		if ( $this->Setting->get( 'usedb' ) ) {
			$Mail_admin_for_save = clone $this->Mail_admin_raw;
		}

		$Mail_admin->set_admin_mail_reaquire_params();
		$Mail_admin = $this->apply_filters_mwform_mail( $Mail_admin );
		$Mail_admin = $this->apply_filters_mwform_admin_mail( $Mail_admin );
		do_action(
			'mwform_before_send_admin_mail_' . $this->form_key,
			clone $Mail_admin,
			clone $this->Data
		);
		$is_admin_mail_sended = $Mail_admin->send();

		// to が false の場合は意図的に送信していない（例えばDB保存だけおこないたい等）ということなので
		// 送信エラー画面が表示されるのはおかしい。そのためここでは true を返す
		if ( ! $Mail_admin->to ) {
			$is_admin_mail_sended = true;
		}

		if ( isset( $Mail_admin_for_save ) && $is_admin_mail_sended ) {
			$saved_mail_id = $this->save( $Mail_admin_for_save );
			$Contact_Data_Setting = new MW_WP_Form_Contact_Data_Setting( $saved_mail_id );
			$Contact_Data_Setting->save();
		}

		// DB非保存時は管理者メール送信後、ファイルを削除
		if ( !$this->Setting->get( 'usedb' ) ) {
			$File = new MW_WP_Form_File();
			$File->delete_files( $this->attachments );
		}

		return $is_admin_mail_sended;
	}

	/**
	 * パースしたMailオブジェクトの取得とデータベースへの保存
	 *
	 * @param MW_WP_Form_Mail $_Mail
	 * @return MW_WP_Form_Mail
	 */
	protected function get_parsed_mail_object( MW_WP_Form_Mail $_Mail ) {
		$Mail = clone $_Mail;
		$Mail->parse( $this->Setting );
		return $Mail;
	}

	/**
	 * メールをデータベースに保存し、保存されたメール（投稿）の ID を返す
	 *
	 * @param MW_WP_Form_Mail $Mail
	 * @return int 保存されたメール（投稿）の ID
	 */
	protected function save( MW_WP_Form_Mail $Mail ) {
		return $Mail->save( $this->Setting );
	}

	/**
	 * 自動返信メールの送信
	 *
	 * @return boolean
	 */
	public function send_reply_mail() {
		$Mail_auto = $this->get_parsed_mail_object( $this->Mail_auto_raw );
		$Mail_auto->set_reply_mail_reaquire_params();
		$Mail_auto = $this->apply_filters_mwform_auto_mail( $Mail_auto );
		do_action(
			'mwform_before_send_reply_mail_' . $this->form_key,
			clone $Mail_auto,
			clone $this->Data
		);
		$is_reply_mail_sended = $Mail_auto->send();
		return $is_reply_mail_sended;
	}

	/**
	 * メールオブジェクトに添付ファイルを添付
	 *
	 * @param MW_WP_Form_Mail $Mail
	 */
	protected function set_attachments( MW_WP_Form_Mail $Mail ) {
		$Mail->attachments = $this->attachments;
	}

	/**
	 * 管理者メールに項目を設定
	 */
	protected function set_admin_mail_raw_params() {
		$this->Mail_admin_raw->set_admin_mail_raw_params( $this->Setting );
	}

	/**
	 * 自動返信メールに項目を設定
	 */
	private function set_reply_mail_raw_params() {
		$this->Mail_auto_raw->set_reply_mail_raw_params( $this->Setting );
	}

	/**
	 * apply_filters_mwform_admin_mail_raw
	 *
	 * @param MW_WP_Form_Mail $Mail
	 * @return MW_WP_Form_Mail $Mail
	 */
	protected function apply_filters_mwform_admin_mail_raw( MW_WP_Form_Mail $Mail ) {
		return apply_filters(
			'mwform_admin_mail_raw_' . $this->form_key,
			$Mail,
			$this->Data->gets(),
			clone $this->Data
		);
	}

	/**
	 * apply_filters_mwform_mail
	 *
	 * @param MW_WP_Form_Mail $Mail
	 * @return MW_WP_Form_Mail $Mail
	 */
	protected function apply_filters_mwform_mail( MW_WP_Form_Mail $Mail ) {
		return apply_filters(
			'mwform_mail_' . $this->form_key,
			$Mail,
			$this->Data->gets(),
			clone $this->Data
		);
	}

	/**
	 * apply_filters_mwform_admin_mail
	 *
	 * @param MW_WP_Form_Mail $Mail
	 * @return MW_WP_Form_Mail $Mail
	 */
	protected function apply_filters_mwform_admin_mail( MW_WP_Form_Mail $Mail ) {
		return apply_filters(
			'mwform_admin_mail_' . $this->form_key,
			$Mail,
			$this->Data->gets(),
			clone $this->Data
		);
	}

	/**
	 * apply_filters_mwform_auto_mail_raw
	 *
	 * @param MW_WP_Form_Mail $Mail
	 * @return MW_WP_Form_Mail $Mail
	 */
	protected function apply_filters_mwform_auto_mail_raw( MW_WP_Form_Mail $Mail ) {
		return apply_filters(
			'mwform_auto_mail_raw_' . $this->form_key,
			$Mail,
			$this->Data->gets(),
			clone $this->Data
		);
	}

	/**
	 * apply_filters_mwform_auto_mail
	 *
	 * @param MW_WP_Form_Mail $Mail
	 * @return MW_WP_Form_Mail $Mail
	 */
	protected function apply_filters_mwform_auto_mail( MW_WP_Form_Mail $Mail ) {
		return apply_filters(
			'mwform_auto_mail_' . $this->form_key,
			$Mail,
			$this->Data->gets(),
			clone $this->Data
		);
	}

	/**
	 * 問い合わせ番号を更新
	 */
	public function update_tracking_number() {
		if ( preg_match( '{' . MWF_Config::TRACKINGNUMBER . '}', $this->Mail_admin_raw->body ) ) {
			$this->Setting->update_tracking_number();
		}
	}
}
