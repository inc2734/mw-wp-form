<?php
class MW_WP_Form_Redirected_Test extends WP_UnitTestCase {

	protected function _create_form() {
		return $this->factory->post->create(
			array(
				'post_type' => MWF_Config::NAME,
			)
		);
	}

	/**
	 * tearDown
	 */
	public function tearDown() {
		parent::tearDown();
		_delete_all_data();
	}

	/**
	 * @test
	 * @group get_url
	 */
	public function get_url__each_url_are_blank() {
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );

		$Setting = new MW_WP_Form_Setting( $form_id );
		$Setting->set( 'input_url'           , '' );
		$Setting->set( 'confirmation_url'    , '' );
		$Setting->set( 'complete_url'        , '' );
		$Setting->set( 'validation_error_url', '' );

		// Pattern: ! $is_valid, back
		$is_valid       = false;
		$post_condition = 'back';
		$Redirected = new MW_WP_Form_Redirected( $form_key, $Setting, $is_valid, $post_condition );
		$this->assertEquals( '', $Redirected->get_url() );

		// Pattern: ! $is_valid, confirm
		$is_valid       = false;
		$post_condition = 'confirm';
		$Redirected = new MW_WP_Form_Redirected( $form_key, $Setting, $is_valid, $post_condition );
		$this->assertEquals( '', $Redirected->get_url() );

		// Pattern: ! $is_valid, complete
		$is_valid       = false;
		$post_condition = 'complete';
		$Redirected = new MW_WP_Form_Redirected( $form_key, $Setting, $is_valid, $post_condition );
		$this->assertEquals( '', $Redirected->get_url() );

		// Pattern: $is_valid, back
		$is_valid       = true;
		$post_condition = 'back';
		$Redirected = new MW_WP_Form_Redirected( $form_key, $Setting, $is_valid, $post_condition );
		$this->assertEquals( '', $Redirected->get_url() );

		// Pattern: $is_valid, confirm
		$is_valid       = true;
		$post_condition = 'confirm';
		$Redirected = new MW_WP_Form_Redirected( $form_key, $Setting, $is_valid, $post_condition );
		$this->assertEquals( '', $Redirected->get_url() );

		// Pattern: $is_valid, complete
		$is_valid       = true;
		$post_condition = 'complete';
		$Redirected = new MW_WP_Form_Redirected( $form_key, $Setting, $is_valid, $post_condition );
		$this->assertEquals( '', $Redirected->get_url() );
	}

	/**
	 * @test
	 * @group get_url
	 */
	public function get_url__each_url_are_set_path() {
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );

		$Setting = new MW_WP_Form_Setting( $form_id );
		$Setting->set( 'input_url'           , '/contact/' );
		$Setting->set( 'confirmation_url'    , '/contact/confirm/' );
		$Setting->set( 'complete_url'        , '/contact/complete/' );
		$Setting->set( 'validation_error_url', '/contact/error/' );

		// Pattern: ! $is_valid, back
		$is_valid       = false;
		$post_condition = 'back';
		$Redirected = new MW_WP_Form_Redirected( $form_key, $Setting, $is_valid, $post_condition );
		$this->assertEquals( home_url( '/contact/error/' ), $Redirected->get_url() );

		// Pattern: ! $is_valid, confirm
		$is_valid       = false;
		$post_condition = 'confirm';
		$Redirected = new MW_WP_Form_Redirected( $form_key, $Setting, $is_valid, $post_condition );
		$this->assertEquals( home_url( '/contact/error/' ), $Redirected->get_url() );

		// Pattern: ! $is_valid, complete
		$is_valid       = false;
		$post_condition = 'complete';
		$Redirected = new MW_WP_Form_Redirected( $form_key, $Setting, $is_valid, $post_condition );
		$this->assertEquals( home_url( '/contact/error/' ), $Redirected->get_url() );

		// Pattern: $is_valid, back
		$is_valid       = true;
		$post_condition = 'back';
		$Redirected = new MW_WP_Form_Redirected( $form_key, $Setting, $is_valid, $post_condition );
		$this->assertEquals( home_url( '/contact/' ), $Redirected->get_url() );

		// Pattern: $is_valid, confirm
		$is_valid       = true;
		$post_condition = 'confirm';
		$Redirected = new MW_WP_Form_Redirected( $form_key, $Setting, $is_valid, $post_condition );
		$this->assertEquals( home_url( '/contact/confirm/' ), $Redirected->get_url() );

		// Pattern: $is_valid, complete
		$is_valid       = true;
		$post_condition = 'complete';
		$Redirected = new MW_WP_Form_Redirected( $form_key, $Setting, $is_valid, $post_condition );
		$this->assertEquals( home_url( '/contact/complete/' ), $Redirected->get_url() );
	}

	/**
	 * @test
	 * @group get_url
	 */
	public function get_url__each_url_are_set_url() {
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );

		$Setting = new MW_WP_Form_Setting( $form_id );
		$Setting->set( 'input_url'           , home_url( '/contact/' ) );
		$Setting->set( 'confirmation_url'    , home_url( '/contact/confirm/' ) );
		$Setting->set( 'complete_url'        , home_url( '/contact/complete/' ) );
		$Setting->set( 'validation_error_url', home_url( '/contact/error/' ) );

		// Pattern: ! $is_valid, back
		$is_valid       = false;
		$post_condition = 'back';
		$Redirected = new MW_WP_Form_Redirected( $form_key, $Setting, $is_valid, $post_condition );
		$this->assertEquals( home_url( '/contact/error/' ), $Redirected->get_url() );

		// Pattern: ! $is_valid, confirm
		$is_valid       = false;
		$post_condition = 'confirm';
		$Redirected = new MW_WP_Form_Redirected( $form_key, $Setting, $is_valid, $post_condition );
		$this->assertEquals( home_url( '/contact/error/' ), $Redirected->get_url() );

		// Pattern: ! $is_valid, complete
		$is_valid       = false;
		$post_condition = 'complete';
		$Redirected = new MW_WP_Form_Redirected( $form_key, $Setting, $is_valid, $post_condition );
		$this->assertEquals( home_url( '/contact/error/' ), $Redirected->get_url() );

		// Pattern: $is_valid, back
		$is_valid       = true;
		$post_condition = 'back';
		$Redirected = new MW_WP_Form_Redirected( $form_key, $Setting, $is_valid, $post_condition );
		$this->assertEquals( home_url( '/contact/' ), $Redirected->get_url() );

		// Pattern: $is_valid, confirm
		$is_valid       = true;
		$post_condition = 'confirm';
		$Redirected = new MW_WP_Form_Redirected( $form_key, $Setting, $is_valid, $post_condition );
		$this->assertEquals( home_url( '/contact/confirm/' ), $Redirected->get_url() );

		// Pattern: $is_valid, complete
		$is_valid       = true;
		$post_condition = 'complete';
		$Redirected = new MW_WP_Form_Redirected( $form_key, $Setting, $is_valid, $post_condition );
		$this->assertEquals( home_url( '/contact/complete/' ), $Redirected->get_url() );
	}

	/**
	 * @test
	 * @group get_url
	 */
	public function get_url__each_url_set_querystring() {
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );

		$_GET = array(
			'foo'     => 'bar',
			'post_id' => 1,
		);

		$Setting = new MW_WP_Form_Setting( $form_id );
		$Setting->set( 'input_url'           , home_url( '/contact/?post_id=2' ) );
		$Setting->set( 'confirmation_url'    , home_url( '/contact/confirm/?post_id=2' ) );
		$Setting->set( 'complete_url'        , home_url( '/contact/complete/?post_id=2' ) );
		$Setting->set( 'validation_error_url', home_url( '/contact/error/?post_id=2' ) );

		// Pattern: ! $is_valid, back
		$is_valid       = false;
		$post_condition = 'back';
		$Redirected = new MW_WP_Form_Redirected( $form_key, $Setting, $is_valid, $post_condition );
		$this->assertEquals( home_url( '/contact/error/?post_id=2' ), $Redirected->get_url() );

		// Pattern: ! $is_valid, confirm
		$is_valid       = false;
		$post_condition = 'confirm';
		$Redirected = new MW_WP_Form_Redirected( $form_key, $Setting, $is_valid, $post_condition );
		$this->assertEquals( home_url( '/contact/error/?post_id=2' ), $Redirected->get_url() );

		// Pattern: ! $is_valid, complete
		$is_valid       = false;
		$post_condition = 'complete';
		$Redirected = new MW_WP_Form_Redirected( $form_key, $Setting, $is_valid, $post_condition );
		$this->assertEquals( home_url( '/contact/error/?post_id=2' ), $Redirected->get_url() );

		// Pattern: $is_valid, back
		$is_valid       = true;
		$post_condition = 'back';
		$Redirected = new MW_WP_Form_Redirected( $form_key, $Setting, $is_valid, $post_condition );
		$this->assertEquals( home_url( '/contact/?post_id=2' ), $Redirected->get_url() );

		// Pattern: $is_valid, confirm
		$is_valid       = true;
		$post_condition = 'confirm';
		$Redirected = new MW_WP_Form_Redirected( $form_key, $Setting, $is_valid, $post_condition );
		$this->assertEquals( home_url( '/contact/confirm/?post_id=2' ), $Redirected->get_url() );

		// Pattern: $is_valid, complete
		$is_valid       = true;
		$post_condition = 'complete';
		$Redirected = new MW_WP_Form_Redirected( $form_key, $Setting, $is_valid, $post_condition );
		$this->assertEquals( home_url( '/contact/complete/?post_id=2' ), $Redirected->get_url() );
	}

	/**
	 * @test
	 * @group get_url
	 */
	public function get_url__each_url_set_querystring__using_querystring() {
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );

		$_GET = array(
			'foo'     => 'bar',
			'post_id' => 1,
			'with_asterisk' => 'foo*bar',
			'with_space' => 'with space',
			'multibyte' => 'マルチバイト',
		);

		$Setting = new MW_WP_Form_Setting( $form_id );
		$Setting->set( 'querystring'         , 1);
		$Setting->set( 'input_url'           , home_url( '/contact/?post_id=2' ) );
		$Setting->set( 'confirmation_url'    , home_url( '/contact/confirm/?post_id=2' ) );
		$Setting->set( 'complete_url'        , home_url( '/contact/complete/?post_id=2' ) );
		$Setting->set( 'validation_error_url', home_url( '/contact/error/?post_id=2' ) );

		// Pattern: ! $is_valid, back
		$is_valid       = false;
		$post_condition = 'back';
		$Redirected = new MW_WP_Form_Redirected( $form_key, $Setting, $is_valid, $post_condition );
		$this->assertEquals( home_url( '/contact/error/?foo=bar&post_id=1&with_asterisk=foo%2Abar&with_space=foo+bar&multibyte=%E3%83%9E%E3%83%AB%E3%83%81%E3%83%90%E3%82%A4%E3%83%88' ), $Redirected->get_url() );

		// Pattern: ! $is_valid, confirm
		$is_valid       = false;
		$post_condition = 'confirm';
		$Redirected = new MW_WP_Form_Redirected( $form_key, $Setting, $is_valid, $post_condition );
		$this->assertEquals( home_url( '/contact/error/?foo=bar&post_id=1&with_asterisk=foo%2Abar&with_space=foo+bar&multibyte=%E3%83%9E%E3%83%AB%E3%83%81%E3%83%90%E3%82%A4%E3%83%88' ), $Redirected->get_url() );

		// Pattern: ! $is_valid, complete
		$is_valid       = false;
		$post_condition = 'complete';
		$Redirected = new MW_WP_Form_Redirected( $form_key, $Setting, $is_valid, $post_condition );
		$this->assertEquals( home_url( '/contact/error/?foo=bar&post_id=1&with_asterisk=foo%2Abar&with_space=foo+bar&multibyte=%E3%83%9E%E3%83%AB%E3%83%81%E3%83%90%E3%82%A4%E3%83%88' ), $Redirected->get_url() );

		// Pattern: $is_valid, back
		$is_valid       = true;
		$post_condition = 'back';
		$Redirected = new MW_WP_Form_Redirected( $form_key, $Setting, $is_valid, $post_condition );
		$this->assertEquals( home_url( '/contact/?foo=bar&post_id=1&with_asterisk=foo%2Abar&with_space=foo+bar&multibyte=%E3%83%9E%E3%83%AB%E3%83%81%E3%83%90%E3%82%A4%E3%83%88' ), $Redirected->get_url() );

		// Pattern: $is_valid, confirm
		$is_valid       = true;
		$post_condition = 'confirm';
		$Redirected = new MW_WP_Form_Redirected( $form_key, $Setting, $is_valid, $post_condition );
		$this->assertEquals( home_url( '/contact/confirm/?foo=bar&post_id=1&with_asterisk=foo%2Abar&with_space=foo+bar&multibyte=%E3%83%9E%E3%83%AB%E3%83%81%E3%83%90%E3%82%A4%E3%83%88' ), $Redirected->get_url() );

		// Pattern: $is_valid, complete
		$is_valid       = true;
		$post_condition = 'complete';
		$Redirected = new MW_WP_Form_Redirected( $form_key, $Setting, $is_valid, $post_condition );
		$this->assertEquals( home_url( '/contact/complete/?foo=bar&post_id=1&with_asterisk=foo%2Abar&with_space=foo+bar&multibyte=%E3%83%9E%E3%83%AB%E3%83%81%E3%83%90%E3%82%A4%E3%83%88' ), $Redirected->get_url() );
	}

	/**
	 * @test
	 * @group get_view_flg
	 */
	public function get_view_flg() {
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );

		$Setting = new MW_WP_Form_Setting( $form_id );

		// Pattern: ! $is_valid, back
		$is_valid       = false;
		$post_condition = 'back';
		$Redirected = new MW_WP_Form_Redirected( $form_key, $Setting, $is_valid, $post_condition );
		$this->assertEquals( 'input', $Redirected->get_view_flg() );

		// Pattern: ! $is_valid, confirm
		$is_valid       = false;
		$post_condition = 'confirm';
		$Redirected = new MW_WP_Form_Redirected( $form_key, $Setting, $is_valid, $post_condition );
		$this->assertEquals( 'input', $Redirected->get_view_flg() );

		// Pattern: ! $is_valid, complete
		$is_valid       = false;
		$post_condition = 'complete';
		$Redirected = new MW_WP_Form_Redirected( $form_key, $Setting, $is_valid, $post_condition );
		$this->assertEquals( 'input', $Redirected->get_view_flg() );

		// Pattern: $is_valid, back
		$is_valid       = true;
		$post_condition = 'back';
		$Redirected = new MW_WP_Form_Redirected( $form_key, $Setting, $is_valid, $post_condition );
		$this->assertEquals( 'input', $Redirected->get_view_flg() );

		// Pattern: $is_valid, confirm
		$is_valid       = true;
		$post_condition = 'confirm';
		$Redirected = new MW_WP_Form_Redirected( $form_key, $Setting, $is_valid, $post_condition );
		$this->assertEquals( 'confirm', $Redirected->get_view_flg() );

		// Pattern: $is_valid, complete
		$is_valid       = true;
		$post_condition = 'complete';
		$Redirected = new MW_WP_Form_Redirected( $form_key, $Setting, $is_valid, $post_condition );
		$this->assertEquals( 'complete', $Redirected->get_view_flg() );
	}

	/**
	 * @test
	 * @group get_request_uri
	 */
	public function get_request_uri() {
		$form_id    = $this->_create_form();
		$form_key   = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Setting    = new MW_WP_Form_Setting( $form_id );
		$Redirected = new MW_WP_Form_Redirected( $form_key, $Setting, 'true', 'confirm' );

		$_SERVER['REQUEST_URI'] = '';
		$this->assertNull( $Redirected->get_request_uri() );

		$_SERVER['REQUEST_URI'] = '/contact/confirm/';
		$this->assertEquals( home_url( '/contact/confirm/' ), $Redirected->get_request_uri() );

		$_SERVER['REQUEST_URI'] = '/contact/confirm/?dummy=true';
		$this->assertEquals( home_url( '/contact/confirm/?dummy=true' ), $Redirected->get_request_uri() );

		add_filter( 'home_url', function( $url, $path ) {
			return untrailingslashit( get_option( 'home' ) ) . '/subdirectory' . $path;
		}, 10, 2 );

		$_SERVER['REQUEST_URI'] = '';
		$this->assertNull( $Redirected->get_request_uri() );

		$_SERVER['REQUEST_URI'] = '/subdirectory/contact/confirm/';
		$this->assertEquals( home_url( '/contact/confirm/' ), $Redirected->get_request_uri() );

		$_SERVER['REQUEST_URI'] = '/subdirectory/contact/confirm/?dummy=true';
		$this->assertEquals( home_url( '/contact/confirm/?dummy=true' ), $Redirected->get_request_uri() );

		remove_all_filters( 'home_url' );
	}

	/**
	 * @test
	 * @group redirect
	 */
	public function redirect() {
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );

		$Setting = new MW_WP_Form_Setting( $form_id );
		$Setting->set( 'input_url'           , home_url( '/contact/' ) );
		$Setting->set( 'confirmation_url'    , home_url( '/contact/confirm/' ) );
		$Setting->set( 'complete_url'        , home_url( '/contact/complete/' ) );
		$Setting->set( 'validation_error_url', home_url( '/contact/error/' ) );

		$_SERVER['REQUEST_URI'] = home_url( '/contact/confirm/' );
		$Redirected = new MW_WP_Form_Redirected( $form_key, $Setting, true, 'confirm' );
		$this->assertNull( $Redirected->redirect() );
	}
}
