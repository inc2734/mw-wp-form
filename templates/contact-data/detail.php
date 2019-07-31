<input type="hidden" name="<?php echo esc_attr( MWF_Config::NAME ); ?>_nonce" value="<?php echo wp_create_nonce( MWF_Config::NAME ); ?>" />
<table border="0" cellpadding="0" cellspacing="0">
	<?php
	$columns  = array();
	$values   = $Contact_Data_Setting->gets();
	$_columns = array();
	foreach ( $values as $key => $value ) {
		if ( $key === MWF_Config::TRACKINGNUMBER ) {
			$columns[ $key ] = MWF_Functions::get_tracking_number_title( $post_type );
			continue;
		}
		if ( in_array( $key, $Contact_Data_Setting->get_permit_keys() ) ) {
			continue;
		}
		$_columns[ $key ] = $key;
	}
	$_columns = apply_filters( 'mwform_inquiry_data_columns-' . $post_type, $_columns );
	$columns = array_merge( $columns, $_columns );
	?>

	<?php foreach ( $columns as $key => $label ) : ?>
	<?php if ( isset( $values[ $key ] ) ) : ?>
	<tr>
		<th>
			<?php
			if ( $key === MWF_Config::TRACKINGNUMBER ) {
				echo MWF_Functions::get_tracking_number_title( $post_type );
			} else {
				echo esc_html( $label );
			}
			?>
		</th>
		<td>
			<?php
			if ( $Contact_Data_Setting->is_upload_file_key( $key ) ) {
				// 過去バージョンでの不具合でメタデータが空になっていることがあるのでその場合は代替処理
				if ( '' === $values[ $key ] ) {
					$values[ $key ] = MWF_Functions::get_multimedia_id__fallback( $post, $key );
				}
				echo MWF_Functions::get_multimedia_data( $values[ $key ] );
			} else {
				echo nl2br( esc_html( $values[ $key ] ) );
			}
			?>
		</td>
	</tr>
	<?php endif; ?>
	<?php endforeach; ?>
	<tr>
		<th><?php esc_html_e( 'Admin Email To', 'mw-wp-form' ); ?></th>
		<td>
			<?php echo esc_html( $Contact_Data_Setting->get( 'admin_mail_to' ) ); ?>
		</td>
	</tr>
	<tr>
		<th><?php esc_html_e( 'Response Status', 'mw-wp-form' ); ?></th>
		<td>
			<select name="<?php echo esc_attr( MWF_Config::INQUIRY_DATA_NAME ); ?>[response_status]">
				<?php foreach ( $Contact_Data_Setting->get_response_statuses() as $key => $value ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $Contact_Data_Setting->get( 'response_status' ) ); ?>>
					<?php echo esc_html( $value ); ?>
				</option>
				<?php endforeach; ?>
			</select>
		</td>
	</tr>
	<tr>
		<th><?php esc_html_e( 'Memo', 'mw-wp-form' ); ?></th>
		<td><textarea name="<?php echo esc_attr( MWF_Config::INQUIRY_DATA_NAME ); ?>[memo]" cols="50" rows="5"><?php echo $Contact_Data_Setting->get( 'memo' ); ?></textarea></td>
	</tr>
</table>
