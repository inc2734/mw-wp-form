<?php
class MW_WP_Form_File_Test extends WP_UnitTestCase {

	public function tear_down() {
		parent::tear_down();
		_delete_all_data();
	}

	/**
	 * @test
	 * @group _upload_mimes
	 */
	public function _upload_mimes() {
		$File = new MW_WP_Form_File();

		$this->assertEquals(
			array(
				'psd' => 'image/vnd.adobe.photoshop',
				'eps' => 'application/octet-stream',
				'ai'  => 'application/pdf',
			),
			$File->_upload_mimes( array() )
		);
	}

	/**
	 * @todo I do not know how to write file upload test...
	 */
	public function upload() {}

	/**
	 * @test
	 * @group get_temp_dir
	 */
	public function get_temp_dir() {
		$File = new MW_WP_Form_File();

		$this->assertEquals(
			array(
				'dir' => ABSPATH . 'wp-content/uploads/mw-wp-form_uploads',
				'url' => home_url( '/wp-content/uploads/mw-wp-form_uploads' ),
			),
			$File->get_temp_dir()
		);
	}

	/**
	 * @test
	 * @group create_temp_dir
	 */
	public function create_temp_dir() {
		$File = new MW_WP_Form_File();
		$File->create_temp_dir();
		$dir  = $File->get_temp_dir();
		$this->assertTrue( file_exists( $dir['dir'] ) );
	}

	/**
	 * @test
	 * @group remove_temp_dir
	 */
	public function remove_temp_dir() {
		$File = new MW_WP_Form_File();
		$File->create_temp_dir();
		$dir  = $File->get_temp_dir();
		$this->assertTrue( file_exists( $dir['dir'] ) );

		$File->remove_temp_dir();
		$this->assertFalse( file_exists( $dir['dir'] ) );
	}
}
