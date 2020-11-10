<?php
/**
 * @package mw-wp-form
 * @author inc2734
 * @license GPL-2.0+
 */

wp_editor(
	$this->_get_option( 'complete_message' ),
	MWF_Config::NAME . '_complete_message',
	array(
		'textarea_name' => MWF_Config::NAME . '[complete_message]',
		'textarea_rows' => 7,
	)
);
?>
<p class="mwf_note">
	<?php esc_html_e( '{name of form tag} is converted to posted data.', 'mw-wp-form' ); ?>
	<?php
	echo sprintf(
		// translators: %s: Tracking Number
		esc_html__( 'It is automatically converted to Tracking number when you input {%s}.', 'mw-wp-form' ),
		MWF_Config::TRACKINGNUMBER
	);
	?>
</p>
