<?php
/**
 * Name       : MW WP Form Field Tel
 * Version    : 2.0.0
 * Author     : Takashi Kitajima
 * Author URI : https://2inc.org
 * Created    : December 14, 2012
 * Modified   : May 30, 2017
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Field_Tel extends MW_WP_Form_Abstract_Form_Field {

	/**
	 * Types of form type.
	 * input|select|button|input_button|error|other
	 * @var string
	 */
	public $type = 'input';

	/**
	 * Set shortcode_name and display_name
	 * Overwrite required for each child class
	 *
	 * @return array(shortcode_name, display_name)
	 */
	protected function set_names() {
		return array(
			'shortcode_name' => 'mwform_tel',
			'display_name'   => __( 'Tel', 'mw-wp-form' ),
		);
	}

	/**
	 * Set default attributes
	 *
	 * @return array defaults
	 */
	protected function set_defaults() {
		return array(
			'name'       => '',
			'class'      => null,
			'value'      => '',
			'show_error' => 'true',
			'conv_half_alphanumeric' => 'true',
		);
	}

	/**
	 * Callback of add shortcode for input page
	 *
	 * @param array $atts
	 * @param string $element_content
	 * @return string HTML
	 */
	protected function input_page() {
		$value = $this->Data->get_raw( $this->atts['name'] );
		if ( is_array( $value ) && isset( $value['data'] ) ) {
			$value = $value['data'];
		}
		if ( is_null( $value ) ) {
			$value = $this->atts['value'];
		}
		$conv_half_alphanumeric = 'true';
		if ( 'true' !== $this->atts['conv_half_alphanumeric'] ) {
			$conv_half_alphanumeric = null;
		}

		$_ret = $this->Form->tel( $this->atts['name'], array(
			'class' => $this->atts['class'],
			'conv-half-alphanumeric' => $conv_half_alphanumeric,
			'value' => $value,
		) );
		if ( 'false' !== $this->atts['show_error'] ) {
			$_ret .= $this->get_error( $this->atts['name'] );
		}
		return $_ret;
	}

	/**
	 * Callback of add shortcode for confirm page
	 *
	 * @param array $atts
	 * @param string $element_content
	 * @return string HTML
	 */
	protected function confirm_page() {
		$value     = $this->Data->get( $this->atts['name'] );
		$separator = $this->Data->get_separator_value( $this->atts['name'] );
		$_ret  = esc_html( $value );
		$_ret .= $this->Form->hidden( $this->atts['name'] . '[data]', $value );
		$_ret .= $this->Form->separator( $this->atts['name'], $separator );
		return $_ret;
	}

	/**
	 * Display tag generator dialog
	 * Overwrite required for each child class
	 *
	 * @param array $options
	 * @return void
	 */
	public function mwform_tag_generator_dialog( array $options = array() ) {
		?>
		<p>
			<strong>name</strong>
			<?php $name = $this->get_value_for_generator( 'name', $options ); ?>
			<input type="text" name="name" value="<?php echo esc_attr( $name ); ?>" />
		</p>
		<p>
			<strong>class</strong>
			<?php $class = $this->get_value_for_generator( 'class', $options ); ?>
			<input type="text" name="class" value="<?php echo esc_attr( $class ); ?>" />
		</p>
		<p>
			<strong><?php esc_html_e( 'Display error', 'mw-wp-form' ); ?></strong>
			<?php $show_error = $this->get_value_for_generator( 'show_error', $options ); ?>
			<label><input type="checkbox" name="show_error" value="false" <?php checked( 'false', $show_error ); ?> /> <?php esc_html_e( 'Don\'t display error.', 'mw-wp-form' ); ?></label>
		</p>
		<p>
			<strong><?php esc_html_e( 'Convert half alphanumeric', 'mw-wp-form' ); ?></strong>
			<?php $conv_half_alphanumeric = $this->get_value_for_generator( 'conv_half_alphanumeric', $options ); ?>
			<label><input type="checkbox" name="conv_half_alphanumeric" value="false" <?php checked( 'false', $conv_half_alphanumeric ); ?> /> <?php esc_html_e( 'Don\'t Convert.', 'mw-wp-form' ); ?></label>
		</p>
		<?php
	}

	/**
	 * This form field is for Japanese environments only
	 *
	 * @param array $validation_rules array of MW_WP_Form_Abstract_Form_Field
	 * @return array $form_fields
	 */
	public function _mwform_form_fields( array $form_fields ) {
		$form_fields = parent::_mwform_form_fields( $form_fields );

		if ( 'ja' === get_locale() ) {
			return $form_fields;
		}

		unset( $form_fields[ $this->get_shortcode_name() ] );
		return $form_fields;
	}
}
