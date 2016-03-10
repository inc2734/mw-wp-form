<?php
/**
 * Name       : MW WP Form Exec Shortcode
 * Version    : 1.2.0
 * Description: ExecShortcode（mwform、mwform_formkey）の存在有無のチェックとそれらの抽象化レイヤー
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : December 31, 2014
 * Modified   : March 10, 2016
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Exec_Shortcode {

	/**
	 * フォームの Post ID
	 * @var int
	 */
	protected $post_id;

	/**
	 * ショートコードが埋め込まれた投稿のオブジェクト
	 * @var WP_Post|null
	 */
	protected $post;

	/**
	 * @var string 表示中のテンプレート
	 */
	protected $template;

	/**
	 * フォームの実行に必須なデータの配列
	 * @var array
	 */
	protected $settings = array(
		'input_url'            => '',
		'confirmation_url'     => '',
		'complete_url'         => '',
		'validation_error_url' => '',
		'key'                  => null,
	);

	/**
	 * 表示すべき画面を示すフラグ
	 * @var string
	 */
	protected $view_flg;

	/**
	 * @var MW_WP_Form_Setting
	 */
	protected $Setting;

	/**
	 * @var MW_WP_Form_Form
	 */
	protected $Form;

	/**
	 * @var MW_WP_Form_Data
	 */
	protected $Data;

	/**
	 * __construct
	 *
	 * @param WP_Post|null $post
	 * @param string $template 使用テンプレートのパス
	 */
	public function __construct( $post, $template ) {
		$this->post     = $post;
		$this->template = $template;

		add_shortcode( 'mwform'        , array( $this, 'set_settings_by_mwform' ) );
		add_shortcode( 'mwform_formkey', array( $this, 'set_settings_by_mwform_formkey' ) );

		$exec_shortcode = $this->get_exec_shortcode();
		if ( $exec_shortcode ) {
			// ここで set_settings_by_mwform(), set_settings_by_mwform_formkey() が実行される
			do_shortcode( $exec_shortcode );
		}

		remove_shortcode( 'mwform' );
		remove_shortcode( 'mwform_formkey' );
	}

	/**
	 * 必要な設定が完了していたらtrue
	 *
	 * @return bool
	 */
	public function has_shortcode() {
		if ( is_null( $this->settings['key'] ) ) {
			return false;
		}
		return true;
	}

	/**
	 * 設定データを取得
	 *
	 * @param string $key
	 * @return string
	 */
	public function get( $key ) {
		if ( isset( $this->settings[$key] ) ) {
			return $this->settings[$key];
		}
	}

	/**
	 * ExecShortcode が含まれていればそのショートコードを返す
	 *
	 * @return string [hoge xxx="xxx"]
	 */
	protected function get_exec_shortcode() {
		$exec_shortcode = '';
		if ( is_singular() && !empty( $this->post->ID ) ) {
			$exec_shortcode = $this->get_in_content( $this->post->post_content );
		}
		if ( empty( $exec_shortcode ) ) {
			$exec_shortcode = $this->get_in_template();
		}
		return $exec_shortcode;
	}

	/**
	 * テンプレートファイル（絶対パス）に ExecShortcode が含まれていればそのショートコードを返す
	 *
	 * @return string [hoge xxx="xxx"]
	 */
	protected function get_in_template() {
		if ( !( defined( 'MWFORM_NOT_USE_TEMPLATE' ) && MWFORM_NOT_USE_TEMPLATE === true ) ) {
			$template_data = @file_get_contents( $this->template );
			if ( $template_data ) {
				$exec_shortcode = $this->get_in_content( $template_data );
				return $exec_shortcode;
			}
		}
	}

	/**
	 * ExecShortcode が含まれていればそのショートコードを返す
	 *
	 * @param string $content
	 * @return string [hoge xxx="xxx"]
	 */
	protected function get_in_content( $content ) {
		preg_match_all( '/' . get_shortcode_regex() . '/s', $content, $matches, PREG_SET_ORDER );
		if ( $matches ) {
			foreach ( $matches as $shortcode ) {
				if ( in_array( $shortcode[2], array( 'mwform', 'mwform_formkey' ) ) ) {
					return $shortcode[0];
				} else {
					$shortcode = $this->get_in_content( $shortcode[5] );
					if ( !empty( $shortcode ) ) {
						return $shortcode;
					}
				}
			}
		}
	}

	/**
	 * ショートコード mwform をもとにフォームの実行に必須のデータを設定
	 *
	 * @param array|'' $attributes
	 */
	public function set_settings_by_mwform( $attributes ) {
		$attributes = shortcode_atts( array(
			'key'              => 'mwform',
			'input'            => '',
			'confirm'          => '',
			'complete'         => '',
			'validation_error' => '',
		), $attributes );
		$this->set_settings( $attributes );
	}

	/**
	 * ショートコード mwform_formkey をもとにフォームの実行に必須のデータを設定
	 *
	 * @param array $attributes|''
	 */
	public function set_settings_by_mwform_formkey( $attributes ) {
		$post_id       = $this->get_form_id_by_mwform_formkey( $attributes );
		$this->post_id = $post_id;
		$settings      = array();
		if ( !empty( $post_id ) ) {
			$Setting = new MW_WP_Form_Setting( $post_id );
			foreach ( $this->settings as $key => $value ) {
				$settings[$key] = $Setting->get( $key );
			}
			$settings['key'] = MWF_Functions::get_form_key_from_form_id( $post_id );
		}
		$this->set_settings( $settings );
	}

	/**
	 * ショートコード mwform_formkey をもとにフォームの ID を取得
	 *
	 * @param array|'' $attributes
	 * @return string|null Post ID
	 */
	protected function get_form_id_by_mwform_formkey( $attributes ) {
		$attributes = shortcode_atts( array(
			'key'  => '',
			'slug' => '',
		), $attributes );

		if ( !empty( $attributes['slug'] ) ) {
			$post = get_page_by_path( $attributes['slug'], OBJECT, MWF_Config::NAME );
		} elseif ( !empty( $attributes['key'] ) ) {
			$post = get_post( $attributes['key'] );
		}

		if ( !empty( $post ) && isset( $post->ID ) ) {
			return $post->ID;
		}
	}

	/**
	 * フォームの実行に必須のデータを設定
	 *
	 * @param array $attributes
	 */
	protected function set_settings( array $attributes ) {
		foreach ( $attributes as $key => $value ) {
			if ( $key === 'key' ) {
				$this->settings['key'] = $value;
			}
			if ( $key === 'input_url' || $key === 'input' ) {
				$this->settings['input_url'] = $value;
			}
			if ( $key === 'confirmation_url' || $key === 'confirm' ) {
				$this->settings['confirmation_url'] = $value;
			}
			if ( $key === 'complete_url' || $key === 'complete' ) {
				$this->settings['complete_url'] = $value;
			}
			if ( $key === 'validation_error_url' || $key === 'validation_error' ) {
				$this->settings['validation_error_url'] = $value;
			}
		}
	}

	/**
	 * 管理画面で作成されたフォームであれば true
	 *
	 * @return bool
	 */
	public function is_generated_by_formkey() {
		if ( $this->post_id ) {
			return true;
		}
		return false;
	}

	/**
	 * フォームの ID を取得
	 *
	 * @return int
	 */
	public function get_form_id() {
		if ( $this->post_id ) {
			return $this->post_id;
		}
	}

	/**
	 * フォームを表示するためのショートコードを登録
	 *
	 * @param string $view_flg
	 * @param MW_WP_Form_Setting $Setting
	 * @param MW_WP_Form_Form $Form
	 */
	public function add_shortcode( $view_flg, MW_WP_Form_Setting $Setting, MW_WP_Form_Form $Form ) {
		$this->view_flg = $view_flg;
		$this->Setting  = $Setting;
		$this->Form     = $Form;
		$this->Data     = MW_WP_Form_Data::getInstance();
		add_shortcode( 'mwform_formkey'         , array( $this, 'mwform_formkey' ) );
		add_shortcode( 'mwform'                 , array( $this, 'mwform' ) );
		add_shortcode( 'mwform_complete_message', array( $this, 'mwform_complete_message' ) );
	}

	/**
	 * 管理画面で作成したフォームを出力（実際の出力は mwform ）
	 *
	 * @param array $attributes
	 * @return string html
	 * @example [mwform_formkey key="post_id"]
	 */
	public function mwform_formkey( $attributes ) {
		$view_flg = $this->view_flg;
		$post_id  = $this->get_form_id_by_mwform_formkey( $attributes );

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
		} else {
			$content = '';
		}
		return do_shortcode( $content );
	}

	/**
	 * 入力画面を表示
	 *
	 * @param int $post_id
	 * @return string $content
	 */
	protected function get_input_page_content( $post_id ) {
		global $post;
		$form_key = $this->get( 'key' );
		$post     = get_post( $post_id );
		setup_postdata( $post );
		$content = apply_filters( 'mwform_post_content_raw_' . $form_key, get_the_content() );

		$has_wpautop = false;
		if ( has_filter( 'the_content', 'wpautop' ) ) {
			$has_wpautop = true;
		}
		$has_wpautop = apply_filters(
			'mwform_content_wpautop_' . $form_key,
			$has_wpautop,
			$this->view_flg
		);

		if ( $has_wpautop ) {
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
	 * 確認画面を表示
	 *
	 * @param int $post_id
	 * @return string $content
	 */
	protected function get_confirm_page_content( $post_id ) {
		return $this->get_input_page_content( $post_id );
	}

	/**
	 * 完了画面を表示
	 *
	 * @return string $content
	 */
	protected function get_complete_page_content() {
		$form_key = $this->get( 'key' );
		$Setting  = $this->Setting;
		$content  = $Setting->get( 'complete_message' );

		$has_wpautop = false;
		if ( has_filter( 'the_content', 'wpautop' ) ) {
			$has_wpautop = true;
		}
		$has_wpautop = apply_filters(
			'mwform_content_wpautop_' . $form_key,
			$has_wpautop,
			$this->view_flg
		);

		if ( $has_wpautop ) {
			$content = wpautop( $content );
		}

		$content = sprintf(
			'[mwform_complete_message]%s[/mwform_complete_message]',
			$content
		);
		return $content;
	}

	/**
	 * フォームを出力
	 *
	 * @param array $attributes
	 * @return string html
	 */
	public function mwform( $attributes, $content = '' ) {
		$form_key = $this->get( 'key' );
		$view_flg = $this->view_flg;
		$Form     = $this->Form;
		$Data     = $this->Data;

		if ( in_array( $view_flg, array( 'input', 'confirm' ) ) ) {
			$content            = $this->get_the_content( $content );
			$upload_file_keys   = $Data->get_post_value_by_key( MWF_Config::UPLOAD_FILE_KEYS );
			$upload_file_hidden = $this->get_upload_file_hidden( $upload_file_keys );
			$old_confirm_class  = $this->get_old_confirm_class();
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
	 * フォームのコンテンツを整形して返す
	 *
	 * @param string $content
	 * @return string $content
	 */
	public function get_the_content( $content ) {
		$content = $this->replace_user_property( $content );
		$content = $this->replace_post_property( $content );
		return $content;
	}

	/**
	 * ユーザーがログイン中の場合、{ユーザー情報のプロパティ}を置換する。
	 *
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
	 * {投稿情報（$post->hoge）}を置換する。
	 *
	 * @param string フォーム内容
	 * @return string フォーム内容
	 */
	protected function replace_post_property( $content ) {
		$Setting = $this->Setting;
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
		$Setting = $this->Setting;
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
	 * ファイルアップロードのname属性を hidden で出力
	 *
	 * @param array|string $upload_file_keys
	 */
	protected function get_upload_file_hidden( $upload_file_keys ) {
		$Form = $this->Form;
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
	 * 下位互換性のための class を付与
	 *
	 * @return string mw_wp_form_preview
	 */
	protected function get_old_confirm_class() {
		$old_confirm_class = '';
		if ( $this->view_flg === 'confirm' ) {
			$old_confirm_class = 'mw_wp_form_preview';
		}
		return $old_confirm_class;
	}

	/**
	 * スタイル機能用の class を付与
	 *
	 * @return string
	 */
	protected function get_class_by_style() {
		$Setting = $this->Setting;
		$style   = $Setting->get( 'style' );
		$class_by_style = '';
		if ( $style ) {
			$class_by_style = 'mw_wp_form_' . $style;
		}
		return $class_by_style;
	}

	/**
	 * 完了後のメッセージ
	 *
	 * @param array $attributes
	 * @return string html
	 */
	public function mwform_complete_message( $attributes, $content = '' ) {
		$form_key = $this->get( 'key' );
		return sprintf(
			'<div id="mw_wp_form_%s" class="mw_wp_form mw_wp_form_%s">
				%s
			<!-- end .mw_wp_form --></div>',
			esc_attr( $form_key ),
			esc_attr( $this->view_flg ),
			$content
		);
	}
}
