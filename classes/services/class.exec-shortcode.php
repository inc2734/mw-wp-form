<?php
/**
 * Name       : MW WP Form Exec Shortcode
 * Version    : 2.0.0
 * Author     : Takashi Kitajima
 * Author URI : https://2inc.org
 * Created    : December 31, 2014
 * Modified   : May 17, 2017
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Exec_Shortcode {

	/**
	 * @var int
	 */
	protected $form_id;

	/**
	 * @var string
	 */
	protected $form_key;

	/**
	 * @param MW_WP_Form_Data
	 */
	protected $Data;

	/**
	 * @var string
	 */
	protected $view_flg;

	public function __construct() {
		add_shortcode( 'mwform'                 , array( $this, '_mwform' ) );
		add_shortcode( 'mwform_complete_message', array( $this, '_mwform_complete_message' ) );

		add_filter( 'mwform_form_end_html', array( $this, '_mwform_form_end_html' ) );

		add_action( 'wp_footer', array( $this, '_enqueue_scripts' ) );
	}

	/**
	 * Add shortcode for [mwform_formkey]
	 *
	 * @param array $attributes
	 * @return string html
	 * @example [mwform_formkey key="post_id"]
	 */
	public function initialize( $attributes ) {
		$this->form_id  = $this->_get_form_id_by_mwform_formkey( $attributes );
		$this->form_key = MWF_Functions::get_form_key_from_form_id( $this->form_id );
		$this->Data     = MW_WP_Form_Data::connect( $this->form_key );
		$this->view_flg = ( $this->Data->get_view_flg() ) ? $this->Data->get_view_flg() : 'input';
		add_action( 'wp_footer', array( $this->Data, 'clear_values' ) );

		do_action( 'mwform_before_load_content_' . $this->form_key );

		if ( $this->Data->get_send_error() ) {
			$content = $this->_get_send_error_page_content();
		} elseif ( $this->view_flg === 'input' ) {
			$content = $this->_get_input_page_content();
		} elseif ( $this->view_flg == 'confirm' ) {
			$content = $this->_get_confirm_page_content();
		} elseif ( $this->view_flg === 'complete' ) {
			$content = $this->_get_complete_page_content();
		} else {
			$content = '';
		}

		do_action( 'mwform_after_load_content_' .  $this->form_key );

		// Enqueue scroll to MW WP Form script
		$Setting = new MW_WP_Form_Setting( $this->form_id );
		if ( $Setting->get( 'scroll' ) ) {
			if ( 'input' !== $this->view_flg || in_array( $this->Data->get_post_condition(), array( 'back', 'confirm' ) ) ) {
				add_action( 'wp_footer', array( $this, '_enqueue_scroll_script' ) );
			}
		}

		$Form_Fields = MW_WP_Form_Form_Fields::instantiation();
		foreach ( $Form_Fields->get_form_fields() as $form_field ) {
			$form_field->initialize( new MW_WP_Form_Form(), $this->form_key, $this->view_flg );
		}

		return do_shortcode( $content );
	}

	/**
	 * Add shortcode for [mwform]
	 *
	 * @param null $attributes
	 * @return string html
	 */
	public function _mwform( $attributes, $content = '' ) {
		$Form = new MW_WP_Form_Form();

		if ( in_array( $this->view_flg, array( 'input', 'confirm' ) ) ) {
			$content            = $this->_get_the_content( $content );
			$upload_file_keys   = $this->Data->get_post_value_by_key( MWF_Config::UPLOAD_FILE_KEYS );
			$upload_file_hidden = $this->_get_upload_file_hidden( $upload_file_keys );
			$old_confirm_class  = $this->_get_old_confirm_class();
			$class_by_style     = $this->_get_class_by_style();

			return sprintf(
				'<div id="mw_wp_form_%s" class="mw_wp_form mw_wp_form_%s %s">
					%s
				<!-- end .mw_wp_form --></div>',
				esc_attr( $this->form_key ),
				esc_attr( $this->view_flg . ' ' . $old_confirm_class ),
				$class_by_style,
				$Form->start() . do_shortcode( $content ) . $upload_file_hidden . $Form->end()
			);
		}
	}

	/**
	 * Add shortcode for [mwform_complete_message]
	 *
	 * @param array $attributes
	 * @return string html
	 */
	public function _mwform_complete_message( $attributes, $content = '' ) {
		return sprintf(
			'<div id="mw_wp_form_%s" class="mw_wp_form mw_wp_form_%s">
				%s
			<!-- end .mw_wp_form --></div>',
			esc_attr( $this->form_key ),
			esc_attr( $this->view_flg ),
			$content
		);
	}

	/**
	 * Display input page
	 *
	 * @return string $content
	 */
	protected function _get_input_page_content() {
		global $post;
		$post = get_post( $this->form_id );
		setup_postdata( $post );
		$content = apply_filters( 'mwform_post_content_raw_' . $this->form_key, get_the_content(), $this->Data );
		$content = $this->_wpautop( $content );
		$content = sprintf(
			'[mwform]%s[/mwform]',
			apply_filters( 'mwform_post_content_' . $this->form_key, $content, $this->Data )
		);
		wp_reset_postdata();
		return $content;
	}

	/**
	 * Display confirm page
	 *
	 * @return string $content
	 */
	protected function _get_confirm_page_content( ) {
		return $this->_get_input_page_content();
	}

	/**
	 * Display complete page
	 *
	 * @return string $content
	 */
	protected function _get_complete_page_content() {
		$Setting = new MW_WP_Form_Setting( $this->form_id );
		$content = apply_filters(
			'mwform_complete_content_raw_' . $this->form_key,
			$Setting->get( 'complete_message' ),
			$this->Data
		);
		$content = $this->_wpautop( $content );
		$content = sprintf(
			'[mwform_complete_message]%s[/mwform_complete_message]',
			apply_filters( 'mwform_complete_content_' . $this->form_key, $content, $this->Data )
		);
		return $content;
	}

	/**
	 * Display validation error page
	 *
	 * @return string $content
	 */
	public function _get_send_error_page_content() {
		$content = sprintf(
			'<div id="mw_wp_form_%s" class="mw_wp_form mw_wp_form_send_error">
				%s
			<!-- end .mw_wp_form --></div>',
			esc_attr( $this->form_key ),
			__( 'There was an error trying to send your message. Please try again later.', 'mw-wp-form' )
		);
		$content = apply_filters( 'mwform_send_error_content_raw_' . $this->form_key, $content, $this->Data );
		$content = $this->_wpautop( $content );
		$content = apply_filters( 'mwform_send_error_content_' . $this->form_key, $content, $this->Data );
		return $content;
	}

	/**
	 * Line breaks content according to wpautop()
	 *
	 * @param string $content
	 * @return string
	 */
	protected function _wpautop( $content ) {
		$has_wpautop = false;
		if ( has_filter( 'the_content', 'wpautop' ) ) {
			$has_wpautop = true;
		}

		$has_wpautop = apply_filters(
			'mwform_content_wpautop_' . $this->form_key,
			$has_wpautop,
			$this->view_flg
		);

		if ( $has_wpautop ) {
			$content = wpautop( $content );
		}

		return $content;
	}

	/**
	 * Replace {key} in the form
	 *
	 * @param string $content
	 * @return string
	 */
	public function _get_the_content( $content ) {
		$content = $this->_replace_user_property( $content );
		$content = $this->_replace_post_property( $content );
		return $content;
	}

	/**
	 * Replace {property of user} when logged in
	 *
	 * @param string $content
	 * @return string
	 */
	protected function _replace_user_property( $content ) {
		$user   = wp_get_current_user();
		$search = array(
			'{user_id}',
			'{user_login}',
			'{user_email}',
			'{user_url}',
			'{user_registered}',
			'{display_name}',
		);

		if ( ! empty( $user ) ) {
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
	 * Replace {foo} in the form. e.g. $post->foo
	 *
	 * @param string $content
	 * @return string
	 */
	protected function _replace_post_property( $content ) {
		$Setting = new MW_WP_Form_Setting( $this->form_id );
		if ( $Setting->get( 'querystring' ) ) {
			$content = preg_replace_callback(
				'/{(.+?)}/',
				array( $this, '_get_post_property_from_querystring' ),
				$content
			);
		} else {
			$content = preg_replace_callback(
				'/{(.+?)}/',
				array( $this, '_get_post_property_from_this' ),
				$content
			);
		}
		return $content;
	}

	/**
	 * Callback from preg_replace_callback when enabled querystring setting
	 *
	 * @param array $matches
	 * @return string|null
	 */
	protected function _get_post_property_from_querystring( $matches ) {
		if ( ! isset( $_GET['post_id'] ) || ! MWF_Functions::is_numeric( $_GET['post_id'] ) ) {
			return;
		}

		$post = get_post( $_GET['post_id'] );
		if ( empty( $post->ID ) ) {
			return;
		}

		return $this->_get_post_property( $post, $matches[1] );
	}

	/**
	 * Callback from preg_replace_callback when disabled querystring setting
	 *
	 * @param array $matches
	 * @return string|null
	 */
	protected function _get_post_property_from_this( $matches ) {
		global $post;

		if ( ! is_singular() ) {
			return;
		}

		if ( empty( $post->ID ) ) {
			return;
		}

		return $this->_get_post_property( $post, $matches[1] );
	}

	/**
	 * Get WP_Post property
	 *
	 * @param WP_Post|null $post
	 * @param string $meta_key
	 * @return string|null
	 */
	protected function _get_post_property( $post, $meta_key ) {
		if ( ! is_a( $post, 'WP_Post' ) ) {
			return;
		}

		if ( isset( $post->$meta_key ) ) {
			return $post->$meta_key;
		}

		$post_meta = get_post_meta( $post->ID, $meta_key, true );
		if ( is_array( $post_meta ) ) {
			return;
		}

		return $post_meta;
	}

	/**
	 * Hidden field for file upload name attribute
	 *
	 * @param array|string $upload_file_keys
	 */
	protected function _get_upload_file_hidden( $upload_file_keys ) {
		$Form = new MW_WP_Form_Form();

		if ( ! is_array( $upload_file_keys ) ) {
			return;
		}

		$upload_file_hidden = '';
		foreach ( $upload_file_keys as $value ) {
			$upload_file_hidden .= $Form->hidden( MWF_Config::UPLOAD_FILE_KEYS . '[]', $value );
		}

		return $upload_file_hidden;
	}

	/**
	 * Get classes for backward compatibility
	 *
	 * @return string mw_wp_form_preview
	 */
	protected function _get_old_confirm_class() {
		if ( 'confirm' === $this->view_flg ) {
			return 'mw_wp_form_preview';
		}
	}

	/**
	 * Get classes for style feature
	 *
	 * @return string
	 */
	protected function _get_class_by_style() {
		$Setting = new MW_WP_Form_Setting( $this->form_id );
		$style   = $Setting->get( 'style' );

		if ( $style ) {
			return 'mw_wp_form_' . $style;
		}
	}

	/**
	 * ショートコード mwform_formkey をもとにフォームの ID を取得
	 *
	 * @param array $attributes
	 * @return string Post ID
	 */
	protected function _get_form_id_by_mwform_formkey( $attributes ) {
		$attributes = shortcode_atts( array(
			'key'  => '',
			'slug' => '',
		), $attributes );

		if ( ! empty( $attributes['slug'] ) ) {
			$post = get_page_by_path( $attributes['slug'], OBJECT, MWF_Config::NAME );
		} elseif ( ! empty( $attributes['key'] ) ) {
			$post = get_post( $attributes['key'] );
		}

		if ( ! empty( $post ) && isset( $post->ID ) ) {
			return $post->ID;
		}
	}

	/**
	 * Add nonce field and form meta data
	 *
	 * @param string $html
	 * @return string
	 */
	public function _mwform_form_end_html( $html ) {
		if ( ! $this->form_key ) {
			return $html;
		}

		$html .= wp_nonce_field( $this->form_key, MWF_Config::TOKEN_NAME, true, false );
		$html .= sprintf(
			'<input type="hidden" name="%1$s" value="%2$s" />',
			esc_attr( MWF_Config::NAME . '-form-id' ),
			esc_attr( $this->form_id )
		);

		$Setting = new MW_WP_Form_Setting( $this->form_id );
		$html .= sprintf(
			'<input type="hidden" name="%1$s" value="%2$s" />',
			esc_attr( MWF_Config::NAME . '-form-verify-token' ),
			esc_attr( $Setting->generate_form_verify_token() )
		);
		return $html;
	}

	/**
	 * Enqueue MW WP Form assets
	 */
	public function _enqueue_scripts() {
		global $post;

		$url = plugin_dir_url( __FILE__ );
		wp_enqueue_style( MWF_Config::NAME, $url . '../../css/style.css' );

		$Setting = new MW_WP_Form_Setting( $this->form_id );
		$style  = $Setting->get( 'style' );
		$styles = apply_filters( 'mwform_styles', array() );
		if ( is_array( $styles ) && isset( $styles[ $style ] ) ) {
			$css = $styles[ $style ];
			wp_enqueue_style( MWF_Config::NAME . '_style', $css );
		}

		do_action( 'mwform_enqueue_scripts_' . $this->form_key );
		wp_enqueue_script( MWF_Config::NAME, $url . '../../js/form.js', array( 'jquery' ), false, true );
	}

	/**
	 * Enqueue scroll to form script
	 */
	public function _enqueue_scroll_script() {
		$url = plugin_dir_url( __FILE__ );
		wp_register_script(
			MWF_Config::NAME . '-scroll',
			$url . '../../js/scroll.js',
			array( 'jquery' ),
			false,
			true
		);
		wp_localize_script( MWF_Config::NAME . '-scroll', 'mwform_scroll', array(
			'offset' => apply_filters( 'mwform_scroll_offset_' . $this->form_key, 0 ),
		) );
		wp_enqueue_script( MWF_Config::NAME . '-scroll' );
	}
}
