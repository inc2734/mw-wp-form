<?php
do_action( 'mwform_tag_generator_dialog' );
?>
<div class="add-mwform-btn">
	<select>
		<option value=""><?php echo esc_html_e( 'Select this.', MWF_Config::DOMAIN ); ?></option>
		<optgroup label="<?php echo esc_attr_e( 'Input fields', MWF_Config::DOMAIN ); ?>">
			<?php do_action( 'mwform_tag_generator_input_option' ); ?>
		</optgroup>
		<optgroup label="<?php echo esc_attr_e( 'Select fields', MWF_Config::DOMAIN ); ?>">
			<?php do_action( 'mwform_tag_generator_select_option' ); ?>
		</optgroup>
		<optgroup label="<?php echo esc_attr_e( 'Button fields', MWF_Config::DOMAIN ); ?>">
			<?php do_action( 'mwform_tag_generator_button_option' ); ?>
		</optgroup>
		<optgroup label="<?php echo esc_attr_e( 'Error fields', MWF_Config::DOMAIN ); ?>">
			<?php do_action( 'mwform_tag_generator_error_option' ); ?>
		</optgroup>
		<optgroup label="<?php echo esc_attr_e( 'Other fields', MWF_Config::DOMAIN ); ?>">
			<?php do_action( 'mwform_tag_generator_option' ); ?>
		</optgroup>
	</select>
	<span class="button"><?php esc_html_e( 'Add form tag', MWF_Config::DOMAIN ); ?></span>
</div>