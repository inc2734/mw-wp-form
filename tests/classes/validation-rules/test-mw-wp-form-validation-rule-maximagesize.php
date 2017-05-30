<?php
class MW_WP_Form_Validation_Rule_MaxImageSize_Test extends WP_UnitTestCase {

	protected $filepath;

	public function setUp() {
		parent::setUp();
		$this->filepath = $this->_save_image();
	}

	public function tearDown() {
		parent::tearDown();
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
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Data     = MW_WP_Form_Data::connect( $form_key );

		$Rule = new MW_WP_Form_Validation_Rule_MaxImageSize();
		$Rule->set_Data( $Data );
		$Data->set( MWF_Config::UPLOAD_FILE_KEYS, array( 'image' ) );
		$Data->set( 'image', MWF_Functions::filepath_to_url( $this->filepath ) );

		$this->assertNull( $Rule->rule( 'image', array( 'width' => 600, 'height' => 600 ) ) );

		$this->assertNotNull( $Rule->rule( 'image', array( 'width' => 600, 'height' => 400 ) ) );

		$this->assertNotNull( $Rule->rule( 'image', array( 'width' => 400, 'height' => 600 ) ) );

		$this->assertNotNull( $Rule->rule( 'image', array( 'width' => 400, 'height' => 400 ) ) );

		$this->assertNull( $Rule->rule( 'image', array( 'width' => 500, 'height' => 500 ) ) );
	}

	protected function _save_image() {
		$wp_upload_dir = wp_upload_dir();
		system( "chmod 777 " . $wp_upload_dir['basedir'] );
		system( "mkdir -p " . $wp_upload_dir['path'] );
		$resource_id = imagecreatetruecolor( 500, 500 );
		$filepath = $wp_upload_dir['path'] . '/1.png';
		imagepng( $resource_id, $filepath );
		return $filepath;
	}
}
