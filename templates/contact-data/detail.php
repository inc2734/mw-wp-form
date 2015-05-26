<input type="hidden" name="<?php echo esc_attr( MWF_Config::NAME ); ?>_nonce" value="<?php echo wp_create_nonce( MWF_Config::NAME ); ?>" />
<table border="0" cellpadding="0" cellspacing="0">
	<?php
	$values = $Contact_Data_Setting->gets();
	foreach ( $values as $key => $value ) :
		if ( in_array( $key, $Contact_Data_Setting->get_permit_keys() ) ) {
			continue;
		}
		?>
	<tr>
		<th>
			<?php
			if ( $key === MWF_Config::TRACKINGNUMBER ) {
				echo MWF_Functions::get_tracking_number_title( $post_type );
			} else {
				echo esc_html( $key );
			}
			?>
		</th>
		<td>
			<?php
			if ( $Contact_Data_Setting->is_upload_file_key( $post, $key ) ) {
				// 過去バージョンでの不具合でメタデータが空になっていることがあるのでその場合は代替処理
				if ( $value === '' ) {
					$value = MWF_Functions::get_multimedia_id__fallback( $post, $key );
				}
				echo MWF_Functions::get_multimedia_data( $value );
			} else {
				echo nl2br( esc_html( $value ) );
			}
			?>
		</td>
	</tr>
	<?php endforeach; ?>
	<tr>
		<th><?php esc_html_e( 'Response Status', MWF_Config::DOMAIN ); ?></th>
		<td>
			<select name="<?php echo esc_attr( MWF_Config::CONTACT_DATA_NAME ); ?>[response_status]">
				<?php foreach ( $Contact_Data_Setting->get_response_statuses() as $key => $value ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $Contact_Data_Setting->get( 'response_status' ) ); ?>>
					<?php echo esc_html( $value ); ?>
				</option>
				<?php endforeach; ?>
			</select>
		</td>
	</tr>
	<tr>
		<th><?php esc_html_e( 'Memo', MWF_Config::DOMAIN ); ?></th>
		<td><textarea name="<?php echo esc_attr( MWF_Config::CONTACT_DATA_NAME ); ?>[memo]" cols="50" rows="5"><?php echo $Contact_Data_Setting->get( 'memo' ); ?></textarea></td>
	</tr>
</table>