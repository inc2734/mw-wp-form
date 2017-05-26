<?php $i = 0; foreach ( $fields as $key => $field ) : $i ++; ?>
<span class="mwform-checkbox-field <?php echo esc_attr( $vertically ); ?>">
	<label <?php echo $field['attributes_for_label']; ?>>
		<input type="checkbox" name="<?php echo esc_attr( $name ); ?>[data][]" value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( $key, $value ), true, true ); ?> <?php echo $field['attributes']; ?> />
		<span class="mwform-checkbox-field-text"><?php echo esc_html( $field['label'] ); ?></span>
	</label>
</span>
<?php endforeach; ?>
