<?php
/**
 * Name: MW WP Form File
 * Description: Tempディレクトリ、ファイルアップロードの処理を行うクラス
 * Version: 1.0.7
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : October 10, 2013
 * Modified: September 1, 2014
 * License: GPLv2
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
	 * upload_mimes
	 * @param array $t MIMEタイプの配列
	 */
	public function upload_mimes( $t ) {
		$t['psd'] = 'image/vnd.adobe.photoshop';
		$t['eps'] = 'application/octet-stream';
		$t['ai'] = 'application/pdf';
		return $t;
	}

	/**
	 * checkFileType
	 * @param string $filepath アップロードされたファイルのパス
	 * @param string $filename ファイル名（未アップロード時の$_FILEの検査の場合、temp_nameは乱数になっているため）
	 * @return bool
	 */
	protected function checkFileType( $filepath, $filename = '' ) {
		// WordPress( get_allowed_mime_types ) で許可されたファイルタイプ限定
		if ( $filename ) {
			$wp_check_filetype = wp_check_filetype( $filename );
		} else {
			$wp_check_filetype = wp_check_filetype( $filepath );
		}
		if ( empty( $wp_check_filetype['type'] ) )
			return false;

		// 1つの拡張子に対し複数のMIMEタイプを持つファイルの対応
		switch ( $wp_check_filetype['ext'] ) {
			case 'avi' :
				$wp_check_filetype['type'] = array(
					'application/x-troff-msvideo',
					'video/avi',
					'video/msvideo',
					'video/x-msvideo',
				);
				break;
			case 'mp3' :
				$wp_check_filetype['type'] = array(
					'audio/mpeg3',
					'audio/x-mpeg3',
					'video/mpeg',
					'video/x-mpeg',
					'audio/mpeg',
				);
				break;
			case 'mpg' :
				$wp_check_filetype['type'] = array(
					'audio/mpeg',
					'video/mpeg',
				);
				break;
			case 'docx' :
			case 'xlsx' :
			case 'pptx' :
				$wp_check_filetype['type'] = array(
					$wp_check_filetype['type'],
					'application/zip',
				);
				break;
		}

		if ( version_compare( phpversion(), '5.3.0' ) >= 0 ) {
			$finfo = new finfo( FILEINFO_MIME_TYPE );
			$type = $finfo->file( $filepath );
			if ( is_array( $wp_check_filetype['type'] ) ) {
				if ( !( $finfo !== false && in_array( $type, $wp_check_filetype['type'] ) ) )
					return false;
			} else {
				if ( !( $finfo !== false && $type === $wp_check_filetype['type'] ) )
					return false;
			}
		}
		return true;
	}

	/**
	 * fileUpload
	 * ファイルアップロード処理。
	 * @return array ( name属性値 => アップロードできたファイルのURL, … )
	 */
	public function fileUpload() {
		$this->createTempDir();
		$this->cleanTempDir();

		$uploadedFiles = array();
		foreach ( $_FILES as $key => $file ) {
			$upload_file_url = $this->_fileUpload( $file );
			if ( $upload_file_url )
				$uploadedFiles[$key] = $upload_file_url;
		}
		return $uploadedFiles;
	}

	/**
	 * singleFileUpload
	 * ファイルアップロード処理。
	 * @param string $key アップロードしたいファイルの name 属性
	 * @return string アップロードできたファイルのURL
	 */
	public function singleFileUpload( $key ) {
		$this->createTempDir();
		$this->cleanTempDir();

		$file = '';
		if ( is_array( $_FILES ) && isset( $_FILES[$key] ) ) {
			$file = $_FILES[$key];
			return $this->_fileUpload( $file );
		}
	}

	/**
	 * _fileUpload
	 * ファイルアップロードの実処理。
	 * @param arary $file $_FILES['hoge'] の配列
	 * @return string アップロードしたファイルの URL
	 */
	protected function _fileUpload( $file ) {
		if ( empty( $file['tmp_name'] ) )
			return;

		$is_uploaded = false;
		if ( $this->checkFileType( $file['tmp_name'], $file['name'] )
			 && $file['error'] == UPLOAD_ERR_OK
			 && is_uploaded_file( $file['tmp_name'] ) ) {

			$extension = pathinfo( $file['name'], PATHINFO_EXTENSION );
			$uploadfile = $this->setUploadFileName( $extension );

			$is_uploaded = move_uploaded_file( $file['tmp_name'], $uploadfile['file'] );
			if ( $is_uploaded ) {
				return $uploadfile['url'];
			}
		}
	}

	/**
	 * saveAttachmentsInMedia
	 * 添付ファイルをメディアに保存、投稿データに添付ファイルのキー（配列）を保存
	 * $this->options_by_formkey が確定した後でのみ利用可能
	 * @param int post_id
	 * @param array ( ファイルのname属性値 => ファイルパス, … )
	 * @param int 生成フォーム（usedb）の post_id
	 */
	public function saveAttachmentsInMedia( $post_id, $attachments, $form_key_post_id ) {
		require_once( ABSPATH . 'wp-admin' . '/includes/media.php' );
		require_once( ABSPATH . 'wp-admin' . '/includes/image.php' );
		$save_attached_key = array();
		foreach ( $attachments as $key => $filepath ) {
			if ( !$this->checkFileType( $filepath ) )
				continue;

			$wp_check_filetype = wp_check_filetype( $filepath );
			$post_type = get_post_type_object( MWF_Config::DBDATA . $form_key_post_id );
			$attachment = array(
				'post_mime_type' => $wp_check_filetype['type'],
				'post_title'     => $key,
				'post_status'    => 'inherit',
				'post_content'   => __( 'Uploaded from ' ) . $post_type->label,
			);
			$attach_id = wp_insert_attachment( $attachment, $filepath, $post_id );
			$attach_data = wp_generate_attachment_metadata( $attach_id, $filepath );
			$update_attachment_flg = wp_update_attachment_metadata( $attach_id, $attach_data );
			if ( $attach_id ) {
				// 代わりにここで attachment_id を保存
				update_post_meta( $post_id, $key, $attach_id );
				// $key が 添付ファイルのキーであるとわかるように隠し設定を保存
				$save_attached_key[] = $key;
			}
		}
		if ( $save_attached_key )
			update_post_meta( $post_id, '_' . MWF_Config::UPLOAD_FILE_KEYS, $save_attached_key );
	}

	/**
	 * setUploadFileName
	 * 一時ファイル名を生成。Tempディレクトリの生成に失敗していた場合はUploadディレクトリを使用
	 * @param string 拡張子 ( ex: jpg )
	 * @return array ( file =>, url => )
	 */
	protected function setUploadFileName( $extension ) {
		$count      = 0;
		$basename   = date( 'Ymdhis' );
		$filename   = $basename . '.' . $extension;
		$temp_dir = $this->getTempDir();
		$upload_dir = $temp_dir['dir'];
		$upload_url = $temp_dir['url'];
		if ( !is_writable( $temp_dir['dir'] ) ) {
			$wp_upload_dir = wp_upload_dir();
			$upload_dir = realpath( $wp_upload_dir['path'] );
			$upload_url = $wp_upload_dir['url'];
		}
		$uploadfile['file'] = trailingslashit( $upload_dir ) . $filename;
		$uploadfile['url']  = trailingslashit( $upload_url ) . $filename;
		while ( file_exists( $uploadfile['file'] ) ) {
			$count ++;
			$filename = $basename . '-' . $count . '.' . $extension;
			$uploadfile['file'] = trailingslashit( $upload_dir ) . $filename;
			$uploadfile['url']  = trailingslashit( $upload_url ) . $filename;
		}
		return $uploadfile;
	}

	/**
	 * getTempDir
	 * Tempディレクトリ名（パス、URL）を返す。ディレクトリの存在可否は関係なし
	 * @return array ( dir => Tempディレクトリのパス, url => Tempディレクトリのurl )
	 */
	protected function getTempDir() {
		$wp_upload_dir = wp_upload_dir();
		$temp_dir_name = '/' . MWF_Config::NAME . '_uploads';
		$temp_dir['dir'] = realpath( $wp_upload_dir['basedir'] ) . $temp_dir_name;
		$temp_dir['url'] = $wp_upload_dir['baseurl'] . $temp_dir_name;
		return $temp_dir;
	}

	/**
	 * createTempDir
	 * Tempディレクトリを作成
	 * @return bool
	 */
	protected function createTempDir() {
		$_ret = false;
		$temp_dir = $this->getTempDir();
		$temp_dir = $temp_dir['dir'];
		if ( !file_exists( $temp_dir ) && !is_writable( $temp_dir ) ) {
			$_ret = wp_mkdir_p( trailingslashit( $temp_dir ) );
			@chmod( $temp_dir, 0733 );
			return $_ret;
		}
		return $_ret;
	}

	/**
	 * removeTempDir
	 * Tempディレクトリを削除
	 * @param string $sub_dir サブディレクトリ名
	 */
	public function removeTempDir( $sub_dir = '' ) {
		$temp_dir = $this->getTempDir();
		$temp_dir = $temp_dir['dir'];
		if ( $sub_dir )
			$temp_dir = trailingslashit( $temp_dir ) . $sub_dir;

		if ( !file_exists( $temp_dir ) )
			return;
		$handle = opendir( $temp_dir );
		if ( $handle === false )
			return;

		while ( false !== ( $file = readdir( $handle ) ) ) {
			if ( $file !== '.' && $file !== '..' ) {
				if ( is_dir( trailingslashit( $temp_dir ) . $file ) ) {
					$this->removeTempDir( $file );
				} else {
					unlink( trailingslashit( $temp_dir ) . $file );
				}
			}
		}
		closedir( $handle );
		rmdir( $temp_dir );
	}

	/**
	 * cleanTempDir
	 * Tempディレクトリ内のファイルを削除
	 */
	protected function cleanTempDir() {
		$temp_dir = $this->getTempDir();
		$temp_dir = $temp_dir['dir'];
		if ( !file_exists( $temp_dir ) )
			return;
		$handle = opendir( $temp_dir );
		if ( $handle === false )
			return;
		while ( false !== ( $filename = readdir( $handle ) ) ) {
			if ( $filename !== '.' && $filename !== '..' && !is_dir( trailingslashit( $temp_dir ) . $filename ) ) {
				$stat = stat( trailingslashit( $temp_dir ) . $filename );
				if ( $stat['mtime'] + 3600 < time() )
					unlink( trailingslashit( $temp_dir ) . $filename );
			}
		}
		closedir( $handle );
	}

	/**
	 * moveTempFileToUploadDir
	 * Tempディレクトリからuploadディレクトリにファイルを移動。
	 * @param string ファイルパス
	 * @return bool
	 */
	public function moveTempFileToUploadDir( $filepath ) {
		$tempdir = dirname( $filepath );
		$filename = basename( $filepath );
		$wp_upload_dir = wp_upload_dir();
		$uploaddir = realpath( $wp_upload_dir['path'] );
		$new_filename = wp_unique_filename( $uploaddir, $filename );

		if ( $tempdir == $uploaddir ) {
			return $filepath;
		}
		if ( rename( $filepath, trailingslashit( $uploaddir ) . $new_filename ) ) {
			return trailingslashit( $uploaddir ) . $new_filename;
		}
		return $filepath;
	}
}