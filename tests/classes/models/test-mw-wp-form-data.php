<?php
class MW_WP_Form_Data_Test extends WP_UnitTestCase {

	public function tearDown() {
		parent::tearDown();
		_delete_all_data();
	}

	protected function _create_form() {
		return $this->factory->post->create(
			array(
				'post_type' => MWF_Config::NAME,
			)
		);
	}

	protected function _instantiation_Data( array $POST = array() ) {
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		return MW_WP_Form_Data::connect( $form_key, $POST );
	}

	/**
	 * @test
	 * @gropu get_form_key
	 */
	public function get_form_key() {
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Data     = MW_WP_Form_Data::connect( $form_key );

		$this->assertSame( $form_key, $Data->get_form_key() );
	}

	/**
	 * @test
	 * @group get_post_condition
	 */
	public function get_post_condition() {
		$Data = $this->_instantiation_Data( array(
			MWF_Config::BACK_BUTTON => true,
		) );
		$this->assertSame( 'back', $Data->get_post_condition() );

		$Data = $this->_instantiation_Data( array(
			MWF_Config::CONFIRM_BUTTON => true,
		) );
		$this->assertSame( 'confirm', $Data->get_post_condition() );

		$Data = $this->_instantiation_Data();
		$this->assertSame( 'input', $Data->get_post_condition() );

		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Data     = MW_WP_Form_Data::connect( $form_key, array(
			MWF_Config::TOKEN_NAME => wp_create_nonce( $form_key ),
		) );
		$this->assertSame( 'complete', $Data->get_post_condition() );
	}

	/**
	 * @test
	 * @group gets
	 */
	public function gets() {
		$POST = array(
			'name-1' => 'value-1',
			'name-2' => 'value-2',
		);
		$Data = $this->_instantiation_Data( $POST );
		$this->assertEquals( $POST, $Data->gets() );
	}

	/**
	 * @test
	 * @group set
	 */
	public function set() {
		$Data = $this->_instantiation_Data();
		$Data->set( 'name-1', 'value-1' );
		$this->assertEquals( 'value-1', $Data->get_post_value_by_key( 'name-1' ) );

		$Data->set( 'name-2', array( 'value-2' ) );
		$this->assertEquals( array( 'value-2' ), $Data->get_post_value_by_key( 'name-2' ) );

		$Data->set( 'name-3', array( 'data' => 'value-3', 'separator' => ',' ) );
		$this->assertEquals( array( 'data' => 'value-3', 'separator' => ',' ), $Data->get_post_value_by_key( 'name-3' ) );
	}

	/**
	 * @test
	 * @group gets
	 */
	public function sets() {
		$Data = $this->_instantiation_Data();
		$values = array(
			'name-1' => 'value-1',
			'name-2' => 'value-2',
		);
		$Data->sets( $values );
		$this->assertEquals($values, $Data->gets() );
	}

	/**
	 * @test
	 * @group clear_value
	 */
	public function clear_value() {
		$Data = $this->_instantiation_Data( array(
			'name-1' => 'value-1',
			'name-2' => 'value-2',
		) );
		$Data->clear_value( 'name-1' );
		$this->assertEquals( array( 'name-2' => 'value-2' ), $Data->gets() );
	}

	/**
	 * @test
	 * @group clear_values
	 */
	public function clear_values() {
		$Data = $this->_instantiation_Data( array(
			'name-1' => 'value-1',
		) );
		$Data->set_validation_error( 'name-1', 'rule', 'error' );
		$Data->clear_values();
		$this->assertEquals( array(), $Data->gets() );
		$this->assertEquals( '', $Data->get_form_key() );
		$this->assertEquals( array(), $Data->get_validation_error( 'name-1' ) );
	}

	/**
	 * @test
	 * @group push
	 */
	public function push() {
		$Data = $this->_instantiation_Data( array(
			'name-1' => array( 'value-1-1' ),
			'name-2' => 'value-2-1',
		) );
		$Data->push( 'name-1', 'value-1-2' );
		$Data->push( 'name-2', 'value-2-2' );
		$Data->push( 'name-3', 'value-3-1' );
		$this->assertEquals( array(
			'name-1' => array( 'value-1-1', 'value-1-2' ),
			'name-2' => 'value-2-1',
			'name-3' => array( 'value-3-1' ),
		), $Data->gets() );
	}

	/**
	 * @test
	 * @group get
	 */
	public function get() {
		// Pattern: null
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Data = MW_WP_Form_Data::connect( $form_key );
		$this->assertNull( $Data->get( 'name-1' ) );

		// Patten: string
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Data = MW_WP_Form_Data::connect( $form_key, array(
			'name-1' => 'value-1',
		) );
		$this->assertSame( 'value-1', $Data->get( 'name-1' ) );

		// Pattern: array doesn't have 'data'
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Data = MW_WP_Form_Data::connect( $form_key, array(
			'name-1' => array( 'value-1-1', 'value-1-2' ),
		) );
		$this->assertNull( $Data->get( 'name-1' ) );

		// Pattern: array has children
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Data = MW_WP_Form_Data::connect( $form_key, array(
			'name-1' => array(
				'data'      => array( 'value-1-1', 'value-1-2' ),
				'separator' => ',',
			),
			'__children' => array(
				'name-1' => array(
					json_encode( array(
						'value-1-1' => 'value-1-1',
					) ),
				),
			),
		) );
		$this->assertEquals( 'value-1-1', $Data->get( 'name-1' ) );
		$this->assertEquals( 'value-1-1', $Data->get( 'name-1', array(
			'value-1-1' => 'value-1-1',
		) ) );
		$this->assertEquals( 'value-1-2', $Data->get( 'name-1', array(
			'value-1-2' => 'value-1-2',
		) ) );

		// Pattern: array doesn't have children
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Data = MW_WP_Form_Data::connect( $form_key, array(
			'name-1' => array(
				'data'      => array( 'value-1-1', 'value-1-2' ),
				'separator' => ',',
			),
		) );
		$this->assertEquals( 'value-1-1,value-1-2', $Data->get( 'name-1' ) );

		// Pattern: string has children
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Data = MW_WP_Form_Data::connect( $form_key, array(
			'name-1' => array(
				'data'      => 'value-1-1,value-1-2',
				'separator' => ',',
			),
			'__children' => array(
				'name-1' => array(
					json_encode( array(
						'value-1-1' => 'value-1-2',
					) ),
				),
			),
		) );
		$this->assertEquals( 'value-1-2', $Data->get( 'name-1' ) );
		$this->assertEquals( 'value-1-1', $Data->get( 'name-1', array(
			'value-1-1' => 'value-1-1',
		) ) );
		$this->assertEquals( 'value-1-2', $Data->get( 'name-1', array(
			'value-1-2' => 'value-1-2',
		) ) );

		// Pattern: string doesn't have children
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Data = MW_WP_Form_Data::connect( $form_key, array(
			'name-1' => array(
				'data'      => 'value-1-1,value-1-2',
				'separator' => ',',
			),
		) );
		$this->assertEquals( 'value-1-1,value-1-2', $Data->get( 'name-1' ) );
	}

	/**
	 * @test
	 * @group get_raw
	 */
	public function get_raw() {
		// Pattern: null
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Data = MW_WP_Form_Data::connect( $form_key );
		$this->assertNull( $Data->get_raw( 'name-1' ) );

		// Patten: string
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Data = MW_WP_Form_Data::connect( $form_key, array(
			'name-1' => 'value-1',
		) );
		$this->assertSame( 'value-1', $Data->get_raw( 'name-1' ) );

		// Pattern: array doesn't have 'data'
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Data = MW_WP_Form_Data::connect( $form_key, array(
			'name-1' => array( 'value-1-1', 'value-1-2' ),
		) );
		$this->assertNull( $Data->get_raw( 'name-1' ) );

		// Pattern: array has children
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Data = MW_WP_Form_Data::connect( $form_key, array(
			'name-1' => array(
				'data'      => array( 'value-1-1', 'value-1-2' ),
				'separator' => ',',
			),
			'__children' => array(
				'name-1' => array(
					json_encode( array(
						'value-1-1' => 'value-1-2',
					) ),
				),
			),
		) );
		$this->assertEquals( 'value-1-1', $Data->get_raw( 'name-1' ) );

		// Pattern: array doesn't have children
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Data = MW_WP_Form_Data::connect( $form_key, array(
			'name-1' => array(
				'data'      => array( 'value-1-1', 'value-1-2' ),
				'separator' => ',',
			),
		) );
		$this->assertEquals( 'value-1-1,value-1-2', $Data->get_raw( 'name-1' ) );

		// Pattern: string has children
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Data = MW_WP_Form_Data::connect( $form_key, array(
			'name-1' => array(
				'data'      => 'value-1-1,value-1-2',
				'separator' => ',',
			),
			'__children' => array(
				'name-1' => array(
					json_encode( array(
						'value-1-1' => 'value-1-2',
					) ),
				),
			),
		) );
		$this->assertEquals( 'value-1-1', $Data->get_raw( 'name-1' ) );

		// Pattern: string doesn't have children
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Data = MW_WP_Form_Data::connect( $form_key, array(
			'name-1' => array(
				'data'      => 'value-1-1,value-1-2',
				'separator' => ',',
			),
		) );
		$this->assertEquals( 'value-1-1,value-1-2', $Data->get_raw( 'name-1' ) );
	}

	/**
	 * @test
	 * @group get_post_value_by_key
	 */
	public function get_post_value_by_key() {
		$Data = $this->_instantiation_Data();
		$Data->set( 'name-1', 'value-1' );
		$this->assertEquals( 'value-1', $Data->get_post_value_by_key( 'name-1' ) );

		$Data->set( 'name-2', array( 'value-2' ) );
		$this->assertEquals( array( 'value-2' ), $Data->get_post_value_by_key( 'name-2' ) );

		$Data->set( 'name-3', array( 'data' => 'value-3', 'separator' => ',' ) );
		$this->assertEquals( array( 'data' => 'value-3', 'separator' => ',' ), $Data->get_post_value_by_key( 'name-3' ) );
	}

	public function get_in_children() {}

	public function get_raw_in_children() {}

	public function get_separator_value() {}

	public function get_separated_raw_value() {}

	public function regenerate_upload_file_keys() {}

	public function push_uploaded_file_keys() {}

	public function set_view_flg() {}

	public function get_view_flg() {}

	public function set_send_error() {}

	public function get_send_error() {}

	public function set_validation_error() {}

	public function get_validation_error() {}

	public function get_validation_errors() {}
}
