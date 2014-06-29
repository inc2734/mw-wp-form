<?php
/**
 * Name: MW WP Form Contact Data Page
 * URI: http://2inc.org
 * Description: DB保存データを扱うクラス
 * Version: 1.1.0
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : October 10, 2013
 * Modified: June 13, 2014
 * License: GPL2
 *
 * Copyright 2014 Takashi Kitajima (email : inc@2inc.org)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
class MW_WP_Form_Contact_Data_Page {

	private $POST_DATA_NAME;
	private $postdata;
	private $form_post_type = array();	// DB登録使用時のカスタム投稿タイプ名

	/**
	 * __construct
	 */
	public function __construct() {
		$this->POST_DATA_NAME = '_' . MWF_Config::NAME . '_data';
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_style' ) );
		add_action( 'admin_head', array( $this, 'cpt_public_false' ) );
		add_action( 'admin_head', array( $this, 'add_forms_columns' ) );
		add_action( 'admin_head', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_post' ) );
		add_action( 'in_admin_footer', array( $this, 'add_csv_download_button' ) );
		add_action( 'wp_loaded', array( $this, 'csv_download' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	/**
	 * get_post_data
	 * フォームの設定データを返す
	 */
	protected function get_post_data( $key ) {
		if ( isset( $this->postdata[$key] ) ) {
			return $this->postdata[$key];
		}
	}

	/**
	 * admin_style
	 * CSS適用
	 */
	public function admin_style() {
		$post_type = get_post_type();
		if ( in_array( $post_type, $this->form_post_type ) ) {
			$url = plugin_dir_url( __FILE__ );
			wp_register_style( MWF_Config::DOMAIN . '-admin', $url . '../css/admin.css' );
			wp_enqueue_style( MWF_Config::DOMAIN . '-admin' );
		}
	}

	/**
	 * register_post_type
	 * メインクラスから呼ばれる
	 */
	public function register_post_type() {
		$_posts = get_posts( array(
			'post_type' => MWF_Config::NAME,
			'posts_per_page' => -1
		) );
		foreach ( $_posts as $_post ) {
			$post_meta = get_post_meta( $_post->ID, MWF_Config::NAME, true );
			if ( empty( $post_meta['usedb'] ) )
				continue;

			$post_type = MWF_Config::DBDATA . $_post->ID;
			register_post_type( $post_type, array(
				'label' => $_post->post_title,
				'labels' => array(
					'name' => $_post->post_title,
					'singular_name' => $_post->post_title,
					'edit_item' => __( 'Edit ', MWF_Config::DOMAIN ) . ':' . $_post->post_title,
					'view_item' => __( 'View', MWF_Config::DOMAIN ) . ':' . $_post->post_title,
					'search_items' => __( 'Search', MWF_Config::DOMAIN ) . ':' . $_post->post_title,
					'not_found' => __( 'No data found', MWF_Config::DOMAIN ),
					'not_found_in_trash' => __( 'No data found in Trash', MWF_Config::DOMAIN ),
				),
				'capability_type' => 'page',
				'public' => false,
				'show_ui' => true,
				'show_in_menu' => false,
				'supports' => array( 'title' ),
			) );
			$this->form_post_type[] = $post_type;
		}
	}

	/**
	 * cpt_public_false
	 * DB登録データの一覧、詳細画面で新規追加のリンクを消す
	 */
	public function cpt_public_false() {
		if ( in_array( get_post_type(), $this->form_post_type ) ) : ?>
		<style type="text/css">
		h2 a.add-new-h2 {
			display: none;
		}
		</style>
		<?php
		endif;
	}

	/**
	 * add_csv_download_button
	 * CSVダウンロードボタンを表示
	 */
	public function add_csv_download_button() {
		$post_type = get_post_type();
		if ( true !== apply_filters( 'mwform_csv_button_' . $post_type, true ) )
			return;
		$page = ( basename( $_SERVER['PHP_SELF'] ) );
		if ( in_array( $post_type, $this->form_post_type ) && $page == 'edit.php' ) {
			$action = $_SERVER['REQUEST_URI'];
			?>
			<form id="mw-wp-form_csv" method="post" action="<?php echo esc_url( $action ); ?>">
				<input type="submit" value="<?php esc_attr_e( 'CSV Download', MWF_Config::DOMAIN ); ?>" class="button-primary" />
				<?php wp_nonce_field( MWF_Config::NAME ); ?>
			</form>
			<?php
		}
	}

	/**
	 * csv_download
	 * CSVを生成、出力
	 */
	public function csv_download() {
		if ( isset( $_GET['post_type'] ) ) {
			$post_type = $_GET['post_type'];
			if ( in_array( $post_type, $this->form_post_type ) && !empty( $_POST ) ) {
				check_admin_referer( MWF_Config::NAME );

				$posts_mwf = get_posts( array(
					'post_type' => $post_type,
					'posts_per_page' => -1,
					'post_status' => 'any',
				) );
				$csv = '';

				// 見出しを追加
				$rows[] = array( 'ID', 'post_date', 'post_modified', 'post_title' );
				foreach ( $posts_mwf as $post ) {
					setup_postdata( $post );
					$columns = array();
					foreach ( $posts_mwf as $post ) {
						$post_custom_keys = get_post_custom_keys( $post->ID );
						if ( ! empty( $post_custom_keys ) && is_array( $post_custom_keys ) ) {
							foreach ( $post_custom_keys as $key ) {
								if ( preg_match( '/^_/', $key ) )
									continue;
								$columns[$key] = $key;
							}
						}
					}
					$rows[0] = array_merge( $rows[0], $columns );
				}
				wp_reset_postdata();

				// 各データを追加
				foreach ( $posts_mwf as $post ) {
					setup_postdata( $post );
					$column = array();
					foreach ( $rows[0] as $key => $value ) {
						$column[$key] = '';
						if ( isset( $post->$value ) ) {
							$post_meta = $post->$value;
							if ( $this->is_upload_file_key( $post, $value ) ) {
								$column[$key] = wp_get_attachment_url( $post_meta );
							} else {
								$column[$key] = ( $post_meta ) ? $this->escape_double_quote( $post_meta ) : '';
							}
						}
					}
					$rows[] = $column;
				}
				wp_reset_postdata();

				// エンコード
				foreach ( $rows as $row ) {
					$csv .= implode( ',', $row ) . "\r\n";
				}
				$csv = mb_convert_encoding( $csv, 'sjis-win', get_option( 'blog_charset' ) );

				$file_name = 'mw_wp_form_' . date( 'YmdHis' ) . '.csv';
				header( 'Content-Type: application/octet-stream' );
				header( 'Content-Disposition: attachment; filename=' . $file_name );
				echo $csv;
				exit;
			}
		}
	}
	private function escape_double_quote( $value ) {
		$value = str_replace( '"', '""', $value );
		return '"' . $value . '"';
	}

	/**
	 * add_meta_box
	 */
	public function add_meta_box() {
		$post_type = get_post_type();
		if ( in_array( $post_type, $this->form_post_type ) ) {
			global $post;
			$this->postdata = get_post_meta( $post->ID, $this->POST_DATA_NAME, true );
			add_meta_box(
				substr( $this->POST_DATA_NAME, 1 ) . '_custom_fields',
				__( 'Custom Fields', MWF_Config::DOMAIN ),
				array( $this, 'custom_fields' ),
				$post_type
			);
		}
	}

	/**
	 * add_form_columns_name
	 * DB登録使用時に問い合わせデータ一覧にカラムを追加
	 */
	public function add_forms_columns() {
		global $wp_query;
		$post_type = get_post_type();
		if ( ! is_admin() )
			return;
		if ( ! in_array( $post_type, $this->form_post_type ) )
			return;
		add_filter( 'manage_' . $post_type . '_posts_columns', array( $this, 'add_form_columns_name' ) );
		add_action( 'manage_' . $post_type . '_posts_custom_column', array( $this, 'add_form_columns' ), 10, 2 );
	}
	public function add_form_columns_name( $columns ) {
		global $posts;
		unset( $columns['date'] );
		$columns['post_date'] = __( 'Registed Date', MWF_Config::DOMAIN );
		foreach ( $posts as $post ) {
			$post_custom_keys = get_post_custom_keys( $post->ID );
			if ( ! empty( $post_custom_keys ) && is_array( $post_custom_keys ) ) {
				foreach ( $post_custom_keys as $key ) {
					if ( preg_match( '/^_/', $key ) )
						continue;
					$columns[$key] = $key;
				}
			}
		}
		return $columns;
	}
	public function add_form_columns( $column, $post_id ) {
		global $post;
		$post_custom_keys = get_post_custom_keys( $post_id );

		if ( $column == 'post_date' ) {
			$post = get_post( $post_id );
			echo esc_html( $post->post_date );
		}
		elseif ( !empty( $post_custom_keys ) && is_array( $post_custom_keys ) && in_array( $column, $post_custom_keys ) ) {
			$post_meta = get_post_meta( $post_id, $column, true );
			if ( $this->is_upload_file_key( $post, $column ) ) {

				$mimetype = get_post_mime_type( $post_meta );
				if ( $mimetype ) {
					// 画像だったら
					if ( preg_match( '/^image\/.+?$/', $mimetype ) ) {
						$src = wp_get_attachment_image_src( $post_meta, 'thumbnail' );
						echo '<img src="' . esc_url( $src[0] ) .'" alt="" style="width:50px;height:50px" />';
					}
					// 画像以外
					else {
						$src = wp_get_attachment_image_src( $post_meta, 'none', true );
						echo '<a href="' . esc_url( wp_get_attachment_url( $post_meta ) ) .'" target="_blank">';
						echo '<img src="' . esc_url( $src[0] ) .'" alt="" style="height:50px" />';
						echo '</a>';
					}
				}

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
	 * custom_fields
	 * DB登録データの詳細画面にカスタムフィールドを表示
	 */
	public function custom_fields() {
		global $post;
		$post_custom = get_post_custom( $post->ID );

		if ( ! empty( $post_custom ) && is_array( $post_custom ) ) {
			?>
			<table border="0" cellpadding="0" cellspacing="0">
				<?php
				foreach ( $post_custom as $key => $value ) :
					if ( preg_match( '/^_/', $key ) ) continue;
					?>
				<tr>
					<th><?php echo esc_html( $key ); ?></th>
					<td>
						<?php
						if ( $this->is_upload_file_key( $post, $key ) ) {
							$mimetype = get_post_mime_type( $value[0] );
							if ( $mimetype ) {
								// 画像だったら
								if ( preg_match( '/^image\/.+?$/', $mimetype ) ) {
									$src = wp_get_attachment_image_src( $value[0], 'medium' );
									echo '<img src="' . esc_url( $src[0] ) .'" alt="" />';
								}
								// 画像以外
								else {
									$src = wp_get_attachment_image_src( $value[0], 'none', true );
									echo '<a href="' . esc_url( wp_get_attachment_url( $value[0] ) ) .'" target="_blank">';
									echo '<img src="' . esc_url( $src[0] ) .'" alt="" />';
									echo '</a>';
								}
							}
						} else {
							echo nl2br( esc_html( $value[0] ) );
						}
						?>
					</td>
				</tr>
				<?php endforeach; ?>
				<tr>
					<th><?php esc_html_e( 'Memo', MWF_Config::DOMAIN ); ?></th>
					<td><textarea name="<?php echo $this->POST_DATA_NAME; ?>[memo]" cols="50" rows="5"><?php echo $this->get_post_data( 'memo' ); ?></textarea></td>
				</tr>
			</table>
			<?php
		}
	}

	/**
	 * save_post
	 * @param	$post_ID
	 */
	public function save_post( $post_ID ) {
		if ( !( isset( $_POST['post_type'] ) && in_array( $_POST['post_type'], $this->form_post_type ) ) )
			return $post_ID;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_ID;
		if ( !current_user_can( MWF_Config::CAPABILITY ) )
			return $post_ID;

		// 保存可能なキー
		$permit_keys = array( 'memo' );
		$data = array();
		foreach ( $permit_keys as $key ) {
			if ( isset( $_POST[$this->POST_DATA_NAME][$key] ) )
				$data[$key] = $_POST[$this->POST_DATA_NAME][$key];
		}
		update_post_meta( $post_ID, $this->POST_DATA_NAME, $data, $this->postdata );
	}

	/**
	 * admin_menu
	 * 問い合わせデータ閲覧ページへのメニューを追加
	 */
	public function admin_menu() {
		if ( empty( $this->form_post_type ) )
			return;

		add_submenu_page(
			'edit.php?post_type=' . MWF_Config::NAME,
			__( 'Inquiry data', MWF_Config::DOMAIN ),  // ページタイトル
			__( 'Inquiry data', MWF_Config::DOMAIN ),  // メニュー名
			MWF_Config::CAPABILITY, // 権限
			MWF_Config::NAME . '-save-data', // 画面のパス
			array( $this, 'inquiry_data_page' ) // 表示用の関数
		);
	}

	/**
	 * inquiry_data_page
	 * 問い合わせデータ閲覧ページを表示
	 */
	public function inquiry_data_page() {
		?>
		<div class="wrap">
			<h2><?php esc_html_e( 'Inquiry data', MWF_Config::DOMAIN ); ?></h2>
			<p>
				<?php esc_html_e( 'You can see the inquiry data that are saved in the database by clicking on the link below.', MWF_Config::DOMAIN ); ?>
			</p>
			<table class="wp-list-table widefat fixed" cellspacing="0">
				<thead>
					<th class="<?php echo MWF_Config::NAME; ?>-table-title"><?php esc_html_e( 'Form title', MWF_Config::DOMAIN ); ?></th>
					<th class="<?php echo MWF_Config::NAME; ?>-table-count"><?php esc_html_e( 'The number of inquiries', MWF_Config::DOMAIN ); ?></th>
					<th class="<?php echo MWF_Config::NAME; ?>-table-date"><?php esc_html_e( 'Updated date', MWF_Config::DOMAIN ); ?></th>
					<th class="<?php echo MWF_Config::NAME; ?>-table-date"><?php esc_html_e( 'Created date', MWF_Config::DOMAIN ); ?></th>
				</thead>
				<tbody>
					<?php $i = 0; foreach ( $this->form_post_type as $post_type ) : $i ++; ?>
					<?php $post_type_object = get_post_type_object( $post_type ); ?>
					<tr <?php if ( $i % 2 == 1 ) echo 'class="alternate"'; ?>>
						<td class="<?php echo MWF_Config::NAME; ?>-table-title"><?php echo esc_html( $post_type_object->labels->singular_name ) ; ?></td>
						<td class="<?php echo MWF_Config::NAME; ?>-table-count"><a href="edit.php?post_type=<?php echo esc_attr( $post_type ); ?>"><?php echo esc_html( $this->get_count( $post_type ) ) ?> <?php esc_html_e( 'cases', MWF_Config::DOMAIN ); ?></a></td>
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
	 * get_count
	 * @param string $post_type 投稿タイプ名
	 * @return numeric 投稿数
	 */
	private function get_count( $post_type ) {
		global $wpdb;
		$count = $wpdb->prepare(
			"SELECT count(*) FROM $wpdb->posts WHERE post_type = '%s'",
			$post_type
		);
		return number_format( $wpdb->get_var( $count ) );
	}

	/**
	 * get_created_datetime
	 * フォームの作成日時を取得
	 * @param string $post_type 投稿タイプ名
	 * @return string 作成日
	 */
	private function get_created_datetime( $post_type ) {
		global $post;
		$post_id = preg_replace( '/^mwf_(.+?)$/', '$1', $post_type );
		$post = get_post( $post_id );
		$post_date = get_the_date();
		wp_reset_postdata();
		return $post_date;
	}

	/**
	 * get_modified_datetime
	 * 問い合わせデータの最新保存日を取得
	 * @param string $post_type 投稿タイプ名
	 * @return string 問い合わせデータの最新保存日
	 */
	private function get_modified_datetime( $post_type ) {
		global $post;
		$inquiry_posts = get_posts( array(
			'post_type' => $post_type,
			'posts_per_page' => 1,
			'orderby' => 'modified',
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

	/**
	 * get_upload_file_keys
	 * その投稿がもつ upload_file_key を取得
	 * @param object $post
	 * @return array $upload_file_keys
	 */
	private function get_upload_file_keys( $_post ) {
		// 前のバージョンでは MWF_Config::UPLOAD_FILE_KEYS を配列で保持していなかったので分岐させる
		$_upload_file_keys = get_post_meta( $_post->ID, '_' . MWF_Config::UPLOAD_FILE_KEYS, true );
		if ( is_array( $_upload_file_keys ) ) {
			$upload_file_keys = $_upload_file_keys;
		} else {
			$upload_file_keys = get_post_custom_values( '_' . MWF_Config::UPLOAD_FILE_KEYS, $_post->ID );
		}
		return $upload_file_keys;
	}

	/**
	 * is_upload_file_key
	 * $meta_key が $post の upload_file_key かどうか
	 * @param object $post
	 * @param string $meta_key
	 * @return bool
	 */
	private function is_upload_file_key( $_post, $meta_key ) {
		$upload_file_keys = $this->get_upload_file_keys( $_post );
		if ( is_array( $upload_file_keys ) && in_array( $meta_key, $upload_file_keys ) ) {
			return true;
		}
		return false;
	}
}