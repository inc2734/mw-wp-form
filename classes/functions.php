<?php
/**
 * Name       : MWF Functions
 * Description: 関数
 * Version    : 1.3.0
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : May 29, 2013
 * Modified   : July 24, 2014
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MWF_Functions {

	/**
	 * is_numeric
	 * 引数で渡された変数が存在し、かつ数値であるなら true
	 * @param string $value 参照渡し
	 * @return bool
	 */
	public static function is_numeric( &$value ) {
		if ( isset( $value ) && preg_match( '/^\d+$/', $value ) ) {
			return true;
		}
		return false;
	}

	/**
	 * array_clean
	 * 配列の空要素を削除
	 * @param array $array
	 * @return array
	 */
	public static function array_clean( $array ) {
		return array_merge( array_diff( $array, array( '' ) ) );
	}

	/**
	 * is_empty
	 * 値が空（0は許可）
	 * @param mixed
	 * @return bool
	 */
	public static function is_empty( $value ) {
		if ( $value === array() || $value === '' || $value === null ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * fileurl_to_path
	 * ファイルURLをファイルパスに変換
	 * @param string $fileurl
	 * @return string
	 */
	public static function fileurl_to_path( $fileurl ) {
		$wp_upload_dir = wp_upload_dir();
		if ( preg_match( '/^https?:\/\//', $fileurl ) ) {
			$baseurl = preg_replace( '/^https?:\/\/(.+)$/', '$1', $wp_upload_dir['baseurl'] );
			$fileurl = preg_replace( '/^https?:\/\/(.+)$/', '$1', $fileurl );
			$filepath = str_replace(
				$baseurl,
				realpath( $wp_upload_dir['basedir'] ),
				$fileurl
			);
			return $filepath;
		}
	}

	/**
	 * filepath_to_url
	 * ファイルパスをURLに変換
	 * @param string $filepath
	 * @return string
	 */
	public static function filepath_to_url( $filepath ) {
		$wp_upload_dir = wp_upload_dir();
		$fileurl = str_replace(
			realpath( $wp_upload_dir['basedir'] ),
			$wp_upload_dir['baseurl'],
			$filepath
		);
		if ( is_ssl() ) {
			$fileurl = preg_replace( '/^https?:\/\//', 'https://', $fileurl );
		}
		return $fileurl;
	}

	/**
	 * convert_eol
	 * 改行コードを \n に統一
	 * @param sring $string
	 * @return string $string
	 */
	public static function convert_eol( $string ) {
		return preg_replace( "/\r\n|\r|\n/", "\n", $string );
	}

	/**
	 * deprecated_message
	 * 古いメソッドを使った場合にエラーを出力
	 * @param string $function_name メソッド名
	 * @param string $new_function 代替のメソッド名
	 */
	public static function deprecated_message( $function_name, $new_function = '' ) {
		global $mwform_deprecated_message;
		$mwform_deprecated_message .= '<div class="error ' . esc_attr( MWF_Config::NAME ) . '-deprecated-message">';
		$mwform_deprecated_message .= sprintf(
			'MW WP Form dosen\'t support "%s" already. This method will be removed in the next version. ',
			$function_name
		);
		if ( $new_function ) {
			$mwform_deprecated_message .= sprintf( 'You should use "%s". ', $new_function );
		}
		$debug_backtrace = debug_backtrace();
		array_shift( $debug_backtrace );
		foreach ( $debug_backtrace as $value ) {
			if ( isset( $value['file'], $value['line'] ) ) {
				$mwform_deprecated_message .= sprintf( '%s line %d', $value['file'], $value['line'] );
			}
			break;
		}
		$mwform_deprecated_message .= '</div>';
		if ( is_admin() ) {
			if ( current_filter() === 'admin_notices' ) {
				self::display_deprecated_message();
			} else {
				add_action( 'admin_notices', 'MWF_Functions::display_deprecated_message' );
			}
		} else {
			if ( current_filter() === 'the_content' ) {
				self::display_deprecated_message();
			} else {
				add_filter( 'the_content', 'MWF_Functions::return_deprecated_message' );
			}
		}
	}
	public static function display_deprecated_message() {
		global $mwform_deprecated_message;
		$content = $mwform_deprecated_message;
		unset( $mwform_deprecated_message );
		echo $content;
	}
	public static function return_deprecated_message( $content ) {
		global $mwform_deprecated_message;
		$content = $mwform_deprecated_message . $content;
		unset( $mwform_deprecated_message );
		return $content;
	}

	/**
	 * move_temp_file_to_upload_dir
	 * Tempディレクトリからuploadディレクトリにファイルを移動。
	 * @param string ファイルパス
	 * @return bool
	 */
	public static function move_temp_file_to_upload_dir( $filepath ) {
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

	/**
	 * save_attachments_in_media
	 * 添付ファイルをメディアに保存、投稿データに添付ファイルのキー（配列）を保存
	 * $this->settings が確定した後でのみ利用可能
	 * @param int post_id
	 * @param array ( ファイルのname属性値 => ファイルパス, … )
	 * @param int 生成フォーム（usedb）の post_id
	 */
	public static function save_attachments_in_media( $post_id, $attachments, $form_key_post_id ) {
		require_once( ABSPATH . 'wp-admin' . '/includes/media.php' );
		require_once( ABSPATH . 'wp-admin' . '/includes/image.php' );
		$save_attached_key = array();
		foreach ( $attachments as $key => $filepath ) {
			if ( !self::check_file_type( $filepath ) ) {
				continue;
			}

			$wp_check_filetype = wp_check_filetype( $filepath );
			$post_type = get_post_type_object( MWF_Config::DBDATA . $form_key_post_id );
			$attachment = array(
				'post_mime_type' => $wp_check_filetype['type'],
				'post_title'     => $key,
				'post_status'    => 'inherit',
				'post_content'   => __( 'Uploaded from ', MWF_Config::DOMAIN ) . $post_type->label,
			);
			$attach_id   = wp_insert_attachment( $attachment, $filepath, $post_id );
			$attach_data = wp_generate_attachment_metadata( $attach_id, $filepath );
			$update_attachment_flg = wp_update_attachment_metadata( $attach_id, $attach_data );
			if ( $attach_id ) {
				// 代わりにここで attachment_id を保存
				update_post_meta( $post_id, $key, $attach_id );
				// $key が 添付ファイルのキーであるとわかるように隠し設定を保存
				$save_attached_key[] = $key;
			}
		}
		if ( $save_attached_key ) {
			update_post_meta( $post_id, '_' . MWF_Config::UPLOAD_FILE_KEYS, $save_attached_key );
		}
	}

	/**
	 * check_file_type
	 * @param string $filepath アップロードされたファイルのパス
	 * @param string $filename ファイル名（未アップロード時の$_FILEの検査の場合、temp_nameは乱数になっているため）
	 * @return bool
	 */
	public static function check_file_type( $filepath, $filename = '' ) {
		// WordPress( get_allowed_mime_types ) で許可されたファイルタイプ限定
		if ( $filename ) {
			$wp_check_filetype = wp_check_filetype( $filename );
		} else {
			$wp_check_filetype = wp_check_filetype( $filepath );
		}
		if ( empty( $wp_check_filetype['type'] ) ) {
			return false;
		}

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
				if ( !( $finfo !== false && in_array( $type, $wp_check_filetype['type'] ) ) ) {
					return false;
				}
			} else {
				if ( !( $finfo !== false && $type === $wp_check_filetype['type'] ) ) {
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * get_tracking_number_title
	 * @param string $post_type　問い合わせデータの投稿タイプ名
	 * @return string
	 */
	public static function get_tracking_number_title( $post_type ) {
		$tracking_number_title = esc_html__( 'Tracking Number', MWF_Config::DOMAIN );
		$form_key = self::contact_data_post_type_to_form_key( $post_type );
		if ( $form_key ) {
			$tracking_number_title = apply_filters(
				'mwform_tracking_number_title_' . $form_key,
				$tracking_number_title
			);
		}
		return $tracking_number_title;
	}

	/**
	 * contact_data_post_type_to_form_key
	 * @param string $post_type 問い合わせデータの投稿タイプ名
	 * @return string|null フォーム識別子
	 */
	public static function contact_data_post_type_to_form_key( $post_type ) {
		if ( preg_match( '/^' . MWF_Config::DBDATA . '(\d+)$/', $post_type, $match ) ) {
			$form_key = MWF_Config::NAME . '-' . $match[1];
			return $form_key;
		}
	}
}