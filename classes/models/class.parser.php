<?php
/**
 * @package mw-wp-form
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * MW_WP_Form_Parser
 */
class MW_WP_Form_Parser {

	/**
	 * @var string
	 */
	protected $form_key;

	/**
	 * @var MW_WP_Form_Setting
	 */
	protected $Setting;

	/**
	 * @var MW_WP_Form_Data
	 */
	protected $Data;

	/**
	 * @var string
	 */
	protected static $pattern = '/{(.+?)}/';

	/**
	 * Constructor.
	 *
	 * @param MW_WP_Form_Setting $Setting MW_WP_Form_Setting object.
	 */
	public function __construct( $Setting ) {
		$this->Setting  = $Setting;
		$form_id        = $Setting->get( 'post_id' );
		$this->form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$this->Data     = MW_WP_Form_Data::connect( $this->form_key );
	}

	/**
	 * Replace {name} for mail destination.
	 *
	 * @param string $value Value.
	 * @return string
	 */
	public function replace_for_mail_destination( $value ) {
		return $this->_replace( $value, array( $this, '_replace_for_mail_destination_callback' ) );
	}

	/**
	 * Callback for replace_for_mail_destination.
	 *
	 * @param array $matches $matches of preg_replace_callback.
	 * @return string|null
	 */
	protected function _replace_for_mail_destination_callback( $matches ) {
		$match = $matches[1];
		$value = MW_WP_Form_Parser::apply_filters_mwform_custom_mail_tag( $this->form_key, null, $match );

		// Return blank when custom mail tag isn't use(= null)
		if ( is_null( $value ) ) {
			return '';
		}
		return $value;
	}

	/**
	 * Replace {name} for mail content.
	 *
	 * @param string $value Value.
	 * @return string
	 */
	public function replace_for_mail_content( $value ) {
		return $this->_replace( $value, array( $this, '_replace_for_mail_content_callback' ) );
	}

	/**
	 * Callback for replace_for_mail_content.
	 *
	 * @param array $matches $matches of preg_replace_callback.
	 * @return string|null
	 */
	protected function _replace_for_mail_content_callback( $matches ) {
		$match = $matches[1];
		return $this->parse( $match );
	}

	/**
	 * Replace {name} for input and confirm page.
	 *
	 * @param string $value Value.
	 * @return string
	 */
	public function replace_for_page( $value ) {
		$value = $this->_replace_user_property( $value );
		$value = $this->_replace_post_property( $value );
		return $value;
	}

	/**
	 * Replace {foo} in the form. e.g. $post->foo.
	 *
	 * @param string $value Value.
	 * @return string
	 */
	protected function _replace_post_property( $value ) {
		if ( $this->Setting->get( 'querystring' ) ) {
			$callback = array( $this, '_get_post_property_from_querystring' );
		} else {
			$callback = array( $this, '_get_post_property_from_this' );
		}

		return $this->_replace( $value, $callback );
	}

	/**
	 * Callback from preg_replace_callback when enabled querystring setting.
	 *
	 * @param array $matches $matches of preg_replace_callback.
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
	 * Callback from preg_replace_callback when disabled querystring setting.
	 *
	 * @param array $matches $matches of preg_replace_callback.
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
	 * Get WP_Post property.
	 *
	 * @param WP_Post|null $post     WP_Post object.
	 * @param string       $meta_key Meta data name.
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
	 * Replace {property of user} when logged in.
	 *
	 * @param string $content Post content.
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
			$content = str_replace(
				$search,
				array(
					$user->get( 'ID' ),
					$user->get( 'user_login' ),
					$user->get( 'user_email' ),
					$user->get( 'user_url' ),
					$user->get( 'user_registered' ),
					$user->get( 'display_name' ),
				),
				$content
			);
		} else {
			$content = str_replace( $search, '', $content );
		}

		return $content;
	}

	/**
	 * Search {$name}.
	 *
	 * @param string $value   Value.
	 * @return array
	 */
	public static function search( $value ) {
		preg_match_all(
			self::$pattern,
			$value,
			$matches
		);

		return $matches;
	}

	/**
	 * そのキーについて送信された値を返す.
	 *
	 * @param string $name Posted field name.
	 * @return string
	 */
	public function parse( $name ) {
		$form_id = $this->Setting->get( 'post_id' );

		// MWF_Config::TRACKINGNUMBER のときはお問い合せ番号を参照する
		if ( MWF_Config::TRACKINGNUMBER === $name ) {
			if ( $form_id ) {
				return $this->Setting->get_tracking_number( $form_id );
			}
		}

		// @see https://github.com/inc2734/mw-wp-form/issues/99
		if ( MWF_Config::TRACKINGNUMBER . '_for_complete_page' === $name ) {
			if ( $form_id ) {
				return $this->Setting->get_tracking_number( $form_id ) - 1;
			}
		}

		$value = $this->Data->get( $name );
		$value = MW_WP_Form_Parser::apply_filters_mwform_custom_mail_tag(
			$this->Data->get_form_key(),
			$value,
			$name,
			$this->Data->get_saved_mail_id()
		);
		return $value;
	}

	/**
	 * Apply mwform_custom_mail_tag filter hook.
	 *
	 * @param string      $form_key      Form key.
	 * @param string|null $value         Value.
	 * @param string      $name          Custom mail tag name.
	 * @param string      $saved_mail_id Saved mail ID.
	 * @return string
	 */
	public static function apply_filters_mwform_custom_mail_tag( $form_key, $value, $name, $saved_mail_id = null ) {
		$value = apply_filters(
			'mwform_custom_mail_tag',
			$value,
			$name,
			$saved_mail_id
		);

		$value = apply_filters(
			'mwform_custom_mail_tag_' . $form_key,
			$value,
			$name,
			$saved_mail_id
		);
		return $value;
	}

	/**
	 * Replace {name} for mail content.
	 *
	 * @param string   $value    Value.
	 * @param function $callback Callback of preg_replace_callback.
	 * @return string
	 */
	protected static function _replace( $value, $callback ) {
		return preg_replace_callback(
			self::$pattern,
			$callback,
			$value
		);
	}
}
