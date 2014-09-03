<?php
/**
 * Name: MW WP Form Contact Data Page
 * Description: DB保存データを扱うクラス
 * Version: 1.2.1
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : October 10, 2013
 * Modified: September 1, 2014
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Contact_Data_Page {

	/**
	 * DB保存データ独自の項目（メモとか）の配列のキー
	 */
	private $POST_DATA_NAME;

	/**
	 * DB保存データ独自の項目（メモとか）
	 */
	private $postdata;

	/**
	 * 対応状況種別の一覧
	 */
	private $response_statuses = array();

	/**
	 * DB登録使用時のカスタム投稿タイプ名
	 */
	private $form_post_type = array();

	/**
	 * __construct
	 */
	public function __construct() {
		$this->POST_DATA_NAME = '_' . MWF_Config::NAME . '_data';
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_style' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_script' ) );
		add_action( 'admin_head', array( $this, 'add_style' ) );
		add_action( 'admin_head', array( $this, 'add_forms_columns' ) );
		add_action( 'admin_head', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_post' ) );
		add_action( 'in_admin_footer', array( $this, 'add_csv_download_button' ) );
		add_action( 'wp_loaded', array( $this, 'csv_download' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'edit_form_top', array( $this, 'display_returning_link' ) );

		$this->response_statuses = array(
			'not-supported' => esc_html__( 'Not supported', MWF_Config::DOMAIN ),
			'reservation' => esc_html__( 'Reservation', MWF_Config::DOMAIN ),
			'supported' => esc_html__( 'Supported', MWF_Config::DOMAIN ),
		);
	}

	/**
	 * get_post_data
	 * フォームの設定データを返す
	 * @param string $key 項目名
	 * @return string
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
			wp_register_style( MWF_Config::NAME . '-admin', $url . '../css/admin.css' );
			wp_enqueue_style( MWF_Config::NAME . '-admin' );
		}
	}

	/**
	 * admin_script
	 * JS適用
	 */
	public function admin_script() {
		$post_type = get_post_type();
		if ( in_array( $post_type, $this->form_post_type ) ) {
			$url = plugin_dir_url( __FILE__ );
			wp_register_script( MWF_Config::NAME . '-admin-data', $url . '../js/admin-data.js' );
			wp_enqueue_script( MWF_Config::NAME . '-admin-data' );
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
	 * add_style
	 * DB登録データの一覧、詳細画面で新規追加のリンクを消す
	 * 詳細画面でタイトル下の空白を消す
	 */
	public function add_style() {
		if ( in_array( get_post_type(), $this->form_post_type ) ) : ?>
		<style type="text/css">
		h2 a.add-new-h2 {
			display: none;
		}
		#normal-sortables {
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
				<input type="hidden" name="<?php echo esc_attr( MWF_Config::NAME . '-csv-download' ); ?>" value="1" />
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
		if ( !isset( $_GET['post_type'] ) )
			return ;

		$post_type = $_GET['post_type'];

		if ( in_array( $post_type, $this->form_post_type ) &&
			!empty( $_POST ) &&
			isset( $_POST[MWF_Config::NAME . '-csv-download'] ) &&
			check_admin_referer( MWF_Config::NAME ) ) {

			$posts_mwf = get_posts( array(
				'post_type' => $post_type,
				'posts_per_page' => -1,
				'post_status' => 'any',
			) );
			$csv = '';

			// 見出しを追加
			$default_headings = array(
				'ID',
				__( 'Response Status', MWF_Config::DOMAIN ),
				'post_date',
				'post_modified',
				'post_title'
			);
			$rows[] = $default_headings;
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
			$rows[0] = array_merge( $rows[0], array(
				__( 'Memo', MWF_Config::DOMAIN )
			) );

			// 各データを追加
			foreach ( $posts_mwf as $post ) {
				setup_postdata( $post );
				$column = array();
				foreach ( $rows[0] as $key => $value ) {
					$_column = '';
					if ( $value === __( 'Response Status', MWF_Config::DOMAIN ) ) {
						$_column = $this->get_post_data_value( 'response_status', $post->ID );
					} elseif ( $value === __( 'Memo', MWF_Config::DOMAIN ) ) {
						$_column = $this->get_post_data_value( 'memo', $post->ID );
					} elseif ( isset( $post->$value ) ) {
						$post_meta = $post->$value;
						if ( $this->is_upload_file_key( $post, $value ) ) {
							$_column = wp_get_attachment_url( $post_meta );
						} else {
							$_column = ( $post_meta ) ? $post_meta : '';
						}
					}
					$column[$key] = $this->escape_double_quote( $_column );
				}
				$rows[] = $column;
			}
			// 見出し行をエスケープ
			foreach ( $rows[0] as $key => $value ) {
				$rows[0][$key] = $this->escape_double_quote( $value );
			}
			wp_reset_postdata();

			// エンコード
			foreach ( $rows as $key => $row ) {
				if ( $key === 0 ) {
					foreach ( $row as $row_key => $column_name ) {
						if ( in_array( $column_name, array( 'Response Status', 'Memo' ) ) ) {
							$column_name = esc_html__( $column_name, MWF_Config::DOMAIN );
						}
						$row[$row_key] = $column_name;
					}
				}
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
		$columns['response_status'] = __( 'Response Status', MWF_Config::DOMAIN );
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
		elseif ( $column == 'response_status' ) {
			echo $this->get_post_data_value( 'response_status', $post_id );
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
					<th><?php esc_html_e( 'Response Status', MWF_Config::DOMAIN ); ?></th>
					<td>
						<select name="<?php echo $this->POST_DATA_NAME; ?>[response_status]">
							<?php foreach ( $this->response_statuses as $key => $value ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $this->get_post_data( 'response_status' ) ); ?>>
								<?php echo esc_html( $value ); ?>
							</option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
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
	 * @param int $post_ID
	 */
	public function save_post( $post_ID ) {
		if ( !( isset( $_POST['post_type'] ) && in_array( $_POST['post_type'], $this->form_post_type ) ) )
			return $post_ID;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_ID;
		if ( !current_user_can( MWF_Config::CAPABILITY ) )
			return $post_ID;

		// 保存可能なキー
		$permit_keys = array( 'memo', 'response_status' );
		$data = array();
		foreach ( $permit_keys as $key ) {
			if ( isset( $_POST[$this->POST_DATA_NAME][$key] ) ) {
				$value = $_POST[$this->POST_DATA_NAME][$key];
				if ( $key === 'response_status' ) {
					if ( !array_key_exists( $value, $this->response_statuses ) )
						continue;
				}
				$data[$key] = $value;
			}
		}
		update_post_meta( $post_ID, $this->POST_DATA_NAME, $data );
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
					<th class="<?php echo MWF_Config::NAME; ?>-table-chart"><?php esc_html_e( 'Display Chart', MWF_Config::DOMAIN ); ?></th>
					<th class="<?php echo MWF_Config::NAME; ?>-table-count"><?php esc_html_e( 'The number of inquiries', MWF_Config::DOMAIN ); ?></th>
					<th class="<?php echo MWF_Config::NAME; ?>-table-date"><?php esc_html_e( 'Updated date', MWF_Config::DOMAIN ); ?></th>
					<th class="<?php echo MWF_Config::NAME; ?>-table-date"><?php esc_html_e( 'Created date', MWF_Config::DOMAIN ); ?></th>
				</thead>
				<tbody>
					<?php $i = 0; foreach ( $this->form_post_type as $post_type ) : $i ++; ?>
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
	 * display_returning_link
	 * 問い合わせデータ詳細画面で一覧に戻るリンクを表示
	 * @param object $post
	 */
	public function display_returning_link( $post ) {
		$post_type = get_post_type();
		if ( in_array( $post_type, $this->form_post_type ) ) {
			$link = admin_url( '/edit.php?post_type=' . $post_type );
			?>
			<p>
				<a href="<?php echo $link; ?>"><?php esc_html_e( '&laquo; Back to the list', MWF_Config::DOMAIN ); ?></a>
			</p>
			<?php
		}
	}

	/**
	 * get_count
	 * @param string $post_type 投稿タイプ名
	 * @return numeric 投稿数
	 */
	private function get_count( $post_type ) {
		global $wpdb;
		$posts_contact = get_posts( array(
			'post_type' => $post_type,
			'posts_per_page' => -1,
		) );
		return count( $posts_contact );
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

	/**
	 * get_post_data_value
	 * DB保存データの編集画面で付け足した項目の値を取得（翻訳済み）
	 * @param string $key 項目名
	 * @param numeric $post_id
	 * @return string
	 */
	private function get_post_data_value( $key, $post_id ) {
		$post_data = get_post_meta( $post_id, $this->POST_DATA_NAME, true );

		if ( $key === 'response_status' ) {
			if ( is_array( $post_data ) && isset( $post_data[$key] ) && array_key_exists( $post_data[$key], $this->response_statuses ) ) {
				return esc_html__( $this->response_statuses[$post_data[$key]], MWF_Config::DOMAIN );
			} else {
				return esc_html__( $this->response_statuses['not-supported'], MWF_Config::DOMAIN );
			}
		}

		if ( is_array( $post_data ) && isset( $post_data[$key] ) ) {
			return $post_data[$key];
		}
	}
}