<button type="submit"
	name="<?php echo esc_attr( $name ); ?>"
	value="<?php echo esc_attr( $value ); ?>"
	<?php echo MWF_Functions::generate_input_attribute( 'class', $class ); ?>
><?php echo wp_kses_post( $element_content ); ?></button>
