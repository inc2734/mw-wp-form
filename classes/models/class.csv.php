<?php
/**
 * Name       : MW WP Form CSV
 * Version    : 1.1.2
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : April 3, 2015
 * Modified   : May 28, 2016
 * License    : GPLv2 or later
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
	 * CSVを生成・出力
	 */
	public function download() {
		$key_of_csv_download = MWF_Config::NAME . '-csv-download';

		if ( !isset( $_POST[$key_of_csv_download] ) || !check_admin_referer( MWF_Config::NAME ) ) {
			return;
		}

		// max_execution_time の制約を回避
		set_time_limit( 0 );

		// ダウンロード条件を取得
		$args         = $this->get_query_args();
		$download_all = isset( $_POST['download-all'] ) && $_POST['download-all'] === 'true';

		// 全件ダウンロードする場合は全ページを列挙、しない場合は指定されたページのみ列挙
		$first_page = $this->get_first_page( $download_all );
		$last_page  = $this->get_last_page( $download_all, $args );

		// レスポンスヘッダーを出力
		$file_name = 'mw_wp_form_' . date( 'YmdHis' ) . '.csv';
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename=' . $file_name );

		// 出力範囲の問い合わせデータを基に見出しを作成
		$header = $this->get_csv_headings( $args, $first_page, $last_page );

		// 分割エンコード＆出力
		for ( $paged = $first_page; $paged <= $last_page; $paged ++ ) {
			$posts_mwf = $this->get_split_posts( $args, $paged );

			// CSVの内容を貯める
			$rows = array();
			$csv  = '';

			// 見出しを追加
			if ( $paged == $first_page ) {
				$rows[] = $header;
			}

			// 各データを追加
			$rows = array_merge( $rows, $this->get_rows( $posts_mwf, $header ) );

			// エンコード
			foreach ( $rows as $key => $row ) {
				foreach ( $row as $column_name => $column ) {
					$row[ $column_name ] = $this->escape_double_quote( $column );
				}
				$csv .= implode( ',', $row ) . "\r\n";
			}
			$to_encoding = apply_filters( 'mwform_csv_encoding-' . $this->post_type, 'sjis-win' );
			$csv         = mb_convert_encoding( $csv, $to_encoding, get_option( 'blog_charset' ) );
			echo $csv;

			// キャッシュされた投稿データと投稿メタデータを開放
			wp_cache_flush();
		}
		exit;
	}

	/**
	 * 分割エンコードする際の最初のページ番号を返す
	 *
	 * @param bool $download_all
	 * @return int
	 */
	public function get_first_page( $download_all ) {
		if ( $download_all ) {
			return 1;
		}
		return $this->get_paged();
	}

	/**
	 * 分割エンコードする際の最後のページ番号を返す
	 *
	 * @param bool $download_all
	 * @param array $args 問い合わせデータ一覧の表示条件
	 * @return int
	 */
	public function get_last_page( $download_all, $args ) {
		$posts_per_page = $this->get_posts_per_page();
		if ( $download_all ) {
			return ceil( $this->get_count( $args ) / $posts_per_page );
		}
		return 1;
	}

	/**
	 * CSVで出力する件数を取得
	 *
	 * @return int
	 */
	public function get_posts_per_page() {
		$current_user    = wp_get_current_user();
		$_posts_per_page = get_user_meta( $current_user->ID, 'edit_' . $this->post_type . '_per_page', true );
		if ( !empty( $_posts_per_page ) ) {
			$posts_per_page = $_posts_per_page;
		} else {
			$posts_per_page = 20;
		}
		return $posts_per_page;
	}

	/**
	 * CSVで出力するページ番号を取得
	 *
	 * @return int
	 */
	public function get_paged() {
		$paged = 1;
		if ( isset( $_GET['paged'] ) ) {
			$_paged = $_GET['paged'];
			if ( MWF_Functions::is_numeric( $_paged ) ) {
				$paged = $_paged;
			}
		}
		return $paged;
	}

	/**
	 * データ件数を取得
	 *
	 * @param array $args
	 * @return int 投稿数
	 */
	public function get_count( array $args ) {
		$args['posts_per_page'] = 1;
		$query                  = new WP_Query( $args );

		return $query->found_posts;
	}

	/**
	 * 問い合わせデータ取得クエリ用の引数を生成
	 * $args には paged, posts_per_page は含んではいけない。
	 *
	 * @return array
	 */
	public function get_query_args() {
		$_args = apply_filters( 'mwform_get_inquiry_data_args-' . $this->post_type, array() );
		$args  = array(
			'post_type'   => $this->post_type,
			'post_status' => 'any',
		);
		if ( !empty( $_args ) && is_array( $_args ) ) {
			$args = array_merge( $_args, $args );
		}
		if ( isset( $args['paged'] ) ) {
			unset( $args['paged'] );
		}
		if ( isset( $args['posts_per_page'] ) ) {
			unset( $args['posts_per_page'] );
		}

		return $args;
	}

	/**
	 * CSVの見出しを生成
	 *
	 * @param array $args
	 * @param int   $first_page
	 * @param int   $last_page
	 * @return array
	 */
	protected function get_csv_headings( array $args, $first_page, $last_page ) {
		$columns = array(
			'ID'              => 'ID',
			'response_status' => __( 'Response Status', 'mw-wp-form' ),
			'post_date'       => 'post_date',
			'post_modified'   => 'post_modified',
			'post_title'      => 'post_title',
		);
		$_columns = array();

		for ( $paged = $first_page; $paged <= $last_page; $paged ++ ) {
			$posts = $this->get_split_posts( $args, $paged );

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
						$columns[$key] = MWF_Functions::get_tracking_number_title( $this->post_type );
						continue;
					}
					$_columns[$key] = $key;
				}
			}

			// キャッシュされた投稿データと投稿メタデータを開放
			wp_cache_flush();
		}

		ksort( $_columns );
		$_columns = apply_filters( 'mwform_inquiry_data_columns-' . $this->post_type, $_columns );
		$columns = array_merge( $columns, $_columns );
		$columns = array_merge( $columns, array( 'memo' => __( 'Memo', 'mw-wp-form' ) ) );
		return $columns;
	}

	/**
	 * 問い合わせデータの分割取得
	 *
	 * @param array $args データ取得条件
	 * @param int 取得するページ番号
	 * @return array 問い合わせデータの配列
	 */
	public function get_split_posts( array $args, $paged ) {
			$args['paged']          = $paged;
			$args['posts_per_page'] = $this->get_posts_per_page();
			return get_posts( $args );
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
				if ( $key === 'response_status' ) {
					$response_status = $Contact_Data_Setting->get( 'response_status' );
					$column = $response_statuses[$response_status];
				} elseif ( $key === 'memo' ) {
					$column = $Contact_Data_Setting->get( 'memo' );
				} elseif ( $key === MWF_Config::TRACKINGNUMBER ) {
					$column = get_post_meta( get_the_ID(), MWF_Config::TRACKINGNUMBER, true );
				} elseif ( isset( $post->$key ) ) {
					$post_meta = $post->$key;
					if ( $Contact_Data_Setting->is_upload_file_key( $post, $key ) ) {
						// 過去バージョンでの不具合でメタデータが空になっていることがあるのでその場合は代替処理
						if ( $post_meta === '' ) {
							$post_meta = MWF_Functions::get_multimedia_id__fallback( $post, $key );
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
