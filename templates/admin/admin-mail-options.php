<p>
	<?php esc_html_e( '{key} is converted form data.', 'mw-wp-form' ); ?>
	<?php echo sprintf(
		esc_html__( 'It is automatically converted to Tracking number when you input {%s}.', 'mw-wp-form' ),
		MWF_Config::TRACKINGNUMBER
	); ?>
</p>
<p>
	<?php esc_html_e( 'If Admin Email Options is a blank, Automatic Replay Email Options is used as Admin Email Options.', 'mw-wp-form' ); ?>
</p>
<p>
	<b><?php esc_html_e( 'To ( E-mail address )', 'mw-wp-form' ); ?></b><br />
	<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[mail_to]" value="<?php echo esc_attr( $mail_to ); ?>" /><br />
	<span class="mwf_note"><?php esc_html_e( 'If empty:', 'mw-wp-form' ); ?> <?php bloginfo( 'admin_email' ); ?></span>
</p>
<p>
	<b><?php esc_html_e( 'CC ( E-mail address )', 'mw-wp-form' ); ?></b><br />
	<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[mail_cc]" value="<?php echo esc_attr( $mail_cc ); ?>" />
</p>
<p>
	<b><?php esc_html_e( 'BCC ( E-mail address )', 'mw-wp-form' ); ?></b><br />
	<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[mail_bcc]" value="<?php echo esc_attr( $mail_bcc ); ?>" />
</p>
<p>
	<b><?php esc_html_e( 'Subject', 'mw-wp-form' ); ?></b><br />
	<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[admin_mail_subject]" value="<?php echo esc_attr( $admin_mail_subject ); ?>" />
</p>
<p>
	<b><?php esc_html_e( 'Sender', 'mw-wp-form' ); ?></b><br />
	<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[admin_mail_sender]" value="<?php echo esc_attr( $admin_mail_sender ); ?>" /><br />
	<span class="mwf_note"><?php esc_html_e( 'If empty:', 'mw-wp-form' ); ?> <?php bloginfo( 'name' ); ?></span>
</p>
<p>
	<b><?php esc_html_e( 'Return-Path ( E-mail address )', 'mw-wp-form' ); ?></b><br />
	<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[mail_return_path]" value="<?php echo esc_attr( $mail_return_path ); ?>" /><br />
	<span class="mwf_note"><?php esc_html_e( 'If empty:', 'mw-wp-form' ); ?> <?php bloginfo( 'admin_email' ); ?></span>
</p>
<p>
	<b><?php esc_html_e( 'From ( E-mail address )', 'mw-wp-form' ); ?></b><br />
	<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[admin_mail_from]" value="<?php echo esc_attr( $admin_mail_from ); ?>" /><br />
	<span class="mwf_note"><?php esc_html_e( 'If empty:', 'mw-wp-form' ); ?> <?php bloginfo( 'admin_email' ); ?></span>
</p>
<p>
	<b><?php esc_html_e( 'Content', 'mw-wp-form' ); ?></b><br />
	<textarea name="<?php echo esc_attr( MWF_Config::NAME ); ?>[admin_mail_content]" cols="30" rows="10"><?php echo esc_attr( $admin_mail_content ); ?></textarea>
</p>
