<?php
class MW_WP_Form_Model {
	
	/**
	 * nocache_headers
	 * Nginx Cache Controller用
	 * @param array $headers
	 * @return array $headers
	 */
	public function nocache_headers( $headers ) {
		$headers['X-Accel-Expires'] = 0;
		return $headers;
	}

	/**
	 * remove_query_vars_from_post
	 * @param array $query_vars $wp_query->query_vars
	 * @param array $post $_POST
	 * @return array $query_vars $wp_query->query_vars
	 */
	public function remove_query_vars_from_post( array $query_vars, array $post ) {
		foreach ( $post as $key => $value ) {
			if ( $key == 'token' )
				continue;
			if ( isset( $query_vars[$key] ) && $query_vars[$key] === $value && !empty( $value ) ) {
				$query_vars[$key] = '';
			}
		}
		return $query_vars;
	}

	/**
	 * get_shortcode
	 * MW WP Form のショートコードが含まれていればそのショートコードを返す
	 * @param WP_Post $post
	 * @param string $template
	 * @return string [hoge xxx="xxx"]
	 */
	public function get_shortcode( $post, $template ) {
		if ( is_singular() && !empty( $post->ID ) ) {
			$shortcode = $this->get_shortcode_in_contnt( $post->post_content );
		}
		if ( empty( $shortcode ) &&
			 !( defined( 'MWFORM_NOT_USE_TEMPLATE' ) && MWFORM_NOT_USE_TEMPLATE === true ) ) {
			$template_data = @file_get_contents( $template );
			$shortcode = $this->get_shortcode_in_contnt( $template_data );
		}
		return $shortcode;
	}

	/**
	 * get_shortcode_in_contnt
	 * MW WP Form のショートコードが含まれていればそのショートコードを返す
	 * @param string $content
	 * @return string [hoge xxx="xxx"]
	 */
	public function get_shortcode_in_contnt( $content ) {
		preg_match_all( '/' . get_shortcode_regex() . '/s', $content, $matches, PREG_SET_ORDER );
		if ( $matches ) {
			foreach ( $matches as $shortcode ) {
				if ( in_array( $shortcode[2], array( 'mwform', 'mwform_formkey' ) ) ) {
					return $shortcode[0];
				} else {
					$_shortcode = $this->get_shortcode_in_contnt( $shortcode[5] );
					if ( is_array( $_shortcode ) && !empty( $_shortcode[0] ) ) {
						return $_shortcode[0];
					}
				}
			}
		}
	}

	/**
	 * meta_mwform
	 * [mwform〜] から設定されるべきプロパティを返す
	 * @param array $atts
	 * @return array $atts
	 */
	public function meta_mwform( $atts ) {
		return shortcode_atts( array(
			'input'            => '',
			'confirm'          => '',
			'complete'         => '',
			'validation_error' => '',
			'key'              => 'mwform'
		), $atts );
	}

	/**
	 * meta_mwform_formkey
	 * [mwform_formkey〜] から設定されるべきプロパティを返す
	 * @param WP_Post $post
	 * @param array $defaults
	 * @return array $options_by_formkey
	 */
	public function meta_mwform_formkey( $_post ) {
		$options_by_formkey = array();
		if ( !empty( $_post->ID ) ) {
			if ( get_post_type( $_post ) === MWF_Config::NAME ) {
				$options_by_formkey = $this->get_options_by_formkey( $_post->ID );
			}
		}
		return $options_by_formkey;
	}

	/**
	 * get_options_by_formkey
	 * フォームの $post_id をもとに設定を返す
	 * @param array $post_id
	 * @return array
	 */
	public function get_options_by_formkey( $post_id ) {
		return ( array )get_post_meta( $post_id, MWF_Config::NAME, true );
	}

	/**
	 * replace_user_property
	 * ユーザーがログイン中の場合、{ユーザー情報のプロパティ}を置換する。
	 * @param string フォーム内容
	 * @return string フォーム内容
	 */
	public function replace_user_property( $content ) {
		$user = wp_get_current_user();
		$search = array(
			'{user_id}',
			'{user_login}',
			'{user_email}',
			'{user_url}',
			'{user_registered}',
			'{display_name}',
		);
		if ( !empty( $user ) ) {
			$content = str_replace( $search, array(
				$user->get( 'ID' ),
				$user->get( 'user_login' ),
				$user->get( 'user_email' ),
				$user->get( 'user_url' ),
				$user->get( 'user_registered' ),
				$user->get( 'display_name' ),
			), $content );
		} else {
			$content = str_replace( $search, '', $content );
		}
		return $content;
	}
}