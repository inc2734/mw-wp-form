<?php
/**
 * Name       : MWF Functions
 * Description: 関数
 * Version    : 1.5.3
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : May 29, 2013
 * Modified   : April 2, 2016
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MWF_Functions {

	/**
	 * 引数で渡された変数が存在し、かつ数値であるなら true
	 *
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
	 * 配列の空要素を削除
	 *
	 * @param array $array
	 * @return array
	 */
	public static function array_clean( $array ) {
		return array_merge( array_diff( $array, array( '' ) ) );
	}

	/**
	 * 値が空（0は許可）
	 *
	 * @param mixed
	 * @return bool
	 */
	public static function is_empty( $value ) {
		if ( $value === array() || $value === '' || $value === null || $value === false ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * ファイルURLをファイルパスに変換
	 *
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
				$wp_upload_dir['basedir'],
				$fileurl
			);
			return $filepath;
		}
	}

	/**
	 * ファイルパスをURLに変換
	 *
	 * @param string $filepath
	 * @return string
	 */
	public static function filepath_to_url( $filepath ) {
		$wp_upload_dir = wp_upload_dir();
		$fileurl = str_replace(
			$wp_upload_dir['basedir'],
			$wp_upload_dir['baseurl'],
			$filepath
		);
		if ( is_ssl() ) {
			$fileurl = preg_replace( '/^https?:\/\//', 'https://', $fileurl );
		}
		return $fileurl;
	}

	/**
	 * 改行コードを \n に統一
	 *
	 * @param sring $string
	 * @return string $string
	 */
	public static function convert_eol( $string ) {
		return preg_replace( "/\r\n|\r|\n/", "\n", $string );
	}

	/**
	 * 古いメソッドを使った場合にエラーを出力
	 *
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
	 * Tempディレクトリからuploadディレクトリにファイルを移動
	 *
	 * @param string Temp ファイルのパス
	 * @param string 新しい保存先ディレクトリ
	 * @param string 新しいファイル名
	 * @return string 新しいファイルパス
	 */
	public static function move_temp_file_to_upload_dir( $filepath, $upload_dir = '', $filename = '' ) {
		$wp_upload_dir = wp_upload_dir();

		if ( !$upload_dir ) {
			$upload_dir = $wp_upload_dir['path'];
		} else {
			$upload_dir = trailingslashit( $wp_upload_dir['basedir'] ) . ltrim( $upload_dir, '/\\' );
			$bool = wp_mkdir_p( $upload_dir );
		}

		if ( !$filename ) {
			$filename = basename( $filepath );
		}

		if ( !preg_match( '/(\..+?)$/', $filename ) ) {
			$extension = pathinfo( $filepath, PATHINFO_EXTENSION );
			$filename = $filename . '.' . $extension;
		}
		$filename = sanitize_file_name( $filename );
		$filename = wp_unique_filename( $upload_dir, $filename );

		$new_filepath = trailingslashit( $upload_dir ) . $filename;

		if ( $filepath == $new_filepath ) {
			return $filepath;
		}

		// もし temp ファイルが存在しない場合、一応リネーム後のパスだけ返す
		if ( !file_exists( $filepath ) ) {
			return $new_filepath;
		}

		// 移動できれば移動、移動できなくてもリネーム後のパスだけ返す
		if ( rename( $filepath, $new_filepath ) ) {
			return $new_filepath;
		}
		return $new_filepath;
	}

	/**
	 * 添付ファイルをメディアに保存、投稿データに添付ファイルのキー（配列）を保存
	 * $this->settings が確定した後でのみ利用可能
	 *
	 * @param int post_id
	 * @param array ( ファイルのname属性値 => ファイルパス, … )
	 * @param int 生成フォーム（usedb）の post_id
	 */
	public static function save_attachments_in_media( $post_id, $attachments, $form_id ) {
		require_once( ABSPATH . 'wp-admin' . '/includes/media.php' );
		require_once( ABSPATH . 'wp-admin' . '/includes/image.php' );
		$save_attached_key = array();
		foreach ( $attachments as $key => $filepath ) {
			if ( !self::check_file_type( $filepath ) ) {
				continue;
			}

			$wp_check_filetype = wp_check_filetype( $filepath );
			$post_type = get_post_type_object( self::get_contact_data_post_type_from_form_id( $form_id ) );
			if ( empty( $post_type->label ) ) {
				continue;
			}
			$attachment = array(
				'post_mime_type' => $wp_check_filetype['type'],
				'post_title'     => $key,
				'post_status'    => 'inherit',
				'post_content'   => __( 'Uploaded from ', 'mw-wp-form' ) . $post_type->label,
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
	 * ファイルタイプのチェック
	 *
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
				$wp_check_filetype['type'] = array(
					$wp_check_filetype['type'],
					'application/zip',
					'application/msword',
				);
				break;
			case 'xlsx' :
				$wp_check_filetype['type'] = array(
					$wp_check_filetype['type'],
					'application/zip',
					'application/excel',
					'application/msexcel',
					'application/vnd.ms-excel',
				);
				break;
			case 'pptx' :
				$wp_check_filetype['type'] = array(
					$wp_check_filetype['type'],
					'application/zip',
					'application/mspowerpoint',
					'application/powerpoint',
					'application/ppt',
				);
				break;
		}

		if ( version_compare( phpversion(), '5.3.0' ) >= 0 ) {
			if ( !file_exists( $filepath ) ) {
				return false;
			}
			$finfo = new finfo( FILEINFO_MIME_TYPE );
			$type = $finfo->file( $filepath );
			if ( $finfo === false ) {
				return false;
			}
			if ( is_array( $wp_check_filetype['type'] ) ) {
				if ( !in_array( $type, $wp_check_filetype['type'] ) ) {
					return false;
				}
			} else {
				if ( $type !== $wp_check_filetype['type'] ) {
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * 問い合わせ番号の表示名を取得
	 *
	 * @param string $post_type　問い合わせデータの投稿タイプ名
	 * @return string
	 */
	public static function get_tracking_number_title( $post_type ) {
		$tracking_number_title = esc_html__( 'Tracking Number', 'mw-wp-form' );
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
	 * 問い合わせデータの投稿タイプ名をフォーム識別子に変換
	 *
	 * @param string $post_type 問い合わせデータの投稿タイプ名
	 * @return string|null フォーム識別子
	 */
	public static function contact_data_post_type_to_form_key( $post_type ) {
		if ( self::is_contact_data_post_type( $post_type ) ) {
			if ( preg_match( '/(\d+)$/', $post_type, $match ) ) {
				$form_key = self::get_form_key_from_form_id( $match[1] );
				return $form_key;
			}
		}
	}

	/**
	 * フォームの投稿 ID をフォーム識別子に変換
	 *
	 * @param int $form_id
	 * @return string フォーム識別子
	 */
	public static function get_form_key_from_form_id( $form_id ) {
		$form_key = MWF_Config::NAME . '-' . $form_id;
		return $form_key;
	}

	/**
	 * フォームの投稿 ID を問い合わせデータの投稿タイプに変換
	 *
	 * @param int $form_id
	 * @return string フォーム識別子
	 */
	public static function get_contact_data_post_type_from_form_id( $form_id ) {
		$contact_data_post_type = MWF_Config::DBDATA . $form_id;
		return $contact_data_post_type;
	}

	/**
	 * 問い合わせデータ投稿タイプかどうか
	 *
	 * @param string $post_type
	 * @return bool
	 */
	public static function is_contact_data_post_type( $post_type ) {
		if ( preg_match( '/^' . MWF_Config::DBDATA . '\d+$/', $post_type ) ) {
			return true;
		}
		return false;
	}

	/**
	 * 添付データを適切なHTMLに変換して返す
	 *
	 * @param string $value
	 * @return string
	 */
	public static function get_multimedia_data( $value ) {
		$mimetype = get_post_mime_type( $value );
		if ( $mimetype ) {
			// 画像だったら
			if ( in_array( $mimetype, array( 'image/jpeg', 'image/gif', 'image/png', 'image/bmp' ) ) ) {
				$src_thumbnail = wp_get_attachment_image_src( $value, 'thumbnail' );
				$src_full      = wp_get_attachment_image_src( $value, 'full' );
				return sprintf(
					'<a href="%s" target="_blank"><img src="%s" alt="" style="max-height:50px" /></a>',
					esc_url( $src_full[0] ),
					esc_url( $src_thumbnail[0] )
				);
			}
			// 画像以外
			else {
				$src = wp_mime_type_icon( $mimetype );
				return sprintf(
					'<a href="%s" target="_blank"><img src="%s" alt="" style="height:32px" /></a>',
					esc_url( wp_get_attachment_url( $value ) ),
					esc_url( $src )
				);
			}
		}
		// 添付されているけど、フック等でメタ情報が書き換えられて添付ファイルID以外になってしまった場合
		else {
			return esc_html( $value );
		}
	}

	/**
	 * 添付データのIDを返す
	 * 過去バージョンでの不具合でアップロードファイルを示すメタデータが空になっていることがあるのでその場合の代替処理
	 *
	 * @param WP_Post $post
	 * @param int $meta_key
	 * @return int
	 */
	public static function get_multimedia_id__fallback( $post, $meta_key ) {
		$Contact_Data_Setting = new MW_WP_Form_Contact_Data_Setting( $post->ID );
		$key = $Contact_Data_Setting->get_key_in_upload_file_keys( $post, $meta_key );
		$attachments = get_posts( array(
			'post_type'      => 'attachment',
			'post_parent'    => $post->ID,
			'posts_per_page' => 1,
			'offset'         => $key,
		) );
		if ( isset( $attachments[0] ) ) {
			return $attachments[0]->ID;
		}
	}
}
