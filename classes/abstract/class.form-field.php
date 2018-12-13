<?php
/**
 * Name       : MW WP Form Abstract Form Field
 * Version    : 2.0.0
 * Author     : Takashi Kitajima
 * Author URI : https://2inc.org
 * Created    : December 14, 2012
 * Modified   : May 30, 2017
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
abstract class MW_WP_Form_Abstract_Form_Field {

	/**
	 * Shortcode name
	 * @var string
	 */
	protected $shortcode_name;

	/**
	 * Display name
	 * @var string
	 */
	protected $display_name;

	/**
	 * @var MW_WP_Form_Form
	 */
	protected $Form;

	/**
	 * @var MW_WP_Form_Data
	 */
	protected $Data;

	/**
	 * Default attributes
	 * @var array
	 */
	protected $defaults = array();

	/**
	 * Attirbutes of shortcode
	 * @var array
	 */
	protected $atts = array();

	/**
	 * Form key
	 * @var string
	 */
	protected $form_key;

	/**
	 * Types of form type.
	 * input|select|button|input_button|error|other
	 * @var string
	 */
	protected $type = 'other';

	/**
	 * Content of shortcode
	 * @var string
	 */
	protected $element_content = null;

	public function __construct() {
		$this->_set_names();
		$this->_set_defaults();
		add_filter( 'mwform_form_fields'  , array( $this, '_mwform_form_fields' ) );
	}

	/**
	 * Add form field shortcodes
	 *
	 * @param MW_WP_Form_Form $Form
	 * @param string $form_key
	 * @param string $view_flg
	 * @return bool
	 */
	public function initialize( MW_WP_Form_Form $Form, $form_key, $view_flg ) {
		if ( empty( $this->shortcode_name ) ) {
			return false;
		}

		$this->Form     = $Form;
		$this->form_key = $form_key;
		$this->Data     = MW_WP_Form_Data::connect( $form_key );

		switch( $view_flg ) {
			case 'input' :
				add_shortcode( $this->shortcode_name, array( $this, '_input_page' ) );
				break;
			case 'confirm' :
				add_shortcode( $this->shortcode_name, array( $this, '_confirm_page' ) );
				break;
			case 'complete' :
				break;
			default :
				exit( '$view_flg is not right value. $view_flg is ' . $view_flg . ' now.' );
		}

		return true;
	}

	/**
	 * Set shortcode_name and display_name
	 * Overwrite required for each child class
	 *
	 * @return array(shortcode_name, display_name)
	 */
	abstract protected function set_names();
	private function _set_names() {
		$args = $this->set_names();

		if ( empty( $args['shortcode_name'] ) || empty( $args['display_name'] ) ) {
			exit( get_class() . '::set_names() returns not right values. Returned values is ' . serialize( $args ) . ' now.' );
		}

		$this->shortcode_name = $args['shortcode_name'];
		$this->display_name   = $args['display_name'];
	}

	/**
	 * Set default attributes
	 *
	 * @return array defaults
	 */
	abstract protected function set_defaults();
	private function _set_defaults() {
		$this->defaults = $this->set_defaults();
	}

	/**
	 * Return HTML of error message
	 *
	 * @param  string $name
	 * @return string HTML of error message
	 */
	protected function get_error( $name ) {
		if ( ! is_array( $this->Data->get_validation_error( $name ) ) ) {
			return;
		}

		$error_html = '';
		$start_tag  = '<span class="error">';
		$end_tag    = '</span>';
		foreach ( $this->Data->get_validation_error( $name ) as $rule => $error ) {
			$rule = strtolower( $rule );
			$error = apply_filters(
				'mwform_error_message_' . $this->form_key,
				$error,
				$name,
				$rule
			);

			$error_html .= apply_filters( 'mwform_error_message_html',
				$start_tag . esc_html( $error ) . $end_tag,
				$error,
				$start_tag,
				$end_tag,
				$this->form_key,
				$name,
				$rule
			);
		}

		if ( $error_html ) {
			return apply_filters( 'mwform_error_message_wrapper', $error_html, $this->form_key );
		}
	}

	/**
	 * Callback of add shortcode for input page
	 *
	 * @param array $atts
	 * @param string $element_content
	 * @return string HTML
	 */
	abstract protected function input_page();
	public function _input_page( $atts, $element_content = null ) {
		$this->element_content = $element_content;

		if ( array_key_exists( 'value', $this->defaults ) && isset( $atts['name'] ) && ! isset( $atts['value'] ) ) {
			$atts['value'] = apply_filters(
				'mwform_value_' . $this->form_key,
				$this->defaults['value'],
				$atts['name']
			);
		}
		$this->atts = shortcode_atts( $this->defaults, $atts );

		return $this->input_page();
	}

	/**
	 * Callback of add shortcode for confirm page
	 *
	 * @param array $atts
	 * @param string $element_content
	 * @return string HTML
	 */
	abstract protected function confirm_page();
	public function _confirm_page( $atts, $element_content = null ) {
		$this->element_content = $element_content;
		$this->atts = shortcode_atts( $this->defaults, $atts );

		return $this->confirm_page();
	}

	/**
	 * Return array for children of select, checkbox and radio
	 * If including ":", Split and use the before as the name and the after as the label
	 *
	 * @param string|array $_children
	 * @return array
	 */
	public function get_children( $_children ) {
		$children = array();
		if ( ! empty( $_children ) && ! is_array( $_children ) ) {
			$_children = explode( ',', $_children );
		}

		if ( is_array( $_children ) ) {
			foreach ( $_children as $child ) {
				$temp_replacement = '@-[_-_]-@';
				if ( preg_match( '/(^:[^:])|([^:]:[^:])/', $child ) ) {
					$child = str_replace( '::', $temp_replacement, $child );
					$child = explode( ':', $child, 2 );
				} else {
					$child = str_replace( '::', $temp_replacement, $child );
					$child = array( $child );
				}
				foreach ( $child as $child_key => $child_value ) {
					$child[ $child_key ] = str_replace( $temp_replacement, ':', $child_value );
				}
				if ( count( $child ) === 1 ) {
					$children[ $child[0] ] = $child[0] ;
				} else {
					$children[ $child[0] ] = $child[1];
				}
			}
		}

		if ( $this->form_key ) {
			$children = apply_filters( 'mwform_choices_' . $this->form_key, $children, $this->atts );
		}

		return $children;
	}

	/**
	 * Generate tag generator
	 *
	 * @return void
	 */
	public function add_tag_generator() {
		add_action( 'mwform_tag_generator_dialog', array( $this, '_mwform_tag_generator_dialog' ) );

		if ( 'other' !== $this->type ) {
			$tag = 'mwform_tag_generator_' . $this->type . '_option';
		} else {
			$tag = 'mwform_tag_generator_option';
		}

		add_action( $tag, array( $this, '_mwform_tag_generator_option' ) );
	}

	/**
	 * Display tag generator wrapper
	 *
	 * @return void
	 */
	public function _mwform_tag_generator_dialog() {
		?>
		<div id="dialog-<?php echo esc_attr( $this->shortcode_name ); ?>" class="mwform-dialog" title="<?php echo esc_attr( $this->shortcode_name ); ?>">
			<div class="form">
				<?php $this->mwform_tag_generator_dialog( $this->defaults ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Display tag generator dialog
	 * Overwrite required for each child class
	 *
	 * @param array $options
	 * @return void
	 */
	public function mwform_tag_generator_dialog( array $options = array() ) {}

	/**
	 * Display tag generator selectbox
	 *
	 * @return void
	 */
	public function _mwform_tag_generator_option() {
		?>
		<option value="<?php echo esc_attr( $this->shortcode_name ); ?>"><?php echo esc_html( $this->display_name ); ?></option>
		<?php
	}

	/**
	 * Generate array of form fields
	 *
	 * @param array $form_fields array of MW_WP_Form_Abstract_Form_Field
	 * @return array
	 */
	public function _mwform_form_fields( array $form_fields ) {
		return array_merge( $form_fields, array( $this->shortcode_name => $this ) );
	}

	/**
	 * Return display name
	 *
	 * @return string
	 */
	public function get_display_name() {
		return $this->display_name;
	}

	/**
	 * Return shortcode name
	 *
	 * @return string
	 */
	public function get_shortcode_name() {
		return $this->shortcode_name;
	}

	/**
	 * Return value for setting field of Tag generator dialog
	 *
	 * @param string $key
	 * @param array $options
	 * @return string
	 */
	public function get_value_for_generator( $key, $options ) {
		$attributes = array_keys( $this->defaults );
		$attributes = array_flip( $attributes );

		if ( ! isset( $attributes[ $key ] ) ) {
			return;
		}

		if ( isset( $options[ $key ] ) ) {
			return $options[ $key ];
		} else {
			return '';
		}
	}
}
