<?php
/**
 * @package mw-wp-form
 * @author inc2734
 * @license GPL-2.0+
 */
?>

<textarea
	name="<?php echo esc_attr( $name ); ?>"
	<?php echo MWF_Functions::generate_input_attribute( 'id', $id ); ?>
	<?php echo MWF_Functions::generate_input_attribute( 'class', $class ); ?>
	<?php echo MWF_Functions::generate_input_attribute( 'maxlength', $maxlength ); ?>
	<?php echo MWF_Functions::generate_input_attribute( 'cols', $cols ); ?>
	<?php echo MWF_Functions::generate_input_attribute( 'rows', $rows ); ?>
	<?php echo MWF_Functions::generate_input_attribute( 'placeholder', $placeholder ); ?>
><?php echo esc_html( $value ); ?></textarea>
