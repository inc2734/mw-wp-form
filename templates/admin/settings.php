<p>
	<label><input type="checkbox" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[querystring]" value="1" <?php checked( $querystring, 1 ); ?> /> <?php esc_html_e( 'Activate Query string of post', MWF_Config::DOMAIN ); ?></label><br />
	<span class="mwf_note"><?php esc_html_e( 'If this field is active, MW WP Form get query string. And get post data from query string "post_id". You can use $post\'s property in editor.', MWF_Config::DOMAIN ); ?><br />
	<?php esc_html_e( 'Example: {ID}, {post_title}, {post_meta} etc...', MWF_Config::DOMAIN ); ?></span>
</p>
<p>
	<label><input type="checkbox" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[usedb]" value="1" <?php checked( $usedb, 1 ); ?> /> <?php esc_html_e( 'Saving inquiry data in database', MWF_Config::DOMAIN ); ?></label>
</p>
<p>
	<label><input type="checkbox" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[scroll]" value="1" <?php checked( $scroll, 1 ); ?> /> <?php esc_html_e( 'Enable scrolling of screen transition.', MWF_Config::DOMAIN ); ?></label>
</p>
<p>
	<?php esc_html_e( 'Next Tracking Number', MWF_Config::DOMAIN ); ?><br />
	<input type="number" name="<?php echo esc_attr( MWF_Config::TRACKINGNUMBER ); ?>" id="tracking_number_field" value="<?php echo esc_attr( $tracking_number ); ?>" step="1" min="1" disabled="disabled" /><br />
	<input type="checkbox" name="open_tracking_number_field" />
	<?php esc_html_e( 'I want to change.', MWF_Config::DOMAIN ); ?>
</p>
<table border="0" cellpadding="0" cellspacing="0" class="akismet">
	<tr>
		<th colspan="2"><?php esc_html_e( 'Akismet Setting', MWF_Config::DOMAIN ); ?></th>
	</tr>
	<tr>
		<td>author</td>
		<td><input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[akismet_author]" value="<?php echo esc_attr( $akismet_author ); ?>" /></td>
	</tr>
	<tr>
		<td>email</td>
		<td><input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[akismet_author_email]" value="<?php echo esc_attr( $akismet_author_email ); ?>" /></td>
	</tr>
	<tr>
		<td>url</td>
		<td><input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[akismet_author_url]" value="<?php echo esc_attr( $akismet_author_url ); ?>" /></td>
	</tr>
</table>
<span class="mwf_note"><?php esc_html_e( 'Input the key to use Akismet.', MWF_Config::DOMAIN ); ?></span>