<?php
/**
 * Name       : MW WP Form Contact Data Setting
 * Description: 管理画面クラス
 * Version    : 1.0.1
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : January 1, 2015
 * Modified   : February 7, 2015
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Contact_Data_Setting {

	/**
	 * $post_id
	 * フォームのPost ID
	 * @var int
	 */
	protected $post_id;

	/**
	 * $options
	 * 各フォーム項目から送信された値を格納
	 * @var array
	 */
	protected $options = array();

	/**
	 * $response_status
	 * @var string not-supported|reservation|supported
	 */
	protected $response_status = 'not-supported';

	/**
	 * $memo
	 * @var string
	 */
	protected $memo = '';

	/**
	 * $response_statuses
	 * 対応状況種別の一覧
	 * @var array
	 */
	protected $response_statuses = array();

	/**
	 * __construct
	 * @param int $post_id
	 */
	public function __construct( $post_id ) {
		if ( preg_match( '/^' . MWF_Config::DBDATA . '/', get_post_type( $post_id ) ) ) {
			$this->post_id = $post_id;
			$this->response_statuses = array(
				'not-supported' => esc_html__( 'Not supported', MWF_Config::DOMAIN ),
				'reservation'   => esc_html__( 'Reservation', MWF_Config::DOMAIN ),
				'supported'     => esc_html__( 'Supported', MWF_Config::DOMAIN ),
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
	 * get_response_statuses
	 * @return array
	 */
	public function get_response_statuses() {
		return $this->response_statuses;
	}

	/**
	 * get_permit_keys
	 * 更新可能なキーを返す
	 * @return array
	 */
	public function get_permit_keys() {
		return array( 'response_status', 'memo' );
	}

	/**
	 * gets
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
	 * get
	 * 属性の取得
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
	 * set
	 * 属性をセット
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
	 * sets
	 * 属性をセット
	 * @param array $values
	 */
	public function sets( array $values ) {
		foreach ( $values as $key => $value ) {
			$this->set( $key, $value );
		}
	}

	/**
	 * save
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
	 * get_posts
	 * @return array
	 */
	public static function get_posts() {
		$contact_data_post_types = array();
		$Admin = new MW_WP_Form_Admin();
		$forms = $Admin->get_forms_using_database();
		foreach ( $forms as $form ) {
			$post_type = MWF_Config::DBDATA . $form->ID;
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
		return $new_post_types;
	}

	/**
	 * is_upload_file_key
	 * $meta_key が $post の upload_file_key かどうか
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
	 * get_upload_file_keys
	 * その投稿がもつ upload_file_key を取得
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