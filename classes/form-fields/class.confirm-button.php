<?php
/**
 * @package mw-wp-form
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * MW_WP_Form_Field_Confirm_Button
 */
class MW_WP_Form_Field_Confirm_Button extends MW_WP_Form_Abstract_Form_Field {

	/**
	 * Types of form type.
	 * input|select|button|input_button|error|other.
	 *
	 * @var string
	 */
	public $type = 'input_button';

	/**
	 * Set shortcode_name and display_name.
	 * Overwrite required for each child class.
	 *
	 * @return array
	 */
	protected function set_names() {
		return array(
			'shortcode_name' => 'mwform_confirmButton',
			'display_name'   => __( 'Confirm Button', 'mw-wp-form' ),
		);
	}

	/**
	 * Set default attributes.
	 *
	 * @return array
	 */
	protected function set_defaults() {
		return array(
			'class' => null,
			'value' => __( 'Confirm', 'mw-wp-form' ),
		);
	}

	/**
	 * Callback of add shortcode for input page.
	 *
	 * @return string
	 */
	protected function input_page() {
		return $this->Form->submit(
			MWF_Config::CONFIRM_BUTTON,
			$this->atts['value'],
			array(
				'class' => $this->atts['class'],
			)
		);
	}

	/**
	 * Callback of add shortcode for confirm page.
	 */
	protected function confirm_page() {
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
			<strong><?php esc_html_e( 'String on the button', 'mw-wp-form' ); ?></strong>
			<?php $name = $this->get_value_for_generator( 'name', $options ); ?>
			<input type="text" name="value" value="<?php echo esc_attr( $name ); ?>" />
		</p>
		<?php
	}
}
