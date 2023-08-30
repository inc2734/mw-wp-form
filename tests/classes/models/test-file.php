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
}
