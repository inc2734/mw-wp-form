<?php
class MW_WP_Form_Test extends WP_UnitTestCase {

	/**
	 * ショートコード mwform_formkey からデータを読めるかテスト（投稿内の場合）
	 */
	public function test_shortocde_mwform_formkey() {
		global $wp_query;
		$wp_query->is_singular = true;

		$form_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$Setting = new MW_WP_Form_Setting( $form_id );
		$Setting->set( 'input_url', '/contact/' );
		$Setting->save();

		$post_id = $this->factory->post->create( array(
			'post_type'    => 'paga',
			'post_content' => sprintf( '[mwform_formkey key="%d"]', $form_id ),
		) );
		$post = get_post( $post_id );

		$ExecShortcode = new MW_WP_Form_Exec_Shortcode( $post, '' );
		$this->assertTrue( $ExecShortcode->has_shortcode() );
		$this->assertEquals( '/contact/', $ExecShortcode->get( 'input_url' ) );
	}

	/**
	 * 囲み型ショートコード内のショートコード mwform_formkey からデータを読めるかテスト（投稿内の場合）
	 */
	public function test_shortocde_mwform_formkey_in_enclosed_shortcode() {
		global $wp_query;
		$wp_query->is_singular = true;

		$form_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$Setting = new MW_WP_Form_Setting( $form_id );
		$Setting->set( 'input_url', '/contact/' );
		$Setting->save();

		$post_id = $this->factory->post->create( array(
			'post_type'    => 'paga',
			'post_content' => sprintf( '[gallery][mwform_formkey key="%d"][/gallery]', $form_id ),
		) );
		$post = get_post( $post_id );

		$ExecShortcode = new MW_WP_Form_Exec_Shortcode( $post, '' );
		$this->assertTrue( $ExecShortcode->has_shortcode() );
		$this->assertEquals( '/contact/', $ExecShortcode->get( 'input_url' ) );
	}

	/**
	 * ショートコード mwform_formkey からデータを読めるかテスト（テンプレートの場合）
	 */
	public function test_get_in_template() {
		$form_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$Setting = new MW_WP_Form_Setting( $form_id );

		$wp_upload_dir = wp_upload_dir();
		$page2_path = $wp_upload_dir['basedir'] . '/page2.php';
		file_put_contents( $page2_path, sprintf( '[mwform_formkey key="%d"]', $form_id ) );
		$ExecShortcode = new MW_WP_Form_Exec_Shortcode( null, $page2_path );
		$this->assertTrue( $ExecShortcode->has_shortcode() );
		unlink( $page2_path );

		$page_path = get_template_directory() . '/page.php';
		$ExecShortcode = new MW_WP_Form_Exec_Shortcode( null, $page_path );
		$this->assertFalse( $ExecShortcode->has_shortcode() );
	}

	/**
	 * MWF_Functions::contact_data_post_type_to_form_key のテスト
	 */
	public function test_contact_data_post_type_to_form_key() {
		$post_type = MWF_Config::DBDATA . '1';
		$form_key = MWF_Functions::contact_data_post_type_to_form_key( $post_type );
		$this->assertEquals( $form_key, MWF_Config::NAME . '-1' );

		$post_type = MWF_Config::DBDATA . '-1';
		$form_key = MWF_Functions::contact_data_post_type_to_form_key( $post_type );
		$this->assertNull( $form_key );
	}

	/**
	 * ショートコード mwform からデータを読めるかテスト
	 */
	public function test_shortocde_mwform() {
		global $wp_query;
		$wp_query->is_singular = true;
		
		$post_id = $this->factory->post->create( array(
			'post_type'    => 'paga',
			'post_content' => sprintf( '[mwform key="testform" input="/contact/"]hoge[/mwform]' ),
		) );
		$post = get_post( $post_id );

		$ExecShortcode = new MW_WP_Form_Exec_Shortcode( $post, '' );
		$this->assertTrue( $ExecShortcode->has_shortcode() );
		$this->assertEquals( 'testform', $ExecShortcode->get( 'key' ) );
		$this->assertEquals( '/contact/', $ExecShortcode->get( 'input_url' ) );
	}

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

