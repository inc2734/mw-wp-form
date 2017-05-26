<?php foreach ( $fields as $key => $field ) : ?>
<span class="mwform-radio-field <?php echo esc_attr( $vertically ); ?>">
	<label <?php echo $field['attributes_for_label']; ?>>
		<input type="radio" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $key ); ?>" <?php checked( $key, $value, true ); ?> <?php echo $field['attributes']; ?> />
		<span class="mwform-radio-field-text"><?php echo esc_attr( $field['label'] ); ?></span>
	</label>
</span>
<?php endforeach; ?>
