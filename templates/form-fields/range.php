<input type="range"
	name="<?php echo esc_attr( $name ); ?>"
	<?php echo MWF_Functions::generate_input_attribute( 'id', $id ); ?>
	<?php echo MWF_Functions::generate_input_attribute( 'class', $class ); ?>
	<?php echo MWF_Functions::generate_input_attribute( 'value', $value ); ?>
	<?php echo MWF_Functions::generate_input_attribute( 'min', $min ); ?>
	<?php echo MWF_Functions::generate_input_attribute( 'max', $max ); ?>
	<?php echo MWF_Functions::generate_input_attribute( 'step', $step ); ?>
/>
