<?php
/**
 * @package mw-wp-form
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * MW_WP_Form_Field_Number
 */
class MW_WP_Form_Field_Number extends MW_WP_Form_Abstract_Form_Field {

	/**
	 * Types of form type.
	 * input|select|button|input_button|error|other.
	 *
	 * @var string
	 */
	public $type = 'input';

	/**
	 * Set shortcode_name and display_name.
	 * Overwrite required for each child class.
	 *
	 * @return array
	 */
	protected function set_names() {
		return array(
			'shortcode_name' => 'mwform_number',
			'display_name'   => __( 'Number', 'mw-wp-form' ),
		);
	}

	/**
	 * Set default attributes.
	 *
	 * @return array
	 */
	protected function set_defaults() {
		return array(
			'name'        => '',
			'id'          => null,
			'class'       => null,
			'value'       => '',
			'min'         => null,
			'max'         => null,
			'step'        => 1,
			'placeholder' => null,
			'show_error'  => 'true',
		);
	}

	/**
	 * Callback of add shortcode for input page.
	 *
	 * @return string
	 */
	protected function input_page() {
		$value = $this->Data->get_raw( $this->atts['name'] );
		if ( is_null( $value ) ) {
			$value = $this->atts['value'];
		}

		$_ret = $this->Form->number(
			$this->atts['name'],
			array(
				'id'          => $this->atts['id'],
				'class'       => $this->atts['class'],
				'value'       => $value,
				'min'         => $this->atts['min'],
				'max'         => $this->atts['max'],
				'step'        => $this->atts['step'],
				'placeholder' => $this->atts['placeholder'],
			)
		);
		if ( 'false' !== $this->atts['show_error'] ) {
			$_ret .= $this->get_error( $this->atts['name'] );
		}
		return $_ret;
	}

	/**
	 * Callback of add shortcode for confirm page.
	 *
	 * @return string
	 */
	protected function confirm_page() {
		$value = $this->Data->get_raw( $this->atts['name'] );
		$_ret  = esc_html( $value );
		$_ret .= $this->Form->hidden( $this->atts['name'], $value );
		return $_ret;
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
			<strong>name<span class="mwf_require">*</span></strong>
			<?php $name = $this->get_value_for_generator( 'name', $options ); ?>
			<input type="text" name="name" value="<?php echo esc_attr( $name ); ?>" />
		</p>
		<p>
			<strong>id</strong>
			<?php $id = $this->get_value_for_generator( 'id', $options ); ?>
			<input type="text" name="id" value="<?php echo esc_attr( $id ); ?>" />
		</p>
		<p>
			<strong>class</strong>
			<?php $class = $this->get_value_for_generator( 'class', $options ); ?>
			<input type="text" name="class" value="<?php echo esc_attr( $class ); ?>" />
		</p>
		<p>
			<strong>min</strong>
			<?php $min = $this->get_value_for_generator( 'min', $options ); ?>
			<input type="text" name="min" value="<?php echo esc_attr( $min ); ?>" />
		</p>
		<p>
			<strong>max</strong>
			<?php $max = $this->get_value_for_generator( 'max', $options ); ?>
			<input type="text" name="max" value="<?php echo esc_attr( $max ); ?>" />
		</p>
		<p>
			<strong>step</strong>
			<?php $step = $this->get_value_for_generator( 'step', $options ); ?>
			<input type="text" name="step" value="<?php echo esc_attr( $step ); ?>" />
		</p>
		<p>
			<strong>placeholder</strong>
			<?php $placeholder = $this->get_value_for_generator( 'placeholder', $options ); ?>
			<input type="text" name="placeholder" value="<?php echo esc_attr( $placeholder ); ?>" />
		</p>
		<p>
			<strong><?php esc_html_e( 'Display error', 'mw-wp-form' ); ?></strong>
			<?php $show_error = $this->get_value_for_generator( 'show_error', $options ); ?>
			<label><input type="checkbox" name="show_error" value="false" <?php checked( 'false', $show_error ); ?> /> <?php esc_html_e( 'Don\'t display error.', 'mw-wp-form' ); ?></label>
		</p>
		<?php
	}
}
