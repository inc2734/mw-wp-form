<input type="hidden" name="<?php echo esc_attr( MWF_Config::NAME ); ?>_nonce" value="<?php echo wp_create_nonce( MWF_Config::NAME ); ?>" />
<table border="0" cellpadding="0" cellspacing="4">
	<tr>
		<th><?php esc_html_e( 'Input Page URL', MWF_Config::DOMAIN ); ?></th>
		<td>
			<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[input_url]" value="<?php echo esc_attr( $input_url ); ?>" />
		</td>
	</tr>
	<tr>
		<th><?php esc_html_e( 'Confirmation Page URL', MWF_Config::DOMAIN ); ?></th>
		<td>
			<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[confirmation_url]" value="<?php echo esc_attr( $confirmation_url ); ?>" />
		</td>
	</tr>
	<tr>
		<th><?php esc_html_e( 'Complete Page URL', MWF_Config::DOMAIN ); ?></th>
		<td>
			<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[complete_url]" value="<?php echo esc_attr( $complete_url ); ?>" />
		</td>
	</tr>
	<tr>
		<th><?php esc_html_e( 'Validation Error Page URL', MWF_Config::DOMAIN ); ?></th>
		<td>
			<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[validation_error_url]" value="<?php echo esc_attr( $validation_error_url ); ?>" />
		</td>
	</tr>
</table>
<p class="description">
	<?php esc_html_e( 'This urls are the redirection urls at the time of button press. When URL setting is empty, The page redirect on the same page.', MWF_Config::DOMAIN ); ?><br />
	<?php printf( esc_html__( 'When a URL doesn\'t begin http or https, %s is complemented.', MWF_Config::DOMAIN ), '<b>' . home_url() . '</b>' ); ?>
</p>