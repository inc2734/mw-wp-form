<?php
/**
 * @package mw-wp-form
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * MWF_Functions
 */
class MWF_Functions {

	/**
	 * Return true when the variable passed as an argument exists and the numeric value.
	 *
	 * @param variable $value Pass by reference.
	 * @return bool
	 */
	public static function is_numeric( &$value ) {
		return ( isset( $value ) && preg_match( '/^\d+$/', $value ) );
	}

	/**
	 * Delete empty element of array.
	 *
	 * @param array $array Array.
	 * @return array
	 */
	public static function array_clean( $array ) {
		return array_filter( $array );
	}

	/**
	 * If the value is empty (0 is permitted).
	 *
	 * @param mixed $value Value.
	 * @return boolean
	 */
	public static function is_empty( $value ) {
		return ( array() === $value || '' === $value || is_null( $value ) || false === $value );
	}

	/**
	 * Convert file URL to file path.
	 *
	 * @param string $fileurl File URL.
	 * @return string
	 */
	public static function fileurl_to_path( $fileurl ) {
		if ( ! preg_match( '/^https?:\/\//', $fileurl ) ) {
			return;
		}

		$wp_upload_dir = wp_upload_dir();
		$baseurl       = preg_replace( '/^https?:\/\/(.+)$/', '$1', $wp_upload_dir['baseurl'] );
		$fileurl       = preg_replace( '/^https?:\/\/(.+)$/', '$1', $fileurl );
		$filepath      = str_replace(
			$baseurl,
			$wp_upload_dir['basedir'],
			$fileurl
		);

		return $filepath;
	}

	/**
	 * Convert file path to file URL.
	 *
	 * @param string $filepath File path.
	 * @return string
	 */
	public static function filepath_to_url( $filepath ) {
		if ( preg_match( '/^https?:\/\//', $filepath ) ) {
			return;
		}

		$wp_upload_dir = wp_upload_dir();
		$fileurl       = str_replace(
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
	 * Unify line feed code to \n.
	 *
	 * @param sring $string String.
	 * @return string
	 */
	public static function convert_eol( $string ) {
		return preg_replace( "/\r\n|\r|\n/", "\n", $string );
	}

	/**
	 * Display deprecated error message.
	 *
	 * @param string $function_name  Function name.
	 * @param string $new_function   New function name.
	 */
	public static function deprecated_message( $function_name, $new_function = '' ) {
		if ( ! defined( 'WP_DEBUG' ) || true !== WP_DEBUG || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		global $mwform_deprecated_message;
		$mwform_deprecated_message .= '<div class="error ' . esc_attr( MWF_Config::NAME ) . '-deprecated-message">';
		$mwform_deprecated_message .= sprintf(
			'MW WP Form dosen\'t support "<b>%s</b>" already. This will be removed in the next version. ',
			$function_name
		);
		if ( $new_function ) {
			$mwform_deprecated_message .= sprintf( 'You should use "<b>%s</b>". ', $new_function );
		}

		// phpcs:disable PHPCompatibility.FunctionUse.ArgumentFunctionsReportCurrentValue.NeedsInspection
		$debug_backtrace = debug_backtrace();
		// phpcs:enable

		array_shift( $debug_backtrace );
		foreach ( $debug_backtrace as $value ) {
			if ( isset( $value['file'], $value['line'] ) ) {
				$mwform_deprecated_message .= sprintf( '<b>%s line %d</b>', $value['file'], $value['line'] );
			}
			break;
		}
		$mwform_deprecated_message .= '</div>';
		if ( is_admin() ) {
			if ( 'admin_notices' === current_filter() ) {
				self::_display_deprecated_message();
			} else {
				add_action( 'admin_notices', 'MWF_Functions::_display_deprecated_message' );
			}
		} else {
			if ( 'the_content' === current_filter() ) {
				self::_display_deprecated_message();
			} else {
				add_filter( 'the_content', 'MWF_Functions::_return_deprecated_message' );
				error_log( strip_tags( self::_return_deprecated_message() ) );
			}
		}
	}

	/**
	 * Display deprecated message.
	 */
	protected static function _display_deprecated_message() {
		global $mwform_deprecated_message;
		$content = $mwform_deprecated_message;
		unset( $mwform_deprecated_message );
		echo $content;
	}

	/**
	 * Return deprecated message.
	 *
	 * @param string $content Content.
	 */
	public static function _return_deprecated_message( $content = '' ) {
		global $mwform_deprecated_message;
		$content = $mwform_deprecated_message . $content;
		unset( $mwform_deprecated_message );
		return $content;
	}

	/**
	 * Move files from Temp directory to upload directory.
	 *
	 * @param string $filepath   Path of temp file.
	 * @param string $upload_dir Directory path of new file.
	 * @param string $filename   New fine name.
	 * @return string
	 */
	public static function move_temp_file_to_upload_dir( $filepath, $upload_dir = '', $filename = '' ) {
		$wp_upload_dir = wp_upload_dir();

		if ( ! $upload_dir ) {
			$upload_dir = $wp_upload_dir['path'];
		} else {
			$upload_dir = trailingslashit( $wp_upload_dir['basedir'] ) . ltrim( $upload_dir, '/\\' );
			wp_mkdir_p( $upload_dir );
		}

		if ( ! $filename ) {
			$filename = basename( $filepath );
		}

		if ( ! preg_match( '/(\..+?)$/', $filename ) ) {
			$extension = pathinfo( $filepath, PATHINFO_EXTENSION );
			$filename  = $filename . '.' . $extension;
		}
		$filename = sanitize_file_name( $filename );
		$filename = wp_unique_filename( $upload_dir, $filename );

		$new_filepath = trailingslashit( $upload_dir ) . $filename;

		if ( $filepath === $new_filepath ) {
			return $filepath;
		}

		// If the temp file doesn't exist, return only the path after the rename
		if ( ! file_exists( $filepath ) ) {
			return $new_filepath;
		}

		// If it can move, even if it can not move, return only the path after rename
		if ( rename( $filepath, $new_filepath ) ) {
			return $new_filepath;
		}
		return $new_filepath;
	}

	/**
	 * Save attached file on media, save attachment key (array) in posting data.
	 *
	 * @param int   $saved_mail_id Saved mail ID.
	 * @param array $attachments   Attachments.
	 * @param int   $form_id       Form ID.
	 * @return void
	 */
	public static function save_attachments_in_media( $saved_mail_id, $attachments, $form_id ) {
		require_once( ABSPATH . 'wp-admin' . '/includes/media.php' );
		require_once( ABSPATH . 'wp-admin' . '/includes/image.php' );
		$save_attached_key = array();
		foreach ( $attachments as $key => $filepath ) {
			if ( ! self::check_file_type( $filepath ) ) {
				continue;
			}

			$wp_check_filetype = wp_check_filetype( $filepath );
			$post_type         = get_post_type_object( self::get_contact_data_post_type_from_form_id( $form_id ) );
			if ( empty( $post_type->label ) ) {
				continue;
			}
			$attachment = array(
				'post_mime_type' => $wp_check_filetype['type'],
				'post_title'     => $key,
				'post_status'    => 'inherit',
				'post_content'   => __( 'Uploaded from ', 'mw-wp-form' ) . $post_type->label,
			);
			$attach_id  = wp_insert_attachment( $attachment, $filepath, $saved_mail_id );
			if ( $attach_id ) {
				// 代わりにここで attachment_id を保存
				update_post_meta( $saved_mail_id, $key, $attach_id );
				// $key が 添付ファイルのキーであるとわかるように隠し設定を保存
				$save_attached_key[] = $key;
			}
		}
		if ( $save_attached_key ) {
			update_post_meta( $saved_mail_id, '_' . MWF_Config::UPLOAD_FILE_KEYS, $save_attached_key );
		}
	}

	/**
	 * Return true when correct file type.
	 *
	 * @param string $filepath Uploaded file path.
	 * @param string $filename File name.
	 * @return bool
	 */
	public static function check_file_type( $filepath, $filename = '' ) {
		if ( ! file_exists( $filepath ) ) {
			return false;
		}

		// File type restricted by WordPress (get_allowed_mime_types)
		if ( $filename ) {
			$wp_check_filetype = wp_check_filetype( $filename );
		} else {
			$wp_check_filetype = wp_check_filetype( $filepath );
		}
		if ( empty( $wp_check_filetype['type'] ) ) {
			return false;
		}

		if ( version_compare( phpversion(), '5.3.0' ) >= 0 && defined( 'FILEINFO_MIME_TYPE' ) ) {
			$finfo = new finfo( FILEINFO_MIME_TYPE );
			if ( false === $finfo ) {
				return false;
			}

			// For files have multi mime types
			switch ( $wp_check_filetype['ext'] ) {
				case 'avi':
					$wp_check_filetype['type'] = array(
						'application/x-troff-msvideo',
						'video/avi',
						'video/msvideo',
						'video/x-msvideo',
					);
					break;
				case 'mp3':
					$wp_check_filetype['type'] = array(
						'audio/mpeg3',
						'audio/x-mpeg3',
						'video/mpeg',
						'video/x-mpeg',
						'audio/mpeg',
					);
					break;
				case 'mpg':
					$wp_check_filetype['type'] = array(
						'audio/mpeg',
						'video/mpeg',
					);
					break;
				case 'docx':
					$wp_check_filetype['type'] = array(
						$wp_check_filetype['type'],
						'application/zip',
						'application/msword',
					);
					break;
				case 'xlsx':
					$wp_check_filetype['type'] = array(
						$wp_check_filetype['type'],
						'application/zip',
						'application/excel',
						'application/msexcel',
						'application/vnd.ms-excel',
					);
					break;
				case 'pptx':
					$wp_check_filetype['type'] = array(
						$wp_check_filetype['type'],
						'application/zip',
						'application/mspowerpoint',
						'application/powerpoint',
						'application/ppt',
					);
					break;
			}

			$type = $finfo->file( $filepath );
			if ( is_array( $wp_check_filetype['type'] ) ) {
				if ( ! in_array( $type, $wp_check_filetype['type'], true ) ) {
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
	 * Return display name of the tracking number.
	 *
	 * @param string $post_type Post type of inquiry data.
	 * @return string
	 */
	public static function get_tracking_number_title( $post_type ) {
		$tracking_number_title = esc_html__( 'Tracking Number', 'mw-wp-form' );
		$form_key              = self::contact_data_post_type_to_form_key( $post_type );
		if ( $form_key ) {
			$tracking_number_title = apply_filters(
				'mwform_tracking_number_title_' . $form_key,
				$tracking_number_title
			);
		}
		return $tracking_number_title;
	}

	/**
	 * Return form key from inquiry data post type.
	 *
	 * @param string $post_type Post type of inquiry data.
	 * @return string|null
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
	 * Return form key from form ID.
	 *
	 * @param int $form_id Form ID.
	 * @return string
	 */
	public static function get_form_key_from_form_id( $form_id ) {
		if ( MWF_Functions::is_numeric( $form_id ) ) {
			return MWF_Config::NAME . '-' . $form_id;
		}
	}

	/**
	 * Return form ID from form key.
	 *
	 * @param string $form_key Form key.
	 * @return int
	 */
	public static function get_form_id_from_form_key( $form_key ) {
		if ( preg_match( '/^' . MWF_Config::NAME . '-(\d+)$/', $form_key, $reg ) ) {
			return $reg[1];
		}
	}

	/**
	 * Return inquiry data post type from form ID.
	 *
	 * @param int $form_id Form ID.
	 * @return string
	 */
	public static function get_contact_data_post_type_from_form_id( $form_id ) {
		if ( MWF_Functions::is_numeric( $form_id ) ) {
			$contact_data_post_type = MWF_Config::DBDATA . $form_id;
			return $contact_data_post_type;
		}
	}

	/**
	 * Whether the inquiry data post type.
	 *
	 * @param string $post_type Post type name.
	 * @return boolean
	 */
	public static function is_contact_data_post_type( $post_type ) {
		return (bool) ( preg_match( '/^' . MWF_Config::DBDATA . '\d+$/', $post_type ) );
	}

	/**
	 * Return converting attached data to appropriate HTML.
	 *
	 * @param string $value Post ID or not.
	 * @return string
	 */
	public static function get_multimedia_data( $value ) {
		$mimetype = get_post_mime_type( $value );
		if ( $mimetype ) {
			if ( in_array( $mimetype, array( 'image/jpeg', 'image/gif', 'image/png', 'image/bmp' ), true ) ) {
				// Image
				$src_thumbnail = wp_get_attachment_image_src( $value, 'thumbnail' );
				$src_full      = wp_get_attachment_image_src( $value, 'full' );
				return sprintf(
					'<a href="%s" target="_blank"><img src="%s" alt="" style="max-height:50px" /></a>',
					esc_url( $src_full[0] ),
					esc_url( $src_thumbnail[0] )
				);
			} else {
				// Other
				$src = wp_mime_type_icon( $mimetype );
				return sprintf(
					'<a href="%s" target="_blank"><img src="%s" alt="" style="height:32px" /></a>',
					esc_url( wp_get_attachment_url( $value ) ),
					esc_url( $src )
				);
			}
		} else {
			// Attached, but $value is not file ID because changed meta data by hook
			return esc_html( $value );
		}
	}

	/**
	 * Return attachment file ID.
	 * 過去バージョンでの不具合でアップロードファイルを示すメタデータが空になっていることがあるのでその場合の代替処理.
	 *
	 * @param WP_Post $post     WP_Post object.
	 * @param int     $meta_key Meta data name.
	 * @return int
	 */
	public static function get_multimedia_id__fallback( $post, $meta_key ) {
		$contact_data_setting = new MW_WP_Form_Contact_Data_Setting( $post->ID );
		$index                = $contact_data_setting->get_index_of_key_in_upload_file_keys( $meta_key );

		if ( false === $index ) {
			return;
		}

		$attachments = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_parent'    => $post->ID,
				'posts_per_page' => 1,
				'offset'         => $index,
			)
		);

		if ( isset( $attachments[0] ) ) {
			return $attachments[0]->ID;
		}
	}

	/**
	 * Enqueue MW WP Form assets.
	 *
	 * @param int $form_id Form ID.
	 * @return void
	 */
	public static function mwform_enqueue_scripts( $form_id ) {
		$Setting  = new MW_WP_Form_Setting( $form_id );
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$url      = plugins_url( MWF_Config::NAME );
		wp_enqueue_style( MWF_Config::NAME, $url . '/css/style.css' );

		$style  = $Setting->get( 'style' );
		$styles = apply_filters( 'mwform_styles', array() );
		if ( is_array( $styles ) && isset( $styles[ $style ] ) ) {
			$css = $styles[ $style ];
			wp_enqueue_style( MWF_Config::NAME . '_style_' . $form_key, $css );
		}

		wp_enqueue_script( MWF_Config::NAME, $url . '/js/form.js', array( 'jquery' ), false, true );
		do_action( 'mwform_enqueue_scripts_' . $form_key );
	}

	/**
	 * Generate input field's attribute and attribute value pair.
	 *
	 * @param string $attribute_name  Attribute name.
	 * @param string $attribute_value Attribute value.
	 * @return string
	 */
	public static function generate_input_attribute( $attribute_name, $attribute_value ) {
		if ( is_null( $attribute_value ) ) {
			return;
		}

		return sprintf(
			'%1$s="%2$s"',
			esc_html( $attribute_name ),
			esc_attr( $attribute_value )
		);
	}
}
