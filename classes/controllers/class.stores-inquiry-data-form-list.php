<?php
/**
 * Name       : MW WP Form Stores Inquiry Data Form List Controller
 * Version    : 1.0.0
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : March 27, 2015
 * Modified   : 
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Stores_Inquiry_Data_Form_List_Controller extends MW_WP_Form_Controller {

	public function index() {
		$contact_data_post_types = MW_WP_Form_Contact_Data_Setting::get_posts();
		$form_list = array();
		foreach ( $contact_data_post_types as $post_type ) {
			$post_type_object = get_post_type_object( $post_type );
			$form_list[$post_type] = array(
				'title'             => $post_type_object->labels->singular_name,
				'count'             => $this->get_count( $post_type ),
				'modified_datetime' => $this->get_modified_datetime( $post_type ),
				'created_datetime'  => $this->get_created_datetime( $post_type )
			);
		}
		$this->assign( 'form_list', $form_list );
		$this->render( 'stores-inquiry-data-form-list/index' );
	}

	/**
	 * データ件数を取得
	 *
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
	 * フォームの作成日時を取得
	 *
	 * @param string $post_type 投稿タイプ名
	 * @return string 作成日
	 */
	protected function get_created_datetime( $post_type ) {
		$post_id   = preg_replace( '/^mwf_(.+?)$/', '$1', $post_type );
		$post_date = get_the_date( get_option( 'date_format' ), $post_id );
		return $post_date;
	}

	/**
	 * 問い合わせデータの最新保存日を取得
	 *
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