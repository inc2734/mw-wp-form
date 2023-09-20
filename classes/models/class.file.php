<?php
/**
 * @package mw-wp-form
 * @author websoudan
 * @license GPL-2.0+
 */

/**
 * MW_WP_Form_File
 */
class MW_WP_Form_File {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'upload_mimes', array( $this, '_upload_mimes' ) );
	}

	/**
	 * Add mimes.
	 *
	 * @param array $t Array of MIME types.
	 */
	public function _upload_mimes( $t ) {
		$t['psd'] = 'image/vnd.adobe.photoshop';
		$t['eps'] = 'application/octet-stream';
		$t['ai']  = 'application/pdf';
		return $t;
	}

	/**
	 * Upload all files.
	 *
	 * @param int $form_id The form ID.
	 * @param array $files Array of upload files.
	 * @return array
	 */
	public function upload( $form_id, array $files = array() ) {
		$uploaded_files = array();
		foreach ( $files as $name => $file ) {
			$uploaded_file = $this->_single_file_upload( $form_id, $name, $file );
			if ( ! $uploaded_file ) {
				continue;
			}
			$uploaded_files[ $name ] = $uploaded_file;
		}

		return $uploaded_files;
	}

	/**
	 * 指定したファイルをアップロード.
	 *
	 * @param int $form_id The form ID.
	 * @param string $name Field name.
	 * @param array A value of $_FIELS
	 * @return string
	 */
	protected function _single_file_upload( $form_id, $name, $file ) {
		if ( empty( $file['tmp_name'] ) ) {
			return false;
		}

		$error = $file['error'];

		try {
			if ( UPLOAD_ERR_OK !== $error && UPLOAD_ERR_NO_FILE !== $error ||
				! MWF_Functions::check_file_type( $file['tmp_name'], $file['name'] ) ) {
				if ( UPLOAD_ERR_INI_SIZE === $error || UPLOAD_ERR_FORM_SIZE === $error ) {
					throw new \RuntimeException( '[MW WP Form] File size of the uploaded file is too large.' );
				}
				throw new \RuntimeException( '[MW WP Form] An error occurred during file upload.' );
			}
		} catch ( \Exception $e ) {
			error_log( $e->getMessage() );
		}

		try {
			$new_user_file_dir = MW_WP_Form_Directory::generate_user_file_dirpath( $form_id, $name );
			if ( ! wp_mkdir_p( $new_user_file_dir ) ) {
				throw new \RuntimeException( '[MW WP Form] Creation of a temporary directory for file upload failed.' );
			}
		} catch ( \Exception $e ) {
			error_log( $e->getMessage() );
		}

		MW_WP_Form_Directory::do_empty( $new_user_file_dir, true );

		$filename = sanitize_file_name( sprintf( '%1$s-%2$s', $name, $file['name'] ) );
		$filepath = MW_WP_Form_Directory::generate_user_filepath( $form_id, $name, $filename );

		try {
			if ( ! move_uploaded_file( $file['tmp_name'], $filepath ) ) {
				throw new \RuntimeException( '[MW WP Form] There was an error saving the uploaded file.' );
			}
		} catch ( \Exception $e ) {
			error_log( $e->getMessage() );
		}

		return $filename;
	}
}
