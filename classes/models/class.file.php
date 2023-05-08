<?php
/**
 * @package mw-wp-form
 * @author inc2734
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
	 * @param array $files Array of upload files.
	 * @return array
	 */
	public function upload( array $files = array() ) {
		$uploaded_files = array();
		foreach ( $files as $name => $file ) {
			$uploaded_file = $this->_single_file_upload( $name );
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
	 * @param string $name Field name.
	 * @return string
	 */
	protected function _single_file_upload( $name ) {
		$this->create_temp_dir();

		if ( ! is_array( $_FILES ) || ! isset( $_FILES[ $name ] ) ) {
			return;
		}

		$file = $_FILES[ $name ];
		return $this->_file_upload( $file );
	}

	/**
	 * Upload process.
	 *
	 * @param arary $file $_FILES['name'].
	 * @return string
	 */
	protected function _file_upload( $file ) {
		if ( empty( $file['tmp_name'] ) ) {
			return false;
		}

		if (
			! MWF_Functions::check_file_type( $file['tmp_name'], $file['name'] )
			|| ! UPLOAD_ERR_OK === $file['error']
			|| ! is_uploaded_file( $file['tmp_name'] )
		) {
			return false;
		}

		$extension   = pathinfo( $file['name'], PATHINFO_EXTENSION );
		$uploadfile  = $this->_set_upload_file_name( $extension );
		$is_uploaded = move_uploaded_file( $file['tmp_name'], $uploadfile['file'] );
		if ( ! $is_uploaded ) {
			return false;
		}

		return $uploadfile['url'];
	}

	/**
	 * Generate temp file name.
	 * If doesn't generate temp directory, using upload directory.
	 *
	 * @param string $extension ext.
	 * @return array
	 */
	protected function _set_upload_file_name( $extension ) {
		$basename   = uniqid( rand() );
		$temp_dir   = $this->get_temp_dir();
		$upload_dir = $temp_dir['dir'];
		$upload_url = $temp_dir['url'];

		if ( ! is_writable( $temp_dir['dir'] ) ) {
			$wp_upload_dir = wp_upload_dir();
			$upload_dir    = $wp_upload_dir['path'];
			$upload_url    = $wp_upload_dir['url'];
		}

		$filename           = wp_unique_filename( trailingslashit( $upload_dir ), $basename . '.' . $extension );
		$uploadfile         = array();
		$uploadfile['file'] = trailingslashit( $upload_dir ) . $filename;
		$uploadfile['url']  = trailingslashit( $upload_url ) . $filename;

		return $uploadfile;
	}

	/**
	 * Return array of temp directory. Return directory even if it does not exist.
	 *
	 * @return array
	 */
	public function get_temp_dir() {
		$wp_upload_dir   = wp_upload_dir();
		$temp_dir_name   = '/' . MWF_Config::NAME . '_uploads';
		$temp_dir        = array();
		$temp_dir['dir'] = $wp_upload_dir['basedir'] . $temp_dir_name;
		$temp_dir['url'] = $wp_upload_dir['baseurl'] . $temp_dir_name;
		return $temp_dir;
	}

	/**
	 * Create temp directory.
	 *
	 * @return bool
	 */
	public function create_temp_dir() {
		$temp_dir = $this->get_temp_dir();
		$temp_dir = $temp_dir['dir'];

		if ( file_exists( $temp_dir ) ) {
			$this->_create_htaccess( $temp_dir );
			return is_writable( $temp_dir );
		}

		$is_created = wp_mkdir_p( trailingslashit( $temp_dir ) );
		if ( $is_created ) {
			$this->_create_htaccess( $temp_dir );
		}
		$is_created = chmod( $temp_dir, 0733 );

		return $is_created;
	}

	public function do_empty_temp_dir( $force = false ) {
		$temp_dir = $this->get_temp_dir();
		$temp_dir = $temp_dir['dir'];
		return $this->_clean_temp_dir( $force );
	}

	public function remove_temp_dir() {
		$this->do_empty_temp_dir( true );
		$temp_dir = $this->get_temp_dir();
		rmdir( $temp_dir['dir'] );
	}

	/**
	 * Delete files in temp directory.
	 */
	protected function _clean_temp_dir( $force = false ) {
		$temp_dir = $this->get_temp_dir();
		$temp_dir = $temp_dir['dir'];

		if ( ! file_exists( $temp_dir ) ) {
			return;
		}

		$handle = opendir( $temp_dir );
		if ( false === $handle ) {
			return;
		}

		// phpcs:disable WordPress.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
		while ( false !== ( $filename = readdir( $handle ) ) ) {
		// phpcs:enable
			if ( '.' === $filename && '..' === $filename || is_dir( trailingslashit( $temp_dir ) . $filename ) ) {
				continue;
			}

			$stat = stat( trailingslashit( $temp_dir ) . $filename );
			if ( $force || $stat['mtime'] + 60 * 15 < time() ) {
				unlink( trailingslashit( $temp_dir ) . $filename );
			}
		}

		closedir( $handle );
	}

	/**
	 * Create .htaccess.
	 *
	 * @param string $save_dir The directory where .htaccess is created.
	 * @return boolean
	 * @throws \RuntimeException If the creation of .htaccess fails.
	 */
	protected function _create_htaccess( $save_dir ) {
		$htaccess = path_join( $save_dir, '.htaccess' );
		if ( file_exists( $htaccess ) ) {
			return true;
		}

		$handle = fopen( $htaccess, 'w' );
		if ( ! $handle ) {
			throw new \RuntimeException( '[MW WP Form] .htaccess can\'t create.' );
		}

		if ( false === fwrite( $handle, "Deny from all\n" ) ) {
			throw new \RuntimeException( '[MW WP Form] .htaccess can\'t write.' );
		}

		if ( ! fclose( $handle ) ) {
			throw new \RuntimeException( '[MW WP Form] .htaccess can\'t close.' );
		}

		return true;
	}
}
