<?php
/**
 * Name       : MW WP Form Main View
 * Version    : 1.0.1
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : December 31, 2014
 * Modified   : March 24, 2015
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Main_View extends MW_WP_Form_View {
	
	/**
	 * add_shortcode_that_display_content
	 */
	public function add_shortcode_that_display_content() {
		add_shortcode( 'mwform_formkey'         , array( $this, 'mwform_formkey' ) );
		add_shortcode( 'mwform'                 , array( $this, 'mwform' ) );
		add_shortcode( 'mwform_complete_message', array( $this, 'mwform_complete_message' ) );

		do_action(
			'mwform_add_shortcode',
			$this->get( 'Form' ),
			$this->get( 'view_flg' ),
			$this->get( 'Error' ),
			$this->get( 'form_key' )
		);
	}

	/**
	 * mwform_formkey
	 * 管理画面で作成したフォームを出力（実際の出力は mwform ）
	 * @param array $attributes
	 * @return string html
	 * @example [mwform_formkey key="post_id"]
	 */
	public function mwform_formkey( $attributes ) {
		$view_flg = $this->get( 'view_flg' );
		$post_id  = $attributes['key'];

		// 入力画面
		if ( $view_flg === 'input' ) {
			$content = $this->get_input_page_content( $post_id );
		}
		// 確認画面
		elseif ( $view_flg == 'confirm' ) {
			$content = $this->get_confirm_page_content( $post_id );
		}
		// 完了画面
		elseif ( $view_flg === 'complete' ) {
			$content = $this->get_complete_page_content();
		}
		return do_shortcode( $content );
	}

	/**
	 * get_input_page_content
	 * @param int $post_id
	 * @return string $content
	 */
	protected function get_input_page_content( $post_id ) {
		global $post;
		$form_key = $this->get( 'form_key' );
		$post     = get_post( $post_id );
		setup_postdata( $post );
		$content = apply_filters( 'mwform_post_content_raw_' . $form_key, get_the_content() );
		if ( has_filter( 'the_content', 'wpautop' ) ) {
			$content = wpautop( $content );
		}
		$content = sprintf(
			'[mwform]%s[/mwform]',
			apply_filters( 'mwform_post_content_' . $form_key, $content )
		);
		wp_reset_postdata();
		return $content;
	}

	/**
	 * get_confirm_page_content
	 * @param int $post_id
	 * @return string $content
	 */
	protected function get_confirm_page_content( $post_id ) {
		return $this->get_input_page_content( $post_id );
	}

	/**
	 * get_complete_page_content
	 * @return string $content
	 */
	protected function get_complete_page_content() {
		$Setting = $this->get( 'Setting' );
		$content = $Setting->get( 'complete_message' );
		if ( has_filter( 'the_content', 'wpautop' ) ) {
			$content = wpautop( $content );
		}
		$content = sprintf(
			'[mwform_complete_message]%s[/mwform_complete_message]',
			$content
		);
		return $content;
	}

	/**
	 * mwform
	 * フォームを出力
	 * @param array $attributes
	 * @return string html
	 */
	public function mwform( $attributes, $content = '' ) {
		$view_flg = $this->get( 'view_flg' );
		$Form     = $this->get( 'Form' );
		$form_key = $this->get( 'form_key' );

		if ( in_array( $view_flg, array( 'input', 'confirm' ) ) ) {
			$content            = $this->get_the_content( $content );
			$upload_file_keys   = $Form->get_raw( MWF_Config::UPLOAD_FILE_KEYS );
			$upload_file_hidden = $this->get_upload_file_hidden( $upload_file_keys );
			$old_confirm_class  = $this->get_old_confirm_class( $view_flg );
			$class_by_style     = $this->get_class_by_style();

			return sprintf(
				'<div id="mw_wp_form_%s" class="mw_wp_form mw_wp_form_%s %s">
					%s
				<!-- end .mw_wp_form --></div>',
				esc_attr( $form_key ),
				esc_attr( $view_flg . ' ' . $old_confirm_class ),
				$class_by_style,
				$Form->start() . do_shortcode( $content ) . $upload_file_hidden . $Form->end()
			);
		}
	}

	/**
	 * mwform_complete_message
	 * 完了後のメッセージ
	 * @param array $attributes
	 * @return string html
	 */
	public function mwform_complete_message( $attributes, $content = '' ) {
		$view_flg = $this->get( 'view_flg' );
		$form_key = $this->get( 'form_key' );
		if ( $view_flg === 'complete' ) {
			return sprintf(
				'<div id="mw_wp_form_%s" class="mw_wp_form mw_wp_form_%s">
					%s
				<!-- end .mw_wp_form --></div>',
				esc_attr( $form_key ),
				esc_attr( $view_flg ),
				$content
			);
		}
	}

	/**
	 * replace_user_property
	 * ユーザーがログイン中の場合、{ユーザー情報のプロパティ}を置換する。
	 * @param string フォーム内容
	 * @return string フォーム内容
	 */
	protected function replace_user_property( $content ) {
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

	/**
	 * replace_post_content
	 * {投稿情報（$post->hoge）}を置換する。
	 * @param string フォーム内容
	 * @return string フォーム内容
	 */
	protected function replace_post_property( $content ) {
		$Setting = $this->get( 'Setting' );
		if ( $Setting->get( 'querystring' ) ) {
			$content = preg_replace_callback(
				'/{(.+?)}/',
				array( $this, 'get_post_property_from_querystring' ),
				$content
			);
		} else {
			$content = preg_replace_callback(
				'/{(.+?)}/',
				array( $this, 'get_post_property_from_this' ),
				$content
			);
		}
		return $content;
	}

	/**
	 * 引数 post_id が有効の場合、投稿情報を取得するために preg_replace_callback から呼び出される。
	 *
	 * @param array $matches
	 * @return string|null
	 */
	protected function get_post_property_from_querystring( $matches ) {
		$Setting = $this->get( 'Setting' );
		if ( $Setting->get( 'querystring' ) &&
			 isset( $_GET['post_id'] ) &&
			 MWF_Functions::is_numeric( $_GET['post_id'] ) ) {

			$post = get_post( $_GET['post_id'] );
			if ( empty( $post->ID ) ) {
				return;
			}
			return $this->get_post_property( $post, $matches[1] );
		}
	}

	/**
	 * 引数 post_id が無効の場合、投稿情報を取得するために preg_replace_callback から呼び出される。
	 *
	 * @param array $matches
	 * @return string|null
	 */
	protected function get_post_property_from_this( $matches ) {
		global $post;
		if ( !is_singular() ) {
			return;
		}
		if ( isset( $post->ID ) && MWF_Functions::is_numeric( $post->ID ) ) {
			return $this->get_post_property( $post, $matches[1] );
		}
	}

	/**
	 * 投稿のプロパティを取得
	 *
	 * @param WP_Post|null $post
	 * @param string $meta_key
	 * @return string|null
	 */
	protected function get_post_property( $post, $meta_key ) {
		if ( !is_a( $post, 'WP_Post' ) ) {
			return;
		}
		if ( isset( $post->$meta_key ) ) {
			return $post->$meta_key;
		}
		$post_meta = get_post_meta( $post->ID, $meta_key, true );
		if ( !is_array( $post_meta ) ) {
			return $post_meta;
		}
	}

	/**
	 * get_the_content
	 * フォームのコンテンツを整形して返す
	 * @param string $content
	 * @return string $content
	 */
	protected function get_the_content( $content ) {
		$content = $this->replace_user_property( $content );
		$content = $this->replace_post_property( $content );
		return $content;
	}

	/**
	 * get_upload_file_hidden
	 * @param array|'' $upload_file_keys
	 */
	protected function get_upload_file_hidden( $upload_file_keys ) {
		$Form = $this->get( 'Form' );
		$upload_file_hidden = '';
		if ( !is_array( $upload_file_keys ) ) {
			return $upload_file_hidden;
		}
		foreach ( $upload_file_keys as $value ) {
			$upload_file_hidden .= $Form->hidden( MWF_Config::UPLOAD_FILE_KEYS . '[]', $value );
		}
		return $upload_file_hidden;
	}

	/**
	 * get_old_confirm_class
	 * 下位互換性のための class を付与
	 * @param string $view_flg
	 * @return string mw_wp_form_preview
	 */
	protected function get_old_confirm_class( $view_flg ) {
		$old_confirm_class = '';
		if ( $view_flg === 'confirm' ) {
			$old_confirm_class = 'mw_wp_form_preview';
		}
		return $old_confirm_class;
	}

	/**
	 * get_class_by_style
	 * スタイル機能用の class を付与
	 * @return string
	 */
	protected function get_class_by_style() {
		$Setting = $this->get( 'Setting' );
		$style   = $Setting->get( 'style' );
		$class_by_style = '';
		if ( $style ) {
			$class_by_style = 'mw_wp_form_' . $style;
		}
		return $class_by_style;
	}
}