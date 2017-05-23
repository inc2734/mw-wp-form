<?php
class MW_WP_Form_Test extends WP_UnitTestCase {

	/**
	 * @test
	 * @group _uninstall
	 */
	public function uninstall() {
		$post_id = $this->factory->post->create(
			array(
				'post_type' => MWF_Config::NAME,
			)
		);

		update_option( MWF_Config::NAME . '-chart-' . $post_id, 'dummy' );
		$data_post_id = $this->factory->post->create(
			array(
				'post_type' => MWF_Functions::get_contact_data_post_type_from_form_id( $post_id ),
			)
		);

		$MW_WP_Form_File = new MW_WP_Form_File;
		$temp_dir = $MW_WP_Form_File->get_temp_dir();
		$temp_dir = $temp_dir['dir'];
		system( "sudo chmod 777 " . WP_CONTENT_DIR . '/uploads' );
		$MW_WP_Form_File->create_temp_dir();
		$this->assertEquals( true, file_exists( $temp_dir ) );

		update_option( MWF_Config::NAME, 'dummy' );

		MW_WP_Form::_uninstall();

		$posts = get_posts( array(
			'post_type'      => MWF_Config::NAME,
			'posts_per_page' => -1,
		) );
		$this->assertEquals( 0, count( $posts ) );

		$data_posts = get_posts( array(
			'post_type'      => MWF_Functions::get_contact_data_post_type_from_form_id( $post_id ),
			'posts_per_page' => -1,
		) );
		$this->assertEquals( 0, count( $data_posts ) );

		$this->assertFalse( file_exists( $temp_dir ) );
		$this->assertFalse( get_option( MWF_Config::NAME . '-chart-' . $post_id ) );
		$this->assertFalse( get_option( MWF_Config::NAME ) );
	}
}
