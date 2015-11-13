<p>
	<select name="<?php echo MWF_Config::NAME; ?>[style]">
		<option value=""><?php esc_html_e( 'Select Style', 'mw-wp-form' ); ?></option>
		<?php foreach ( $styles as $style_key => $css ) : ?>
		<option value="<?php echo esc_attr( $style_key ); ?>" <?php selected( $style, $style_key ); ?>>
			<?php echo esc_html( $style_key ); ?>
		</option>
		<?php endforeach; ?>
	</select>
</p>