<?php
/**
 * Name       : MW WP Form CSV
 * Version    : 1.0.2
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : April 3, 2015
 * Modified   : May 26, 2015
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_CSV {

	/**
	 * @var string
	 */
	protected $post_type;

	/**
	 * __construct
	 */
	public function __construct( $post_type ) {
		$this->post_type = $post_type;
	}

	/**
	 * CSVを生成、出力
	 */
	public function download() {
		$key_of_csv_download = MWF_Config::NAME . '-csv-download';
		
		if ( !isset( $_POST[$key_of_csv_download] ) || !check_admin_referer( MWF_Config::NAME ) ) {
			return;
		}

		$posts_per_page = $this->get_posts_per_page();
		$paged          = $this->get_paged();

		$_args = apply_filters( 'mwform_get_inquiry_data_args-' . $this->post_type, array() );
		$args  = array(
			'post_type'      => $this->post_type,
			'posts_per_page' => $posts_per_page,
			'paged'          => $paged,
			'post_status'    => 'any',
		);
		if ( !empty( $_args ) && is_array( $_args ) ) {
			$args = array_merge( $_args, $args );
		}
		$posts_mwf = get_posts( $args );

		// CSVの内容を貯める
		$csv = '';

		// 見出しを追加
		$rows[0] = $this->get_csv_headings( $posts_mwf );

		// 各データを追加
		$rows = array_merge( $rows, $this->get_rows( $posts_mwf, $rows[0] ) );

		// エンコード
		foreach ( $rows as $key => $row ) {
			foreach ( $row as $column_name => $column ) {
				$row[$column_name] = $this->escape_double_quote( $column );
			}
			$csv .= implode( ',', $row ) . "\r\n";
		}
		$to_encoding = apply_filters( 'mwform_csv_encoding-' . $this->post_type, 'sjis-win' );
		$csv = mb_convert_encoding( $csv, $to_encoding, get_option( 'blog_charset' ) );

		$file_name = 'mw_wp_form_' . date( 'YmdHis' ) . '.csv';
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename=' . $file_name );
		echo $csv;
		exit;
	}

	/**
	 * CSVで出力する件数を取得
	 *
	 * @return int
	 */
	public function get_posts_per_page() {
		$posts_per_page = -1;
		if ( ( isset( $_POST['download-all'] ) && $_POST['download-all'] === 'true' ) === false ) {
			$current_user = wp_get_current_user();
			$_posts_per_page = get_user_meta( $current_user->ID, 'edit_' . $this->post_type . '_per_page', true );
			if ( !empty( $_posts_per_page ) ) {
				$posts_per_page = $_posts_per_page;
			} else {
				$posts_per_page = 20;
			}
		}
		return $posts_per_page;
	}

	/**
	 * CSVで出力するページ番号を取得
	 *
	 * @return int
	 */
	public function get_paged() {
		$posts_per_page = $this->get_posts_per_page();
		$paged = 1;
		if ( isset( $_GET['paged'] ) ) {
			$_paged = $_GET['paged'];
			if ( MWF_Functions::is_numeric( $_paged ) && $posts_per_page > 0 ) {
				$paged = $_paged;
			}
		}
		return $paged;
	}

	/**
	 * CSVの見出しを生成
	 *
	 * @param array $posts
	 * @return array
	 */
	protected function get_csv_headings( array $posts ) {
		$default_headings = array(
			'ID',
			__( 'Response Status', MWF_Config::DOMAIN ),
			'post_date',
			'post_modified',
			'post_title'
		);
		$rows[] = $default_headings;
		$columns = array();
		foreach ( $posts as $post ) {
			$post_custom_keys = get_post_custom_keys( $post->ID );
			if ( !is_array( $post_custom_keys ) ) {
				continue;
			}
			foreach ( $post_custom_keys as $key ) {
				if ( preg_match( '/^_/', $key ) ) {
					continue;
				}
				if ( $key === MWF_Config::TRACKINGNUMBER ) {
					$column = MWF_Functions::get_tracking_number_title( $this->post_type );
				} else {
					$column = $key;
				}
				$columns[$key] = $column;
			}
		}
		ksort( $columns );
		$rows[0] = array_merge( $rows[0], $columns );
		$rows[0] = array_merge( $rows[0], array( __( 'Memo', MWF_Config::DOMAIN ) ) );
		return $rows[0];
	}

	/**
	 * CSVの各行を生成
	 *
	 * @param array $posts
	 * @param array $headings
	 * @return array
	 */
	protected function get_rows( array $posts, array $headings ) {
		global $post;
		$rows = array();
		foreach ( $posts as $post ) {
			setup_postdata( $post );
			$columns = array();
			foreach ( $headings as $key => $value ) {
				$Contact_Data_Setting = new MW_WP_Form_Contact_Data_Setting( $post->ID );
				$response_statuses    = $Contact_Data_Setting->get_response_statuses();
				$column = '';
				if ( $value === __( 'Response Status', MWF_Config::DOMAIN ) ) {
					$response_status = $Contact_Data_Setting->get( 'response_status' );
					$column = $response_statuses[$response_status];
				} elseif ( $value === __( 'Memo', MWF_Config::DOMAIN ) ) {
					$column = $Contact_Data_Setting->get( 'memo' );
				} elseif ( $value === MWF_Functions::get_tracking_number_title( $this->post_type ) ) {
					$column = get_post_meta( get_the_ID(), MWF_Config::TRACKINGNUMBER, true );
				} elseif ( isset( $post->$value ) ) {
					$post_meta = $post->$value;
					if ( $Contact_Data_Setting->is_upload_file_key( $post, $value ) ) {
						// 過去バージョンでの不具合でメタデータが空になっていることがあるのでその場合は代替処理
						if ( $post_meta === '' ) {
							$post_meta = MWF_Functions::get_multimedia_id__fallback( $post, $value );
						}
						$column = wp_get_attachment_url( $post_meta );
					} else {
						$column = ( $post_meta ) ? $post_meta : '';
					}
				}
				$columns[$key] = $column;
			}
			$rows[] = $columns;
		}
		wp_reset_postdata();
		return $rows;
	}

	/**
	 * CSVのダブルクオートのエスケープ
	 *
	 * @param string $value
	 * @return string
	 */
	protected function escape_double_quote( $value ) {
		$value = str_replace( '"', '""', $value );
		return '"' . $value . '"';
	}
}
