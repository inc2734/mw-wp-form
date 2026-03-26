<?php
class MW_WP_Form_Directory_Test extends WP_UnitTestCase {

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
	 * @group generate_user_filepath
	 */
	public function generate_user_filepath() {
		MW_WP_Form_Csrf::save_token();
		$form_id       = $this->_create_form();
		$name          = 'file-1';
		$user_file_dir = MW_WP_Form_Directory::generate_user_file_dirpath( $form_id, $name );
		wp_mkdir_p( $user_file_dir );

		$filepath = MW_WP_Form_Directory::generate_user_filepath( $form_id, $name, 'test.jpg' );
		$this->assertSame(
			wp_normalize_path( trailingslashit( $user_file_dir ) . 'test.jpg' ),
			$filepath
		);
	}

	/**
	 * @test
	 * @group generate_user_filepath
	 */
	public function generate_user_filepath_should_reject_absolute_path() {
		MW_WP_Form_Csrf::save_token();
		$form_id       = $this->_create_form();
		$name          = 'file-1';
		$user_file_dir = MW_WP_Form_Directory::generate_user_file_dirpath( $form_id, $name );
		wp_mkdir_p( $user_file_dir );

		$this->expectException( '\RuntimeException' );
		MW_WP_Form_Directory::generate_user_filepath( $form_id, $name, '/tmp/evil.php' );
	}

	/**
	 * @test
	 * @group generate_user_filepath
	 */
	public function generate_user_filepath_should_reject_nested_path() {
		MW_WP_Form_Csrf::save_token();
		$form_id       = $this->_create_form();
		$name          = 'file-1';
		$user_file_dir = MW_WP_Form_Directory::generate_user_file_dirpath( $form_id, $name );
		wp_mkdir_p( $user_file_dir );

		$this->expectException( '\RuntimeException' );
		MW_WP_Form_Directory::generate_user_filepath( $form_id, $name, 'nested/test.jpg' );
	}

	/**
	 * @test
	 * @group generate_user_filepath
	 */
	public function generate_user_filepath_should_reject_windows_absolute_path() {
		MW_WP_Form_Csrf::save_token();
		$form_id       = $this->_create_form();
		$name          = 'file-1';
		$user_file_dir = MW_WP_Form_Directory::generate_user_file_dirpath( $form_id, $name );
		wp_mkdir_p( $user_file_dir );

		$this->expectException( '\RuntimeException' );
		MW_WP_Form_Directory::generate_user_filepath( $form_id, $name, 'C:\\tmp\\evil.php' );
	}
}
