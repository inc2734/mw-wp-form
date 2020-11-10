<?php
/**
 * @package mw-wp-form
 * @author inc2734
 * @license GPL-2.0+
 */
?>

<p>
	<span id="formkey_field">[mwform_formkey key="<?php echo esc_html( $post_id ); ?>"]</span>
	<span class="mwf_note">
		<?php esc_html_e( 'Copy and Paste this shortcode.', 'mw-wp-form' ); ?><br />
		<?php esc_html_e( 'The key to use with hook is ', 'mw-wp-form' ); ?><?php echo MWF_Config::NAME; ?>-<?php echo esc_html( $post_id ); ?>
	</span>
</p>
