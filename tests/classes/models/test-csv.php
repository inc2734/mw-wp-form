<?php
class MW_WP_Form_CSV_Test extends WP_UnitTestCase {

	public function tearDown() {
		parent::tearDown();
		_delete_all_data();
	}

	protected function _create_inquiry_data() {
		$post_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );

		$Setting = new MW_WP_Form_Setting( $post_id );
		$Setting->set( 'usedb', 1 );

		return $this->factory->post->create_many(
			50,
			array(
				'post_type' => MWF_Functions::get_contact_data_post_type_from_form_id( $Setting->get( 'post_id' ) ),
			)
		);
	}

	/**
	 * Create new user and return the user id
	 *
	 * @return int
	 */
	protected function _set_current_user() {
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

	/**
	 * @test
	 * @group _get_posts_per_page
	 */
	public function _get_posts_per_page() {
		$posts = $this->_create_inquiry_data();
		$post_type = get_post_type( $posts[0] );
		$CSV = new MW_WP_Form_CSV( $post_type );

		$this->assertEquals( 20, $CSV->_get_posts_per_page() );

		$user_id = $this->_set_current_user();
		update_user_meta( $user_id, 'edit_' . $post_type . '_per_page', 10 );
		$this->assertEquals( 10, $CSV->_get_posts_per_page() );

		$_POST['download-all'] = 'true';
		$this->assertEquals( -1, $CSV->_get_posts_per_page() );

		$_POST['download-all'] = 'dummy';
		$this->assertEquals( 10, $CSV->_get_posts_per_page() );
	}

	/**
	 * @test
	 * @group _get_paged
	 */
	public function _get_paged() {
		$posts = $this->_create_inquiry_data();
		$post_type = get_post_type( $posts[0] );
		$CSV = new MW_WP_Form_CSV( $post_type );

		$this->assertEquals( 1, $CSV->_get_paged() );

		$_GET['paged'] = 2;
		$this->assertEquals( 2, $CSV->_get_paged() );

		$_GET['paged'] = 2;
		$_POST['download-all'] = 'true';
		$this->assertEquals( 1, $CSV->_get_paged() );
	}
}
