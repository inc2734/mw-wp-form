<?php
class MW_WP_Form_Validation_Rule_MinImageSize_Test extends WP_UnitTestCase {

	protected $filepath;

	public function set_up() {
		parent::set_up();
		$this->form_id  = $this->_create_form();
		$this->filepath = $this->_save_image();
	}

	public function tear_down() {
		parent::tear_down();
		_delete_all_data();
		unlink( $this->filepath );
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
	 * @group rule
	 */
	public function rule() {
		$form_key = MWF_Functions::get_form_key_from_form_id( $this->form_id );
		$Data     = MW_WP_Form_Data::connect( $form_key );
		$Rule     = new MW_WP_Form_Validation_Rule_MinImageSize( $Data );

		$Data->set( MWF_Config::UPLOAD_FILE_KEYS, array( 'image' ) );
		$Data->set( 'image', basename( $this->filepath ) );

		$this->assertNotNull( $Rule->rule( 'image', array( 'width' => 600, 'height' => 600 ) ) );

		$this->assertNotNull( $Rule->rule( 'image', array( 'width' => 600, 'height' => 400 ) ) );

		$this->assertNotNull( $Rule->rule( 'image', array( 'width' => 400, 'height' => 600 ) ) );

		$this->assertNull( $Rule->rule( 'image', array( 'width' => 400, 'height' => 400 ) ) );

		$this->assertNull( $Rule->rule( 'image', array( 'width' => 500, 'height' => 500 ) ) );
	}

	protected function _save_image() {
		$name        = 'image';
		$resource_id = imagecreatetruecolor( 500, 500 );
		$dir         = MW_WP_Form_Directory::generate_user_file_dirpath( $this->form_id, $name );
		wp_mkdir_p( $dir );
		$filepath = $dir . '/1.png';
		imagepng( $resource_id, $filepath );
		return $filepath;
	}
}
