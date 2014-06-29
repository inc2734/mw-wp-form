<?php
/**
 * Name: MW Akismet
 * URI: http://2inc.org
 * Description: Akismetクラス
 * Version: 1.0.0
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : April 30, 2014
 * Modified:
 * License: GPL2
 *
 * Copyright 2014 Takashi Kitajima (email : inc@2inc.org)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
class MW_Akismet {
	public function __construct() {
	}

	private function is_enable() {
		if ( is_callable( array( 'Akismet', 'get_api_key' ) ) ) {
			return Akismet::get_api_key();
		}
		if ( function_exists( 'akismet_get_key' ) ) {
			return akismet_get_key();
		}
		return false;
	}

	public function check( $akismet_author, $akismet_author_email, $akismet_author_url, $data ) {
		global $akismet_api_host, $akismet_api_port;

		if ( !$this->is_enable() )
			return false;

		$doAkismet = false;

		$author = '';
		if ( !empty( $data[ $akismet_author ] ) ) {
			$author = $data[ $akismet_author ];
			$doAkismet = true;
		}

		$author_email = '';
		if ( !empty( $data[ $akismet_author_email ] ) ) {
			$author_email = $data[ $akismet_author_email ];
			$doAkismet = true;
		}

		$author_url = '';
		if ( !empty( $data[ $akismet_author_url ] ) ) {
			$author_url = $data[ $akismet_author_url ];
			$doAkismet = true;
		}

		if ( $doAkismet ) {
			$content = '';
			foreach ( $data as $value ) {
				if ( is_array( $value ) && isset( $value['data'] ) && is_array( $value['data'] ) ) {
					$value = implode( $value['separator'], $value['data'] );
				}
				if ( !is_array( $value ) ) {
					$content .= $value . "\n\n";
				}
			}
			$permalink = get_permalink();
			$akismet = array();
			$akismet['blog']         = get_option( 'home' );
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
				if ( !in_array( $key, array( 'HTTP_COOKIE', 'HTTP_COOKIE2', 'PHP_AUTH_PW' ) ) )
					$akismet[$key] = $value;
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
			return ( $response[1] == 'true' ) ? true : false;
		}
	}
}
?>