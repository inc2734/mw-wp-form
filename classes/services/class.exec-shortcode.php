<?php
/**
 * @package mw-wp-form
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * MW_WP_Form_Exec_Shortcode
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
	 * @var MW_WP_Form_Data
	 */
	protected $Data;

	/**
	 * @var string
	 */
	protected $view_flg;

	/**
	 * @var MW_WP_Form_Setting
	 */
	protected $Setting;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_shortcode( 'mwform', array( $this, '_mwform' ) );
		add_shortcode( 'mwform_complete_message', array( $this, '_mwform_complete_message' ) );

		add_filter( 'mwform_form_end_html', array( $this, '_mwform_form_end_html' ) );

		add_action( 'wp_footer', array( $this, '_enqueue_scripts' ) );
	}

	/**
	 * Add shortcode for [mwform_formkey]
	 *
	 * @example [mwform_formkey key="post_id"]
	 *
	 * @param array $attributes Attributes of [mwform_formkey].
	 * @return string
	 */
	public function initialize( $attributes ) {
		$this->form_id  = $this->_get_form_id_by_mwform_formkey( $attributes );
		$this->form_key = MWF_Functions::get_form_key_from_form_id( $this->form_id );

		/**
		 * @deprecated since v4.0.0
		 * Because refactoring changed the timing to execute the shortcode
		 */
		do_action( 'mwform_after_exec_shortcode', $this->form_key );

		do_action( 'mwform_start_main_process', $this->form_key );

		$this->Data     = MW_WP_Form_Data::connect( $this->form_key );
		$this->view_flg = ( $this->Data->get_view_flg() ) ? $this->Data->get_view_flg() : 'input';
		$this->Setting  = new MW_WP_Form_Setting( $this->form_id );

		add_action( 'wp_footer', array( $this->Data, 'clear_values' ) );

		$Validation = new MW_WP_Form_Validation( $this->form_key );
		$is_valid   = $Validation->is_valid();

		$Redirected = new MW_WP_Form_Redirected( $this->form_key, $this->Setting, $is_valid, $this->Data->get_post_condition() );
		if ( $Redirected->get_request_uri() !== $Redirected->get_url() && $Redirected->get_url() ) {
			$Redirected->redirect_js();
		}

		do_action( 'mwform_before_load_content_' . $this->form_key );

		if ( $this->_is_direct_access() ) {
			$content = $this->_get_direct_access_error_page_content();
		} elseif ( $this->Data->get_send_error() ) {
			$content = $this->_get_send_error_page_content();
		} elseif ( 'input' === $this->view_flg ) {
			$content = $this->_get_input_page_content();
		} elseif ( 'confirm' === $this->view_flg ) {
			$content = $this->_get_confirm_page_content();
		} elseif ( 'complete' === $this->view_flg ) {
			$content = $this->_get_complete_page_content();
		} else {
			$content = '';
		}

		do_action( 'mwform_after_load_content_' . $this->form_key );

		// Enqueue scroll to MW WP Form script
		if ( $this->Setting->get( 'scroll' ) ) {
			if (
				'input' !== $this->view_flg
				|| in_array( $this->Data->get_post_condition(), array( 'back', 'confirm', 'complete' ), true )
			) {
				add_action( 'wp_footer', array( $this, '_enqueue_scroll_script' ) );
			}
		}

		$Form_Fields = MW_WP_Form_Form_Fields::instantiation( $this->form_key );
		foreach ( $Form_Fields->get_form_fields() as $form_field ) {
			$form_field->initialize( new MW_WP_Form_Form(), $this->form_key, $this->view_flg );
		}

		return do_shortcode( $content );
	}

	/**
	 * Add shortcode for [mwform].
	 *
	 * @param array  $attributes Attributes of [mwform].
	 * @param string $content    Content of [mwform].
	 * @return string
	 */
	public function _mwform( $attributes, $content = '' ) {
		$Form = new MW_WP_Form_Form();

		if ( in_array( $this->view_flg, array( 'input', 'confirm' ), true ) ) {
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
	 * Add shortcode for [mwform_complete_message].
	 *
	 * @param array  $attributes Attributes of [mwform_complete_message].
	 * @param string $content    Content of [mwform_complete_message].
	 * @return string
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
	 * Display input page.
	 *
	 * @return string
	 */
	protected function _get_input_page_content() {
		global $post;
		$post = get_post( $this->form_id );
		setup_postdata( $post );
		// @todo 共通化 main._file_upload()
		$content = apply_filters( 'mwform_post_content_raw_' . $this->form_key, get_the_content(), $this->Data );
		$content = $this->_wpautop( $content );
		$content = apply_filters( 'mwform_post_content_' . $this->form_key, $content, $this->Data );
		$content = sprintf( '[mwform]%s[/mwform]', $content );
		wp_reset_postdata();
		return $content;
	}

	/**
	 * Display confirm page.
	 *
	 * @return string
	 */
	protected function _get_confirm_page_content() {
		return $this->_get_input_page_content();
	}

	/**
	 * Display complete page.
	 *
	 * @return string
	 */
	protected function _get_complete_page_content() {
		$Parser = new MW_WP_Form_Parser( $this->Setting );

		$content = apply_filters(
			'mwform_complete_content_raw_' . $this->form_key,
			$this->Setting->get( 'complete_message' ),
			$this->Data
		);

		$content = str_replace( '{' . MWF_Config::TRACKINGNUMBER . '}', '{' . MWF_Config::TRACKINGNUMBER . '_for_complete_page}', $content );
		$content = $this->_wpautop( $content );
		$content = $Parser->replace_for_mail_content( $content );
		$content = apply_filters( 'mwform_complete_content_' . $this->form_key, $content, $this->Data );

		$content = sprintf(
			'[mwform_complete_message]%s[/mwform_complete_message]',
			$content
		);
		return $content;
	}

	/**
	 * Display validation error page.
	 *
	 * @return string
	 */
	protected function _get_send_error_page_content() {
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
	 * Display direct access error page
	 *
	 * @return string $content
	 */
	protected function _get_direct_access_error_page_content() {
		$content = sprintf(
			'<div id="mw_wp_form_%s" class="mw_wp_form mw_wp_form_direct_access_error">
				%s
			<!-- end .mw_wp_form --></div>',
			esc_attr( $this->form_key ),
			__( 'You can not access this page directly.', 'mw-wp-form' )
		);
		$content = apply_filters( 'mwform_direct_access_error_content_raw_' . $this->form_key, $content, $this->Data );
		$content = $this->_wpautop( $content );
		$content = apply_filters( 'mwform_direct_access_error_content_' . $this->form_key, $content, $this->Data );
		return $content;
	}

	/**
	 * Return true when direct access to confirm or complete or validation error page.
	 *
	 * @return bool
	 */
	protected function _is_direct_access() {
		if ( 'input' !== $this->view_flg ) {
			return false;
		}

		$confirm  = $this->Setting->get( 'confirmation_url' );
		$complete = $this->Setting->get( 'complete_url' );
		$error    = $this->Setting->get( 'validation_error_url' );

		if ( ! $confirm && ! $complete && ! $error ) {
			return false;
		}

		$Validation = new MW_WP_Form_Validation( $this->form_key );
		$is_valid   = $Validation->is_valid();

		$Redirected = new MW_WP_Form_Redirected( $this->form_key, $this->Setting, $is_valid, $this->Data->get_post_condition() );
		if ( $Redirected->get_request_uri() === $Redirected->get_url() ) {
			return false;
		}

		return true;
	}

	/**
	 * Line breaks content according to wpautop().
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	protected function _wpautop( $content ) {
		$has_wpautop = false;

		if ( has_filter( 'the_content', '_restore_wpautop_hook' ) ) {
			$has_wpautop = true;
		} elseif ( has_filter( 'the_content', 'wpautop' ) ) {
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
	 * Replace {key} in the form.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public function _get_the_content( $content ) {
		$Parser  = new MW_WP_Form_Parser( $this->Setting );
		$content = $Parser->replace_for_page( $content );
		return $content;
	}

	/**
	 * Hidden field for file upload name attribute.
	 *
	 * @param array|string $upload_file_keys Upload file keys.
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
	 * Get classes for backward compatibility.
	 *
	 * @return string
	 */
	protected function _get_old_confirm_class() {
		if ( 'confirm' === $this->view_flg ) {
			return 'mw_wp_form_preview';
		}
	}

	/**
	 * Get classes for style feature.
	 *
	 * @return string
	 */
	protected function _get_class_by_style() {
		$style = $this->Setting->get( 'style' );
		if ( $style ) {
			return 'mw_wp_form_' . $style;
		}
	}

	/**
	 * ショートコード mwform_formkey をもとにフォームの ID を取得.
	 *
	 * @param array $attributes Attributes of mwform_formkey.
	 * @return string
	 */
	protected function _get_form_id_by_mwform_formkey( $attributes ) {
		$attributes = shortcode_atts(
			array(
				'key'  => '',
				'slug' => '',
			),
			$attributes
		);

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
	 * Add nonce field and form meta data.
	 *
	 * @param string $html HTML.
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

		$html .= sprintf(
			'<input type="hidden" name="%1$s" value="%2$s" />',
			esc_attr( MWF_Config::NAME . '-form-verify-token' ),
			esc_attr( $this->Setting->generate_form_verify_token() )
		);
		return $html;
	}

	/**
	 * Enqueue MW WP Form assets
	 *
	 * @return void
	 */
	public function _enqueue_scripts() {
		if ( wp_style_is( MWF_Config::NAME ) ) {
			return;
		}

		MWF_Functions::mwform_enqueue_scripts( $this->form_id );
	}

	/**
	 * Enqueue scroll to form script
	 *
	 * @return void
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
		wp_localize_script(
			MWF_Config::NAME . '-scroll',
			'mwform_scroll',
			array(
				'offset' => apply_filters( 'mwform_scroll_offset_' . $this->form_key, 0 ),
			)
		);
		wp_enqueue_script( MWF_Config::NAME . '-scroll' );
	}
}
