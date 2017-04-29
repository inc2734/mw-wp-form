<?php
/**
 * Name       : MW WP Form Mail Parser
 * Description: メールパーサー
 * Version    : 1.3.0
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : April 14, 2015
 * Modified   : April 29, 2017
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Mail_Parser {

	/**
	 * 保存した問い合わせデータの Post ID
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
		$this->Data    = MW_WP_Form_Data::getInstance();
		$this->Setting = $Setting;
	}

	/**
	 * パースした Mail オブジェクトの取得
	 *
	 * @return MW_WP_Form_Mail
	 */
	public function get_parsed_mail_object() {
		return $this->parse_mail_object();
	}

	/**
	 * Getter : $this->saved_mail_id
	 *
	 * @return int
	 */
	public function get_saved_mail_id(){
		return $this->saved_mail_id;
	}

	/**
	 * メールオブジェクトの各プロパティを変換
	 *
	 * @return MW_WP_Form_Mail $Mail
	 */
	protected function parse_mail_object() {
		$parsed_Mail_vars = get_object_vars( $this->Mail );
		foreach ( $parsed_Mail_vars as $key => $value ) {
			if ( is_array( $value ) ) {
				continue;
			}

			if ( $key == 'to' || $key == 'cc' || $key == 'bcc' ) {
				$this->Mail->$key = $this->parse_mail_destination( $value );
				continue;
			}
			$this->Mail->$key = $this->parse_mail_content( $value );
		}
		return $this->Mail;
	}

	/**
	 * メール送信先用に {name属性} を置換。Data からの取得は行わない
	 *
	 * @param string $value
	 * @return string
	 */
	protected function parse_mail_destination( $value ) {
		return preg_replace_callback(
			'/{(.+?)}/',
			array( $this, '_parse_mail_destination' ),
			$value
		);
	}
	protected function _parse_mail_destination( $matches ) {
		$match    = $matches[1];
		$form_id  = $this->Setting->get( 'post_id' );
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$value    = $this->apply_filters_mwform_custom_mail_tag( $form_key, null, $match );

		// カスタムメールタグが利用されていない = null ときは送信先の初期値である空白を返す
		if ( !is_null( $value ) ) {
			return $value;
		}
		return '';
	}

	/**
	 * メール本文用に {name属性} を置換
	 *
	 * @param string $value
	 * @return string
	 */
	protected function parse_mail_content( $value ) {
		return preg_replace_callback(
			'/{(.+?)}/',
			array( $this, '_parse_mail_content' ),
			$value
		);
	}
	protected function _parse_mail_content( $matches ) {
		$match = $matches[1];
		return $this->_parse( $match );
	}

	/**
	 * メール本文・添付ファイルを保存、保存したメール（投稿）の ID をプロパティにセット
	 */
	public function save() {
		$form_id = $this->Setting->get( 'post_id' );
		$saved_mail_id = wp_insert_post( array(
			'post_title'  => $this->parse_mail_content( $this->Mail->subject ),
			'post_status' => 'publish',
			'post_type'   => MWF_Functions::get_contact_data_post_type_from_form_id( $form_id ),
		) );

		// 添付ファイルをメディアに保存
		// save_mail_body 内のフックで添付ファイルの情報を使えるように、
		// save_mail_body より前にこのブロックを実行する
		// ここでポストメタとしてURLではなくファイルのIDを保存
		if ( !empty( $saved_mail_id ) ) {
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
	 * {キー}の部分を検索し、その値をデータベースに保存
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

		foreach ( $matches[1] as $key ) {
			$value = $this->_parse( $key );
			// 値が null でも保存（チェッボックス未チェックで直送信でも保存させるため）
			$ignore_keys = apply_filters( 'mwform_no_save_keys_' . $form_key, array() );
			if ( ! in_array( $key, $ignore_keys ) ) {
				// ファイルは MWF_Functions::save_attachments_in_media() で ID が保存されるため
				// ここで送信された値（URL）は保存しない
				if ( ! array_key_exists( $key, $this->Mail->attachments ) ) {
					update_post_meta( $this->saved_mail_id, $key, $value );
				}
			}
		}
	}

	/**
	 * そのキーについて送信された値を返す
	 *
	 * @param string $key
	 * @return string
	 */
	protected function _parse( $key ) {
		$form_id = $this->Setting->get( 'post_id' );
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		// MWF_Config::TRACKINGNUMBER のときはお問い合せ番号を参照する
		if ( $key === MWF_Config::TRACKINGNUMBER ) {
			if ( $form_id ) {
				$value = $this->Setting->get_tracking_number( $form_id );
			}
		} else {
			$value = $this->Data->get( $key );
			$value = $this->apply_filters_mwform_custom_mail_tag( $form_key, $value, $key );
		}
		return $value;
	}

	/**
	 * フィルターフック mwform_custom_mail_tag を実行
	 *
	 * @param string $form_key
	 * @param string|null $value
	 * @param string $match
	 * @return string
	 */
	protected function apply_filters_mwform_custom_mail_tag( $form_key, $value, $match ) {
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
