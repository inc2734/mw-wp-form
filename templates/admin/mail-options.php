<p>
	<?php esc_html_e( '{key} is converted form data.', 'mw-wp-form' ); ?>
	<?php echo sprintf(
		esc_html__( 'It is automatically converted to Tracking number when you input {%s}.', 'mw-wp-form' ),
		MWF_Config::TRACKINGNUMBER
	); ?>
</p>
<p>
	<b><?php esc_html_e( 'Subject', 'mw-wp-form' ); ?></b><br />
	<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[mail_subject]" value="<?php echo esc_attr( $mail_subject ); ?>" />
</p>
<p>
	<b><?php esc_html_e( 'Sender', 'mw-wp-form' ); ?></b><br />
	<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[mail_sender]" value="<?php echo esc_attr( $mail_sender ); ?>" /><br />
	<span class="mwf_note"><?php esc_html_e( 'If empty:', 'mw-wp-form' ); ?> <?php bloginfo( 'name' ); ?></span>
</p>
<p>
	<b><?php esc_html_e( 'From ( E-mail address )', 'mw-wp-form' ); ?></b><br />
	<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[mail_from]" value="<?php echo esc_attr( $mail_from ); ?>" /><br />
	<span class="mwf_note"><?php esc_html_e( 'If empty:', 'mw-wp-form' ); ?> <?php bloginfo( 'admin_email' ); ?></span>
</p>
<p>
	<b><?php esc_html_e( 'Content', 'mw-wp-form' ); ?></b><br />
	<textarea name="<?php echo esc_attr( MWF_Config::NAME ); ?>[mail_content]" cols="30" rows="10"><?php echo esc_attr( $mail_content ); ?></textarea>
</p>
<p>
	<b><?php esc_html_e( 'Automatic reply email', 'mw-wp-form' ); ?></b><br />
	<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[automatic_reply_email]" value="<?php echo esc_attr( $automatic_reply_email ); ?>" /><br />
	<span class="mwf_note"><?php esc_html_e( 'Input the key to use as transmission to automatic reply email. {} is unnecessary.', 'mw-wp-form' ); ?></span>
</p>
