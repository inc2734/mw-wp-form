<?php
/**
 * @package mw-wp-form
 * @author inc2734
 * @license GPL-2.0+
 */
?>

<input type="submit"
	name="<?php echo esc_attr( $name ); ?>"
	value="<?php echo esc_attr( $value ); ?>"
	<?php echo MWF_Functions::generate_input_attribute( 'class', $class ); ?>
/>
