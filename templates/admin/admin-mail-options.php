<p>
	<?php esc_html_e( '{key} is converted form data.', MWF_Config::DOMAIN ); ?>
	<?php echo sprintf(
		esc_html__( 'It is automatically converted to Tracking number when you input {%s}.', MWF_Config::DOMAIN ),
		MWF_Config::TRACKINGNUMBER
	); ?>
</p>
<p>
	<?php esc_html_e( 'If Admin Email Options is a blank, Automatic Replay Email Options is used as Admin Email Options.', MWF_Config::DOMAIN ); ?>
</p>
<p>
	<b><?php esc_html_e( 'To ( E-mail address )', MWF_Config::DOMAIN ); ?></b><br />
	<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[mail_to]" value="<?php echo esc_attr( $mail_to ); ?>" /><br />
	<span class="mwf_note"><?php esc_html_e( 'If empty:', MWF_Config::DOMAIN ); ?> <?php bloginfo( 'admin_email' ); ?></span>
</p>
<p>
	<b><?php esc_html_e( 'CC ( E-mail address )', MWF_Config::DOMAIN ); ?></b><br />
	<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[mail_cc]" value="<?php echo esc_attr( $mail_cc ); ?>" />
</p>
<p>
	<b><?php esc_html_e( 'BCC ( E-mail address )', MWF_Config::DOMAIN ); ?></b><br />
	<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[mail_bcc]" value="<?php echo esc_attr( $mail_bcc ); ?>" />
</p>
<p>
	<b><?php esc_html_e( 'Subject', MWF_Config::DOMAIN ); ?></b><br />
	<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[admin_mail_subject]" value="<?php echo esc_attr( $admin_mail_subject ); ?>" />
</p>
<p>
	<b><?php esc_html_e( 'Sender', MWF_Config::DOMAIN ); ?></b><br />
	<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[admin_mail_sender]" value="<?php echo esc_attr( $admin_mail_sender ); ?>" /><br />
	<span class="mwf_note"><?php esc_html_e( 'If empty:', MWF_Config::DOMAIN ); ?> <?php bloginfo( 'name' ); ?></span>
</p>
<p>
	<b><?php esc_html_e( 'From ( E-mail address )', MWF_Config::DOMAIN ); ?></b><br />
	<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[admin_mail_from]" value="<?php echo esc_attr( $admin_mail_from ); ?>" /><br />
	<span class="mwf_note"><?php esc_html_e( 'If empty:', MWF_Config::DOMAIN ); ?> <?php bloginfo( 'admin_email' ); ?></span>
</p>
<p>
	<b><?php esc_html_e( 'Content', MWF_Config::DOMAIN ); ?></b><br />
	<textarea name="<?php echo esc_attr( MWF_Config::NAME ); ?>[admin_mail_content]" cols="30" rows="10"><?php echo esc_attr( $admin_mail_content ); ?></textarea>
</p>