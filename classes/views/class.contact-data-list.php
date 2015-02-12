<?php
/**
 * Name       : MW WP Form Contact Data List View
 * Version    : 1.0.1
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : January 2, 2015
 * Modified   : February 13, 2015
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Contact_Data_List_View extends MW_WP_Form_View {

	/**
	 * admin_print_styles_for_list
	 */
	public function admin_print_styles_for_list() {
		?>
		<style type="text/css">
		#normal-sortables {
			display: none;
		}
		</style>
		<?php
	}

	/**
	 * csv_button
	 */
	public function csv_button() {
		$action = $this->get( 'action' );
		?>
		<form id="mw-wp-form_csv" method="post" action="<?php echo esc_url( $action ); ?>">
			<input type="submit" value="<?php esc_attr_e( 'CSV Download', MWF_Config::DOMAIN ); ?>" class="button-primary" />
			&nbsp;
			&nbsp;
			<label><input type="checkbox" name="download-all" value="true" checked="checked" /> Download All</label>
			<input type="hidden" name="<?php echo esc_attr( MWF_Config::NAME . '-csv-download' ); ?>" value="1" />
			<?php wp_nonce_field( MWF_Config::NAME ); ?>
		</form>
		<?php
	}

	/**
	 * manage_posts_custom_column
	 */
	public function manage_posts_custom_column() {
		$column  = $this->get( 'column' );
		$post_id = $this->get( 'post_id' );
		$post    = get_post( $post_id );
		$post_custom_keys     = $this->get( 'post_custom_keys' );
		$Contact_Data_Setting = $this->get( 'Contact_Data_Setting' );

		if ( $column === 'post_date' ) {
			echo esc_html( $post->post_date );
		}
		elseif ( $column === 'response_status' ) {
			$response_statuses = $Contact_Data_Setting->get_response_statuses();
			$response_status   = $Contact_Data_Setting->get( 'response_status' );
			echo $response_statuses[$response_status];
		}
		elseif ( is_array( $post_custom_keys ) && in_array( $column, $post_custom_keys ) ) {
			$post_meta = get_post_meta( $post_id, $column, true );
			if ( $Contact_Data_Setting->is_upload_file_key( $post, $column ) ) {
				echo $this->get_multimedia_data( $post_meta );
			} elseif ( $post_meta ) {
				echo esc_html( $post_meta );
			} else {
				echo '&nbsp;';
			}
		} else {
			echo '&nbsp;';
		}
	}

	/**
	 * get_multimedia_data
	 * @param string $value
	 * @return string
	 */
	protected function get_multimedia_data( $value ) {
		$mimetype = get_post_mime_type( $value );
		if ( $mimetype ) {
			// 画像だったら
			if ( preg_match( '/^image\/.+?$/', $mimetype ) ) {
				$src = wp_get_attachment_image_src( $value, 'thumbnail' );
				return sprintf(
					'<img src="%s" alt="" style="width:50px;height:50px" />',
					esc_url( $src[0] )
				);
			}
			// 画像以外
			else {
				$src = wp_get_attachment_image_src( $value, 'none', true );
				return sprintf(
					'<a href="%s" target="_blank"><img src="%s" alt="" style="height:50px" /></a>',
					esc_url( wp_get_attachment_url( $value ) ),
					esc_url( $src[0] )
				);
			}
		}
		// 添付されているけど、フック等でメタ情報が書き換えられて添付ファイルID以外になってしまった場合
		else {
			echo esc_html( $value );
		}
	}
}