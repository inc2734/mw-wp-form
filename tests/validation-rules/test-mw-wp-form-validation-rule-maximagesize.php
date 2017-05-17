<?php
class MW_WP_Form_Validation_Rule_MaxImageSize_Test extends WP_UnitTestCase {

	/**
	 * @var MW_WP_Form_Validation_Rule_MaxImageSize
	 */
	protected $Rule;

	/**
	 * setUp
	 */
	public function setUp() {
		parent::setUp();
		$form_key   = MWF_Config::NAME . '-1';
		$this->Data = NEW_MW_WP_Form_Data::connect( $form_key );
		$this->Rule = new MW_WP_Form_Validation_Rule_MaxImageSize();
		$this->Rule->set_Data( $this->Data );
		$this->filepath = $this->save_image();
		$this->Data->set( MWF_Config::UPLOAD_FILE_KEYS, array( 'image' ) );
		$this->Data->set( 'image', MWF_Functions::filepath_to_url( $this->filepath ) );
	}

	/**
	 * tearDown
	 */
	public function tearDown() {
		parent::tearDown();
		$this->Data->clear_values();
		unlink( $this->filepath );
	}

	/**
	 * @group MW_WP_Form_Validation_Rule_MaxImageSize
	 */
	public function test__幅も高さも小さい() {
		$this->assertNull( $this->Rule->rule( 'image', array( 'width' => 600, 'height' => 600 ) ) );
	}

	/**
	 * @group MW_WP_Form_Validation_Rule_MaxImageSize
	 */
	public function test__幅は小さい() {
		$this->assertNotNull( $this->Rule->rule( 'image', array( 'width' => 600, 'height' => 400 ) ) );
	}

	/**
	 * @group MW_WP_Form_Validation_Rule_MaxImageSize
	 */
	public function test__高さは小さい() {
		$this->assertNotNull( $this->Rule->rule( 'image', array( 'width' => 400, 'height' => 600 ) ) );
	}

	/**
	 * @group MW_WP_Form_Validation_Rule_MaxImageSize
	 */
	public function test__幅も高さも大きい() {
		$this->assertNotNull( $this->Rule->rule( 'image', array( 'width' => 400, 'height' => 400 ) ) );
	}

	/**
	 * @group MW_WP_Form_Validation_Rule_MaxImageSize
	 */
	public function test__幅も高さも同じ() {
		$this->assertNull( $this->Rule->rule( 'image', array( 'width' => 500, 'height' => 500 ) ) );
	}

	protected function save_image() {
		$wp_upload_dir = wp_upload_dir();
		system( "sudo chmod 777 " . $wp_upload_dir['basedir'] );
		system( "sudo mkdir -p " . $wp_upload_dir['path'] );
		$resource_id = imagecreatetruecolor( 500, 500 );
		$filepath = $wp_upload_dir['path'] . '/1.png';
		imagepng( $resource_id, $filepath );
		return $filepath;
	}
}
