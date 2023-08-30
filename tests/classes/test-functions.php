<?php
class MWF_Functions_Test extends WP_UnitTestCase {

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

	/**
	 * @test
	 * @group is_numeric
	 */
	public function is_numeric() {
		$numeric = 1;
		$this->assertFalse( MWF_Functions::is_numeric( $dummy ) );
		$this->assertTrue( MWF_Functions::is_numeric( $numeric ) );
	}

	/**
	 * @test
	 * @group array_clean
	 */
	public function array_clean() {
		$this->assertSame(
			array(),
			MWF_Functions::array_clean( array(
				'name-1' => '',
				'name-2' => '',
				'name-3' => null,
			) )
		);
	}

	/**
	 * @test
	 * @group is_empty
	 */
	public function is_empty() {
		$this->assertTrue( MWF_Functions::is_empty( array() ) );
		$this->assertTrue( MWF_Functions::is_empty( null ) );
		$this->assertTrue( MWF_Functions::is_empty( false ) );
		$this->assertTrue( MWF_Functions::is_empty( '' ) );
		$this->assertFalse( MWF_Functions::is_empty( 0 ) );
	}

	/**
	 * @test
	 * @group move_temp_file_to_upload_dir
	 */
	public function move_temp_file_to_upload_dir() {
		$old_filepath  = '/old/file/path/test.jpg';
		$wp_upload_dir = wp_upload_dir();

		$new_filepath  = MWF_Functions::move_temp_file_to_upload_dir( $old_filepath );
		$this->assertEquals(
			$wp_upload_dir['path'] . '/test.jpg',
			$new_filepath
		);

		$new_filepath  = MWF_Functions::move_temp_file_to_upload_dir( $old_filepath, '/dir' );
		$this->assertEquals(
			$wp_upload_dir['basedir'] . '/dir/test.jpg',
			$new_filepath
		);

		$new_filepath  = MWF_Functions::move_temp_file_to_upload_dir( $old_filepath, '', 'file.jpg' );
		$this->assertEquals(
			$wp_upload_dir['path'] . '/file.jpg',
			$new_filepath
		);

		$new_filepath  = MWF_Functions::move_temp_file_to_upload_dir( $old_filepath, '/dir', 'file.jpg' );
		$this->assertEquals(
			$wp_upload_dir['basedir'] . '/dir/file.jpg',
			$new_filepath
		);
	}

	public function save_attachments_in_media() {}

	public function check_file_type() {}

	/**
	 * @test
	 * @group get_tracking_number_title
	 */
	public function get_tracking_number_title() {
		$form_id = $this->_create_form();
		$inquiry_data_post_type = MWF_Functions::get_contact_data_post_type_from_form_id( $form_id );
		$this->assertEquals( 'Tracking Number', MWF_Functions::get_tracking_number_title( $inquiry_data_post_type ) );
	}

	/**
	 * @test
	 * @group contact_data_post_type_to_form_key
	 */
	public function contact_data_post_type_to_form_key() {
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$this->assertEquals(
			$form_key,
			MWF_Functions::contact_data_post_type_to_form_key( 'mwf_' . $form_id )
		);

		$this->assertNull( MWF_Functions::contact_data_post_type_to_form_key( 'dummy' ) );
	}

	/**
	 * @test
	 * @group get_form_key_from_form_id
	 */
	public function get_form_key_from_form_id() {
		$form_id = $this->_create_form();
		$this->assertEquals( MWF_Config::NAME . '-' . $form_id, MWF_Functions::get_form_key_from_form_id( $form_id ) );
		$this->assertNull( MWF_Functions::get_form_key_from_form_id( 'dummy' ) );
	}

	/**
	 * @test
	 * @group get_form_id_from_form_key
	 */
	public function get_form_id_from_form_key() {
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$this->assertEquals( $form_id, MWF_Functions::get_form_id_from_form_key( $form_key ) );
		$this->assertNull( MWF_Functions::get_form_id_from_form_key( 'dummy' ) );
	}

	/**
	 * @test
	 * @group get_contact_data_post_type_from_form_id
	 */
	public function get_contact_data_post_type_from_form_id() {
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$this->assertEquals( 'mwf_' . $form_id, MWF_Functions::get_contact_data_post_type_from_form_id( $form_id ) );
		$this->assertNull( MWF_Functions::get_contact_data_post_type_from_form_id( 'dummy' ) );
	}

	/**
	 * @test
	 * @group is_contact_data_post_type
	 */
	public function is_contact_data_post_type() {
		$this->assertFalse( MWF_Functions::is_contact_data_post_type( 'dummy' ) );
		$this->assertTrue( MWF_Functions::is_contact_data_post_type( 'mwf_1' ) );
		$this->assertFalse( MWF_Functions::is_contact_data_post_type( 'mwf_dummy' ) );
	}

	public function get_multimedia_data() {}

	public function mwform_enqueue_scripts() {}

	/**
	 * @test
	 * @group generate_input_attribute
	 */
	public function generate_input_attribute() {
		$this->assertEquals( 'name-1="value-1"', MWF_Functions::generate_input_attribute( 'name-1', 'value-1' ) );
		$this->assertEquals( 'name-1=""', MWF_Functions::generate_input_attribute( 'name-1', '' ) );
		$this->assertNull( MWF_Functions::generate_input_attribute( 'name-1', null ) );
	}
}
