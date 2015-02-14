<?php
/**
 * Name       : MW WP Form Contact Data View
 * Version    : 1.0.1
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : January 2, 2015
 * Modified   : February 13, 2015
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Contact_Data_View extends MW_WP_Form_View {

	/**
	 * admin_print_styles_for_detail
	 */
	public function admin_print_styles_for_detail() {
		?>
		<style type="text/css">
		h2 a.add-new-h2 {
			display: none;
		}
		</style>
		<?php
	}

	/**
	 * index
	 */
	public function index() {
		$contact_data_post_types = $this->get( 'contact_data_post_types' );
		?>
		<div class="wrap">
			<h2><?php esc_html_e( 'Inquiry data', MWF_Config::DOMAIN ); ?></h2>
			<p>
				<?php esc_html_e( 'You can see the inquiry data that are saved in the database by clicking on the link below.', MWF_Config::DOMAIN ); ?>
			</p>
			<table class="wp-list-table widefat fixed" cellspacing="0">
				<thead>
					<th class="<?php echo MWF_Config::NAME; ?>-table-title"><?php esc_html_e( 'Form title', MWF_Config::DOMAIN ); ?></th>
					<th class="<?php echo MWF_Config::NAME; ?>-table-chart"><?php esc_html_e( 'Display Chart', MWF_Config::DOMAIN ); ?></th>
					<th class="<?php echo MWF_Config::NAME; ?>-table-count"><?php esc_html_e( 'The number of inquiries', MWF_Config::DOMAIN ); ?></th>
					<th class="<?php echo MWF_Config::NAME; ?>-table-date"><?php esc_html_e( 'Updated date', MWF_Config::DOMAIN ); ?></th>
					<th class="<?php echo MWF_Config::NAME; ?>-table-date"><?php esc_html_e( 'Created date', MWF_Config::DOMAIN ); ?></th>
				</thead>
				<tbody>
					<?php $i = 0; foreach ( $contact_data_post_types as $post_type ) : $i ++; ?>
					<?php $post_type_object = get_post_type_object( $post_type ); ?>
					<tr <?php if ( $i % 2 == 1 ) echo 'class="alternate"'; ?>>
						<td class="<?php echo MWF_Config::NAME; ?>-table-title"><?php echo esc_html( $post_type_object->labels->singular_name ) ; ?></td>
						<td class="<?php echo MWF_Config::NAME; ?>-table-chart"><a href="<?php echo admin_url( 'edit.php?post_type=' . MWF_Config::NAME . '&page=' . MWF_Config::NAME . '-chart&formkey=' . $post_type ); ?>"><?php esc_html_e( 'Display Chart', MWF_Config::DOMAIN ); ?></td>
						<td class="<?php echo MWF_Config::NAME; ?>-table-count"><a href="<?php echo admin_url( 'edit.php?post_type=' . $post_type ); ?>"><?php echo esc_html( $this->get_count( $post_type ) ) ?> <?php esc_html_e( 'cases', MWF_Config::DOMAIN ); ?></a></td>
						<td class="<?php echo MWF_Config::NAME; ?>-table-date"><?php echo esc_html( $this->get_modified_datetime( $post_type ) ); ?></td>
						<td class="<?php echo MWF_Config::NAME; ?>-table-date"><?php echo esc_html( $this->get_created_datetime( $post_type ) ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<!-- end .wrap --></div>
		<?php
	}

	/**
	 * detail
	 */
	public function detail() {
		global $post;
		$Contact_Data_Setting = $this->get( 'Contact_Data_Setting' );
		$post_type = $this->get( 'post_type' );
		?>
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
						echo $this->get_multimedia_data( $value );
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
		<?php
	}

	/**
	 * returning_link
	 */
	public function returning_link() {
		$link = $this->get( 'link' );
		?>
		<p>
			<a href="<?php echo $link; ?>"><?php esc_html_e( '&laquo; Back to the list', MWF_Config::DOMAIN ); ?></a>
		</p>
		<?php
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
				$src = wp_get_attachment_image_src( $value, 'medium' );
				return sprintf(
					'<img src="%s" alt="" />',
					esc_url( $src[0] )
				);
			}
			// 画像以外
			else {
				$src = wp_get_attachment_image_src( $value, 'none', true );
				return sprintf(
					'<a href="%s" target="_blank"><img src="%s" alt="" /></a>',
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

	/**
	 * get_count
	 * @param string $post_type 投稿タイプ名
	 * @return numeric 投稿数
	 */
	protected function get_count( $post_type ) {
		$_args = apply_filters( 'mwform_get_inquiry_data_args-' . $post_type, array() );
		$args  = array(
			'post_type'      => $post_type,
			'posts_per_page' => 1,
		);
		if ( !empty( $_args ) && is_array( $_args ) ) {
			$args = array_merge( $_args, $args );
		}
		$query = new WP_Query( $args );
		return $query->found_posts;
	}

	/**
	 * get_created_datetime
	 * フォームの作成日時を取得
	 * @param string $post_type 投稿タイプ名
	 * @return string 作成日
	 */
	protected function get_created_datetime( $post_type ) {
		$post_id   = preg_replace( '/^mwf_(.+?)$/', '$1', $post_type );
		$post_date = get_the_date( get_option( 'date_format' ), $post_id );
		return $post_date;
	}

	/**
	 * get_modified_datetime
	 * 問い合わせデータの最新保存日を取得
	 * @param string $post_type 投稿タイプ名
	 * @return string 問い合わせデータの最新保存日
	 */
	protected function get_modified_datetime( $post_type ) {
		global $post;
		$inquiry_posts = get_posts( array(
			'post_type'      => $post_type,
			'posts_per_page' => 1,
			'orderby'        => 'modified',
		) );

		$modified_datetime = '';
		foreach ( $inquiry_posts as $post ) {
			setup_postdata( $post );
			$modified_datetime = get_the_modified_date();
			break;
		}
		wp_reset_postdata();
		return $modified_datetime;
	}
}