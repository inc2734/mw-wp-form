<input type="text" name="<?php echo esc_attr( $name ); ?>" <?php echo $attributes; ?> />
<script type="text/javascript">
jQuery(function($) {
	$("input[name='<?php echo esc_js( $name ); ?>']").MonthPicker({
		<?php echo trim( $js, '{}' ); ?>
	});
});
</script>
