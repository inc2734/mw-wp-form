<?php
/**
 * @package mw-wp-form
 * @author inc2734
 * @license GPL-2.0+
 */
?>

<div class="section">
	<label>
		<input
			type="checkbox"
			name="<?php echo esc_attr( MWF_Config::NAME ); ?>[querystring]"
			value="1"
			<?php checked( $querystring, 1 ); ?>
		/>
		<?php esc_html_e( 'Activate Query string of post', 'mw-wp-form' ); ?>
	</label>
	<br />
	<span class="mwf_note">
		<?php esc_html_e( 'If this field is active, MW WP Form get query string. And get post data from query string "post_id". You can use $post\'s property in editor.', 'mw-wp-form' ); ?>
		<br />
		<?php esc_html_e( 'Example: {ID}, {post_title}, {post_meta} etc...', 'mw-wp-form' ); ?>
	</span>
</div>

<div class="section">
	<label>
		<input
			type="checkbox"
			name="<?php echo esc_attr( MWF_Config::NAME ); ?>[usedb]"
			value="1"
			<?php checked( $usedb, 1 ); ?>
		/>
		<?php esc_html_e( 'Saving inquiry data in database', 'mw-wp-form' ); ?>
	</label>
</div>

<div class="section">
	<label>
		<input
			type="checkbox"
			name="<?php echo esc_attr( MWF_Config::NAME ); ?>[scroll]"
			value="1"
			<?php checked( $scroll, 1 ); ?>
		/>
		<?php esc_html_e( 'Enable scrolling of screen transition.', 'mw-wp-form' ); ?>
	</label>
</div>

<div class="section">
	<?php esc_html_e( 'Next Tracking Number', 'mw-wp-form' ); ?>
	<br />
	<input
		type="number"
		name="<?php echo esc_attr( MWF_Config::TRACKINGNUMBER ); ?>"
		id="tracking_number_field"
		value="<?php echo esc_attr( $tracking_number ); ?>"
		step="1"
		min="1"
		disabled="disabled"
	/>
	<br />
	<input type="checkbox" name="open_tracking_number_field" />
	<?php esc_html_e( 'I want to change.', 'mw-wp-form' ); ?>
</div>

<div class="section">
	<table border="0" cellpadding="0" cellspacing="0" class="akismet">
		<tr>
			<th colspan="2"><?php esc_html_e( 'Akismet Setting', 'mw-wp-form' ); ?></th>
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
	<span class="mwf_note"><?php esc_html_e( 'Input the key to use Akismet.', 'mw-wp-form' ); ?></span>
</div>

<?php
do_action( 'mwform_settings_meta_box' );
