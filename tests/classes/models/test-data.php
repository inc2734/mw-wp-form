<?php
class MW_WP_Form_Data_Test extends WP_UnitTestCase {

	public function tear_down() {
		parent::tear_down();
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
		$this->assertEquals(
			array(
				'name-1' => array( 'value-1-1', 'value-1-2' ),
				'name-2' => array( 'value-2-1', 'value-2-2' ),
				'name-3' => array( 'value-3-1' ),
			),
			$Data->gets()
		);
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

	/**
	 * @test
	 * @group get_in_children
	 */
	public function get_in_children() {
		// Pattern: null
		$Data = $this->_instantiation_Data();
		$this->assertNull( $Data->get_in_children( 'name-1', array(
				'value-1-1' => 'value-1-1-label',
				'value-1-2' => 'value-1-2-label',
		) ) );

		// Pattern: array
		$Data = $this->_instantiation_Data( array(
			'name-1' => array( 'value-1-1' ),
		) );
		$this->assertNull( $Data->get_in_children( 'name-1', array(
				'value-1-1' => 'value-1-1-label',
				'value-1-2' => 'value-1-2-label',
		) ) );

		// Pattern: isset
		$Data = $this->_instantiation_Data( array(
			'name-1' => 'value-1-1',
		) );
		$this->assertEquals( 'value-1-1-label', $Data->get_in_children( 'name-1', array(
				'value-1-1' => 'value-1-1-label',
				'value-1-2' => 'value-1-2-label',
		) ) );

		// Pattern: ! isset
		$Data = $this->_instantiation_Data( array(
			'name-1' => 'value-1-1',
		) );
		$this->assertEquals( '', $Data->get_in_children( 'name-1', array(
				'value-1-2' => 'value-1-2-label',
		) ) );
	}

	/**
	 * @test
	 * @group get_raw_in_children
	 */
	public function get_raw_in_children() {
		// Pattern: null
		$Data = $this->_instantiation_Data();
		$this->assertNull( $Data->get_raw_in_children( 'name-1', array(
				'value-1-1' => 'value-1-1-label',
				'value-1-2' => 'value-1-2-label',
		) ) );

		// Pattern: array
		$Data = $this->_instantiation_Data( array(
			'name-1' => array( 'value-1-1' ),
		) );
		$this->assertNull( $Data->get_raw_in_children( 'name-1', array(
				'value-1-1' => 'value-1-1-label',
				'value-1-2' => 'value-1-2-label',
		) ) );

		// Pattern: isset
		$Data = $this->_instantiation_Data( array(
			'name-1' => 'value-1-1',
		) );
		$this->assertEquals( 'value-1-1', $Data->get_raw_in_children( 'name-1', array(
				'value-1-1' => 'value-1-1-label',
				'value-1-2' => 'value-1-2-label',
		) ) );

		// Pattern: ! isset
		$Data = $this->_instantiation_Data( array(
			'name-1' => 'value-1-1',
		) );
		$this->assertEquals( '', $Data->get_raw_in_children( 'name-1', array(
				'value-1-2' => 'value-1-2-label',
		) ) );
	}

	/**
	 * @test
	 * @group get_separator_value
	 */
	public function get_separator_value() {
		$Data = $this->_instantiation_Data( array(
			'name-1' => 'value-1',
		) );
		$this->assertNull( $Data->get_separator_value( 'name-1' ) );

		$Data = $this->_instantiation_Data( array(
			'name-1' => array(
				'separator' => ',',
			),
		) );
		$this->assertEquals( ',', $Data->get_separator_value( 'name-1' ) );
	}

	/**
	 * @test
	 * @group get_separated_value
	 */
	public function get_separated_value() {
		// Pattern: $children is empty
		$Data = $this->_instantiation_Data( array(
			'name-1' => array(
				'data'      => array( 'value-1-1' ),
				'separator' => ',',
			),
		) );
		$this->assertNull( $Data->get_separated_value( 'name-1', array() ) );

		// Pattern: value is empty
		$Data = $this->_instantiation_Data( array(
			'name-1' => array(
				'data'      => array( 'value-1-1' ),
				'separator' => ',',
			),
		) );
		$this->assertNull( $Data->get_separated_value( 'name-2', array(
			'value-1-1' => 'value-1-1-label',
		) ) );

		// Pattern: The key doesn't have 'data'
		$Data = $this->_instantiation_Data( array(
			'name-1' => array(
				'separator' => ',',
			),
		) );
		$this->assertNull( $Data->get_separated_value( 'name-1', array(
			'value-1-1' => 'value-1-1-label',
		) ) );

		// Pattern: value is empty
		$Data = $this->_instantiation_Data( array(
			'name-1' => array(
				'data'      => array( 'value-1-1' ),
				'separator' => ',',
			),
		) );
		$this->assertNull( $Data->get_separated_value( 'name-2', array(
			'value-1-1' => 'value-1-1-label',
		) ) );

		// Pattern: The key doesn't have separator
		$Data = $this->_instantiation_Data( array(
			'name-1' => array(
				'data' => array( 'value-1-1' ),
			),
		) );
		$this->assertNull( $Data->get_separated_value( 'name-2', array(
			'value-1-1' => 'value-1-1-label',
		) ) );

		// Pattern: normal
		$Data = $this->_instantiation_Data( array(
			'name-1' => array(
				'data'      => array( 'value-1-1' ),
				'separator' => ',',
			),
		) );
		$this->assertEquals( 'value-1-1-label', $Data->get_separated_value( 'name-1', array(
			'value-1-1' => 'value-1-1-label',
		) ) );

		// Pattern: string
		$Data = $this->_instantiation_Data( array(
			'name-1' => array(
				'data'      => 'value-1-1',
				'separator' => ',',
			),
		) );
		$this->assertEquals( 'value-1-1-label', $Data->get_separated_value( 'name-1', array(
			'value-1-1' => 'value-1-1-label',
		) ) );
	}

	/**
	 * @test
	 * @group get_separated_raw_value
	 */
	public function get_separated_raw_value() {
		// Pattern: $children is empty
		$Data = $this->_instantiation_Data( array(
			'name-1' => array(
				'data'      => array( 'value-1-1' ),
				'separator' => ',',
			),
		) );
		$this->assertNull( $Data->get_separated_raw_value( 'name-1', array() ) );

		// Pattern: value is empty
		$Data = $this->_instantiation_Data( array(
			'name-1' => array(
				'data'      => array( 'value-1-1' ),
				'separator' => ',',
			),
		) );
		$this->assertNull( $Data->get_separated_raw_value( 'name-2', array(
			'value-1-1' => 'value-1-1-label',
		) ) );

		// Pattern: The key doesn't have 'data'
		$Data = $this->_instantiation_Data( array(
			'name-1' => array(
				'separator' => ',',
			),
		) );
		$this->assertNull( $Data->get_separated_raw_value( 'name-1', array(
			'value-1-1' => 'value-1-1-label',
		) ) );

		// Pattern: value is empty
		$Data = $this->_instantiation_Data( array(
			'name-1' => array(
				'data'      => array( 'value-1-1' ),
				'separator' => ',',
			),
		) );
		$this->assertNull( $Data->get_separated_raw_value( 'name-2', array(
			'value-1-1' => 'value-1-1-label',
		) ) );

		// Pattern: The key doesn't have separator
		$Data = $this->_instantiation_Data( array(
			'name-1' => array(
				'data' => array( 'value-1-1' ),
			),
		) );
		$this->assertNull( $Data->get_separated_raw_value( 'name-2', array(
			'value-1-1' => 'value-1-1-label',
		) ) );

		// Pattern: normal
		$Data = $this->_instantiation_Data( array(
			'name-1' => array(
				'data'      => array( 'value-1-1' ),
				'separator' => ',',
			),
		) );
		$this->assertEquals( 'value-1-1', $Data->get_separated_raw_value( 'name-1', array(
			'value-1-1' => 'value-1-1-label',
		) ) );

		// Pattern: string
		$Data = $this->_instantiation_Data( array(
			'name-1' => array(
				'data'      => 'value-1-1',
				'separator' => ',',
			),
		) );
		$this->assertEquals( 'value-1-1', $Data->get_separated_raw_value( 'name-1', array(
			'value-1-1' => 'value-1-1-label',
		) ) );
	}

	/**
	 * @test
	 * @group regenerate_upload_file_keys
	 */
	public function regenerate_upload_file_keys() {
		// Pattern: ! array
		$Data = $this->_instantiation_Data( array(
			MWF_Config::UPLOAD_FILE_KEYS => 'dummy',
		) );
		$form_id = MWF_Functions::get_form_id_from_form_key( $Data->get_form_key() );
		$name    = 'name-1';
		$dirpath = MW_WP_Form_Directory::generate_user_file_dirpath( $form_id, $name );
		wp_mkdir_p( $dirpath );
		file_put_contents( $dirpath . '/1.txt', 1 );
		$Data->regenerate_upload_file_keys();
		$this->assertEquals( array(), $Data->get_post_value_by_key( MWF_Config::UPLOAD_FILE_KEYS ) );
		unlink( $dirpath . '/1.txt' );

		// Pattern: ! unique
		$Data = $this->_instantiation_Data( array(
			MWF_Config::UPLOAD_FILE_KEYS => array( 'name-1', 'name-1' ),
		) );
		$form_id = MWF_Functions::get_form_id_from_form_key( $Data->get_form_key() );
		$name    = 'name-1';
		$dirpath = MW_WP_Form_Directory::generate_user_file_dirpath( $form_id, $name );
		wp_mkdir_p( $dirpath );
		file_put_contents( $dirpath . '/1.txt', 1 );
		$Data->set( 'name-1', '1.txt' );
		$Data->regenerate_upload_file_keys();
		$this->assertEquals( array( 'name-1' ), $Data->get_post_value_by_key( MWF_Config::UPLOAD_FILE_KEYS ) );
		unlink( $dirpath . '/1.txt' );

		// Pattern: file url is empty
		$Data = $this->_instantiation_Data( array(
			MWF_Config::UPLOAD_FILE_KEYS => array( 'name-1' ),
		) );
		$form_id = MWF_Functions::get_form_id_from_form_key( $Data->get_form_key() );
		$name    = 'name-1';
		$dirpath = MW_WP_Form_Directory::generate_user_file_dirpath( $form_id, $name );
		wp_mkdir_p( $dirpath );
		file_put_contents( $dirpath . '/1.txt', 1 );
		$Data->regenerate_upload_file_keys();
		$this->assertEquals( array(), $Data->get_post_value_by_key( MWF_Config::UPLOAD_FILE_KEYS ) );
		unlink( $dirpath . '/1.txt' );

		// Pattern: ! file_exists
		$Data = $this->_instantiation_Data( array(
			MWF_Config::UPLOAD_FILE_KEYS => array( 'name-1', 'name-1' ),
		) );
		$form_id = MWF_Functions::get_form_id_from_form_key( $Data->get_form_key() );
		$name    = 'name-1';
		$dirpath = MW_WP_Form_Directory::generate_user_file_dirpath( $form_id, $name );
		wp_mkdir_p( $dirpath );
		file_put_contents( $dirpath . '/1.txt', 1 );
		$Data->set( 'name-1', '2.txt' );
		$Data->regenerate_upload_file_keys();
		$this->assertEquals( array(), $Data->get_post_value_by_key( MWF_Config::UPLOAD_FILE_KEYS ) );
		unlink( $dirpath . '/1.txt' );

		// Pattern: file_exists
		$Data = $this->_instantiation_Data( array(
			MWF_Config::UPLOAD_FILE_KEYS => array( 'name-1' ),
		) );
		$form_id = MWF_Functions::get_form_id_from_form_key( $Data->get_form_key() );
		$name    = 'name-1';
		$dirpath = MW_WP_Form_Directory::generate_user_file_dirpath( $form_id, $name );
		wp_mkdir_p( $dirpath );
		file_put_contents( $dirpath . '/1.txt', 1 );
		$Data->set( 'name-1', '1.txt' );
		$Data->regenerate_upload_file_keys();
		$this->assertEquals( array( 'name-1' ), $Data->get_post_value_by_key( MWF_Config::UPLOAD_FILE_KEYS ) );
		unlink( $dirpath . '/1.txt' );
	}

	/**
	 * @test
	 * @group push_uploaded_file_keys
	 */
	public function push_uploaded_file_keys() {
		$attachments = array(
			'name-2' => 'https://exemple.com/dummy-1.txt',
		);

		// Pattern: ! array
		$Data = $this->_instantiation_Data( array(
			MWF_Config::UPLOAD_FILE_KEYS => 'name-1',
		) );
		$Data->push_uploaded_file_keys( $attachments );
		$this->assertEquals( array( 'name-2' ), $Data->get_post_value_by_key( MWF_Config::UPLOAD_FILE_KEYS ) );

		// Pattern: array
		$Data = $this->_instantiation_Data( array(
			MWF_Config::UPLOAD_FILE_KEYS => array( 'name-1' ),
		) );
		$Data->push_uploaded_file_keys( $attachments );
		$this->assertEquals( array( 'name-1', 'name-2' ), $Data->get_post_value_by_key( MWF_Config::UPLOAD_FILE_KEYS ) );
	}

	/**
	 * @test
	 * @group set_view_flg
	 */
	public function set_view_flg() {
		$Data = $this->_instantiation_Data();
		$Data->set_view_flg( 'input' );
		$this->assertEquals( 'input', $Data->get_view_flg() );
	}

	/**
	 * @test
	 * @group get_view_flg
	 */
	public function get_view_flg() {
		$Data = $this->_instantiation_Data();
		$Data->set_view_flg( 'input' );
		$this->assertEquals( 'input', $Data->get_view_flg() );
	}

	/**
	 * @test
	 * @group set_saved_mail_id
	 */
	public function set_saved_mail_id() {
		$Data = $this->_instantiation_Data();
		$Data->set_saved_mail_id( 1 );
		$this->assertEquals( 1, $Data->get_saved_mail_id() );
	}

	/**
	 * @test
	 * @group get_saved_mail_id
	 */
	public function get_saved_mail_id() {
		$Data = $this->_instantiation_Data();
		$Data->set_saved_mail_id( 1 );
		$this->assertEquals( 1, $Data->get_saved_mail_id() );
	}

	/**
	 * @test
	 * @group set_send_error
	 */
	public function set_send_error() {
		$Data = $this->_instantiation_Data();
		$Data->set_send_error();
		$this->assertTrue( $Data->get_send_error() );
	}

	/**
	 * @test
	 * @group get_send_error
	 */
	public function get_send_error() {
		$Data = $this->_instantiation_Data();
		$Data->set_send_error();
		$this->assertTrue( $Data->get_send_error() );
	}

	/**
	 * @test
	 * @group set_validation_error
	 */
	public function set_validation_error() {
		$Data = $this->_instantiation_Data();
		$Data->set_validation_error( 'name-1', 'noempty', 'message' );
		$this->assertEquals( array( 'noempty' => 'message' ), $Data->get_validation_error( 'name-1' ) );

		$Data->set_validation_error( 'name-2', 'noempty', 'message' );
		$this->assertEquals( array( 'noempty' => 'message' ), $Data->get_validation_error( 'name-2' ) );
	}

	/**
	 * @test
	 * @group get_validation_error
	 */
	public function get_validation_error() {
		$Data = $this->_instantiation_Data();
		$Data->set_validation_error( 'name-1', 'noempty', 'message' );
		$this->assertEquals( array( 'noempty' => 'message' ), $Data->get_validation_error( 'name-1' ) );

		$Data->set_validation_error( 'name-2', 'noempty', 'message' );
		$this->assertEquals( array( 'noempty' => 'message' ), $Data->get_validation_error( 'name-2' ) );

		$this->assertEquals( array(), $Data->get_validation_error( 'name-3' ) );
	}

	/**
	 * @test
	 * @group get_validation_errors
	 */
	public function get_validation_errors() {
		$Data = $this->_instantiation_Data();
		$this->assertEquals( array(), $Data->get_validation_errors() );

		$Data->set_validation_error( 'name-1', 'noempty', 'message' );
		$Data->set_validation_error( 'name-2', 'noempty', 'message' );
		$this->assertEquals( array(
			'name-1' => array( 'noempty' => 'message' ),
			'name-2' => array( 'noempty' => 'message' ),
		), $Data->get_validation_errors() );

		$Data->set_validation_error( 'name-1', 'hiragana', 'message' );
		$this->assertEquals( array(
			'name-1' => array( 'noempty' => 'message', 'hiragana' => 'message' ),
			'name-2' => array( 'noempty' => 'message' ),
		), $Data->get_validation_errors() );
	}

	/**
	 * @test
	 * @group custom_mail_tag
	 */
	public function custom_mail_tag() {
		add_filter( 'mwform_custom_mail_tag', function( $value, $name ) {
			if ( 'custom' === $name ) {
				return 'custom';
			}
			return $value;
		}, 10, 2 );

		$Data = $this->_instantiation_Data( array(
			MWF_Config::CUSTOM_MAIL_TAG_KEYS => array( 'custom' ),
		) );

		$this->assertSame( 'custom', $Data->get_raw( 'custom' ) );
	}
}
