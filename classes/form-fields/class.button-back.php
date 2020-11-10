<?php
/**
 * @package mw-wp-form
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * MW_WP_Form_Field_Button_Back
 */
class MW_WP_Form_Field_Button_Back extends MW_WP_Form_Abstract_Form_Field {

	/**
	 * Types of form type.
	 * input|select|button|input_button|error|other.
	 *
	 * @var string
	 */
	public $type = 'button';

	/**
	 * Set shortcode_name and display_name.
	 * Overwrite required for each child class.
	 *
	 * @return array
	 */
	protected function set_names() {
		return array(
			'shortcode_name' => 'mwform_bback',
			'display_name'   => __( 'Back Button', 'mw-wp-form' ),
		);
	}

	/**
	 * Set default attributes.
	 *
	 * @return array
	 */
	protected function set_defaults() {
		return array(
			'class'           => null,
			'value'           => 'back',
			'element_content' => __( 'Back', 'mw-wp-form' ),
		);
	}

	/**
	 * Callback of add shortcode for input page.
	 */
	protected function input_page() {
	}

	/**
	 * Callback of add shortcode for confirm page.
	 *
	 * @return string
	 */
	protected function confirm_page() {
		return $this->Form->button_submit(
			MWF_Config::BACK_BUTTON,
			$this->atts['value'],
			array(
				'class' => $this->atts['class'],
			),
			$this->element_content
		);
	}

	/**
	 * Display tag generator dialog.
	 * Overwrite required for each child class.
	 *
	 * @param array $options Options.
	 */
	public function mwform_tag_generator_dialog( array $options = array() ) {
		?>
		<p>
			<strong>class</strong>
			<?php $class = $this->get_value_for_generator( 'class', $options ); ?>
			<input type="text" name="class" value="<?php echo esc_attr( $class ); ?>" />
		</p>
		<p>
			<strong><?php esc_html_e( 'Value', 'mw-wp-form' ); ?></strong>
			<?php $value = $this->get_value_for_generator( 'value', $options ); ?>
			<input type="text" name="value" value="<?php echo esc_attr( $value ); ?>" />
		</p>
		<p>
			<strong><?php esc_html_e( 'String on the button', 'mw-wp-form' ); ?></strong>
			<?php $element_content = $this->get_value_for_generator( 'element_content', $options ); ?>
			<input type="text" name="element_content" value="<?php echo esc_attr( $element_content ); ?>" />
		</p>
		<?php
	}
}
