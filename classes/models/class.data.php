<?php
/**
 * Name       : MW WP Form Data
 * Version    : 2.0.0
 * Author     : Takashi Kitajima
 * Author URI : https://2inc.org
 * Created    : October 10, 2013
 * Modified   : May 17, 2017
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Data {

	/**
	 * @var array of MW_WP_Form_Data
	 */
	protected static $Instances;

	/**
	 * @var MW_WP_Form_Sesion
	 */
	protected $Session;
	protected $Session_meta;
	protected $Session_validation_error;

	/**
	 * @var array
	 */
	protected $POST = array();

	/**
	 * @var array
	 */
	protected $FILES = array();

	/**
	 * @var array
	 */
	protected $variables         = array();
	protected $meta              = array();
	protected $validation_errors = array();

	/**
	 * @param string $form_key
	 * @param array $POST $_POST
	 * @param array $FILES $_FILES
	 */
	private function __construct( $form_key, array $POST = array(), array $FILES = array() ) {
		$this->Session                  = new MW_WP_Form_Session( $form_key );
		$this->Session_meta             = new MW_WP_Form_Session( $form_key . '-meta' );
		$this->Session_validation_error = new MW_WP_Form_Session( $form_key . '-validation-error' );

		$this->variables         = $this->Session->gets();
		$this->meta              = $this->Session_meta->gets();
		$this->validation_errors = $this->Session_validation_error->gets();

		$this->POST  = $POST;
		$this->FILES = $FILES;

		$this->_set_form_key( $form_key );
		$this->_set_request_valiables();
		$this->_set_files_valiables();

		add_action( 'shutdown', array( $this, '_save_to_session' ) );
	}

	public function _save_to_session() {
		$this->Session->clear_values();
		$this->Session->save( $this->variables );

		$this->Session_meta->clear_values();
		$this->Session_meta->save( $this->meta );

		$this->Session_validation_error->clear_values();
		$this->Session_validation_error->save( $this->validation_errors );
	}

	/**
	 * Instantiation MW_WP_Form_Data
	 *
 	 * @param string $form_key
 	 * @param array $POST $_POST
 	 * @param array $FILES $_FILES
	 * @return MW_WP_Form_Data
	 */
	public static function connect( $form_key, $POST = null, $FILES = null ) {
		if ( isset( self::$Instances[ $form_key ] ) && is_null( $POST ) && is_null( $FILES ) ) {
			return self::$Instances[ $form_key ];
		}

		if ( ! is_array( $POST ) ) {
			$POST = array();
		}

		if ( ! is_array( $FILES ) ) {
			$FILES = array();
		}

		self::$Instances[ $form_key ] = new self( $form_key, $POST, $FILES );
		return self::$Instances[ $form_key ];
	}

	public static function getInstance( $form_key, $POST = null, $FILES = null ) {
		MWF_Functions::deprecated_message(
			'MW_WP_Form_Data::getInstance()',
			'MW_WP_Form_Data::connect()'
		);

		self::connect( $form_key, $POST, $FILES );
	}

	/**
	 * Return form key
	 *
	 * @return string
	 */
	public function get_form_key() {
		if ( isset( $this->meta['form_key'] ) ) {
			return $this->meta['form_key'];
		}
	}

	/**
	 * @param string $form_key
	 */
	protected function _set_form_key( $form_key ) {
		$this->meta['form_key'] = $form_key;
	}

	/**
	 * Set $_POST variables
	 */
	protected function _set_request_valiables() {
		if ( ! empty( $this->POST ) ) {
			$this->sets( stripslashes_deep( $this->POST ) );
		}
	}

	/**
	 * Set $_FILES variables
	 */
	protected function _set_files_valiables() {
		$files = array();
		foreach ( $this->FILES as $key => $file ) {
			if ( ! isset( $this->POST[ $key ] ) || ! empty( $file['name'] ) ) {
				if ( $file['error'] == UPLOAD_ERR_OK && is_uploaded_file( $file['tmp_name'] ) ) {
					$this->set( $key, $file['name'] );
				} else {
					$this->set( $key, '' );
				}

				if ( ! empty( $file['name'] ) ) {
					$files[ $key ] = $file;
				}
			}
		}

		// この条件判定がないと fileSize チェックが正しく動作しない
		if ( $files ) {
			$this->set( MWF_Config::UPLOAD_FILES, $files );
		}
	}

	/**
	 * 送信データからどのページを表示すべきかの状態を判定して返す
	 * ただし実際に表示するページと同じとは限らない（バリデーション通らないとかあるので）
	 *
	 * @return string back|confirm|complete|input
	 */
	public function get_post_condition() {
		$backButton    = $this->get_post_value_by_key( MWF_Config::BACK_BUTTON );
		$confirmButton = $this->get_post_value_by_key( MWF_Config::CONFIRM_BUTTON );

		if ( $backButton ) {
			return 'back';
		} elseif ( $confirmButton ) {
			return 'confirm';
		} elseif ( ! $confirmButton && ! $backButton && $this->_is_valid_token() ) {
			return 'complete';
		}

		return 'input';
	}

	/**
	 * Get values
	 *
	 * @return array
	 */
	public function gets() {
		return $this->variables;
	}

	/**
	 * Set the value
	 *
	 * @param string $key
	 * @param string $value
	 */
	public function set( $key, $value ){
		$this->variables[ $key ] = $value;
	}

	/**
	 * Set values
	 *
	 * @param array 値
	 */
	public function sets( array $array ) {
		foreach ( $array as $key => $value ) {
			$this->set( $key, $value );
		}
	}

	/**
	 * Clear the value
	 *
	 * @param string $key データのキー
	 */
	public function clear_value( $key ) {
		if ( isset( $this->variables[ $key ] ) ) {
			unset( $this->variables[ $key ] );
		}
	}

	/**
	 * Clear all values
	 */
	public function clear_values() {
		$this->variables         = array();
		$this->meta              = array();
		$this->validation_errors = array();
	}

	/**
	 * Push the value
	 *
	 * @param string $key データのキー
	 * @param string $value 値
	 */
	public function push( $key, $value ) {
		if ( ! isset( $this->variables[ $key ] ) ) {
			$this->variables[ $key ] = array( $value );
		} else {
			if ( is_array( $this->variables[ $key ] ) ) {
				$this->variables[ $key ][] = $value;
			} else {
				$this->variables[ $key ] = array( $this->variables[ $key ] );
				$this->variables[ $key ][] = $value;
			}
		}
	}

	/**
	 * 整形済み（メール送信可能な）データを取得。送信値、表示値を自動判別
	 *
	 * @param string $key
	 * @param array $children
	 * @return string|null
	 */
	public function get( $key, array $children = array() ) {
		$post_value = $this->get_post_value_by_key( $key );

		if ( is_null( $post_value ) ) {
			return;
		}

		if ( is_array( $post_value ) && ! array_key_exists( 'data', $post_value ) ) {
			return;
		}

		$__children = $this->get_post_value_by_key( '__children' );

		if ( empty( $children ) && isset( $__children[ $key ] ) ) {
			if ( is_array( $__children[ $key ] ) ) {
				$_children = $__children[ $key ];
				foreach ( $_children as $_child ) {
					if ( is_array( $_child ) ) {
						continue;
					}
					$_child = json_decode( $_child, true );
					if ( ! is_array( $_child ) ) {
						continue;
					}
					foreach ( $_child as $_child_key => $_child_value ) {
						$children[ $_child_key ] = $_child_value;
					}
				}
			}
		}

		if ( is_array( $post_value ) ) {
			if ( $children ) {
				return $this->get_separated_value( $key, $children );
			}
			return $this->get_separated_value_not_children_set( $key );
		} else {
			if ( $children ) {
				return $this->get_in_children( $key, $children );
			}
			return $this->get_raw( $key );
		}
	}

	/**
	 * Get the raw value
	 *
	 * @param string $key
	 * @return string|null
	 */
	public function get_raw( $key ) {
		$post_value = $this->get_post_value_by_key( $key );

		if ( is_null( $post_value ) ) {
			return;
		}

		if ( is_array( $post_value ) && ! array_key_exists( 'data', $post_value ) ) {
			return;
		}

		$__children = $this->get_post_value_by_key( '__children' );

		$children = array();
		if ( isset( $__children[ $key ] ) && is_array( $__children[ $key ] ) ) {
			$_children = $__children[ $key ];
			if ( is_array( $_children ) ) {
				foreach ( $_children as $_child ) {
					if ( is_array( $_child ) ) {
						continue;
					}
					$_child = json_decode( $_child, true );
					if ( ! is_array( $_child ) ) {
						continue;
					}
					foreach ( $_child as $_child_key => $_child_value ) {
						$children[ $_child_key ] = $_child_value;
					}
				}
			}
		}

		if ( is_array( $post_value ) ) {
			if ( $children ) {
				return $this->get_separated_raw_value( $key, $children );
			}
			return $this->get_separated_value_not_children_set( $key );
		} else {
			if ( $children ) {
				return $this->get_raw_in_children( $key, $children );
			}
			return $this->get_post_value_by_key( $key );
		}
	}

	/**
	 * そのキーに紐づく送信データを取得（通常の value 以外に separator や data などが紐づく）
	 *
	 * @param string $key name attribute
	 * @return mixed
	 */
	public function get_post_value_by_key( $key ) {
		if ( isset( $this->variables[ $key ] ) ) {
			return $this->variables[ $key ];
		}
	}

	/**
	 * $children の中に値が含まれているときだけ返す
	 *
	 * @param string $key name attribute
	 * @param array $children
	 * @return string
	 */
	public function get_in_children( $key, array $children ) {
		$value = $this->get_post_value_by_key( $key );
		if ( is_null( $value ) || is_array( $value ) ) {
			return;
		}

		if ( isset( $children[ $value ] ) ) {
			return $children[ $value ];
		}

		return '';
	}

	/**
	 * $children の中に値が含まれているときだけ返す
	 *
	 * @param string $key name attribute
	 * @param array $children
	 * @return string
	 */
	public function get_raw_in_children( $key, array $children ) {
		$value = $this->get_post_value_by_key( $key );
		if ( is_null( $value ) || is_array( $value ) ) {
			return;
		}

		if ( isset( $children[ $value ] ) ) {
			return $value;
		}

		return '';
	}

	/**
	 * 送られてきたseparatorを返す
	 *
	 * @param string $key name attribute
	 * @return string
	 */
	public function get_separator_value( $key ) {
		$value = $this->get_post_value_by_key( $key );
		if ( is_array( $value ) && isset( $value['separator'] ) ) {
			return $value['separator'];
		}
	}

	/**
	 * 配列データを整形して表示値を返す。separator が送信されていない場合は null
	 *
	 * @param string $key name attribute
	 * @param array $children 選択肢
	 * @return string|null
	 */
	public function get_separated_value( $key, array $children ) {
		$separator = $this->get_separator_value( $key );
		$value     = $this->get_post_value_by_key( $key );

		if ( ! $children ) {
			return;
		}

		if ( ! is_array( $value ) ) {
			return;
		}

		if ( ! isset( $value['data'] ) ) {
			return;
		}

		if ( ! $separator ) {
			return;
		}

		// 入力 -> 確認のときは配列、確認 -> 入力のときは文字列
		if ( ! is_array( $value['data'] ) ) {
			$value['data'] = explode( $separator, $value['data'] );
		}

		$rightData = array();
		foreach ( $value['data'] as $child ) {
			if ( isset( $children[ $child ] ) && !in_array( $children[ $child ], $rightData ) ) {
				$rightData[] = $children[ $child ];
			}
		}

		return implode( $separator, $rightData );
	}

	/**
	 * 配列データを整形して送信値を返す。separator が送信されていない場合は null
	 * 本当は protected 後方互換
	 *
	 * @param string $key name attribute
	 * @param array $children 選択肢
	 * @return string|null
	 */
	public function get_separated_raw_value( $key, array $children ) {
		$separator = $this->get_separator_value( $key );
		$value     = $this->get_post_value_by_key( $key );

		if ( ! $children ) {
			return;
		}

		if ( ! is_array( $value ) ) {
			return;
		}

		if ( ! isset( $value['data'] ) ) {
			return;
		}

		if ( ! $separator ) {
			return;
		}

		// 入力 -> 確認のときは配列、確認 -> 入力のときは文字列
		if ( ! is_array( $value['data'] ) ) {
			$value['data'] = explode( $separator, $value['data'] );
		}

		$rightData = array();
		foreach ( $value['data'] as $child ) {
			if ( isset( $children[ $child ] ) && !in_array( $child, $rightData ) ) {
				$rightData[] = $child;
			}
		}
		return implode( $separator, $rightData );
	}

	/**
	 * すべて空のからのときはimplodeしないように（---がいってしまうため）= 一個でも値ありがあれば返す
	 *
	 * @param array $data
	 * @param string $separator
	 * @return string|null
	 */
	protected function get_separated_value_not_children_set( $key ) {
		$separator = $this->get_separator_value( $key );
		$value     = $this->get_post_value_by_key( $key );

		if ( ! is_array( $value ) ) {
			return;
		}

		if ( ! isset( $value['data'] ) ) {
			return;
		}

		if ( ! $separator ) {
			return;
		}

		if ( ! is_array( $value['data'] ) ) {
			$value['data'] = explode( $separator, $value['data'] );
		}

		foreach ( $value['data'] as $child ) {
			if ( $child !== '' && ! is_null( $child ) ) {
				return implode( $separator, $value['data'] );
			}
		}

		return '';
	}

	/**
	 * アップロードに失敗、もしくはファイルが削除されている key を UPLOAD_FILE_KEYS から削除
	 */
	public function regenerate_upload_file_keys() {
		$upload_file_keys = $this->get_post_value_by_key( MWF_Config::UPLOAD_FILE_KEYS );
		if ( ! is_array( $upload_file_keys ) ) {
			$upload_file_keys = array();
		}

		$upload_file_keys = apply_filters(
			'mwform_upload_file_keys_' . $this->get_form_key(),
			$upload_file_keys,
			clone $this
		);
		if ( ! is_array( $upload_file_keys ) ) {
			$upload_file_keys = array();
		}
		$upload_file_keys = array_values( array_unique( $upload_file_keys ) );

		$wp_upload_dir = wp_upload_dir();
		foreach ( $upload_file_keys as $key => $upload_file_key ) {
			$upload_file_url = $this->get_post_value_by_key( $upload_file_key );
			$filepath = MWF_Functions::fileurl_to_path( $upload_file_url );
			if ( ! $upload_file_url || ! file_exists( $filepath ) ) {
				unset( $upload_file_keys[ $key ] );
			}
		}

		$this->set( MWF_Config::UPLOAD_FILE_KEYS, $upload_file_keys );
	}

	/**
	 * アップロードに成功したファイルを UPLOAD_FILE_KEYS に格納
	 *
	 * @param array $uploaded_files アップロード済みファイルのパスの配列
	 */
	public function push_uploaded_file_keys( array $uploaded_files = array() ) {
		$upload_file_keys = $this->get_post_value_by_key( MWF_Config::UPLOAD_FILE_KEYS );
		if ( ! is_array( $upload_file_keys ) ) {
			$upload_file_keys = array();
			$this->set( MWF_Config::UPLOAD_FILE_KEYS, $upload_file_keys );
		}

		foreach ( $uploaded_files as $key => $upload_file ) {
			$this->set( $key, $upload_file );
			if ( is_array( $upload_file_keys ) && ! in_array( $key, $upload_file_keys ) ) {
				$this->push( MWF_Config::UPLOAD_FILE_KEYS, $key );
			}
		}
	}

	/**
	 * 表示すべき画面を示すフラグを設定
	 *
	 * @param string null|input|confirm|complete
	 */
	public function set_view_flg( $view_flg ) {
		$this->meta['view_flg'] = $view_flg;
	}

	/**
	 * 表示すべき画面を示すフラグを返す
	 *
	 * @param string null|input|confirm|complete
	 */
	public function get_view_flg() {
		if ( isset( $this->meta['view_flg'] ) ) {
			return $this->meta['view_flg'];
		}
	}

	/**
	 * 送信エラーを示すフラグをセット
	 */
	public function set_send_error() {
		$this->meta[ MWF_Config::SEND_ERROR ] = true;
	}

	/**
	 * 送信エラーを示すフラグを返す
	 *
	 * @return boolean
	 */
	public function get_send_error() {
		if ( isset( $this->meta[ MWF_Config::SEND_ERROR ] ) ) {
			return $this->meta[ MWF_Config::SEND_ERROR ];
		}
	}

	/**
	 * Nonce check
	 *
	 * @return bool
	 */
	protected function _is_valid_token() {
		$request_token = $this->get_post_value_by_key( MWF_Config::TOKEN_NAME );
		$values        = $this->gets();
		$form_key      = $this->get_form_key();
		return ( isset( $request_token ) && wp_verify_nonce( $request_token, $form_key ) );
	}

	/**
	 * Set the error message
	 *
	 * @param string $key name attribute
	 * @param string $rule
	 * @param string $message
	 */
	public function set_validation_error( $key, $rule, $message ) {
		if ( ! is_string( $message ) ) {
			exit( 'The Validate error message must be string!' );
		}
		$errors = $this->get_validation_error( $key );
		$errors[ $rule ] = $message;
		$this->validation_errors[ $key ] = $errors;
	}

	/**
	 * Return error messages the one form field
	 *
	 * @param string $key name attribute
	 * @return array
	 */
	public function get_validation_error( $key ) {
		if ( isset( $this->validation_errors[ $key ] ) ) {
			$errors = $this->validation_errors[ $key ];
		} else {
			$errors = array();
		}
		if ( is_null( $errors ) || ! is_array( $errors ) ) {
			return array();
		}
		return $errors;
	}

	/**
	 * Return all error messages
	 *
	 * @return array
	 */
	public function get_validation_errors() {
		return $this->validation_errors;
	}
}
