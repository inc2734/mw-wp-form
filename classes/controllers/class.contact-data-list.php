<?php
/**
 * Name       : MW WP Form Contact Data List Controller
 * Version    : 1.0.2
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : January 1, 2015
 * Modified   : February 14, 2015
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Contact_Data_List_Controller {

	/**
	 * initialize
	 */
	public function initialize() {
		add_action( 'current_screen', array( $this, 'current_screen' ) );
	}

	/**
	 * current_screen
	 * @param WP_Screen $screen
	 */
	public function current_screen( $screen ) {
		if ( preg_match( '/^edit-' . MWF_Config::DBDATA . '\d+$/', $screen->id ) ) {
			if ( !$this->is_contact_data_list() ) {
				exit;
			}
			$this->csv_download();
			add_action( 'pre_get_posts'        , array( $this, 'pre_get_posts' ) );
			add_action( 'admin_head'           , array( $this, 'add_columns' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
			add_action( 'admin_print_styles'   , array( $this, 'admin_print_styles' ) );
			add_action( 'in_admin_footer'      , array( $this, 'add_csv_download_button' ) );
			add_filter( 'wp_count_posts'       , array( $this, 'wp_count_posts' ), 10, 2 );
		}
	}

	/**
	 * pre_get_posts
	 * @param WP_Query $wp_query
	 */
	public function pre_get_posts( $wp_query ) {
		if ( $wp_query->is_main_query() ) {
			$post_type = $wp_query->get( 'post_type' );
			$args = apply_filters( 'mwform_get_inquiry_data_args-' . $post_type, array() );
			if ( !empty( $args ) && is_array( $args ) ) {
				foreach ( $args as $key => $value ) {
					$wp_query->set( $key, $value );
				}
			}
		}
	}

	/**
	 * admin_enqueue_scripts
	 */
	public function admin_enqueue_scripts() {
		$url = plugins_url( MWF_Config::NAME );
		wp_enqueue_style( MWF_Config::NAME . '-admin-data-list', $url . '/css/admin-data-list.css' );
		wp_enqueue_script( MWF_Config::NAME . '-admin-data-list', $url . '/js/admin-data-list.js' );
	}

	/**
	 * admin_print_styles
	 * DB登録データの一覧で新規追加のリンクを消す
	 */
	public function admin_print_styles() {
		$View = new MW_WP_Form_Contact_Data_List_View();
		$View->admin_print_styles_for_list();
	}

	/**
	 * add_csv_download_button
	 * CSVダウンロードボタンを表示
	 */
	public function add_csv_download_button() {
		$post_type = get_post_type();
		if ( true !== apply_filters( 'mwform_csv_button_' . $post_type, true ) ) {
			return;
		}
		$page = ( basename( $_SERVER['PHP_SELF'] ) );
		if ( $page !== 'edit.php' ) {
			return;
		}
		$action = $_SERVER['REQUEST_URI'];
		$View = new MW_WP_Form_Contact_Data_List_View();
		$View->set( 'action', $action );
		$View->csv_button();
	}

	/**
	 * csv_download
	 * CSVを生成、出力
	 */
	public function csv_download() {
		if ( !$this->is_contact_data_list() ) {
			return;
		}
		if ( !isset( $_GET['post_type'] ) ) {
			return ;
		}

		$post_type           = $_GET['post_type'];
		$key_of_csv_download = MWF_Config::NAME . '-csv-download';

		if ( !isset( $_POST[$key_of_csv_download] ) || !check_admin_referer( MWF_Config::NAME ) ) {
			return;
		}

		$posts_per_page = $this->get_posts_per_page();
		$paged          = $this->get_paged();

		$_args = apply_filters( 'mwform_get_inquiry_data_args-' . $post_type, array() );
		$args  = array(
			'post_type'      => $post_type,
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
		$csv = mb_convert_encoding( $csv, 'sjis-win', get_option( 'blog_charset' ) );

		$file_name = 'mw_wp_form_' . date( 'YmdHis' ) . '.csv';
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename=' . $file_name );
		echo $csv;
		exit;
	}

	/**
	 * get_csv_headings
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
			$post_type        = get_post_type( $post->ID );
			$post_custom_keys = get_post_custom_keys( $post->ID );
			$columns          = array();
			if ( !is_array( $post_custom_keys ) ) {
				continue;
			}
			foreach ( $post_custom_keys as $key ) {
				if ( preg_match( '/^_/', $key ) ) {
					continue;
				}
				if ( $key === MWF_Config::TRACKINGNUMBER ) {
					$column = MWF_Functions::get_tracking_number_title( $post_type );
				} else {
					$column = $key;
				}
				$columns[$key] = $column;
			}
		}
		$rows[0] = array_merge( $rows[0], $columns );
		$rows[0] = array_merge( $rows[0], array( __( 'Memo', MWF_Config::DOMAIN ) ) );
		return $rows[0];
	}

	/**
	 * get_rows
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
				} elseif ( isset( $post->$value ) ) {
					$post_meta = $post->$value;
					if ( $Contact_Data_Setting->is_upload_file_key( $post, $value ) ) {
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
	 * escape_double_quote
	 * @param string $value
	 * @return string
	 */
	protected function escape_double_quote( $value ) {
		$value = str_replace( '"', '""', $value );
		return '"' . $value . '"';
	}

	/**
	 * get_posts_per_page
	 * @return int
	 */
	protected function get_posts_per_page() {
		$posts_per_page = -1;
		if ( ( isset( $_POST['download-all'] ) && $_POST['download-all'] === 'true' ) === false ) {
			$current_user = wp_get_current_user();
			$_posts_per_page = get_user_meta( $current_user->ID, 'edit_' . $post_type . '_per_page', true );
			if ( !empty( $_posts_per_page ) ) {
				$posts_per_page = $_posts_per_page;
			}
		}
		return $posts_per_page;
	}

	/**
	 * get_paged
	 * @return int
	 */
	protected function get_paged() {
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
	 * add_columns
	 * DB登録使用時に問い合わせデータ一覧にカラムを追加
	 */
	public function add_columns() {
		$post_type = get_post_type();
		add_filter(
			'manage_' . $post_type . '_posts_columns',
			array( $this, 'add_form_columns_name' )
		);
		add_action(
			'manage_' . $post_type . '_posts_custom_column',
			array( $this, 'add_form_columns' ),
			10,
			2
		);
	}

	/**
	 * add_form_columns_name
	 * @param array $columns
	 * @return array $columns
	 */
	public function add_form_columns_name( $columns ) {
		global $posts;
		unset( $columns['date'] );
		$columns['post_date']       = __( 'Registed Date', MWF_Config::DOMAIN );
		$columns['response_status'] = __( 'Response Status', MWF_Config::DOMAIN );
		foreach ( $posts as $post ) {
			$post_custom_keys = get_post_custom_keys( $post->ID );
			if ( ! empty( $post_custom_keys ) && is_array( $post_custom_keys ) ) {
				foreach ( $post_custom_keys as $key ) {
					if ( preg_match( '/^_/', $key ) ) {
						continue;
					}
					if ( $key === MWF_Config::TRACKINGNUMBER ) {
						$columns[$key] = MWF_Functions::get_tracking_number_title( get_post_type( $post->ID ) );
						continue;
					}
					$columns[$key] = $key;
				}
			}
		}
		return $columns;
	}

	/**
	 * add_form_columns_name
	 * @param string $column カラム名
	 * @param int $post_id
	 */
	public function add_form_columns( $column, $post_id ) {
		$post_custom_keys     = get_post_custom_keys( $post_id );
		$Contact_Data_Setting = new MW_WP_Form_Contact_Data_Setting( $post_id );
		$View = new MW_WP_Form_Contact_Data_List_View();
		$View->set( 'column', $column );
		$View->set( 'post_id', $post_id );
		$View->set( 'post_custom_keys', $post_custom_keys );
		$View->set( 'Contact_Data_Setting', $Contact_Data_Setting );
		$View->manage_posts_custom_column();
	}

	/**
	 * wp_count_posts
	 * @param object $counts
	 * @param string $type 投稿タイプ名
	 * @return object
	 */
	public function wp_count_posts( $counts, $type ) {
		$args = apply_filters( 'mwform_get_inquiry_data_args-' . $type, array() );
		if ( !empty( $args ) && is_array( $args ) ) {
			$args = array_merge( $args, array(
				'post_type'      => $type,
				'posts_per_page' => 1,
			) );

			foreach ( $counts as $key => $count ) {
				$query = new WP_Query( array_merge( $args, array( 'post_status' => $key ) ) );
				$counts->$key = $query->found_posts;
			}
		}
		return $counts;
	}

	/**
	 * is_contact_data_list
	 * @return bool
	 */
	protected function is_contact_data_list() {
		if ( isset( $_GET['post_type'] ) ) {
			$post_type = $_GET['post_type'];
			$contact_data_post_types = MW_WP_Form_Contact_Data_Setting::get_posts();
			if ( in_array( $post_type, $contact_data_post_types ) ) {
				return true;
			}
		}
		return false;
	}
}