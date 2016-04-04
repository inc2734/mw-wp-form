<?php
/**
 * Name       : MW WP Form Data
 * Description: MW WP Form のデータ操作用
 * Version    : 1.5.1
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : October 10, 2013
 * Modified   : April 4, 2016
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Data {

	/**
	 * @var MW_WP_Form_Data
	 */
	protected static $Instance;

	/**
	 * フォーム識別子
	 * @var string
	 */
	protected $form_key;

	/**
	 * フォームから送信された内容を保存した配列
	 * @var array
	 */
	protected $data = array();

	/**
	 * @var MW_WP_Form_Sesion
	 */
	protected $Session;

	/**
	 * @var array
	 */
	protected $POST = array();

	/**
	 * @var array
	 */
	protected $FILES = array();

	/**
	 * __construct
	 *
	 * @param string $form_key フォーム識別子
	 * @param array $POST $_POSTを想定
	 * @param array $FILES $_FILESを想定
	 */
	private function __construct( $form_key, array $POST = array(), array $FILES = array() ) {
		$this->form_key = $form_key;
		$this->POST     = $POST;
		$this->FILES    = $FILES;
		$this->Session  = new MW_WP_Form_Session( $form_key );
		$this->data     = $this->Session->gets();
		$this->set_request_valiables( $this->POST );
		$this->set_files_valiables( $this->POST, $this->FILES );
	}

	/**
	 * getInstance
	 *
	 * @param null|string $form_key フォーム識別子
	 * @param null|array $POST $_POSTを想定
	 * @param null|array $FILES $_FILESを想定
	 */
	public static function getInstance( $form_key = null, $POST = null, $FILES = null ) {
		if ( is_null( $POST ) || !is_array( $POST ) ) {
			$POST = array();
		}
		if ( is_null( $FILES ) || !is_array( $FILES ) ) {
			$FILES = array();
		}
		if ( is_null( $form_key ) && !is_null( self::$Instance ) ) {
			return self::$Instance;
		}
		if ( !is_null( $form_key ) ) {
			self::$Instance = new self( $form_key, $POST, $FILES );
			return self::$Instance;
		}
		exit( 'MW_WP_Form_Data instantiation error.' );
	}

	/**
	 * Return form key
	 *
	 * @return string
	 */
	public function get_form_key() {
		return $this->form_key;
	}

	/**
	 * $_POST をセット
	 *
	 * @param array $POST $_POSTを想定
	 */
	protected function set_request_valiables( array $POST ) {
		if ( !empty( $POST ) ) {
			$this->sets( stripslashes_deep( $POST ) );
		}
	}

	/**
	 * $_FILES をセット
	 *
	 * @param array $POST $_POSTを想定
	 * @param array $FILES $_FILESを想定
	 */
	protected function set_files_valiables( array $POST, array $FILES ) {
		$files = array();
		foreach ( $FILES as $key => $file ) {
			if ( !isset( $POST[$key] ) || !empty( $file['name'] ) ) {
				if ( $file['error'] == UPLOAD_ERR_OK && is_uploaded_file( $file['tmp_name'] ) ) {
					$this->set( $key, $file['name'] );
				} else {
					$this->set( $key, '' );
				}
				if ( !empty( $file['name'] ) ) {
					$files[$key] = $file;
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
	 * @param bool $token_check
	 * @return string back|confirm|complete|input
	 */
	public function get_post_condition( $token_check ) {
		$backButton    = $this->get_post_value_by_key( MWF_Config::BACK_BUTTON );
		$confirmButton = $this->get_post_value_by_key( MWF_Config::CONFIRM_BUTTON );
		if ( $backButton ) {
			return 'back';
		} elseif ( $confirmButton ) {
			return 'confirm';
		} elseif ( !$confirmButton && !$backButton && $token_check ) {
			return 'complete';
		}
		return 'input';
	}

	/**
	 * 全ての送信データを取得
	 *
	 * @return array
	 */
	public function gets() {
		if ( $this->data === null ) {
			$this->data = array();
		}
		return $this->data;
	}

	/**
	 * データを追加
	 *
	 * @param string $key データのキー
	 * @param string $value 値
	 */
	public function set( $key, $value ){
		$this->data[$key] = $value;
		$this->Session->set( $key, $value );
	}

	/**
	 * 複数のデータを一括で追加
	 *
	 * @param array 値
	 */
	public function sets( array $array ) {
		foreach ( $array as $key => $value ) {
			$this->data[$key] = $value;
			$this->Session->set( $key, $value );
		}
	}

	/**
	 * データを消す
	 *
	 * @param string $key データのキー
	 */
	public function clear_value( $key ) {
		unset( $this->data[$key] );
		$this->Session->clear_value( $key );
	}

	/**
	 * 全てのデータを消す
	 *
	 * @param string $key データのキー
	 */
	public function clear_values() {
		$this->data = array();
		$this->Session->clear_values();
	}

	/**
	 * 指定した $key をキーと配列にデータを追加
	 *
	 * @param string $key データのキー
	 * @param string $value 値
	 */
	public function push( $key, $value ) {
		$this->data[$key][] = $value;
		$this->Session->push( $key, $value );
	}

	/**
	 * 整形済み（メール送信可能な）データを取得。送信値、表示値を自動判別
	 *
	 * @param string $key データのキー
	 * @param array $children
	 * @return string|null
	 */
	public function get( $key, array $children = array() ) {
		$post_value = $this->get_post_value_by_key( $key );

		if ( is_null( $post_value ) ) {
			return;
		}

		if ( empty( $children ) && isset( $this->data['__children'][$key] ) && is_array( $this->data['__children'][$key] ) ) {
			$_children = $this->data['__children'][$key];
			foreach ( $_children as $_child ) {
				$_child = json_decode( $_child, true );
				foreach ( $_child as $_child_key => $_child_value ) {
					$children[$_child_key] = $_child_value;
				}
			}
		}

		if ( is_array( $post_value ) ) {
			if ( !array_key_exists( 'data', $post_value ) ) {
				return;
			}
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
	 * 送信データを取得
	 *
	 * @param string $key データのキー
	 * @return string|null
	 */
	public function get_raw( $key ) {
		$post_value = $this->get_post_value_by_key( $key );

		if ( is_null( $post_value ) ) {
			return;
		}
		if ( is_array( $post_value ) && !array_key_exists( 'data', $post_value ) ) {
			return;
		}

		$children = array();
		if ( isset( $this->data['__children'][$key] ) && is_array( $this->data['__children'][$key] ) ) {
			$_children = $this->data['__children'][$key];
			if ( is_array( $_children ) ) {
				foreach ( $_children as $_child ) {
					$_child = json_decode( $_child, true );
					foreach ( $_child as $_child_key => $_child_value ) {
						$children[$_child_key] = $_child_value;
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
	 * @param string $key name 属性値
	 * @return mixed
	 */
	public function get_post_value_by_key( $key ) {
		if ( isset( $this->data[$key] ) ) {
			return $this->data[$key];
		}
	}

	/**
	 * $children の中に値が含まれているときだけ返す
	 * 本当は protected 後方互換
	 *
	 * @param string $key name属性
	 * @param array $children
	 * @return string
	 */
	public function get_in_children( $key, array $children ) {
		$value = $this->get_post_value_by_key( $key );
		if ( !is_null( $value ) && !is_array( $value ) ) {
			if ( isset( $children[$value] ) ) {
				return $children[$value];
			} else {
				return '';
			}
		}
	}

	/**
	 * $children の中に値が含まれているときだけ返す
	 * 本当は protected 後方互換
	 *
	 * @param string $key name属性
	 * @param array $children
	 * @return string
	 */
	public function get_raw_in_children( $key, array $children ) {
		$value = $this->get_post_value_by_key( $key );
		if ( !is_null( $value ) && !is_array( $value ) ) {
			if ( isset( $children[$value] ) ) {
				return $value;
			} else {
				return '';
			}
		}
	}

	/**
	 * 送られてきたseparatorを返す
	 *
	 * @param string $key name属性
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
	 * 本当は protected 後方互換
	 *
	 * @param string $key name属性
	 * @param array $children 選択肢
	 * @return string|null
	 */
	public function get_separated_value( $key, array $children ) {
		$separator = $this->get_separator_value( $key );
		$value     = $this->get_post_value_by_key( $key );

		if ( !is_array( $value ) ) {
			return;
		}
		if ( !isset( $value['data'] ) ) {
			return;
		}
		if ( !$separator ) {
			return;
		}

		// 入力 -> 確認のときは配列、確認 -> 入力のときは文字列
		if ( !is_array( $value['data'] ) ) {
			$value['data'] = explode( $separator, $value['data'] );
		}
		if ( $children ) {
			$rightData = array();
			foreach ( $value['data'] as $child ) {
				if ( isset( $children[$child] ) && !in_array( $children[$child], $rightData ) ) {
					$rightData[] = $children[$child];
				}
			}
			return implode( $separator, $rightData );
		}
	}

	/**
	 * 配列データを整形して送信値を返す。separator が送信されていない場合は null
	 * 本当は protected 後方互換
	 *
	 * @param string $key name属性
	 * @param array $children 選択肢
	 * @return string|null
	 */
	public function get_separated_raw_value( $key, array $children ) {
		$separator = $this->get_separator_value( $key );
		$value     = $this->get_post_value_by_key( $key );

		if ( !is_array( $value ) ) {
			return;
		}
		if ( !isset( $value['data'] ) ) {
			return;
		}
		if ( !$separator ) {
			return;
		}

		// 入力 -> 確認のときは配列、確認 -> 入力のときは文字列
		if ( !is_array( $value['data'] ) ) {
			$value['data'] = explode( $separator, $value['data'] );
		}
		if ( $children ) {
			$rightData = array();
			foreach ( $value['data'] as $child ) {
				if ( isset( $children[$child] ) && !in_array( $child, $rightData ) ) {
					$rightData[] = $child;
				}
			}
			return implode( $separator, $rightData );
		}
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

		if ( !is_array( $value ) ) {
			return;
		}
		if ( !isset( $value['data'] ) ) {
			return;
		}
		if ( !$separator ) {
			return;
		}

		if ( !is_array( $value['data'] ) ) {
			$value['data'] = explode( $separator, $value['data'] );
		}

		foreach ( $value['data'] as $child ) {
			if ( $child !== '' && $child !== null ) {
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
		if ( !is_array( $upload_file_keys ) ) {
			$upload_file_keys = array();
		}

		$upload_file_keys = apply_filters(
			'mwform_upload_file_keys_' . $this->form_key,
			$upload_file_keys,
			clone $this
		);
		if ( !is_array( $upload_file_keys ) ) {
			$upload_file_keys = array();
		}
		$upload_file_keys = array_values( array_unique( $upload_file_keys ) );

		$wp_upload_dir = wp_upload_dir();
		foreach ( $upload_file_keys as $key => $upload_file_key ) {
			$upload_file_url = $this->get_post_value_by_key( $upload_file_key );
			if ( $upload_file_url ) {
				$filepath = MWF_Functions::fileurl_to_path( $upload_file_url );
				if ( !file_exists( $filepath ) ) {
					unset( $upload_file_keys[$key] );
				}
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
		if ( !is_array( $upload_file_keys ) ) {
			$upload_file_keys = array();
		}
		foreach ( $uploaded_files as $key => $upload_file ) {
			$this->set( $key, $upload_file );
			if ( is_array( $upload_file_keys ) && !in_array( $key, $upload_file_keys ) ) {
				$this->push( MWF_Config::UPLOAD_FILE_KEYS, $key );
			}
		}
	}
}
