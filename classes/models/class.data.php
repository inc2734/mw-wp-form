<?php
/**
 * @package mw-wp-form
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * MW_WP_Form_Data
 */
class MW_WP_Form_Data {

	/**
	 * @var array Array of MW_WP_Form_Data
	 */
	protected static $Instances;

	/**
	 * @var MW_WP_Form_Sesion
	 */
	protected $Session;

	/**
	 * @var MW_WP_Form_Sesion
	 */
	protected $Session_meta;

	/**
	 * @var MW_WP_Form_Sesion
	 */
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
	protected $variables = array();

	/**
	 * @var array
	 */
	protected $meta = array();

	/**
	 * @var array
	 */
	protected $validation_errors = array();

	/**
	 * Constructor.
	 *
	 * @param string $form_key Form key.
	 * @param array  $POST     $_POST.
	 * @param array  $FILES    $_FILES.
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

		if ( isset( $POST[ MWF_Config::CUSTOM_MAIL_TAG_KEYS ] ) ) {
			foreach ( $POST[ MWF_Config::CUSTOM_MAIL_TAG_KEYS ] as $custom_mail_tag_key ) {
				$value = MW_WP_Form_Parser::apply_filters_mwform_custom_mail_tag( $form_key, '', $custom_mail_tag_key );
				if ( '' !== $value ) {
					$this->set( $custom_mail_tag_key, $value );
				}
			}
		}

		add_action( 'shutdown', array( $this, '_save_to_session' ) );
	}

	/**
	 * Save posted data to session
	 */
	public function _save_to_session() {
		$this->Session->clear_values();
		$this->Session->save( $this->variables );

		$this->Session_meta->clear_values();
		$this->Session_meta->save( $this->meta );

		$this->Session_validation_error->clear_values();
		$this->Session_validation_error->save( $this->validation_errors );
	}

	/**
	 * Instantiation MW_WP_Form_Data.
	 *
	 * @param string $form_key Form key.
	 * @param array  $POST     $_POST.
	 * @param array  $FILES    $_FILES.
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

	/**
	 * Get instance.
	 *
	 * @param string $form_key Form key.
	 * @param array  $POST     $_POST.
	 * @param array  $FILES    $_FILES.
	 */
	public static function getInstance( $form_key = null, $POST = null, $FILES = null ) {
		MWF_Functions::deprecated_message(
			'MW_WP_Form_Data::getInstance()',
			'MW_WP_Form_Data::connect()'
		);

		if ( is_null( $form_key ) ) {
			if ( 1 === count( self::$Instances ) ) {
				$form_key = key( array_slice( self::$Instances, 0, 1 ) );
			}
		}

		return self::connect( $form_key, $POST, $FILES );
	}

	/**
	 * Return form key.
	 *
	 * @return string
	 */
	public function get_form_key() {
		if ( isset( $this->meta['form_key'] ) ) {
			return $this->meta['form_key'];
		}
	}

	/**
	 * Set form key.
	 *
	 * @param string $form_key Form key.
	 */
	protected function _set_form_key( $form_key ) {
		$this->meta['form_key'] = $form_key;
	}

	/**
	 * Set $_POST variables.
	 */
	protected function _set_request_valiables() {
		if ( ! empty( $this->POST ) ) {
			$this->sets( stripslashes_deep( $this->POST ) );
		}
	}

	/**
	 * Set $_FILES variables.
	 */
	protected function _set_files_valiables() {
		$files = array();
		foreach ( $this->FILES as $name => $file ) {
			if ( ! isset( $this->POST[ $name ] ) || ! empty( $file['name'] ) ) {
				if ( UPLOAD_ERR_OK === $file['error'] && is_uploaded_file( $file['tmp_name'] ) ) {
					$this->set( $name, $file['name'] );
				} else {
					$this->set( $name, '' );
				}

				if ( ! empty( $file['name'] ) ) {
					$files[ $name ] = $file;
				}
			}
		}

		// この条件判定がないと fileSize チェックが正しく動作しない
		if ( $files ) {
			$this->set( MWF_Config::UPLOAD_FILES, $files );
		}
	}

	/**
	 * 送信データからどのページを表示すべきかの状態を判定して返す.
	 * Return post condition based on posted data.
	 * But this post condition is not the page to actually display (e.g. validation error).
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
	 * Get values.
	 *
	 * @return array
	 */
	public function gets() {
		return $this->variables;
	}

	/**
	 * Set the value.
	 *
	 * @param string $name  Field name.
	 * @param string $value Posted value.
	 * @return void
	 */
	public function set( $name, $value ) {
		$this->variables[ $name ] = $value;
	}

	/**
	 * Set values.
	 *
	 * @param array $array Posted data.
	 */
	public function sets( array $array ) {
		foreach ( $array as $name => $value ) {
			$this->set( $name, $value );
		}
	}

	/**
	 * Clear the value.
	 *
	 * @param string $name Field name.
	 */
	public function clear_value( $name ) {
		if ( isset( $this->variables[ $name ] ) ) {
			unset( $this->variables[ $name ] );
		}
	}

	/**
	 * Clear all values.
	 */
	public function clear_values() {
		$this->variables         = array();
		$this->meta              = array();
		$this->validation_errors = array();
	}

	/**
	 * Push the value.
	 *
	 * @param string $name  Field name.
	 * @param string $value Posted value.
	 * @return void
	 */
	public function push( $name, $value ) {
		if ( ! isset( $this->variables[ $name ] ) ) {
			$this->variables[ $name ] = array( $value );
		} else {
			if ( is_array( $this->variables[ $name ] ) ) {
				$this->variables[ $name ][] = $value;
			} else {
				$this->variables[ $name ]   = array( $this->variables[ $name ] );
				$this->variables[ $name ][] = $value;
			}
		}
	}

	/**
	 * Return Formatted (transmittable) data. Auto discrimination name value or label.
	 *
	 * @param string $name     Field name.
	 * @param array  $children Children.
	 * @return string|null
	 */
	public function get( $name, array $children = array() ) {
		$post_value = $this->get_post_value_by_key( $name );

		if ( is_null( $post_value ) ) {
			return;
		}

		if ( is_array( $post_value ) && ! array_key_exists( 'data', $post_value ) ) {
			return;
		}

		$__children = $this->get_post_value_by_key( '__children' );

		if ( empty( $children ) && isset( $__children[ $name ] ) ) {
			if ( is_array( $__children[ $name ] ) ) {
				$_children = $__children[ $name ];
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
				return $this->get_separated_value( $name, $children );
			}
			return $this->get_separated_value_not_children_set( $name );
		} else {
			if ( $children ) {
				return $this->get_in_children( $name, $children );
			}
			return $this->get_raw( $name );
		}
	}

	/**
	 * Get the raw value.
	 *
	 * @param string $name Field name.
	 * @return string|null
	 */
	public function get_raw( $name ) {
		$post_value = $this->get_post_value_by_key( $name );

		if ( is_null( $post_value ) ) {
			return;
		}

		if ( is_array( $post_value ) && ! array_key_exists( 'data', $post_value ) ) {
			return;
		}

		$__children = $this->get_post_value_by_key( '__children' );

		$children = array();
		if ( isset( $__children[ $name ] ) && is_array( $__children[ $name ] ) ) {
			$_children = $__children[ $name ];
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
				return $this->get_separated_raw_value( $name, $children );
			}
			return $this->get_separated_value_not_children_set( $name );
		} else {
			if ( $children ) {
				return $this->get_raw_in_children( $name, $children );
			}
			return $this->get_post_value_by_key( $name );
		}
	}

	/**
	 * Return posted data specify the name (In addition to value, separator and data etc are linked).
	 *
	 * @param string $name Field name.
	 * @return mixed
	 */
	public function get_post_value_by_key( $name ) {
		if ( isset( $this->variables[ $name ] ) ) {
			return $this->variables[ $name ];
		}
	}

	/**
	 * Return value when only in $children.
	 *
	 * @param string $name     Field name.
	 * @param array  $children Children.
	 * @return string
	 */
	public function get_in_children( $name, array $children ) {
		$value = $this->get_post_value_by_key( $name );
		if ( is_null( $value ) || is_array( $value ) ) {
			return;
		}

		if ( isset( $children[ $value ] ) ) {
			return $children[ $value ];
		}

		return '';
	}

	/**
	 * Return raw value when only in $children.
	 *
	 * @param string $name     Field name.
	 * @param array  $children Children.
	 * @return string
	 */
	public function get_raw_in_children( $name, array $children ) {
		$value = $this->get_post_value_by_key( $name );
		if ( is_null( $value ) || is_array( $value ) ) {
			return;
		}

		if ( isset( $children[ $value ] ) ) {
			return $value;
		}

		return '';
	}

	/**
	 * Return posted separator value.
	 *
	 * @param string $name Field name.
	 * @return string
	 */
	public function get_separator_value( $name ) {
		$value = $this->get_post_value_by_key( $name );
		if ( is_array( $value ) && isset( $value['separator'] ) ) {
			return $value['separator'];
		}
	}

	/**
	 * Return formatted label from array. If doesn't have separator, return null.
	 *
	 * @param string $name     Field name.
	 * @param array  $children Children.
	 * @return string|null
	 */
	public function get_separated_value( $name, array $children ) {
		$separator = $this->get_separator_value( $name );
		$value     = $this->get_post_value_by_key( $name );

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
			if ( isset( $children[ $child ] ) && ! in_array( $children[ $child ], $rightData, true ) ) {
				$rightData[] = $children[ $child ];
			}
		}

		return implode( $separator, $rightData );
	}

	/**
	 * Return formatted name value from array. If doesn't have separator, return null.
	 *
	 * @param string $name     Field name.
	 * @param array  $children Children.
	 * @return string|null
	 */
	public function get_separated_raw_value( $name, array $children ) {
		$separator = $this->get_separator_value( $name );
		$value     = $this->get_post_value_by_key( $name );

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
			if ( isset( $children[ $child ] ) && ! in_array( $child, $rightData, true ) ) {
				$rightData[] = $child;
			}
		}
		return implode( $separator, $rightData );
	}

	/**
	 * Return formatted label from array. If doesn't have separator, return null.
	 * If it is all empty don't implode, even if there is even one value return.
	 *
	 * @param string $name Field name.
	 * @return string|null
	 */
	protected function get_separated_value_not_children_set( $name ) {
		$separator = $this->get_separator_value( $name );
		$value     = $this->get_post_value_by_key( $name );

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
			if ( '' !== $child && ! is_null( $child ) ) {
				return implode( $separator, $value['data'] );
			}
		}

		return '';
	}

	/**
	 * Delete name of upload failed file or name of deleted file from UPLOAD_FILE_KEYS.
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

		foreach ( $upload_file_keys as $key => $upload_file_key ) {
			$upload_file_url = $this->get_post_value_by_key( $upload_file_key );
			$filepath        = MWF_Functions::fileurl_to_path( $upload_file_url );
			if ( ! $upload_file_url || ! file_exists( $filepath ) ) {
				unset( $upload_file_keys[ $key ] );
			}
		}

		$this->set( MWF_Config::UPLOAD_FILE_KEYS, $upload_file_keys );
	}

	/**
	 * Store uploaded files in UPLOAD_FILE_KEYS.
	 *
	 * @param array $uploaded_files Array of uploaded file url.
	 */
	public function push_uploaded_file_keys( array $uploaded_files = array() ) {
		$upload_file_keys = $this->get_post_value_by_key( MWF_Config::UPLOAD_FILE_KEYS );
		if ( ! is_array( $upload_file_keys ) ) {
			$upload_file_keys = array();
			$this->set( MWF_Config::UPLOAD_FILE_KEYS, $upload_file_keys );
		}

		foreach ( $uploaded_files as $key => $upload_file ) {
			$this->set( $key, $upload_file );
			if ( is_array( $upload_file_keys ) && ! in_array( $key, $upload_file_keys, true ) ) {
				$this->push( MWF_Config::UPLOAD_FILE_KEYS, $key );
			}
		}
	}

	/**
	 * Set view flg that is shows the screen to be displayed.
	 *
	 * @param string $view_flg null|input|confirm|complete.
	 */
	public function set_view_flg( $view_flg ) {
		$this->meta['view_flg'] = $view_flg;
	}

	/**
	 * Return view flg that is shows the screen to be displayed.
	 *
	 * @return string null|input|confirm|complete
	 */
	public function get_view_flg() {
		if ( isset( $this->meta['view_flg'] ) ) {
			return $this->meta['view_flg'];
		}
	}

	/**
	 * Set saved mail id.
	 *
	 * @param int $saved_mail_id Saved mail ID.
	 */
	public function set_saved_mail_id( $saved_mail_id ) {
		$this->meta['saved_mail_id'] = $saved_mail_id;
	}

	/**
	 * Return saved mail id.
	 *
	 * @return int|null
	 */
	public function get_saved_mail_id() {
		if ( isset( $this->meta['saved_mail_id'] ) ) {
			return $this->meta['saved_mail_id'];
		}
	}

	/**
	 * Set send error flg.
	 */
	public function set_send_error() {
		$this->meta[ MWF_Config::SEND_ERROR ] = true;
	}

	/**
	 * Return send error flg.
	 *
	 * @return boolean
	 */
	public function get_send_error() {
		if ( isset( $this->meta[ MWF_Config::SEND_ERROR ] ) ) {
			return $this->meta[ MWF_Config::SEND_ERROR ];
		}
	}

	/**
	 * Nonce check.
	 *
	 * @return bool
	 */
	protected function _is_valid_token() {
		$request_token = $this->get_post_value_by_key( MWF_Config::TOKEN_NAME );
		$form_key      = $this->get_form_key();
		return ( isset( $request_token ) && wp_verify_nonce( $request_token, $form_key ) );
	}

	/**
	 * Set the error message.
	 *
	 * @param string $name    Field name.
	 * @param string $rule    Validation name.
	 * @param string $message Validation error message.
	 */
	public function set_validation_error( $name, $rule, $message ) {
		if ( ! is_string( $message ) ) {
			exit( 'The Validate error message must be string!' );
		}
		$errors                           = $this->get_validation_error( $name );
		$errors[ $rule ]                  = $message;
		$this->validation_errors[ $name ] = $errors;
	}

	/**
	 * Return error messages the one form field.
	 *
	 * @param string $name Field name.
	 * @return array
	 */
	public function get_validation_error( $name ) {
		if ( isset( $this->validation_errors[ $name ] ) ) {
			$errors = $this->validation_errors[ $name ];
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
