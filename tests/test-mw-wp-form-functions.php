<?php
class MW_WP_Form_Functions_Test extends WP_UnitTestCase {

	/**
	 * @group temp
	 * @group move_temp_file_to_upload_dir
	 */
	public function test_move_temp_file_to_upload_dir() {
		$old_filepath  = '/old/file/path/test.jpg';
		$wp_upload_dir = wp_upload_dir();

		$new_filepath  = MWF_Functions::move_temp_file_to_upload_dir( $old_filepath );
		$this->assertEquals(
			$wp_upload_dir['path'] . '/test.jpg',
			$new_filepath
		);

		$new_filepath  = MWF_Functions::move_temp_file_to_upload_dir( $old_filepath, '/dir' );
		$this->assertEquals(
			'/dir/test.jpg',
			$new_filepath
		);

		$new_filepath  = MWF_Functions::move_temp_file_to_upload_dir( $old_filepath, '', 'file.jpg' );
		$this->assertEquals(
			$wp_upload_dir['path'] . '/file.jpg',
			$new_filepath
		);

		$new_filepath  = MWF_Functions::move_temp_file_to_upload_dir( $old_filepath, '/dir', 'file.jpg' );
		$this->assertEquals(
			'/dir/file.jpg',
			$new_filepath
		);
	}
}
