<?php
/**
 * Name       : MW WP Form File
 * Version    : 2.0.0
 * Author     : Takashi Kitajima
 * Author URI : https://2inc.org
 * Created    : October 10, 2013
 * Modified   : June 1, 2017
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_File {

	public function __construct() {
		add_filter( 'upload_mimes', array( $this, '_upload_mimes' ) );
	}

	/**
	 * Add mimes
	 *
	 * @param array $t array of MIME types
	 */
	public function _upload_mimes( $t ) {
		$t['psd'] = 'image/vnd.adobe.photoshop';
		$t['eps'] = 'application/octet-stream';
		$t['ai']  = 'application/pdf';
		return $t;
	}

	/**
	 * Upload all files
	 *
	 * @param array $files array of upload files
	 * @return array (name => uploaded file url)
	 */
	public function upload( array $files = array() ) {
		$this->_clean_temp_dir();

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
	 * 指定したファイルをアップロード
	 *
	 * @param string $name
	 * @return string Uploaded file URL
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
	 * Upload process
	 *
	 * @param arary $file $_FILES['name']
	 * @return string Uploaded file URL
	 */
	protected function _file_upload( $file ) {
		if ( empty( $file['tmp_name'] ) ) {
			return false;
		}

		if ( ! MWF_Functions::check_file_type( $file['tmp_name'], $file['name'] )
				 || ! $file['error'] == UPLOAD_ERR_OK
				 || ! is_uploaded_file( $file['tmp_name'] ) ) {

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
	 * Generate temp file name
	 * If doesn't generate temp directory, using upload directory
	 *
	 * @param string ext
	 * @return array (file =>, url =>)
	 */
	protected function _set_upload_file_name( $extension ) {
		$count      = 0;
		$basename   = uniqid( rand() );
		$temp_dir   = $this->get_temp_dir();
		$upload_dir = $temp_dir['dir'];
		$upload_url = $temp_dir['url'];

		if ( ! is_writable( $temp_dir['dir'] ) ) {
			$wp_upload_dir = wp_upload_dir();
			$upload_dir    = $wp_upload_dir['path'];
			$upload_url    = $wp_upload_dir['url'];
		}

		$filename = wp_unique_filename( trailingslashit( $upload_dir ), $basename . '.' . $extension );
		$uploadfile['file'] = trailingslashit( $upload_dir ) . $filename;
		$uploadfile['url']  = trailingslashit( $upload_url ) . $filename;

		return $uploadfile;
	}

	/**
	 * Return array of temp directory. Return directory even if it does not exist
	 *
	 * @return array (dir => temp directory path, url => temp directory url)
	 */
	public function get_temp_dir() {
		$wp_upload_dir = wp_upload_dir();
		$temp_dir_name = '/' . MWF_Config::NAME . '_uploads';
		$temp_dir['dir'] = $wp_upload_dir['basedir'] . $temp_dir_name;
		$temp_dir['url'] = $wp_upload_dir['baseurl'] . $temp_dir_name;
		return $temp_dir;
	}

	/**
	 * Create temp directory
	 *
	 * @return bool
	 */
	public function create_temp_dir() {
		$temp_dir = $this->get_temp_dir();
		$temp_dir = $temp_dir['dir'];

		if ( file_exists( $temp_dir ) ) {
			return is_writable( $temp_dir );
		}

		$is_created = wp_mkdir_p( trailingslashit( $temp_dir ) );
		$is_created = chmod( $temp_dir, 0733 );

		return $is_created;
	}

	/**
	 * Delete temp directory
	 *
	 * @param string $sub_dir
	 */
	public function remove_temp_dir( $sub_dir = '' ) {
		$temp_dir = $this->get_temp_dir();
		$temp_dir = $temp_dir['dir'];
		if ( $sub_dir ) {
			$temp_dir = trailingslashit( $temp_dir ) . $sub_dir;
		}

		if ( ! file_exists( $temp_dir ) ) {
			return;
		}

		$handle = opendir( $temp_dir );
		if ( false === $handle ) {
			return;
		}

		while ( false !== ( $file = readdir( $handle ) ) ) {
			if ( '.' === $file || '..' === $file ) {
				continue;
			}

			if ( is_dir( trailingslashit( $temp_dir ) . $file ) ) {
				$this->remove_temp_dir( $file );
			} else {
				unlink( trailingslashit( $temp_dir ) . $file );
			}
		}

		closedir( $handle );
		rmdir( $temp_dir );
	}

	/**
	 * Delete files in temp directory
	 *
	 * @return void
	 */
	protected function _clean_temp_dir() {
		$temp_dir = $this->get_temp_dir();
		$temp_dir = $temp_dir['dir'];

		if ( ! file_exists( $temp_dir ) ) {
			return;
		}

		$handle = opendir( $temp_dir );
		if ( false === $handle ) {
			return;
		}

		while ( false !== ( $filename = readdir( $handle ) ) ) {
			if ( '.' === $filename && '..' === $filename || is_dir( trailingslashit( $temp_dir ) . $filename ) ) {
				continue;
			}

			$stat = stat( trailingslashit( $temp_dir ) . $filename );
			if ( $stat['mtime'] + 3600 < time() ) {
				unlink( trailingslashit( $temp_dir ) . $filename );
			}
		}

		closedir( $handle );
	}
}
