<?php
/**
 * Name       : MW WP Form File
 * Description: Tempディレクトリ、ファイルアップロードの処理を行うクラス
 * Version    : 1.0.8
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : October 10, 2013
 * Modified   : March 18, 2016
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_File {

	/**
	 * __construct
	 */
	public function __construct() {
		add_filter( 'upload_mimes', array( $this, 'upload_mimes' ) );
	}

	/**
	 * 独自の mimes を追加
	 *
	 * @param array $t MIMEタイプの配列
	 */
	public function upload_mimes( $t ) {
		$t['psd'] = 'image/vnd.adobe.photoshop';
		$t['eps'] = 'application/octet-stream';
		$t['ai']  = 'application/pdf';
		return $t;
	}

	/**
	 * 全てのファイルをアップロード
	 *
	 * @param array $files アップロードするファイルの配列
	 * @return array ( name属性値 => アップロードできたファイルのURL, … )
	 */
	public function upload( array $files = array() ) {
		$uploaded_files = array();
		foreach ( $files as $key => $file ) {
			$uploaded_file = $this->single_file_upload( $key );
			if ( $uploaded_file ) {
				$uploaded_files[$key] = $uploaded_file;
			}
		}
		return $uploaded_files;
	}

	/**
	 * 指定したファイルをアップロード
	 *
	 * @param string $key アップロードしたいファイルの name 属性
	 * @return string アップロードできたファイルのURL
	 */
	protected function single_file_upload( $key ) {
		$this->create_temp_dir();
		$this->clean_temp_dir();

		$file = '';
		if ( is_array( $_FILES ) && isset( $_FILES[$key] ) ) {
			$file = $_FILES[$key];
			return $this->_file_upload( $file );
		}
	}

	/**
	 * ファイルアップロードの実処理
	 *
	 * @param arary $file $_FILES['hoge'] の配列
	 * @return string アップロードしたファイルの URL
	 */
	protected function _file_upload( $file ) {
		if ( empty( $file['tmp_name'] ) ) {
			return;
		}

		$is_uploaded = false;
		if ( MWF_Functions::check_file_type( $file['tmp_name'], $file['name'] )
			 && $file['error'] == UPLOAD_ERR_OK
			 && is_uploaded_file( $file['tmp_name'] ) ) {

			$extension = pathinfo( $file['name'], PATHINFO_EXTENSION );
			$uploadfile = $this->set_upload_file_name( $extension );

			$is_uploaded = move_uploaded_file( $file['tmp_name'], $uploadfile['file'] );
			if ( $is_uploaded ) {
				return $uploadfile['url'];
			}
		}
	}

	/**
	 * 一時ファイル名を生成。Tempディレクトリの生成に失敗していた場合はUploadディレクトリを使用
	 *
	 * @param string 拡張子 ( ex: jpg )
	 * @return array ( file =>, url => )
	 */
	protected function set_upload_file_name( $extension ) {
		$count      = 0;
		$basename   = uniqid( rand() );
		$temp_dir   = $this->get_temp_dir();
		$upload_dir = $temp_dir['dir'];
		$upload_url = $temp_dir['url'];
		if ( !is_writable( $temp_dir['dir'] ) ) {
			$wp_upload_dir = wp_upload_dir();
			$upload_dir    = $wp_upload_dir['path'];
			$upload_url    = $wp_upload_dir['url'];
		}

		$filename_no_exension = $basename;
		$filepath_no_exension = trailingslashit( $upload_dir ) . $filename_no_exension;
		while ( glob( $filepath_no_exension . '.*' ) ) {
			$count ++;
			$filename_no_exension = $basename . '-' . $count;
			$filepath_no_exension = trailingslashit( $upload_dir ) . $filename_no_exension;
		}
		$filename = $filename_no_exension . '.' . $extension;
		$uploadfile['file'] = trailingslashit( $upload_dir ) . $filename;
		$uploadfile['url']  = trailingslashit( $upload_url ) . $filename;
		return $uploadfile;
	}

	/**
	 * Temp ディレクトリ名（パス、URL）を返す。ディレクトリの存在可否は関係なし
	 *
	 * @return array ( dir => Tempディレクトリのパス, url => Tempディレクトリのurl )
	 */
	public function get_temp_dir() {
		$wp_upload_dir = wp_upload_dir();
		$temp_dir_name = '/' . MWF_Config::NAME . '_uploads';
		$temp_dir['dir'] = $wp_upload_dir['basedir'] . $temp_dir_name;
		$temp_dir['url'] = $wp_upload_dir['baseurl'] . $temp_dir_name;
		return $temp_dir;
	}

	/**
	 * Temp ディレクトリを作成
	 *
	 * @return bool
	 */
	public function create_temp_dir() {
		$_ret = false;
		$temp_dir = $this->get_temp_dir();
		$temp_dir = $temp_dir['dir'];
		if ( !file_exists( $temp_dir ) && !is_writable( $temp_dir ) ) {
			$_ret = wp_mkdir_p( trailingslashit( $temp_dir ) );
			@chmod( $temp_dir, 0733 );
			return $_ret;
		}
		return $_ret;
	}

	/**
	 * Temp ディレクトリを削除
	 *
	 * @param string $sub_dir サブディレクトリ名
	 */
	public function remove_temp_dir( $sub_dir = '' ) {
		$temp_dir = $this->get_temp_dir();
		$temp_dir = $temp_dir['dir'];
		if ( $sub_dir ) {
			$temp_dir = trailingslashit( $temp_dir ) . $sub_dir;
		}

		if ( !file_exists( $temp_dir ) ) {
			return;
		}
		$handle = opendir( $temp_dir );
		if ( $handle === false ) {
			return;
		}

		while ( false !== ( $file = readdir( $handle ) ) ) {
			if ( $file !== '.' && $file !== '..' ) {
				if ( is_dir( trailingslashit( $temp_dir ) . $file ) ) {
					$this->remove_temp_dir( $file );
				} else {
					unlink( trailingslashit( $temp_dir ) . $file );
				}
			}
		}
		closedir( $handle );
		rmdir( $temp_dir );
	}

	/**
	 * Tempディレクトリ内のファイルを削除
	 */
	protected function clean_temp_dir() {
		$temp_dir = $this->get_temp_dir();
		$temp_dir = $temp_dir['dir'];
		if ( !file_exists( $temp_dir ) ) {
			return;
		}
		$handle = opendir( $temp_dir );
		if ( $handle === false ) {
			return;
		}
		while ( false !== ( $filename = readdir( $handle ) ) ) {
			if ( $filename !== '.' && $filename !== '..' &&
				 !is_dir( trailingslashit( $temp_dir ) . $filename ) ) {
				$stat = stat( trailingslashit( $temp_dir ) . $filename );
				if ( $stat['mtime'] + 3600 < time() ) {
					unlink( trailingslashit( $temp_dir ) . $filename );
				}
			}
		}
		closedir( $handle );
	}

	/**
	 * 指定したパスのファイルを削除
	 *
	 * @param array $attachments 消去するファイルパスの配列
	 */
	public function delete_files( array $files ) {
		foreach ( $files as $file ) {
			if ( file_exists( $file ) ) {
				unlink( $file );
			}
		}
	}
}
