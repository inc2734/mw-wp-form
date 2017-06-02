<?php
if ( 'true' === $vertically ) {
	$vertically_class = 'vertical-item';
} else {
	$vertically_class = 'horizontal-item';
}
?>
<?php foreach ( $fields as $field_value => $field ) : ?>
	<span class="mwform-checkbox-field <?php echo esc_attr( $vertically_class ); ?>">
		<label <?php echo MWF_Functions::generate_input_attribute( 'for', $field['id'] ); ?>>
			<input type="checkbox"
				name="<?php echo esc_attr( $field['name'] ); ?>"
				value="<?php echo esc_attr( $field_value ); ?>"
				<?php checked( in_array( $field_value, $value ), true, true ); ?>
				<?php echo MWF_Functions::generate_input_attribute( 'id', $field['id'] ); ?>
				<?php echo MWF_Functions::generate_input_attribute( 'class', $field['class'] ); ?>
			/>
			<span class="mwform-checkbox-field-text"><?php echo esc_attr( $field['label'] ); ?></span>
		</label>
	</span>
<?php endforeach; ?>
