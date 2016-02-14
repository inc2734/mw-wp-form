<?php
/**
 * Name       : MW WP Form Contact Data Setting
 * Description: 管理画面クラス
 * Version    : 1.0.3
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : January 1, 2015
 * Modified   : May 26, 2015
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Contact_Data_Setting {

	/**
	 * 問い合わせデータを保存しているフォームの投稿タイプの一覧
	 * @var array
	 */
	protected static $contact_data_post_types;

	/**
	 * 保存された問い合わせデータの Post ID
	 * @var int
	 */
	protected $post_id;

	/**
	 * 各フォーム項目から送信された値を格納
	 * @var array
	 */
	protected $options = array();

	/**
	 * 問い合わせデータのステータス
	 * @var string not-supported|reservation|supported
	 */
	protected $response_status = 'not-supported';

	/**
	 * メモ
	 * @var string
	 */
	protected $memo = '';

	/**
	 * 対応状況種別の一覧
	 * @var array
	 */
	protected $response_statuses = array();

	/**
	 * __construct
	 *
	 * @param int $post_id
	 */
	public function __construct( $post_id ) {
		if ( MWF_Functions::is_contact_data_post_type( get_post_type( $post_id ) ) ) {
			$this->post_id = $post_id;
			$this->response_statuses = array(
				'not-supported' => esc_html__( 'Not supported', 'mw-wp-form' ),
				'reservation'   => esc_html__( 'Reservation', 'mw-wp-form' ),
				'supported'     => esc_html__( 'Supported', 'mw-wp-form' ),
			);

			$post_custom = get_post_custom( $post_id );
			$post_meta   = array();
			foreach ( $post_custom as $key => $value ) {
				if ( preg_match( '/^_/', $key ) ) {
					continue;
				}
				$post_meta[$key] = $value[0];
			}

			$permit_values = get_post_meta( $this->post_id, MWF_config::CONTACT_DATA_NAME, true );
			if ( !$permit_values ) {
				$permit_values = array();
			}

			$values = array_merge( $post_meta, $permit_values );
			if ( is_array( $values ) ) {
				$this->sets( $values );
			}
		}
	}

	/**
	 * 問い合わせステータスの種類を取得
	 *
	 * @return array
	 */
	public function get_response_statuses() {
		return $this->response_statuses;
	}

	/**
	 * 更新可能なキーを返す
	 *
	 * @return array
	 */
	public function get_permit_keys() {
		return array( 'response_status', 'memo' );
	}

	/**
	 * 全てのメタデータを取得
	 */
	public function gets() {
		$options = $this->options;
		$permit_keys = $this->get_permit_keys();
		foreach ( $permit_keys as $value ) {
			$options[$value] = $this->$value;
		}
		return $options;
	}

	/**
	 * メタデータの取得
	 *
	 * @param string $key
	 * @return mixed|null
	 */
	public function get( $key ) {
		$permit_keys = $this->get_permit_keys();
		if ( in_array( $key, $permit_keys ) ) {
			$value = $this->$key;
			if ( $key === 'response_status' ) {
				if ( isset( $this->response_statuses[$value] ) ) {
					return $value;
				}
				return $value;
			}
			return $value;
		} elseif ( isset( $this->options[$key] ) ) {
			return $this->options[$key];
		}
	}

	/**
	 * 属性をセット
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function set( $key, $value ) {
		$permit_keys = $this->get_permit_keys();
		if ( in_array( $key, $permit_keys ) ) {
			$this->$key = $value;
		} else {
			$this->options[$key] = $value;
		}
	}

	/**
	 * 属性をセット
	 *
	 * @param array $values
	 */
	public function sets( array $values ) {
		foreach ( $values as $key => $value ) {
			$this->set( $key, $value );
		}
	}

	/**
	 * 保存
	 *
	 * @param bool $non_permit_keys_save_flg permit_keys以外のメタデータも更新する
	 */
	public function save( $non_permit_keys_save_flg = false ) {
		$permit_keys   = $this->get_permit_keys();
		$permit_values = array();
		foreach ( $permit_keys as $key ) {
			$permit_values[$key] = $this->$key;
		}
		update_post_meta( $this->post_id, MWF_config::CONTACT_DATA_NAME, $permit_values );

		if ( $non_permit_keys_save_flg !== true ) {
			return;
		}

		foreach ( $this->options as $key => $value ) {
			update_post_meta( $this->post_id, $key, $value );
		}
	}

	/**
	 * データベースに保存に設定されているフォーム（投稿）を取得
	 *
	 * @return array
	 */
	public static function get_posts() {
		if ( self::$contact_data_post_types !== null ) {
			return self::$contact_data_post_types;
		}
		$contact_data_post_types = array();
		$Admin = new MW_WP_Form_Admin();
		$forms = $Admin->get_forms_using_database();
		foreach ( $forms as $form ) {
			$post_type = MWF_Functions::get_contact_data_post_type_from_form_id( $form->ID );
			$contact_data_post_types[] = $post_type;
		}
		$raw_post_types = $contact_data_post_types;
		$new_post_types = array();
		$contact_data_post_types = apply_filters(
			'mwform_contact_data_post_types',
			$contact_data_post_types
		);
		// もともとの配列に含まれていない値は削除する
		foreach ( $contact_data_post_types as $post_type ) {
			if ( in_array( $post_type, $raw_post_types ) ) {
				$new_post_types[] = $post_type;
			}
		}
		self::$contact_data_post_types = $new_post_types;
		return self::$contact_data_post_types;
	}

	/**
	 * $meta_key が $post の upload_file_key かどうか
	 *
	 * @param WP_Post $post
	 * @param string $meta_key
	 * @return bool
	 */
	public function is_upload_file_key( $post, $meta_key ) {
		$upload_file_keys = $this->get_upload_file_keys( $post );
		if ( is_array( $upload_file_keys ) && in_array( $meta_key, $upload_file_keys ) ) {
			return true;
		}
		return false;
	}

	/**
	 * $meta_key が upload_file_key に含まれている場合にキーを返す
	 *
	 * @param WP_Post $post
	 * @param string $meta_key
	 * @return int|false
	 */
	public function get_key_in_upload_file_keys( $post, $meta_key ) {
		$upload_file_keys = $this->get_upload_file_keys( $post );
		if ( is_array( $upload_file_keys ) ) {
			return array_search( $meta_key, $upload_file_keys );
		}
		return false;
	}

	/**
	 * その投稿がもつ upload_file_key を取得
	 *
	 * @param WP_Post $post
	 * @return array $upload_file_keys
	 */
	protected function get_upload_file_keys( $post ) {
		// 前のバージョンでは MWF_Config::UPLOAD_FILE_KEYS を配列で保持していなかったので分岐させる
		$_upload_file_keys = get_post_meta( $post->ID, '_' . MWF_Config::UPLOAD_FILE_KEYS, true );
		if ( is_array( $_upload_file_keys ) ) {
			$upload_file_keys = $_upload_file_keys;
		} else {
			$upload_file_keys = get_post_custom_values( '_' . MWF_Config::UPLOAD_FILE_KEYS, $post->ID );
		}
		return $upload_file_keys;
	}
}
