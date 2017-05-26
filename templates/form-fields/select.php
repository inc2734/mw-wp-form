<select name="<?php echo esc_attr( $name ); ?>" <?php echo $attributes; ?>>
	<?php foreach ( $children as $key => $label ) : ?>
		<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $value, true ); ?>><?php echo esc_attr( $label ); ?></option>
	<?php endforeach; ?>
</select>
