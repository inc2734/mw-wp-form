<?php
/**
 * Name       : MW WP Form Contact Data List Controller
 * Version    : 1.2.0
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : January 1, 2015
 * Modified   : March 26, 2016
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Contact_Data_List_Controller extends MW_WP_Form_Controller {

	/**
	 * @var string
	 */
	protected $post_type;

	/**
	 * initialize
	 */
	public function initialize() {
		$contact_data_post_types = MW_WP_Form_Contact_Data_Setting::get_posts();
		if ( !isset( $_GET['post_type'] ) ) {
			exit;
		}
		$this->post_type = $_GET['post_type'];
		if ( !in_array( $this->post_type, $contact_data_post_types ) ) {
			exit;
		}

		$CSV = new MW_WP_Form_CSV( $this->post_type );
		$CSV->download();

		add_action( 'pre_get_posts'        , array( $this, 'pre_get_posts' ) );
		add_action( 'admin_head'           , array( $this, 'add_columns' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'admin_print_styles'   , array( $this, 'admin_print_styles' ) );
		add_action( 'in_admin_footer'      , array( $this, 'add_csv_download_button' ) );
		add_filter( 'wp_count_posts'       , array( $this, 'wp_count_posts' ), 10, 2 );
	}

	/**
	 * フックで表示するレコードに変更があれば変更
	 *
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
	 * CSS と JS の読み込み
	 */
	public function admin_enqueue_scripts() {
		$url = plugins_url( MWF_Config::NAME );
		wp_enqueue_style( MWF_Config::NAME . '-admin-data-list', $url . '/css/admin-data-list.css' );
		wp_enqueue_script( MWF_Config::NAME . '-admin-data-list', $url . '/js/admin-data-list.js' );
	}

	/**
	 * DB登録データの一覧で新規追加のリンクを消す
	 */
	public function admin_print_styles() {
		$this->render( 'contact-data-list/admin-print-styles' );
	}

	/**
	 * CSVダウンロードボタンを表示
	 */
	public function add_csv_download_button() {
		if ( true !== apply_filters( 'mwform_csv_button_' . $this->post_type, true ) ) {
			return;
		}
		$page = ( basename( $_SERVER['PHP_SELF'] ) );
		if ( $page !== 'edit.php' ) {
			return;
		}
		$action = $_SERVER['REQUEST_URI'];
		$this->assign( 'action', $action );
		$this->render( 'contact-data-list/csv-button' );
	}

	/**
	 * DB登録使用時に問い合わせデータ一覧にカラムを追加
	 */
	public function add_columns() {
		add_filter(
			'manage_' . $this->post_type . '_posts_columns',
			array( $this, 'add_form_columns_name' )
		);
		add_action(
			'manage_' . $this->post_type . '_posts_custom_column',
			array( $this, 'add_form_columns' ),
			10,
			2
		);
	}

	/**
	 * カラム名を取得
	 *
	 * @param array $columns
	 * @return array $columns
	 */
	public function add_form_columns_name( $columns ) {
		global $posts;
		unset( $columns['date'] );
		$columns['post_date']       = __( 'Registed Date', 'mw-wp-form' );
		$columns['response_status'] = __( 'Response Status', 'mw-wp-form' );
		$_columns = array();
		foreach ( $posts as $post ) {
			$post_custom_keys = get_post_custom_keys( $post->ID );
			if ( !empty( $post_custom_keys ) && is_array( $post_custom_keys ) ) {
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
		}
		ksort( $_columns );
		$_columns = apply_filters( 'mwform_inquiry_data_columns-' . $this->post_type, $_columns );
		$columns  = array_merge( $columns, $_columns );
		return $columns;
	}

	/**
	 * 各カラムのデータを出力
	 *
	 * @param string $column カラム名
	 * @param int $post_id
	 */
	public function add_form_columns( $column, $post_id ) {
		$post                 = get_post( $post_id );
		$post_custom_keys     = get_post_custom_keys( $post_id );
		$Contact_Data_Setting = new MW_WP_Form_Contact_Data_Setting( $post_id );

		if ( $column === 'post_date' ) {
			$value = esc_html( $post->post_date );
		} elseif ( $column === 'response_status' ) {
			$response_statuses = $Contact_Data_Setting->get_response_statuses();
			$response_status   = $Contact_Data_Setting->get( 'response_status' );
			$value = $response_statuses[$response_status];
		} elseif ( is_array( $post_custom_keys ) && in_array( $column, $post_custom_keys ) ) {
			$post_meta = get_post_meta( $post_id, $column, true );
			if ( $Contact_Data_Setting->is_upload_file_key( $post, $column ) ) {
				// 過去バージョンでの不具合でメタデータが空になっていることがあるのでその場合は代替処理
				if ( $post_meta === '' ) {
					$post_meta = MWF_Functions::get_multimedia_id__fallback( $post, $column );
				}
				$value = MWF_Functions::get_multimedia_data( $post_meta );
			} elseif ( $post_meta ) {
				$value = esc_html( $post_meta );
			} else {
				$value = '&nbsp;';
			}
		} else {
			$value = '&nbsp;';
		}

		$this->assign( 'column', $value );
		$this->render( 'contact-data-list/column' );
	}

	/**
	 * 件数をカスタマイズ
	 *
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
}
