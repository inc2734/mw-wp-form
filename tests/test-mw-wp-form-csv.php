<?php
class MW_WP_Form_CSV_Test extends WP_UnitTestCase {

	/**
	 * @var MW_WP_Form_CSV
	 */
	protected $CSV;

	/**
	 * @var string
	 */
	protected $post_type;

	/**
	 * setUp
	 */
	public function setUp() {
		parent::setUp();

		$post_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$Setting = new MW_WP_Form_Setting( $post_id );
		$Setting->set( 'usedb', 1 );

		$post_ids = $this->factory->post->create_many(
			50,
			array(
				'post_type' => MWF_Functions::get_contact_data_post_type_from_form_id( $Setting->get( 'post_id' ) ),
			)
		);

		$this->post_type = get_post_type( $post_ids[0] );
		$this->CSV = new MW_WP_Form_CSV( $this->post_type );
	}

	/**
	 * @group get_posts_per_page
	 */
	public function test_get_posts_per_page_Allなし_表示件数設定なし() {
		$this->assertEquals( 20, $this->CSV->get_posts_per_page() );
	}

	/**
	 * @group get_posts_per_page
	 */
	public function test_get_posts_per_page_Allなし_表示件数設定あり() {
		$user_id = $this->set_current_user();
		update_user_meta( $user_id, 'edit_' . $this->post_type . '_per_page', 10 );
		$this->assertEquals( 10, $this->CSV->get_posts_per_page() );
	}

	/**
	 * @group get_first_page
	 */
	public function test_get_first_page() {
		$this->assertEquals( 1, $this->CSV->get_first_page( true ) );
		$this->assertEquals( 1, $this->CSV->get_first_page( false ) );
		$_GET['paged'] = 2;
		$this->assertEquals( 2, $this->CSV->get_first_page( false ) );
	}

	/**
	 * @group get_last_page
	 */
	public function test_get_last_page() {
		$args = $this->CSV->get_query_args();
		$posts_per_page = $this->CSV->get_posts_per_page();
		$rows_count = $this->CSV->get_count( $args );
		$this->assertEquals( ceil( $rows_count / $posts_per_page ), $this->CSV->get_last_page( true, $args ) );

		$this->assertEquals( 1, $this->CSV->get_last_page( false, $args ) );
	}

	/**
	 * @group get_paged
	 */
	public function test_get_paged_1ページ目() {
		$this->assertEquals( 1, $this->CSV->get_paged() );
	}

	/**
	 * @group get_paged
	 */
	public function test_get_paged_2ページ目() {
		$_GET['paged'] = 2;
		$this->assertEquals( 2, $this->CSV->get_paged() );
	}

	/**
	 * @group get_query_args
	 */
	public function test_get_query_args() {
		$expected = array(
			'post_type'   => $this->post_type,
			'post_status' => 'any',
		);

		$this->assertEquals( $expected, $this->CSV->get_query_args() );

		add_filter( 'mwform_get_inquiry_data_args-' . $this->post_type, function( $args ) {
			return array_merge( $args, array(
				'posts_per_page' => -1,
				'paged'          => 1,
			) );
		} );

		$this->assertEquals( $expected, $this->CSV->get_query_args() );
	}

	/**
	 * @group get_query_args
	 */
	public function test_get_query_args_add_filter() {
		add_filter( 'mwform_get_inquiry_data_args-' . $this->post_type, function( $args ) {
			return array_merge( $args, array(
				'meta_query' => array(
					array(
						'key'   => '予約日',
						'value' => '2015-01-01',
					),
				),
			) );
		} );

		$this->assertEquals( array(
			'post_type'   => $this->post_type,
			'post_status' => 'any',
			'meta_query' => array(
				array(
					'key'   => '予約日',
					'value' => '2015-01-01',
				),
			),
		), $this->CSV->get_query_args() );
	}

	/**
	 * @group get_count
	 * @depends test_get_query_args
	 */
	public function test_get_count() {
		$args = $this->CSV->get_query_args();
		$this->assertEquals( 50, $this->CSV->get_count( $args ) );
	}

	/**
	 * ユーザーを作成して current_user に設定
	 *
	 * @return int
	 */
	protected function set_current_user() {
		global $current_user;
		$user_attributes = array(
			'user_login'   => 'user_login',
			'user_email'   => 'info@example.com',
			'user_url'     => 'http://example.com',
			'user_login'   => 'user_login',
			'display_name' => 'display_name',
		);
		$user_id = $this->factory->user->create( $user_attributes );
		$current_user = get_userdata( $user_id );
		return $user_id;
	}
}
