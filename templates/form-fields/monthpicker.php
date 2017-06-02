<input type="text"
	name="<?php echo esc_attr( $name ); ?>"
	<?php echo MWF_Functions::generate_input_attribute( 'id', $id ); ?>
	<?php echo MWF_Functions::generate_input_attribute( 'class', $class ); ?>
	<?php echo MWF_Functions::generate_input_attribute( 'size', $size ); ?>
	<?php echo MWF_Functions::generate_input_attribute( 'value', $value ); ?>
	<?php echo MWF_Functions::generate_input_attribute( 'placeholder', $placeholder ); ?>
/>
<script type="text/javascript">
jQuery(function($) {
	$("input[name='<?php echo esc_js( $name ); ?>']").MonthPicker({
		<?php echo trim( $js, '{}' ); ?>
	});
});
</script>
