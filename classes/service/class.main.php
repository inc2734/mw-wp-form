<?php
class MW_WP_Form_Main_Service extends MW_WP_Form_Service {

	/**
	 * $key
	 * フォームを識別するためのキー
	 * @example mw-wp-form-88
	 */
	protected $key = null;

	/**
	 * $input
	 * 入力画面のURL
	 * @example 空値、もしくはURL
	 */
	protected $input = null;

	/**
	 * $confirm
	 * 確認画面のURL
	 * @example 空値、もしくはURL
	 */
	protected $confirm = null;

	/**
	 * $complete
	 * 完了画面のURL
	 * @example 空値、もしくはURL
	 */
	protected $complete = null;

	/**
	 * $validation_error
	 * バリデーションエラー画面のURL
	 * @example 空値、もしくはURL
	 */
	protected $validation_error = null;

	/**
	 * $viewFlg
	 * どの画面を表示するべきかを示すフラグ
	 * @example input, confirm, complete
	 */
	protected $viewFlg = 'input';

	/**
	 * $insert_contact_data_id
	 * 保存した問い合わせデータの Post ID
	 */
	protected $insert_contact_data_id;

	/**
	 * $settings
	 * form_key をもとに取得した設定値
	 */
	protected $settings = array();

	/**
	 * Data
	 * データを管理するためのオブジェクト
	 */
	protected $Data;

	/**
	 * Form
	 * フォームを管理するためのオブジェクト
	 */
	protected $Form;

	/**
	 * Validation
	 * バリデーションを管理するためのオブジェクト
	 */
	protected $Validation;

	/**
	 * File
	 * ファイル操作を管理するためのオブジェクト
	 */
	protected $File;

	/**
	 * initialize
	 * key、各URLを設定
	 * __construct にしたほうが良いかも…
	 * @param WP_Post $post
	 * @param string $template 使用テンプレートのパス
	 */
	public function initialize( $post, $template ) {
		add_shortcode( 'mwform'        , array( $this, 'set_form_meta_by_mwform' ) );
		add_shortcode( 'mwform_formkey', array( $this, 'set_form_meta_by_mwform_formkey' ) );
		do_shortcode( $post->post_content );

		$shortcode = $this->get_shortcode( $post, $template );
		if ( $shortcode ) {
			// ここで set_form_meta_by_mwform(), set_form_meta_by_mwform_formkey() が実行される
			do_shortcode( $shortcode );
		}

		remove_shortcode( 'mwform' );
		remove_shortcode( 'mwform_formkey' );
	}

	/**
	 * init_request_data
	 * @param array $POST $_POSTを想定
	 * @param array $FILES $_FILESを想定
	 */
	public function init_request_data( array $POST, array $FILES ) {
		$this->set_request_data( $POST );
		$this->set_files_data( $POST, $FILES );
	}

	/**
	 * set_request_data
	 * @param array $POST $_POSTを想定
	 */
	protected function set_request_data( array $POST ) {
		$this->Data = MW_WP_Form_Data::getInstance( $this->get_key() );
		if ( !empty( $POST ) ) {
			$this->Data->setValues( stripslashes_deep( $POST ) );
		}
	}

	/**
	 * set_files_data
	 * @param array $POST $_POSTを想定
	 * @param array $FILES $_FILESを想定
	 */
	protected function set_files_data( array $POST, array $FILES ) {
		$files = array();
		foreach ( $FILES as $key => $file ) {
			if ( !isset( $POST[$key] ) || !empty( $file['name'] ) ) {
				if ( $file['error'] == UPLOAD_ERR_OK && is_uploaded_file( $file['tmp_name'] ) ) {
					$this->Data->setValue( $key, $file['name'] );
				} else {
					$this->Data->setValue( $key, '' );
				}
				if ( !empty( $file['name'] ) ) {
					$files[$key] = $file;
				}
			}
		}
		// この条件判定がないと fileSize チェックが正しく動作しない
		if ( $files ) {
			$this->Data->setValue( MWF_Config::UPLOAD_FILES, $files );
		}
	}

	/**
	 * init_validation
	 * @param MW_Validation $Validation
	 */
	public function set_validation_object( MW_Validation $Validation ) {
		$this->Validation = $Validation;
		foreach ( $this->validation_rules as $validation_name => $instance ) {
			if ( is_callable( array( $instance, 'rule' ) ) ) {
				$this->Validation->add_validation_rule( $instance->getName(), $instance );
			}
		}
	}

	/**
	 * validate
	 * バリデーション用フィルタ。フィルタの実行結果としてValidationオブジェクトが返ってこなければエラー
	 * 各バリデーションメソッドの詳細は /system/mw_validation.php を参照
	 */
	public function validate() {
		$validations = $this->get_option( 'validation' );
		if ( $validations ) {
			foreach ( $validations as $validation ) {
				foreach ( $validation as $key => $value ) {
					if ( $key == 'target' ) {
						continue;
					}
					if ( is_array( $value ) ) {
						$this->Validation->setRule( $validation['target'], $key, $value );
					} else {
						$this->Validation->setRule( $validation['target'], $key );
					}
				}
			}
		}

		$Akismet = new MW_Akismet();
		$akismet_check = $Akismet->check(
			$this->get_option( 'akismet_author' ),
			$this->get_option( 'akismet_author_email' ),
			$this->get_option( 'akismet_author_url' ),
			$this->Data
		);
		if ( $akismet_check ) {
			$this->Validation->setRule( MWF_Config::AKISMET, 'akismet_check' );
		}

		$this->Validation = apply_filters(
			'mwform_validation_' . $this->get_key(),
			$this->Validation,
			$this->Data->getValues()
		);
		if ( !is_a( $this->Validation, 'MW_Validation' ) ) {
			exit( esc_html__( 'Validation Object is not a MW Validation Class.', MWF_Config::DOMAIN ) );
		}
	}

	/**
	 * set_form_object
	 * @param MW_Form $Form
	 */
	public function set_form_object( MW_Form $Form ) {
		$this->Form = $Form;
	}

	/**
	 * set_file_object
	 * @param MW_WP_Form_File $File
	 */
	public function set_file_object( MW_WP_Form_File $File ) {
		$this->File = $File;
		$this->File->initialize();
	}

	/**
	 * set_form_meta_by_mwform
	 * @param array $attributes
	 */
	public function set_form_meta_by_mwform( array $attributes ) {
		$attributes = $this->get_form_meta_by_mwform( $attributes );
		$this->set_form_meta( $attributes );
	}

	/**
	 * set_form_meta_by_mwform_formkey
	 * @param array $attributes
	 */
	public function set_form_meta_by_mwform_formkey( array $attributes ) {
		$settings = $this->get_form_meta_by_mwform_formkey( $attributes );
		if ( $settings ) {
			$post_id = $this->get_form_id_by_mwform_formkey( $attributes );
			$this->settings = $settings;
			$this->settings['post_id'] = $post_id;
			$settings['key']           = MWF_Config::NAME . '-' . $post_id;
			$this->set_form_meta( $settings );
		}
	}

	/**
	 * set_form_meta
	 * @param array $attributes
	 */
	private function set_form_meta( array $attributes ) {
		if ( isset( $attributes['key'] ) ) {
			$this->key = $attributes['key'];
		}
		if ( isset( $attributes['input_url'] ) ) {
			$this->input = $this->parse_url( $attributes['input_url'] );
		}
		if ( isset( $attributes['confirmation_url'] ) ) {
			$this->confirm = $this->parse_url( $attributes['confirmation_url'] );
		}
		if ( isset( $attributes['complete_url'] ) ) {
			$this->complete = $this->parse_url( $attributes['complete_url'] );
		}
		if ( isset( $attributes['validation_error_url'] ) ) {
			$this->validation_error = $this->parse_url( $attributes['validation_error_url'] );
		}
	}

	/**
	 * get_form_meta_by_mwform
	 * @param array $attributes
	 * @return array $attributes
	 */
	protected function get_form_meta_by_mwform( array $attributes ) {
		return shortcode_atts( array(
			'input'            => '',
			'confirm'          => '',
			'complete'         => '',
			'validation_error' => '',
			'key'              => 'mwform'
		), $attributes );
	}

	/**
	 * get_form_meta_by_mwform_formkey
	 * @param array $attributes
	 * @return array $settings
	 */
	protected function get_form_meta_by_mwform_formkey( $attributes ) {
		$post_id = $this->get_form_id_by_mwform_formkey( $attributes );
		$settings = array();
		if ( !empty( $post_id ) ) {
			$Admin = new MW_WP_Form_Admin_page();
			$settings = $Admin->get_settings( $post_id );
		}
		return $settings;
	}

	/**
	 * get_form_id_by_mwform_formkey
	 * @param array $attributes
	 * @return string|null Post ID
	 */
	protected function get_form_id_by_mwform_formkey( array $attributes ) {
		$attributes = shortcode_atts( array(
			'key' => '',
		), $attributes );
		$post = get_post( $attributes['key'] );
		if ( isset( $post->ID ) ) {
			return $post->ID;
		}
	}

	/**
	 * get_shortcode
	 * MW WP Form のショートコードが含まれていればそのショートコードを返す
	 * @param WP_Post $post
	 * @param string $template
	 * @return string [hoge xxx="xxx"]
	 */
	protected function get_shortcode( $post, $template ) {
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
	protected function get_shortcode_in_contnt( $content ) {
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
	 * is_initialized
	 * 必要な設定が完了していたらtrue
	 * @return bool
	 */
	public function is_initialized() {
		if ( is_null( $this->key ) ||
			 is_null( $this->input ) ||
			 is_null( $this->confirm ) ||
			 is_null( $this->complete ) ||
			 is_null( $this->validation_error ) ) {

			return false;
		}
		return true;
	}

	/**
	 * get_key
	 * @return string $this->key
	 */
	public function get_key() {
		return $this->key;
	}

	/**
	 * get_input
	 * @return string $this->input
	 */
	public function get_input() {
		return $this->parse_url( $this->input );
	}

	/**
	 * get_confirm
	 * @return string $this->confirm
	 */
	public function get_confirm() {
		return $this->parse_url( $this->confirm );
	}

	/**
	 * get_complete
	 * @return string $this->complete
	 */
	public function get_complete() {
		return $this->parse_url( $this->complete );
	}

	/**
	 * get_coget_validation_errormplete
	 * @return string $this->validation_error
	 */
	public function get_validation_error() {
		return $this->parse_url( $this->validation_error );
	}

	/**
	 * get_post_condition
	 * 送信データからどのページを表示すべきかの状態を判定して返す
	 * ただし実際に表示するページと同じとは限らない（バリデーション通らないとかあるので）
	 * @return string back|confirm|complete|input
	 */
	public function get_post_condition() {
		if ( $this->Form->isBack() ) {
			return 'back';
		} elseif ( $this->Form->isConfirm() ) {
			return 'confirm';
		} elseif ( $this->Form->isComplete() ) {
			return 'complete';
		}
		return 'input';
	}

	/**
	 * is_valid
	 * バリデーションエラーがなければtrue
	 * @return bool
	 */
	public function is_valid() {
		if ( $this->Validation->check() ) {
			return true;
		}
		return false;
	}

	/**
	 * set_view_flg
	 * 実際に表示するページを判別するためのフラグを設定
	 * @param string $view_flg input|confirm|complete
	 */
	public function set_view_flg( $view_flg ) {
		if ( in_array( $view_flg, array( 'input', 'confirm', 'complete' ) ) ) {
			$this->viewFlg = $view_flg;
		}
	}

	/**
	 * get_view_flg
	 * 実際に表示するページを判別するためのフラグを返す
	 * @return string $this->view_flg input|confirm|complete
	 */
	public function get_view_flg() {
		return $this->viewFlg;
	}

	/**
	 * get_token
	 * @return string フォームのトークン
	 */
	public function get_token() {
		$this->Data->getValue( $this->Form->getTokenName() );
	}

	/**
	 * clear_token
	 * フォームのトークンを消去
	 */
	public function clear_token() {
		$this->Data->clearValue( $this->Form->getTokenName() );
	}

	/**
	 * is_generated_by_formkey
	 * 管理画面で作成されたフォームであればtrue
	 * @return bool
	 */
	public function is_generated_by_formkey() {
		if ( $this->settings ) {
			return true;
		}
		return false;
	}

	/**
	 * clear_data
	 * 保持していたリクエストデータを全て消去
	 */
	public function clear_data() {
		$this->Data->clearValues();
	}

	/**
	 * get_option
	 * 管理画面で作成したフォームの設定値を返す
	 * @param string $key
	 * @return mixed
	 */
	public function get_option( $key ) {
		if ( isset( $this->settings[$key] ) ) {
			return $this->settings[$key];
		}
	}
	
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
			if ( $key == 'token' ) {
				continue;
			}
			if ( isset( $query_vars[$key] ) && $query_vars[$key] === $value && !empty( $value ) ) {
				$query_vars[$key] = '';
			}
		}
		return $query_vars;
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
	 * add_shortocde_that_display_content
	 * 画面にフォーム、完了画面を表示するためのショートコードを有効化
	 */
	public function add_shortocde_that_display_content() {
		add_shortcode( 'mwform_formkey'         , array( $this, 'mwform_formkey' ) );
		add_shortcode( 'mwform'                 , array( $this, 'mwform' ) );
		add_shortcode( 'mwform_complete_message', array( $this, 'mwform_complete_message' ) );
	}

	/**
	 * mwform_formkey
	 * 管理画面で作成したフォームを出力（実際の出力は mwform ）
	 * @param array $attributes
	 * @return string html
	 * @example [mwform_formkey key="post_id"]
	 */
	public function mwform_formkey( $attributes ) {
		$post_id = $this->get_form_id_by_mwform_formkey( $attributes );

		$view_flg = $this->get_view_flg();

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
		$post = get_post( $post_id );
		setup_postdata( $post );
		$content = apply_filters( 'mwform_post_content_raw_' . $this->get_key(), get_the_content() );
		if ( has_filter( 'the_content', 'wpautop' ) ) {
			$content = wpautop( $content );
		}
		$content = sprintf(
			'[mwform]%s[/mwform]',
			apply_filters( 'mwform_post_content_' . $this->get_key(), $content )
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
		$content = $this->get_option( 'complete_message' );
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
		$view_flg = $this->get_view_flg();
		if ( $view_flg === 'input' || $view_flg == 'confirm' ) {

			do_action(
				'mwform_add_shortcode',
				$this->Form,
				$view_flg ,
				$this->Validation->Error(),
				$this->get_key()
			);

			$content = $this->get_the_content( $content );

			$upload_file_keys = $this->Form->getValue( MWF_Config::UPLOAD_FILE_KEYS );
			$upload_file_hidden = '';
			if ( is_array( $upload_file_keys ) ) {
				foreach ( $upload_file_keys as $value ) {
					$upload_file_hidden .= $this->Form->hidden( MWF_Config::UPLOAD_FILE_KEYS . '[]', $value );
				}
			}

			// 下位互換性のための class を付与
			$_preview_class = '';
			if ( $view_flg === 'confirm' ) {
				$_preview_class = 'mw_wp_form_preview';
			}

			// スタイル機能用の class を付与
			$style = $this->get_option( 'style' );
			$class_for_style = '';
			if ( $style ) {
				$class_for_style = 'mw_wp_form_' . $style;
			}

			return sprintf(
				'<div id="mw_wp_form_%s" class="mw_wp_form mw_wp_form_%s %s">%s<!-- end .mw_wp_form --></div>',
				esc_attr( $this->get_key() ),
				esc_attr( $view_flg . ' ' . $_preview_class ),
				$class_for_style,
				$this->Form->start() . do_shortcode( $content ) . $upload_file_hidden . $this->Form->end()
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
		if ( $this->get_view_flg() === 'complete' ) {
			return sprintf(
				'<div id="mw_wp_form_%s" class="mw_wp_form mw_wp_form_%s">%s<!-- end .mw_wp_form --></div>',
				esc_attr( $this->get_key() ),
				esc_attr( $this->get_view_flg() ),
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
		if ( $this->get_option( 'querystring' ) ) {
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
	 * get_post_property_from_querystring
	 * 引数 post_id が有効の場合、投稿情報を取得するために preg_replace_callback から呼び出される。
	 * @param array $matches
	 * @return string
	 */
	protected function get_post_property_from_querystring( $matches ) {
		if ( $this->get_option( 'querystring' ) &&
			 isset( $_GET['post_id'] ) &&
			 MWF_Functions::is_numeric( $_GET['post_id'] ) ) {

			$_post = get_post( $_GET['post_id'] );
			if ( empty( $_post->ID ) )
				return;
			if ( isset( $_post->$matches[1] ) ) {
				return $_post->$matches[1];
			} else {
				// post_meta の処理
				$pm = get_post_meta( $_post->ID, $matches[1], true );
				if ( !empty( $pm ) ) {
					return $pm;
				}
			}
		}
	}

	/**
	 * get_post_property_from_this
	 * 引数 post_id が無効の場合、投稿情報を取得するために preg_replace_callback から呼び出される。
	 * @param array $matches
	 * @return string
	 */
	protected function get_post_property_from_this( $matches ) {
		global $post;
		if ( !is_singular() )
			return;
		$post_id = get_the_ID();
		if ( isset( $post->ID ) && MWF_Functions::is_numeric( $post->ID ) ) {
			if ( isset( $post->$matches[1] ) ) {
				return $post->$matches[1];
			} else {
				// post_meta の処理
				$pm = get_post_meta( $post->ID, $matches[1], true );
				if ( !empty( $pm ) )
					return $pm;
			}
		}
	}

	/**
	 * parse_url
	 * http:// からはじまるURLに変換する
	 * @param string URL
	 * @return string URL
	 */
	protected function parse_url( $url ) {
		if ( empty( $url ) )
			return '';

		$query_string = array();
		preg_match( '/\?(.*)$/', $url, $reg );
		if ( !empty( $reg[1] ) ) {
			$url = str_replace( '?', '', $url );
			$url = str_replace( $reg[1], '', $url );
			parse_str( $reg[1], $query_string );
		}
		if ( !preg_match( '/^https?:\/\//', $url ) ) {
			$home_url = home_url();
			$url = $home_url . $url;
		}
		$url = preg_replace( '/([^:])\/+/', '$1/', $url );

		// URL設定でURL引数が使用されている場合はそれを使う。
		// 「URL引数を有効にする」が有効の場合は $_GET を利用する（重複するURL引数はURL設定のものが優先される ※post_id除く）
		if ( $this->get_option( 'querystring' ) ) {
			$query_string = array_merge( $_GET, $query_string );
			if ( isset( $_GET['post_id'] ) && MWF_Functions::is_numeric( $_GET['post_id'] ) ) {
				$query_string['post_id'] = $_GET['post_id'];
			}
		}

		if ( !empty( $query_string ) ) {
			$url = $url . '?' . http_build_query( $query_string, null, '&' );
		}
		return $url;
	}

	/**
	 * get_request_uri
	 * $_SERVER['REQUEST_URI'] を http:// からはじまるURLに変換する
	 * @return string URL
	 */
	public function get_request_uri() {
		$_REQUEST_URI = $_SERVER['REQUEST_URI'];
		if ( !preg_match( '/^https?:\/\//', $_REQUEST_URI ) ) {
			$REQUEST_URI = home_url() . $_REQUEST_URI;
			$parse_url = parse_url( home_url() );
			// サブディレクトリ型の場合
			if ( !empty( $parse_url['path'] ) ) {
				$pettern = preg_quote( $parse_url['path'], '/' );
				if ( preg_match( '/^' . $pettern . '/', $_REQUEST_URI ) ) {
					$REQUEST_URI = preg_replace( '/' . $pettern . '$/', $_REQUEST_URI, home_url() );
				}
			}
		} else {
			$REQUEST_URI = $_REQUEST_URI;
		}
		return $this->parse_url( $REQUEST_URI );
	}

	/**
	 * fileupload
	 * ファイルアップロード処理。実際のアップロード状況に合わせてフォームデータも再生成する。
	 */
	public function fileupload() {
		$uploadedFiles = array();
		$files = $this->Data->getValue( MWF_Config::UPLOAD_FILES );
		if ( !is_array( $files ) ) {
			$files = array();
		}
		foreach ( $files as $key => $file ) {
			if ( $this->Validation->singleCheck( $key ) ) {
				$uploadedFile = $this->File->singleFileupload( $key );
				if ( $uploadedFile ) {
					$uploadedFiles[$key] = $uploadedFile;
				}
			}
		}

		// 時間切れなどで削除されたファイルのキーを削除
		$upload_file_keys = $this->Data->getValue( MWF_Config::UPLOAD_FILE_KEYS );
		if ( !$upload_file_keys ) {
			$upload_file_keys = array();
		}

		$wp_upload_dir = wp_upload_dir();
		foreach ( $upload_file_keys as $upload_file_key ) {
			$upload_file_url = $this->Data->getValue( $upload_file_key );
			if ( $upload_file_url ) {
				$filepath = MWF_Functions::fileurl_to_path( $upload_file_url );
				if ( !file_exists( $filepath ) ) {
					unset( $upload_file_keys[$upload_file_key] );
				}
			}
		}
		$this->Data->setValue( MWF_Config::UPLOAD_FILE_KEYS, $upload_file_keys );

		// アップロードに成功したファイルをフォームデータに格納
		foreach ( $uploadedFiles as $key => $uploadfile ) {
			$this->Data->setValue( $key, $uploadfile );
			if ( !in_array( $key, $upload_file_keys ) ) {
				$this->Data->pushValue( MWF_Config::UPLOAD_FILE_KEYS, $key );
			}
		}
	}

	/**
	 * send
	 * メール送信
	 */
	public function send() {
		$Mail = new MW_Mail();
		$Mail_raw = clone $Mail;

		// 管理画面で作成した場合だけ自動で送信
		if ( $this->is_generated_by_formkey() ) {
			$attachments = $this->get_attachments();
			$attachment_files = array();
			foreach ( $attachments as $attachment ) {
				$attachment_files[$key] = $attachment['file'];
			}
			$attachment_urls = array();
			foreach ( $attachments as $attachment ) {
				$attachment_urls[$key] = $attachment['url'];
			}
			foreach ( $attachment_urls as $attachment_url ) {
				$this->Data->setValue( $key, $attachment_url );
			}

			$Mail_raw = $this->set_admin_mail_raw_params( $Mail_raw );
			$Mail_raw->attachments = $attachment_files;

			$Mail_raw = apply_filters(
				'mwform_admin_mail_raw_' . $this->get_key(),
				$Mail_raw,
				$this->Data->getValues()
			);
			if ( !is_a( $Mail_raw, 'MW_Mail' ) ) {
				return;
			}

			$Mail = $this->parse_mail_object( $Mail_raw );
			$Mail = $this->set_admin_mail_reaquire_params( $Mail );

			$Mail = apply_filters(
				'mwform_mail_' . $this->get_key(),
				$Mail,
				$this->Data->getValues()
			);
			if ( !is_a( $Mail, 'MW_Mail' ) ) {
				return;
			}

			// メール送信前にファイルのリネームをしないと、tempファイル名をメールで送信してしまう。
			if ( $this->get_option( 'usedb' ) ) {
				// save_mail_body で登録されないように
				foreach ( $attachments as $key => $attachment ) {
					$this->Data->clearValue( $key );
				}

				// $this->insert_contact_data_id を設定 ( save_mail_body で 使用 )
				$this->insert_contact_data_id = $this->save_contact_data( $attachment_files );
			}

			$Mail = apply_filters(
				'mwform_admin_mail_' . $this->get_key(),
				$Mail,
				$this->Data->getValues()
			);
			if ( !is_a( $Mail, 'MW_Mail' ) ) {
				return;
			}
			$Mail->send();

			// DB非保存時は管理者メール送信後、ファイルを削除
			if ( !$this->get_option( 'usedb' ) ) {
				$this->delete_files( $attachment_files );
			}

			// 自動返信メールの送信
			if ( $this->get_option( 'automatic_reply_email' ) ) {
				$automatic_reply_email = $this->Data->getValue( $this->get_option( 'automatic_reply_email' ) );
				if ( $automatic_reply_email &&
					 !$this->validation_rules['mail']->rule( $automatic_reply_email ) ) {

					$Mail_auto_raw = clone $Mail_raw;
					$Mail_auto_raw = $this->set_reply_mail_raw_params( $Mail_auto_raw );

					// 自動返信メールからは添付ファイルを削除
					$Mail_auto_raw->attachments = array();

					$Mail_auto_raw = apply_filters(
						'mwform_auto_mail_raw_' . $this->get_key(),
						$Mail_auto_raw,
						$this->Data->getValues()
					);
					if ( !is_a( $Mail_auto_raw, 'MW_Mail' ) ) {
						return;
					}

					$Mail_auto = $this->parse_mail_object( $Mail_auto_raw );
					$Mail_auto = $this->set_reply_mail_reaquire_params( $Mail_auto );

					$Mail_auto = apply_filters(
						'mwform_auto_mail_' . $this->get_key(),
						$Mail_auto,
						$this->Data->getValues()
					);
					if ( !is_a( $Mail_auto, 'MW_Mail' ) ) {
						return;
					}
					$Mail_auto->send();
				}
			}

			// 問い合わせ番号を加算
			if ( preg_match( '{' . MWF_Config::TRACKINGNUMBER . '}', $Mail_raw->body, $reg ) ) {
				$this->update_tracking_number();
			}
		}
		// 手動送信対応
		else {
			$Mail = apply_filters(
				'mwform_mail_' . $this->get_key(),
				$Mail,
				$this->Data->getValues()
			);
		}
	}

	/**
	 * delete_files
	 * @param array $attachments 消去するファイルパスの配列
	 */
	protected function delete_files( array $files ) {
		foreach ( $files as $file ) {
			if ( file_exists( $file ) ) {
				unlink( $file );
			}
		}
	}

	/**
	 * update_tracking_number
	 */
	protected function update_tracking_number() {
		$form_id = $this->get_option( 'post_id' );
		$tracking_number = $this->get_tracking_number( $form_id );
		$new_tracking_number = $tracking_number + 1;
		update_post_meta( $form_id, MWF_Config::TRACKINGNUMBER, $new_tracking_number );
	}

	/**
	 * get_attachments
	 * @return array $attachments path と url の配列
	 */
	protected function get_attachments() {
		// 添付ファイルのデータをためた配列を作成
		$attachments = array();
		$upload_file_keys = $this->Data->getValue( MWF_Config::UPLOAD_FILE_KEYS );
		if ( $upload_file_keys !== null && is_array( $upload_file_keys ) ) {
			$wp_upload_dir = wp_upload_dir();
			foreach ( $upload_file_keys as $key ) {
				$upload_file_url = $this->Data->getValue( $key );
				if ( !$upload_file_url ) {
					continue;
				}
				$filepath = MWF_Functions::fileurl_to_path( $upload_file_url );
				if ( file_exists( $filepath ) ) {
					$filepath            = $this->File->moveTempFileToUploadDir( $filepath );
					$new_upload_file_url = MWF_Functions::filepath_to_url( $filepath );
					$attachments[$key]   = array(
						'path' => $filepath,
						'url'  => $new_upload_file_url,
					);
				}
			}
		}
		return $attachments;
	}

	/**
	 * save_contact_data
	 * @param array $files 保存するファイルパスの配列
	 */
	protected function save_contact_data( array $files ) {
		$insert_contact_data_id = wp_insert_post( array(
			'post_title'  => $Mail->subject,
			'post_status' => 'publish',
			'post_type'   => MWF_Config::DBDATA . $this->get_option( 'post_id' ),
		) );
		// メタデータを保存
		$this->save_mail_body( $Mail_raw->body );

		// 添付ファイルをメディアに保存
		if ( !empty( $insert_contact_data_id ) ) {
			$this->File->saveAttachmentsInMedia(
				$insert_contact_data_id,
				$files,
				$this->get_option( 'post_id' )
			);
		}
		return $insert_contact_data_id;
	}

	/**
	 * parse_mail_object
	 * @param MW_Mail $obj
	 * @return MW_Mail $parsed_obj
	 */
	private function parse_mail_object( MW_Mail $obj ) {
		$parsed_obj = clone $obj;
		$parsed_obj_vars = get_object_vars( $parsed_obj );
		foreach ( $parsed_obj_vars as $key => $value ) {
			if ( is_array( $value ) || $key == 'to' || $key == 'cc' || $key == 'bcc' ) {
				continue;
			}
			$value = $this->parse_mail_content( $value );
			$parsed_obj->$key = $value;
		}
		return $parsed_obj;
	}

	/**
	 * parse_mail_content
	 * メール本文用に {name属性} を置換
	 * @param string $value
	 * @return string
	 */
	protected function parse_mail_content( $value ) {
		return preg_replace_callback(
			'/{(.+?)}/',
			array( $this, '_parse_mail_content' ),
			$value
		);
	}
	protected function _parse_mail_content( $matches ) {
		return $this->parse_mail_body( $matches, false );
	}

	/**
	 * save_mail_body
	 * DB保存用に {name属性} を置換、保存
	 */
	protected function save_mail_body( $value ) {
		return preg_replace_callback(
			'/{(.+?)}/',
			array( $this, '_save_mail_body' ),
			$value
		);
	}
	protected function _save_mail_body( $matches ) {
		return $this->parse_mail_body( $matches, true );
	}

	/**
	 * parse_mail_body
	 * $this->create_mail_body(), $this->save_mail_body の本体
	 * 第2引数でDB保存するか判定
	 * @param array $matches
	 * @param bool $doUpdate
	 * @return string $value
	 */
	protected function parse_mail_body( $matches, $doUpdate = false ) {
		$match = $matches[1];
		// MWF_Config::TRACKINGNUMBER のときはお問い合せ番号を参照する
		if ( $match === MWF_Config::TRACKINGNUMBER ) {
			if ( $this->get_option( 'post_id' ) ) {
				$form_id = $this->get_option( 'post_id' );
				$tracking_number_title = esc_html__( 'Tracking Number', MWF_Config::DOMAIN );
				$match = apply_filters(
					'mwform_tracking_number_title_' . $this->get_key(),
					$tracking_number_title
				);
				$value = $this->get_tracking_number( $form_id );
			}
		} else {
			$value = $this->Data->get( $match );
		}
		if ( $value !== null && $doUpdate ) {
			update_post_meta( $this->insert_contact_data_id, $match, $value );
		}
		return $value;
	}

	/**
	 * set_admin_mail_reaquire_params
	 * 管理者メールに必須の項目を設定
	 * @param MW_Mail $Mail
	 * @return MW_Mail $Mail
	 */
	private function set_admin_mail_reaquire_params( MW_Mail $Mail ) {
		$admin_mail_to     = get_bloginfo( 'admin_email' );
		$admin_mail_from   = get_bloginfo( 'admin_email' );
		$admin_mail_sender = get_bloginfo( 'name' );

		if ( !$Mail->to ) {
			$Mail->to = $admin_mail_to;
		}
		if ( !$Mail->from ) {
			$Mail->from = $admin_mail_from;;
		}
		if ( !$Mail->sender ) {
			$Mail->sender = $admin_mail_sender;;
		}
		return $Mail;
	}

	/**
	 * set_reply_mail_reaquire_params
	 * 自動返信メールに必須の項目を設定
	 * @param MW_Mail $Mail
	 * @return MW_Mail $Mail
	 */
	private function set_reply_mail_reaquire_params( MW_Mail $Mail ) {
		$reply_mail_from   = get_bloginfo( 'admin_email' );
		$reply_mail_sender = get_bloginfo( 'name' );

		if ( !$Mail->from ) {
			$Mail->from = $reply_mail_from;;
		}
		if ( !$Mail->sender ) {
			$Mail->sender = $reply_mail_sender;;
		}
		return $Mail;
	}

	/**
	 * set_admin_mail_raw_params
	 * 管理者メールに項目を設定
	 * @param MW_Mail $Mail
	 * @return MW_Mail $Mail
	 */
	private function set_admin_mail_raw_params( MW_Mail $Mail ) {
		if ( $this->is_generated_by_formkey() ) {
			// タイトルを指定
			$admin_mail_subject = $this->get_option( 'mail_subject' );
			if ( $this->get_option( 'admin_mail_subject' ) ) {
				$admin_mail_subject = $this->get_option( 'admin_mail_subject' );
			}
			$Mail->subject = $admin_mail_subject;

			// 本文を指定
			$admin_mail_content = $this->get_option( 'mail_content' );
			if ( $this->get_option( 'admin_mail_content' ) ) {
				$admin_mail_content = $this->get_option( 'admin_mail_content' );
			}
			$Mail->body = $admin_mail_content;

			// 送信先を指定
			$admin_mail_to = get_bloginfo( 'admin_email' );
			if ( $this->get_option( 'mail_to' ) ) {
				$admin_mail_to = $this->get_option( 'mail_to' );
			}
			$Mail->to = $admin_mail_to;

			// CCを指定
			$admin_mail_cc = '';
			if ( $this->get_option( 'mail_cc' ) ) {
				$admin_mail_cc = $this->get_option( 'mail_cc' );
			}
			$Mail->cc = $admin_mail_cc;

			// BCCを指定
			$admin_mail_bcc = '';
			if ( $this->get_option( 'mail_bcc' ) ) {
				$admin_mail_bcc = $this->get_option( 'mail_bcc' );
			}
			$Mail->bcc = $admin_mail_bcc;

			// 送信元を指定
			$admin_mail_from = get_bloginfo( 'admin_email' );
			if ( $this->get_option( 'admin_mail_from' ) ) {
				$admin_mail_from = $this->get_option( 'admin_mail_from' );
			}
			$Mail->from = $admin_mail_from;

			// 送信者を指定
			$admin_mail_sender = get_bloginfo( 'name' );
			if ( $this->get_option( 'admin_mail_sender' ) ) {
				$admin_mail_sender = $this->get_option( 'admin_mail_sender' );
			}
			$Mail->sender = $admin_mail_sender;
		}
		return $Mail;
	}

	/**
	 * set_reply_mail_raw_params
	 * 自動返信メールに項目を設定
	 * @param MW_Mail $Mail
	 * @return MW_Mail $Mail
	 */
	private function set_reply_mail_raw_params( MW_Mail $Mail ) {
		$Mail->to  = '';
		$Mail->cc  = '';
		$Mail->bcc = '';
		if ( $this->settings ) {
			$automatic_reply_email = $this->Data->getValue( $this->get_option( 'automatic_reply_email' ) );
			if ( $automatic_reply_email && !$this->validation_rules['mail']->rule( $automatic_reply_email ) ) {
				// 送信先を指定
				$Mail->to = $automatic_reply_email;

				// 送信元を指定
				$reply_mail_from = get_bloginfo( 'admin_email' );
				if ( $this->get_option( 'mail_from' ) ) {
					$reply_mail_from = $this->get_option( 'mail_from' );
				}
				$Mail->from = $reply_mail_from;

				// 送信者を指定
				$reply_mail_sender = get_bloginfo( 'name' );
				if ( $this->get_option( 'mail_sender' ) ) {
					$reply_mail_sender = $this->get_option( 'mail_sender' );
				}
				$Mail->sender = $reply_mail_sender;

				// タイトルを指定
				$reply_mail_subject = $this->get_option( 'mail_subject' );
				$Mail->subject = $reply_mail_subject;

				// 本文を指定
				$reply_mail_content = $this->get_option( 'mail_content' );
				$Mail->body = $reply_mail_content;
			}
		}
		return $Mail;
	}

	/**
	 * get_tracking_number
	 * @param int $post_id フォームの Post ID
	 * @return int $tracking_number
	 */
	protected function get_tracking_number( $post_id ) {
		$tracking_number = get_post_meta( $post_id, MWF_Config::TRACKINGNUMBER, true );
		if ( empty( $tracking_number ) ) {
			$tracking_number = 1;
		}
		return intval( $tracking_number );
	}
}