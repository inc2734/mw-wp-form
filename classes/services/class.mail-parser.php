<?php
/**
 * Name       : MW WP Form Mail Parser
 * Description: メールパーサー
 * Version    : 1.0.4
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : April 14, 2015
 * Modified   : May 25, 2015
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Mail_Parser {

	/**
	 * 保存した問い合わせデータの Post ID
	 * @var int
	 */
	protected $insert_contact_data_id;

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
		$this->Mail    = clone $Mail;
		$this->Data    = MW_WP_Form_Data::getInstance();
		$this->Setting = $Setting;
	}

	/**
	 * パースした Mail オブジェクトの取得とデータベースへの保存
	 *
	 * @param bool $do_update
	 * @return MW_WP_Form_Mail
	 */
	public function get_parsed_mail_object( $do_update = false ) {
		if ( $do_update ) {
			$form_id = $this->Setting->get( 'post_id' );
			$insert_contact_data_id = wp_insert_post( array(
				'post_title'  => $this->parse_mail_content( $this->Mail->subject ),
				'post_status' => 'publish',
				'post_type'   => MWF_Functions::get_contact_data_post_type_from_form_id( $form_id ),
			) );

			// 添付ファイルをメディアに保存
			// save_mail_body 内のフックで添付ファイルの情報を使えるように、
			// save_mail_body より前にこのブロックを実行する
			if ( !empty( $insert_contact_data_id ) ) {
				MWF_Functions::save_attachments_in_media(
					$insert_contact_data_id,
					$this->Mail->attachments,
					$form_id
				);
			}
			$this->insert_contact_data_id = $insert_contact_data_id;
		}
		return $this->parse_mail_object( $do_update );
	}

	/**
	 * メールオブジェクトの各プロパティを変換
	 *
	 * @param bool $do_update
	 * @return MW_WP_Form_Mail $Mail
	 */
	protected function parse_mail_object( $do_update = false ) {
		$parsed_Mail_vars = get_object_vars( $this->Mail );
		foreach ( $parsed_Mail_vars as $key => $value ) {
			if ( is_array( $value ) ) {
				continue;
			}

			if ( $key == 'to' || $key == 'cc' || $key == 'bcc' ) {
				$this->Mail->$key = $this->parse_mail_destination( $value );
				continue;
			}

			if ( $key == 'body' && $do_update ) {
				$value = $this->parse_mail_content( $value, true );
			} else {
				$value = $this->parse_mail_content( $value );
			}
			$this->Mail->$key = $value;
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
	 * @param bool $do_update
	 * @return string
	 */
	protected function parse_mail_content( $value, $do_update = false ) {
		if ( $do_update ) {
			$callback = '_save_mail_content';
		} else {
			$callback = '_parse_mail_content';
		}
		return preg_replace_callback(
			'/{(.+?)}/',
			array( $this, $callback ),
			$value
		);
	}
	protected function _parse_mail_content( $matches ) {
		return $this->parse( $matches, false );
	}
	protected function _save_mail_content( $matches ) {
		return $this->parse( $matches, true );
	}

	/**
	 * $this->_parse_mail_content(), $this->_save_mail_content の本体
	 * 第2引数でDB保存するか判定
	 *
	 * @param array $matches
	 * @param bool $do_update
	 * @return string $value
	 */
	protected function parse( $matches, $do_update = false ) {
		$match = $matches[1];
		$form_id = $this->Setting->get( 'post_id' );
		// MWF_Config::TRACKINGNUMBER のときはお問い合せ番号を参照する
		if ( $match === MWF_Config::TRACKINGNUMBER ) {
			if ( $form_id ) {
				$value = $this->Setting->get_tracking_number( $form_id );
			}
		} else {
			$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
			$value = $this->Data->get( $match );
			$value = $this->apply_filters_mwform_custom_mail_tag( $form_key, $value, $match );
		}

		// 値が null でも保存（チェッボックス未チェックで直送信でも保存させるため）
		// ただし、画像の場合はURLが保存されないように調整がはいるため除外が必要
		if ( $do_update ) {
			if ( !array_key_exists( $match, $this->Mail->attachments ) ) {
				update_post_meta( $this->insert_contact_data_id, $match, $value );
			}
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
		return apply_filters(
			'mwform_custom_mail_tag_' . $form_key,
			$value,
			$match,
			$this->insert_contact_data_id
		);
	}
}
