<?php
class MW_WP_Form_Contact_Data_Setting_Test extends WP_UnitTestCase {

	/**
	 * MW_WP_Form_Contact_Data_Setting の配列
	 * @var array
	 */
	protected $settings = array();

	/**
	 * 1つめの MW_WP_Form_Contact_Data_Setting の投稿ID
	 * @var int
	 */
	protected $post_id;

	public function tearDown() {
		parent::tearDown();
		_delete_all_data();
	}

	/**
	 * Instantiation MW_WP_Form_Contact_Data_Setting
	 *
	 * @param array meta data
	 * @return MW_WP_Form_Contact_Data_Setting
	 */
	protected function _instantiation_Contact_Data_Setting( array $data = array() ) {
		$post_id = $this->factory->post->create(
			array(
				'post_type' => MWF_Config::NAME,
			)
		);
		update_post_meta( $post_id, MWF_Config::NAME, array(
			'usedb' => 1,
		) );
		$contact_data_id = $this->factory->post->create( array(
			'post_type' => MWF_Functions::get_contact_data_post_type_from_form_id( $post_id ),
		) );

		$Setting = new MW_WP_Form_Contact_Data_Setting( $contact_data_id );
		$Setting->sets( $data );
		$Setting->save();

		return $Setting;
	}

	/**
	 * @test
	 * @group get_response_statuses
	 */
	public function get_response_statuses() {
		$Setting = $this->_instantiation_Contact_Data_Setting();

		$this->assertEquals(
			array(
				'not-supported',
				'reservation',
				'supported',
			),
			array_keys( $Setting->get_response_statuses() )
		);
	}

	/**
	 * @test
	 * @group get_permit_keys
	 */
	public function get_permit_keys() {
		$Setting = $this->_instantiation_Contact_Data_Setting();

		$this->assertEquals(
			array(
				'response_status',
				'admin_mail_to',
				'memo',
			),
			$Setting->get_permit_keys()
		);
	}

	/**
	 * @test
	 * @group gets
	 */
	public function gets() {
		$Setting = $this->_instantiation_Contact_Data_Setting();
		$this->assertSame(
			array(
				'response_status' => 'not-supported',
				'admin_mail_to'   => null,
				'memo'            => '',
			),
			$Setting->gets()
		);

		$Setting = $this->_instantiation_Contact_Data_Setting( array(
			'first-name' => 'John',
		) );
		$this->assertSame(
			array(
				'first-name'      => 'John',
				'response_status' => 'not-supported',
				'admin_mail_to'   => null,
				'memo'            => '',
			),
			$Setting->gets()
		);

		$Setting = $this->_instantiation_Contact_Data_Setting( array(
			'memo' => 'dummy',
		) );
		$this->assertSame(
			array(
				'response_status' => 'not-supported',
				'admin_mail_to'   => null,
				'memo'            => 'dummy',
			),
			$Setting->gets()
		);

		$Setting = $this->_instantiation_Contact_Data_Setting( array(
			'response_status' => 'dummy',
		) );
		$this->assertSame(
			array(
				'response_status' => 'not-supported',
				'admin_mail_to'   => null,
				'memo'            => '',
			),
			$Setting->gets()
		);

		$Setting = $this->_instantiation_Contact_Data_Setting( array(
			'admin_mail_to' => 'info@example.com',
		) );
		$this->assertSame(
			array(
				'response_status' => 'not-supported',
				'admin_mail_to'   => 'info@example.com',
				'memo'            => '',
			),
			$Setting->gets()
		);
	}

	/**
	 * @test
	 * @group get
	 */
	public function get() {
		$Setting = $this->_instantiation_Contact_Data_Setting( array(
			'first-name' => 'John',
		) );
		$this->assertSame( 'John', $Setting->get( 'first-name' ) );
		$this->assertSame( 'not-supported', $Setting->get( 'response_status' ) );
		$this->assertSame( '', $Setting->get( 'memo' ) );
	}

	/**
	 * @test
	 * @group sets
	 */
	public function sets() {
		$Setting = $this->_instantiation_Contact_Data_Setting();
		$Setting->sets( array(
			'first-name'      => 'John',
			'response_status' => 'dummy',
			'memo'            => 'dummy',
		) );
		$this->assertSame(
			array(
				'first-name'      => 'John',
				'response_status' => 'not-supported',
				'admin_mail_to'   => null,
				'memo'            => 'dummy',
			),
			$Setting->gets()
		);
	}

	/**
	 * @test
	 * @group set
	 */
	public function set() {
		$Setting = $this->_instantiation_Contact_Data_Setting();
		$Setting->set( 'first-name', 'John' );
		$Setting->set( 'response_status', 'dummy' );
		$Setting->set( 'memo', 'dummy' );
		$this->assertSame(
			array(
				'first-name'      => 'John',
				'response_status' => 'not-supported',
				'admin_mail_to'   => null,
				'memo'            => 'dummy',
			),
			$Setting->gets()
		);

		$Setting->set( 'response_status', 'supported' );
		$this->assertSame( 'supported', $Setting->get( 'response_status' ) );
	}

	/**
	 * @test
	 * @group save
	 */
	public function save() {
		$Setting = $this->_instantiation_Contact_Data_Setting( array(
			'first-name' => 'John',
			'last-name'  => null,
		) );
		$Setting->save();

		$this->assertEquals( 'John', get_post_meta( $Setting->get( 'post_id' ), 'first-name', true ) );
		$this->assertSame( '', get_post_meta( $Setting->get( 'post_id' ), 'last-name', true ) );
		$this->assertEquals( '', get_post_meta( $Setting->get( 'post_id' ), 'response_status', true ) );
		$this->assertEquals( '', get_post_meta( $Setting->get( 'post_id' ), 'memo', true ) );
	}

	/**
	 * @test
	 * @group is_upload_file_key
	 */
	public function is_upload_file_key() {
		$Setting = $this->_instantiation_Contact_Data_Setting( array(
			'first-name' => 'John',
			'_' . MWF_Config::UPLOAD_FILE_KEYS => array( 'upload-1', 'upload-2' ),
		) );

		$this->assertFalse( $Setting->is_upload_file_key( 'first-name' ) );
		$this->assertTrue( $Setting->is_upload_file_key( 'upload-1' ) );
		$this->assertTrue( $Setting->is_upload_file_key( 'upload-2' ) );
	}

	/**
	 * @test
	 * @group get_index_of_key_in_upload_file_keys
	 */
	public function get_index_of_key_in_upload_file_keys() {
		$Setting = $this->_instantiation_Contact_Data_Setting( array(
			'first-name' => 'John',
			'_' . MWF_Config::UPLOAD_FILE_KEYS => array( 'upload-1', 'upload-2' ),
		) );

		$this->assertFalse( $Setting->get_index_of_key_in_upload_file_keys( 'first-name' ) );
		$this->assertSame( 0, $Setting->get_index_of_key_in_upload_file_keys( 'upload-1' ) );
		$this->assertSame( 1, $Setting->get_index_of_key_in_upload_file_keys( 'upload-2' ) );
	}

	/**
	 * @test
	 * @group get_form_post_types
	 */
	public function get_form_post_types() {
		$this->assertEquals( 0, count( MW_WP_Form_Contact_Data_Setting::get_form_post_types() ) );

		$post_ids = $this->factory->post->create_many(
			5,
			array(
				'post_type' => MWF_Config::NAME,
			)
		);

		update_post_meta( current( $post_ids ), MWF_Config::NAME, array(
			'usedb' => 1,
		) );

		$this->assertEquals( 1, count( MW_WP_Form_Contact_Data_Setting::get_form_post_types() ) );

		add_filter( 'mwform_contact_data_post_types', function( $post_types ) {
			$post_types[] = 'incorrect-1';
			$post_types[] = 'incorrect-2';
			return $post_types;
		} );

		$this->assertEquals( 1, count( MW_WP_Form_Contact_Data_Setting::get_form_post_types() ) );

		add_filter( 'mwform_contact_data_post_types', function( $post_types ) use ( $post_ids ) {
			$post_type = MWF_Functions::get_contact_data_post_type_from_form_id( current( $post_ids ) );
			$key       = array_search( $post_type, $post_types );
			if ( false !== $key ) {
				unset( $post_types[ $key ] );
			}
			return $post_types;
		} );

		$this->assertEquals( 0, count( MW_WP_Form_Contact_Data_Setting::get_form_post_types() ) );
	}
}
