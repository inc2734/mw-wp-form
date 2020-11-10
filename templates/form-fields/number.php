<?php
/**
 * @package mw-wp-form
 * @author inc2734
 * @license GPL-2.0+
 */
?>

<input type="number"
	name="<?php echo esc_attr( $name ); ?>"
	<?php echo MWF_Functions::generate_input_attribute( 'id', $id ); ?>
	<?php echo MWF_Functions::generate_input_attribute( 'class', $class ); ?>
	<?php echo MWF_Functions::generate_input_attribute( 'value', $value ); ?>
	<?php echo MWF_Functions::generate_input_attribute( 'min', $min ); ?>
	<?php echo MWF_Functions::generate_input_attribute( 'max', $max ); ?>
	<?php echo MWF_Functions::generate_input_attribute( 'step', $step ); ?>
	<?php echo MWF_Functions::generate_input_attribute( 'placeholder', $placeholder ); ?>
/>
