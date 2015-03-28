<p>
	<?php esc_html_e( '{key} is converted form data.', MWF_Config::DOMAIN ); ?>
	<?php echo sprintf(
		esc_html__( 'It is automatically converted to Tracking number when you input {%s}.', MWF_Config::DOMAIN ),
		MWF_Config::TRACKINGNUMBER
	); ?>
</p>
<p>
	<b><?php esc_html_e( 'Subject', MWF_Config::DOMAIN ); ?></b><br />
	<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[mail_subject]" value="<?php echo esc_attr( $mail_subject ); ?>" />
</p>
<p>
	<b><?php esc_html_e( 'Sender', MWF_Config::DOMAIN ); ?></b><br />
	<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[mail_sender]" value="<?php echo esc_attr( $mail_sender ); ?>" /><br />
	<span class="mwf_note"><?php esc_html_e( 'If empty:', MWF_Config::DOMAIN ); ?> <?php bloginfo( 'name' ); ?></span>
</p>
<p>
	<b><?php esc_html_e( 'From ( E-mail address )', MWF_Config::DOMAIN ); ?></b><br />
	<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[mail_from]" value="<?php echo esc_attr( $mail_from ); ?>" /><br />
	<span class="mwf_note"><?php esc_html_e( 'If empty:', MWF_Config::DOMAIN ); ?> <?php bloginfo( 'admin_email' ); ?></span>
</p>
<p>
	<b><?php esc_html_e( 'Content', MWF_Config::DOMAIN ); ?></b><br />
	<textarea name="<?php echo esc_attr( MWF_Config::NAME ); ?>[mail_content]" cols="30" rows="10"><?php echo esc_attr( $mail_content ); ?></textarea>
</p>
<p>
	<b><?php esc_html_e( 'Automatic reply email', MWF_Config::DOMAIN ); ?></b><br />
	<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[automatic_reply_email]" value="<?php echo esc_attr( $automatic_reply_email ); ?>" /><br />
	<span class="mwf_note"><?php esc_html_e( 'Input the key to use as transmission to automatic reply email. {} is unnecessary.', MWF_Config::DOMAIN ); ?></span>
</p>