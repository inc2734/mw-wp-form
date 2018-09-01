<?php
/**
 * Name       : MW WP Form Akismet
 * Version    : 2.0.0
 * Author     : Takashi Kitajima
 * Author URI : https://2inc.org
 * Created    : April 30, 2014
 * Modified   : June 1, 2017
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Akismet {

	/**
	 * Return akismet api key when akismet is active
	 *
	 * @return string API Key
	 */
	private function _is_enable() {
		if ( is_callable( array( 'Akismet', 'get_api_key' ) ) ) {
			return Akismet::get_api_key();
		}

		if ( function_exists( 'akismet_get_key' ) ) {
			return akismet_get_key();
		}

		return false;
	}

	/**
	 * Return true when through akismet check
	 *
	 * @param string $akismet_author
	 * @param string $akismet_author_email
	 * @param string $akismet_author_url
	 * @param MW_WP_Form_Data $Data
	 * @return bool
	 */
	public function is_valid( $akismet_author, $akismet_author_email, $akismet_author_url, $Data ) {
		global $akismet_api_host, $akismet_api_port;

		if ( ! $this->_is_enable() ) {
			return false;
		}

		$doAkismet = false;

		$author = '';
		if ( $Data->get_post_value_by_key( $akismet_author ) ) {
			$author = $Data->get_post_value_by_key( $akismet_author );
			$doAkismet = true;
		}

		$author_email = '';
		if ( $Data->get_post_value_by_key( $akismet_author_email ) ) {
			$author_email = $Data->get_post_value_by_key( $akismet_author_email );
			$doAkismet = true;
		}

		$author_url = '';
		if ( $Data->get_post_value_by_key( $akismet_author_url ) ) {
			$author_url = $Data->get_post_value_by_key( $akismet_author_url );
			$doAkismet = true;
		}

		if ( ! $doAkismet ) {
			return false;
		}

		$content = '';
		foreach ( $Data->gets() as $key => $value ) {
			$value    = $Data->get( $key );
			$content .= $value . "\n\n";
		}

		$permalink = get_permalink();
		$akismet   = array();

		$akismet['blog']         = home_url();
		$akismet['blog_lang']    = get_locale();
		$akismet['blog_charset'] = get_option( 'blog_charset' );
		$akismet['user_ip']      = preg_replace( '/[^0-9., ]/', '', $_SERVER['REMOTE_ADDR'] );
		$akismet['user_agent']   = $_SERVER['HTTP_USER_AGENT'];
		$akismet['referrer']     = $_SERVER['HTTP_REFERER'];
		$akismet['comment_type'] = MWF_Config::NAME;

		if ( $permalink )    $akismet['permalink']            = $permalink;
		if ( $author )       $akismet['comment_author']       = $author;
		if ( $author_email ) $akismet['comment_author_email'] = $author_email;
		if ( $author_url )   $akismet['comment_author_url']   = $author_url;
		if ( $content )      $akismet['comment_content']      = $content;

		foreach ( $_SERVER as $key => $value ) {
			if ( in_array( $key, array( 'HTTP_COOKIE', 'HTTP_COOKIE2', 'PHP_AUTH_PW' ) ) ) {
				continue;
			}
			$akismet[ $key ] = $value;
		}

		$query_string = http_build_query( $akismet, null, '&' );
		if ( is_callable( array( 'Akismet', 'http_post' ) ) ) {
			$response = Akismet::http_post( $query_string, 'comment-check' );
		} else {
			$response = akismet_http_post(
				$query_string,
				$akismet_api_host, '/1.1/comment-check',
				$akismet_api_port
			);
		}
		$response = apply_filters( 'mwform_akismet_responce', $response );
		return ( 'true' === $response[1] ) ? true : false;
	}
}
