<?php
/**
 * @package mw-wp-form
 * @author inc2734
 * @license GPL-2.0+
 */
?>

<span class="mwform-tel-field">
	<input type="text"
		name="<?php echo esc_attr( $fields[0]['name'] ); ?>"
		<?php echo MWF_Functions::generate_input_attribute( 'class', $fields[0]['class'] ); ?>
		<?php echo MWF_Functions::generate_input_attribute( 'size', $fields[0]['size'] ); ?>
		<?php echo MWF_Functions::generate_input_attribute( 'maxlength', $fields[0]['maxlength'] ); ?>
		<?php echo MWF_Functions::generate_input_attribute( 'value', $fields[0]['value'] ); ?>
		<?php echo MWF_Functions::generate_input_attribute( 'data-conv-half-alphanumeric', $fields[0]['conv-half-alphanumeric'] ); ?>
	/>
	<?php echo esc_html( $separator ); ?>
	<input type="text"
		name="<?php echo esc_attr( $fields[1]['name'] ); ?>"
		<?php echo MWF_Functions::generate_input_attribute( 'class', $fields[1]['class'] ); ?>
		<?php echo MWF_Functions::generate_input_attribute( 'size', $fields[1]['size'] ); ?>
		<?php echo MWF_Functions::generate_input_attribute( 'maxlength', $fields[1]['maxlength'] ); ?>
		<?php echo MWF_Functions::generate_input_attribute( 'value', $fields[1]['value'] ); ?>
		<?php echo MWF_Functions::generate_input_attribute( 'data-conv-half-alphanumeric', $fields[1]['conv-half-alphanumeric'] ); ?>
	/>
	<?php echo esc_html( $separator ); ?>
	<input type="text"
		name="<?php echo esc_attr( $fields[2]['name'] ); ?>"
		<?php echo MWF_Functions::generate_input_attribute( 'class', $fields[2]['class'] ); ?>
		<?php echo MWF_Functions::generate_input_attribute( 'size', $fields[2]['size'] ); ?>
		<?php echo MWF_Functions::generate_input_attribute( 'maxlength', $fields[2]['maxlength'] ); ?>
		<?php echo MWF_Functions::generate_input_attribute( 'value', $fields[2]['value'] ); ?>
		<?php echo MWF_Functions::generate_input_attribute( 'data-conv-half-alphanumeric', $fields[2]['conv-half-alphanumeric'] ); ?>
	/>
</span>
