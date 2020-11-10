<?php
/**
 * @package mw-wp-form
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * MW_WP_Form_CSV
 */
class MW_WP_Form_CSV {

	/**
	 * @var string
	 */
	protected $post_type;

	/**
	 * Constructor.
	 *
	 * @param string $post_type Post type name.
	 */
	public function __construct( $post_type ) {
		$this->post_type = $post_type;
	}

	/**
	 * Download CSV.
	 */
	public function download() {
		$key_of_csv_download = MWF_Config::NAME . '-csv-download';

		if ( ! isset( $_POST[ $key_of_csv_download ] ) || ! check_admin_referer( MWF_Config::NAME ) ) {
			return;
		}

		$csv = $this->_generate_csv();

		$file_name = 'mw_wp_form_' . date_i18n( 'YmdHis' ) . '.csv';
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename=' . $file_name );
		echo $csv;
		exit;
	}

	/**
	 * Generate CSV.
	 *
	 * @return string
	 */
	protected function _generate_csv() {
		$posts_per_page = $this->_get_posts_per_page();
		$paged          = $this->_get_paged();

		$args = apply_filters( 'mwform_get_inquiry_data_args-' . $this->post_type, array() );
		if ( empty( $args ) || ! is_array( $args ) ) {
			$args = array();
		}
		$args = array_merge(
			$args,
			array(
				'post_type'      => $this->post_type,
				'posts_per_page' => $posts_per_page,
				'paged'          => $paged,
				'post_status'    => 'any',
			)
		);

		$posts_mwf = get_posts( $args );

		// CSVの内容を貯める
		$csv = '';

		// 見出しを追加
		$rows    = array();
		$rows[0] = $this->_get_csv_headings( $posts_mwf );

		// 各データを追加
		$rows = array_merge( $rows, $this->_get_rows( $posts_mwf, $rows[0] ) );

		// エンコード
		foreach ( $rows as $row ) {
			foreach ( $row as $column_name => $column ) {
				$row[ $column_name ] = $this->_escape_double_quote( $column );
			}
			$csv .= implode( ',', $row ) . "\r\n";
		}
		$to_encoding = apply_filters( 'mwform_csv_encoding-' . $this->post_type, 'sjis-win' );

		return mb_convert_encoding( $csv, $to_encoding, get_option( 'blog_charset' ) );
	}

	/**
	 * Return number of CSV output.
	 *
	 * @return int
	 */
	public function _get_posts_per_page() {
		if ( isset( $_POST['download-all'] ) && 'true' === $_POST['download-all'] ) {
			return -1;
		}

		$current_user   = wp_get_current_user();
		$posts_per_page = get_user_meta( $current_user->ID, 'edit_' . $this->post_type . '_per_page', true );
		if ( ! empty( $posts_per_page ) ) {
			return $posts_per_page;
		}

		return 20;
	}

	/**
	 * Return page number of CSV output.
	 *
	 * @return int
	 */
	public function _get_paged() {
		$posts_per_page = $this->_get_posts_per_page();
		if ( isset( $_GET['paged'] ) ) {
			if ( MWF_Functions::is_numeric( $_GET['paged'] ) && $posts_per_page > 0 ) {
				return $_GET['paged'];
			}
		}
		return 1;
	}

	/**
	 * Genrate headings of CSV.
	 *
	 * @param array $posts Array of WP_Post.
	 * @return array
	 */
	protected function _get_csv_headings( array $posts ) {
		$columns  = array(
			'ID'              => 'ID',
			'admin_mail_to'   => __( 'Admin Email To', 'mw-wp-form' ),
			'response_status' => __( 'Response Status', 'mw-wp-form' ),
			'post_date'       => 'post_date',
			'post_modified'   => 'post_modified',
			'post_title'      => 'post_title',
		);
		$_columns = array();
		foreach ( $posts as $post ) {
			$post_custom_keys = get_post_custom_keys( $post->ID );
			if ( ! is_array( $post_custom_keys ) ) {
				continue;
			}

			foreach ( $post_custom_keys as $key ) {
				if ( preg_match( '/^_/', $key ) ) {
					continue;
				}

				if ( MWF_Config::TRACKINGNUMBER === $key ) {
					$columns[ $key ] = MWF_Functions::get_tracking_number_title( $this->post_type );
					continue;
				}

				$_columns[ $key ] = $key;
			}
		}

		ksort( $_columns );
		$_columns = apply_filters( 'mwform_inquiry_data_columns-' . $this->post_type, $_columns );
		$columns  = array_merge( $columns, $_columns );
		$columns  = array_merge( $columns, array( 'memo' => __( 'Memo', 'mw-wp-form' ) ) );
		$columns  = apply_filters( 'mwform_csv_columns-' . $this->post_type, $columns );
		return $columns;
	}

	/**
	 * Generate rows of CSV.
	 *
	 * @param array $posts    Array of WP_Post.
	 * @param array $headings Headings of CSV.
	 * @return array
	 */
	protected function _get_rows( array $posts, array $headings ) {
		global $post;

		$rows = array();
		foreach ( $posts as $post ) {
			setup_postdata( $post );
			$columns = array();
			foreach ( $headings as $key => $value ) {
				$contact_data_setting = new MW_WP_Form_Contact_Data_Setting( $post->ID );
				$response_statuses    = $contact_data_setting->get_response_statuses();
				$column               = '';

				if ( 'response_status' === $key ) {
					$response_status = $contact_data_setting->get( 'response_status' );
					$column          = $response_statuses[ $response_status ];
				} elseif ( 'admin_mail_to' === $key ) {
					$column = $contact_data_setting->get( 'admin_mail_to' );
				} elseif ( 'memo' === $key ) {
					$column = $contact_data_setting->get( 'memo' );
				} elseif ( MWF_Config::TRACKINGNUMBER === $key ) {
					$column = get_post_meta( get_the_ID(), MWF_Config::TRACKINGNUMBER, true );
				} elseif ( isset( $post->$key ) ) {
					$post_meta = $post->$key;

					if ( $contact_data_setting->is_upload_file_key( $key ) ) {
						// 過去バージョンでの不具合でメタデータが空になっていることがあるのでその場合は代替処理
						if ( '' === $post_meta ) {
							$post_meta = MWF_Functions::get_multimedia_id__fallback( $post, $key );
						}
						$column = wp_get_attachment_url( $post_meta );
					} else {
						$column = ( '' === $post_meta || null === $post_meta || false === $post_meta ) ? '' : $post_meta;
					}
				}
				$columns[ $key ] = $column;
			}
			$rows[] = $columns;
		}
		wp_reset_postdata();
		return $rows;
	}

	/**
	 * Escape double quotes.
	 *
	 * @param string $value Value.
	 * @return string
	 */
	protected function _escape_double_quote( $value ) {
		$value = str_replace( '"', '""', $value );
		return '"' . $value . '"';
	}
}
