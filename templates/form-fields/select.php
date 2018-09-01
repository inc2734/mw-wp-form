<select name="<?php echo esc_attr( $name ); ?>"
	<?php echo MWF_Functions::generate_input_attribute( 'id', $id ); ?>
	<?php echo MWF_Functions::generate_input_attribute( 'class', $class ); ?>
>
	<?php foreach ( $children as $option_value => $option_label ) : ?>
		<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $option_value, $value, true ); ?>>
			<?php echo esc_html( $option_label ); ?>
		</option>
	<?php endforeach; ?>
</select>
