<form id="mw-wp-form_csv" method="post" action="<?php echo esc_url( $action ); ?>">
	<input type="submit" value="<?php esc_attr_e( 'CSV Download', 'mw-wp-form' ); ?>" class="button-primary" />
	&nbsp;
	&nbsp;
	<label><input type="checkbox" name="download-all" value="true" checked="checked" /> Download All</label>
	<input type="hidden" name="<?php echo esc_attr( MWF_Config::NAME . '-csv-download' ); ?>" value="1" />
	<?php wp_nonce_field( MWF_Config::NAME ); ?>
</form>