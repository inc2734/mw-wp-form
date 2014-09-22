<?php
/**
 * Name: MWF Functions
 * Description: 関数
 * Version: 1.2.1
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : May 29, 2013
 * Modified: July 24, 2014
 * License: GPLv2
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
	 * deprecated_message
	 * 古いメソッドを使った場合にエラーを出力
	 * @param string $function_name メソッド名
	 * @param string $new_function 代替のメソッド名
	 */
	public static function deprecated_message( $function_name, $new_function = '' ) {
		global $mwform_deprecated_message;
		$mwform_deprecated_message .= '<div class="' . esc_attr( MWF_Config::NAME ) . '-deprecated-message">';
		$mwform_deprecated_message .= sprintf( 'MW WP Form dosen\'t support "%s" already. ', $function_name );
		if ( $new_function ) {
			$mwform_deprecated_message .= sprintf( 'You should use "%s". ', $new_function );
		}
		$debug_backtrace = debug_backtrace();
		array_shift( $debug_backtrace );
		foreach ( $debug_backtrace as $value ) {
			$mwform_deprecated_message .= sprintf( '%s line %d', @$value['file'], @$value['line'] );
			break;
		}
		$mwform_deprecated_message .= '</div>';
		add_filter( 'the_content', 'MWF_Functions::_deprecated_message' );
	}
	public static function _deprecated_message( $content ) {
		global $mwform_deprecated_message;
		$content = $mwform_deprecated_message . $content;
		unset( $mwform_deprecated_message );
		return $content;
	}
}