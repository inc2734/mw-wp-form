<?php
class MW_WP_Form_File_Test extends WP_UnitTestCase {

	/**
	 * @var MW_WP_Form_File $File
	 */
	protected $File;

	/**
	 * setUp
	 */
	public function setUp() {
		parent::setUp();
		$this->File = new MW_WP_Form_File();
	}

	/**
	 * @group upload_mimes
	 */
	public function test_upload_mimes() {
		$this->assertEquals(
			array(
				'psd' => 'image/vnd.adobe.photoshop',
				'eps' => 'application/octet-stream',
				'ai'  => 'application/pdf',
			),
			$this->File->upload_mimes( array() )
		);
	}

	/**
	 * @group get_temp_dir
	 */
	public function test_get_temp_dir() {
		$this->assertEquals(
			array(
				'dir' => ABSPATH . 'wp-content/uploads/mw-wp-form_uploads',
				'url' => home_url( '/wp-content/uploads/mw-wp-form_uploads' ),
			),
			$this->File->get_temp_dir()
		);
	}
}
