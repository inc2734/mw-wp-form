<?php
/**
 * @package mw-wp-form
 * @author inc2734
 * @license GPL-2.0+
 */
?>

<p>
	<?php esc_html_e( '{name of form tag} is converted to posted data.', 'mw-wp-form' ); ?>
	<?php
	echo sprintf(
		// translators: %s: Tracking number
		esc_html__( 'It is automatically converted to Tracking number when you input {%s}.', 'mw-wp-form' ),
		MWF_Config::TRACKINGNUMBER
	);
	?>
</p>
<p>
	<b><?php esc_html_e( 'To ( E-mail address )', 'mw-wp-form' ); ?></b><br />
	<input class="widefat" type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[mail_to]" value="<?php echo esc_attr( $mail_to ); ?>" />
</p>
<p>
	<b><?php esc_html_e( 'CC ( E-mail address )', 'mw-wp-form' ); ?></b><br />
	<input class="widefat" type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[mail_cc]" value="<?php echo esc_attr( $mail_cc ); ?>" />
</p>
<p>
	<b><?php esc_html_e( 'BCC ( E-mail address )', 'mw-wp-form' ); ?></b><br />
	<input class="widefat" type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[mail_bcc]" value="<?php echo esc_attr( $mail_bcc ); ?>" />
</p>
<p>
	<b><?php esc_html_e( 'Subject', 'mw-wp-form' ); ?></b><br />
	<input class="widefat" type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[admin_mail_subject]" value="<?php echo esc_attr( $admin_mail_subject ); ?>" />
</p>
<p>
	<b><?php esc_html_e( 'Sender', 'mw-wp-form' ); ?></b><br />
	<input class="widefat" type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[admin_mail_sender]" value="<?php echo esc_attr( $admin_mail_sender ); ?>" />
</p>
<p>
	<b><?php esc_html_e( 'Reply-to ( E-mail address )', 'mw-wp-form' ); ?></b><br />
	<input class="widefat" type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[admin_mail_reply_to]" value="<?php echo esc_attr( $admin_mail_reply_to ); ?>" />
</p>
<p>
	<b><?php esc_html_e( 'Content', 'mw-wp-form' ); ?></b><br />
	<textarea class="widefat" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[admin_mail_content]" cols="30" rows="10"><?php echo esc_attr( $admin_mail_content ); ?></textarea>
</p>
<p>
	<b><?php esc_html_e( 'Return-Path ( E-mail address )', 'mw-wp-form' ); ?></b><br />
	<input class="widefat" type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[mail_return_path]" value="<?php echo esc_attr( $mail_return_path ); ?>" />
	<span class="mwf_note"><?php esc_html_e( 'Optional. You should specify an email address in the same domain as your server.', 'mw-wp-form' ); ?></span>
</p>
<p>
	<b><?php esc_html_e( 'From ( E-mail address )', 'mw-wp-form' ); ?></b><br />
	<input class="widefat" type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[admin_mail_from]" value="<?php echo esc_attr( $admin_mail_from ); ?>" />
	<span class="mwf_note"><?php esc_html_e( 'Optional. You should specify an email address in the same domain as your server.', 'mw-wp-form' ); ?></span>
</p>
