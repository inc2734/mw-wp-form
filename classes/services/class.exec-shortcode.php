<?php
/**
 * Name       : MW WP Form Exec Shortcode
 * Version    : 1.0.1
 * Description: ExecShortcode（mwform、mwform_formkey）の存在有無のチェックとそれらの抽象化レイヤー
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : December 31, 2014
 * Modified   : January 14, 2015
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Exec_Shortcode {

	/**
	 * $post_id
	 * フォームの Post ID
	 * @var int
	 */
	protected $post_id;

	/**
	 * $post
	 * ショートコードが埋め込まれた投稿のオブジェクト
	 * @var WP_Post|null
	 */
	protected $post;

	/**
	 * $template
	 * @var string 表示中のテンプレート
	 */
	protected $template;

	/**
	 * $settings
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
	 * __construct
	 * @param WP_Post|null $_post
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
	 * has_shortcode
	 * 必要な設定が完了していたらtrue
	 * @return bool
	 */
	public function has_shortcode() {
		if ( is_null( $this->settings['key'] ) ) {
			return false;
		}
		return true;
	}

	/**
	 * get
	 */
	public function get( $key ) {
		if ( isset( $this->settings[$key] ) ) {
			return $this->settings[$key];
		}
	}

	/**
	 * get_exec_shortcode
	 * ExecShortcode が含まれていればそのショートコードを返す
	 * @return string [hoge xxx="xxx"]
	 */
	protected function get_exec_shortcode() {
		$exec_shortcode = '';
		if ( is_singular() && !empty( $this->post->ID ) ) {
			$exec_shortcode = $this->get_in_content( $this->post->post_content );
		}
		if ( empty( $exec_shortcode ) &&
			 !( defined( 'MWFORM_NOT_USE_TEMPLATE' ) && MWFORM_NOT_USE_TEMPLATE === true ) ) {
			$response = wp_remote_get( $this->template );
			if ( !is_wp_error( $response ) && $response['response']['code'] === 200 ) {
				$template_data  = $response['body'];
				$exec_shortcode = $this->get_in_content( $template_data );
			}
		}
		return $exec_shortcode;
	}

	/**
	 * get_in_content
	 * ExecShortcode が含まれていればそのショートコードを返す
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
					if ( is_array( $shortcode ) && !empty( $shortcode[0] ) ) {
						return $shortcode[0];
					}
				}
			}
		}
	}

	/**
	 * set_settings_by_mwform
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
	 * set_settings_by_mwform_formkey
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
			$settings['key'] = MWF_Config::NAME . '-' . $post_id;
		}
		$this->set_settings( $settings );
	}

	/**
	 * get_form_id_by_mwform_formkey
	 * @param array|'' $attributes
	 * @return string|null Post ID
	 */
	protected function get_form_id_by_mwform_formkey( $attributes ) {
		$attributes = shortcode_atts( array(
			'key' => '',
		), $attributes );
		$post = get_post( $attributes['key'] );
		if ( isset( $post->ID ) ) {
			return $post->ID;
		}
	}

	/**
	 * set_settings
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
	 * is_generated_by_formkey
	 * 管理画面で作成されたフォームであればtrue
	 * @return bool
	 */
	public function is_generated_by_formkey() {
		if ( $this->post_id ) {
			return true;
		}
		return false;
	}

	/**
	 * get_form_id
	 */
	public function get_form_id() {
		if ( $this->post_id ) {
			return $this->post_id;
		}
	}
}