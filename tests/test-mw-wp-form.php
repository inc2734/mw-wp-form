<?php
class MW_WP_Form_Test extends WP_UnitTestCase {

	/**
	 * アンインストールのテスト
	 */
	public function test_uninstall() {
		$post_ids = $this->factory->post->create_many(
			5,
			array(
				'post_type' => MWF_Config::NAME,
			)
		);

		foreach ( $post_ids as $post_id ) {
			update_option( MWF_Config::NAME . '-chart-' . $post_id, 1 );
			$data_post_ids = $this->factory->post->create_many(
				5,
				array(
					'post_type' => MWF_Config::DBDATA . $post_id,
				)
			);
		}

		$MW_WP_Form_File = new MW_WP_Form_File;
		$temp_dir = $MW_WP_Form_File->get_temp_dir();
		$temp_dir = $temp_dir['dir'];
		system( "sudo chmod 777 " . WP_CONTENT_DIR . '/uploads' );
		$MW_WP_Form_File->create_temp_dir();
		$this->assertEquals( true, file_exists( $temp_dir ) );

		update_option( MWF_Config::NAME, 1 );

		MW_WP_Form::uninstall();

		$posts = get_posts( array(
			'post_type' => MWF_Config::NAME,
			'posts_per_page' => -1,
		) );

		$this->assertEquals( 0, count( $posts ) );

		foreach ( $post_ids as $post_id ) {
			$option = get_option( MWF_Config::NAME . '-chart-' . $post_id );
			$this->assertEquals( null, $option );

			$data_posts = get_posts( array(
				'post_type' => MWF_Config::DBDATA . $post_id,
				'posts_per_page' => -1,
			) );
			$this->assertEquals( 0, count( $data_posts ) );
		}

		$this->assertEquals( false, file_exists( $temp_dir ) );

		$option = get_option( MWF_Config::NAME );
		$this->assertEquals( null, $option );
	}
}
