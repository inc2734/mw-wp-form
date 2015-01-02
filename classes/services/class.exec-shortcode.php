<?php
/**
 * Name       : MW WP Form Exec Shortcode
 * Version    : 1.0.0
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : December 31, 2014
 * Modified   : 
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
	 * @var WP_Post
	 */
	protected $post;

	/**
	 * $template
	 * @var string 表示中のテンプレート
	 */
	protected $template;

	/**
	 * $defaults
	 * @var array
	 */
	protected $defaults = array(
		'input_url'            => null,
		'confirmation_url'     => null,
		'complete_url'         => null,
		'validation_error_url' => null,
		'key'                  => '',
	);

	/**
	 * $settings
	 * @var array
	 */
	protected $settings = array(
		'input'            => null,
		'confirm'          => null,
		'complete'         => null,
		'validation_error' => null,
		'key'              => '',
	);

	/**
	 * __construct
	 * @param WP_Post $post
	 * @param string $template 使用テンプレートのパス
	 */
	public function __construct( WP_Post $post, $template ) {
		$this->post     = $post;
		$this->template = $template;

		add_shortcode( 'mwform'        , array( $this, 'set_settings_by_mwform' ) );
		add_shortcode( 'mwform_formkey', array( $this, 'set_settings_by_mwform_formkey' ) );
		do_shortcode( $this->post->post_content );

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
		if ( is_null( $this->settings['key'] ) ||
			 is_null( $this->settings['input'] ) ||
			 is_null( $this->settings['confirm'] ) ||
			 is_null( $this->settings['complete'] ) ||
			 is_null( $this->settings['validation_error'] ) ) {

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
		if ( is_singular() && !empty( $this->post->ID ) ) {
			$exec_shortcode = $this->get_in_contnt( $this->post->post_content );
		}
		if ( empty( $exec_shortcode ) &&
			 !( defined( 'MWFORM_NOT_USE_TEMPLATE' ) && MWFORM_NOT_USE_TEMPLATE === true ) ) {
			$template_data  = @file_get_contents( $this->template );
			$exec_shortcode = $this->get_in_contnt( $template_data );
		}
		return $exec_shortcode;
	}

	/**
	 * get_in_contnt
	 * ExecShortcode が含まれていればそのショートコードを返す
	 * @param string $content
	 * @return string [hoge xxx="xxx"]
	 */
	protected function get_in_contnt( $content ) {
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
	 * @param array $attributes
	 */
	public function set_settings_by_mwform( array $attributes ) {
		$settings = array();
		foreach ( $this->defaults as $key => $value ) {
			$settings[$key] = '';
		}
		$settings['key'] = 'mwform';
		if ( isset( $attributes['input'] ) ) {
			$settings['input_url'] = $attributes['input'];
		}
		if ( isset( $attributes['confirm'] ) ) {
			$settings['confirmation_url'] = $attributes['confirm'];
		}
		if ( isset( $attributes['complete'] ) ) {
			$settings['complete_url'] = $attributes['complete'];
		}
		if ( isset( $attributes['validation_error'] ) ) {
			$settings['validation_error_url'] = $attributes['validation_error'];
		}
		$this->set_settings( $settings );
	}

	/**
	 * set_settings_by_mwform_formkey
	 * @param array $attributes
	 */
	public function set_settings_by_mwform_formkey( $attributes ) {
		$post_id       = $this->get_form_id_by_mwform_formkey( $attributes );
		$this->post_id = $post_id;
		$settings      = array();
		if ( !empty( $post_id ) ) {
			$Setting = new MW_WP_Form_Setting( $post_id );
			foreach ( $this->defaults as $key => $value ) {
				$settings[$key] = $Setting->get( $key );
			}
			$settings['key'] = MWF_Config::NAME . '-' . $post_id;
		}
		$this->set_settings( $settings );
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
	 * set_settings
	 * @param array $attributes
	 */
	protected function set_settings( array $attributes ) {
		if ( isset( $attributes['key'] ) ) {
			$this->settings['key'] = $attributes['key'];
		}
		if ( isset( $attributes['input_url'] ) ) {
			$this->settings['input'] = $attributes['input_url'];
		}
		if ( isset( $attributes['confirmation_url'] ) ) {
			$this->settings['confirm'] = $attributes['confirmation_url'];
		}
		if ( isset( $attributes['complete_url'] ) ) {
			$this->settings['complete'] = $attributes['complete_url'];
		}
		if ( isset( $attributes['validation_error_url'] ) ) {
			$this->settings['validation_error'] = $attributes['validation_error_url'];
		}
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
	 * get_form_id
	 */
	public function get_form_id() {
		if ( $this->post_id ) {
			return $this->post_id;
		}
	}
}