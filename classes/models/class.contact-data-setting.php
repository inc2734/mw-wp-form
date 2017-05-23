<?php
/**
 * Name       : MW WP Form Contact Data Setting
 * Description: 管理画面クラス
 * Version    : 1.0.4
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : January 1, 2015
 * Modified   : February 2, 2017
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Contact_Data_Setting {

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
	 * @param int $post_id
	 */
	public function __construct( $post_id ) {
		if ( ! MWF_Functions::is_contact_data_post_type( get_post_type( $post_id ) ) ) {
			return;
		}

		$this->post_id = $post_id;

		$post_custom = get_post_custom( $post_id );
		$post_meta   = array();
		foreach ( $post_custom as $key => $value ) {
			if ( preg_match( '/^_/', $key ) ) {
				continue;
			}
			$post_meta[ $key ] = $value[0];
		}

		$permit_values = get_post_meta( $this->post_id, MWF_config::CONTACT_DATA_NAME, true );
		if ( ! $permit_values ) {
			$permit_values = array();
		}

		$values = array_merge( $post_meta, $permit_values );
		if ( is_array( $values ) ) {
			$this->sets( $values );
		}
	}

	/**
	 * 問い合わせステータスの種類を取得
	 *
	 * @return array
	 */
	public function get_response_statuses() {
		return array(
			'not-supported' => esc_html__( 'Not supported', 'mw-wp-form' ),
			'reservation'   => esc_html__( 'Reservation', 'mw-wp-form' ),
			'supported'     => esc_html__( 'Supported', 'mw-wp-form' ),
		);
	}

	/**
	 * 更新可能なキーを返す
	 *
	 * @return array
	 */
	public function get_permit_keys() {
		$vars = get_object_vars( $this );
		unset( $vars[ 'post_id' ] );
		unset( $vars[ 'options' ] );
		return array_keys( $vars );
	}

	/**
	 * 全てのメタデータを取得
	 */
	public function gets() {
		$options = $this->options;
		$permit_keys = $this->get_permit_keys();
		foreach ( $permit_keys as $permit_key ) {
			$options[ $permit_key ] = $this->$permit_key;
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
		$vars = get_object_vars( $this );
		unset( $vars['options'] );
		$attributes = array_keys( $vars );

		if ( in_array( $key, $attributes ) ) {
			if ( 'response_status' === $key ) {
				$response_statuses = $this->get_response_statuses();
				if ( isset( $response_statuses[ $this->response_status ] ) ) {
					return $this->response_status;
				}
			}
			return $this->$key;
		} elseif ( isset( $this->options[ $key ] ) ) {
			return $this->options[ $key ];
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
		if ( ! in_array( $key, $permit_keys ) ) {
			$this->options[ $key ] = $value;
			return;
		}

		if ( 'response_status' !== $key ) {
			$this->$key = $value;
			return;
		}

		if ( array_key_exists( $value, $this->get_response_statuses() ) ) {
			$this->$key = $value;
			return;
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
	 * Save values of permit keys and options
	 */
	public function save() {
		$permit_keys   = $this->get_permit_keys();
		$permit_values = array();
		foreach ( $permit_keys as $key ) {
			$permit_values[ $key ] = $this->$key;
		}
		update_post_meta( $this->post_id, MWF_config::CONTACT_DATA_NAME, $permit_values );

		foreach ( $this->options as $key => $value ) {
			update_post_meta( $this->post_id, $key, $value );
		}

		$contact_data_post_type = get_post_type( $this->post_id );
		do_action( 'mwform_contact_data_save-' . $contact_data_post_type, $this->post_id );
	}

	/**
	 * データベースに保存に設定されているフォーム（投稿）を取得
	 *
	 * @return array
	 */
	public static function get_form_post_types() {
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

		return $new_post_types;
	}

	public static function get_posts() {
		MWF_Functions::deprecated_message(
			'MW_WP_Form_Contact_Data_Setting::get_posts()',
			'MW_WP_Form_Contact_Data_Setting::get_form_post_types()'
		);

		return self::get_form_post_types();
	}

	/**
	 * $meta_key が $post の upload_file_key かどうか
	 *
	 * @param string $meta_key
	 * @return bool
	 */
	public function is_upload_file_key( $meta_key ) {
		$upload_file_keys = $this->_get_upload_file_keys();
		return ( is_array( $upload_file_keys ) && in_array( $meta_key, $upload_file_keys ) );
	}

	/**
	 * $meta_key が upload_file_key に含まれている場合に添字を返す
	 *
	 * @param string $meta_key
	 * @return int|false
	 */
	public function get_index_of_key_in_upload_file_keys( $meta_key ) {
		$upload_file_keys = $this->_get_upload_file_keys();
		if ( is_array( $upload_file_keys ) ) {
			return array_search( $meta_key, $upload_file_keys );
		}
		return false;
	}

	/**
	 * その投稿がもつ upload_file_key を取得
	 *
	 * @return array $upload_file_keys
	 */
	protected function _get_upload_file_keys() {
		// 前のバージョンでは MWF_Config::UPLOAD_FILE_KEYS を配列で保持していなかったので分岐させる
		$_upload_file_keys = get_post_meta( $this->post_id, '_' . MWF_Config::UPLOAD_FILE_KEYS, true );
		if ( is_array( $_upload_file_keys ) ) {
			$upload_file_keys = $_upload_file_keys;
		} else {
			$upload_file_keys = get_post_custom_values( '_' . MWF_Config::UPLOAD_FILE_KEYS, $this->post_id );
		}
		return $upload_file_keys;
	}
}
