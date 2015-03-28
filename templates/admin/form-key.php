<p>
	<span id="formkey_field">[mwform_formkey key="<?php echo esc_html( $post_id ); ?>"]</span>
	<span class="mwf_note">
		<?php esc_html_e( 'Copy and Paste this shortcode.', MWF_Config::DOMAIN ); ?><br />
		<?php esc_html_e( 'The key to use with hook is ', MWF_Config::DOMAIN ); ?><?php echo MWF_Config::NAME; ?>-<?php echo esc_html( $post_id ); ?>
	</span>
</p>