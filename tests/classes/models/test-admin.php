<?php
class MW_WP_Form_Admin_Test extends WP_UnitTestCase {

	public function tear_down() {
		parent::tear_down();
		_delete_all_data();
	}

	/**
	 * @test
	 * @group get_forms
	 */
	public function get_forms() {
		$Admin = new MW_WP_Form_Admin();

		$this->assertEquals( 0, count( $Admin->get_forms() ) );

		$post_ids = $this->factory->post->create_many(
			5,
			array(
				'post_type' => MWF_Config::NAME,
			)
		);

		$this->assertEquals( 5, count( $Admin->get_forms() ) );
	}

	/**
	 * @test
	 * @group get_forms_using_database
	 */
	public function get_forms_using_database() {
		$Admin = new MW_WP_Form_Admin();

		$this->assertEquals( 0, count( $Admin->get_forms_using_database() ) );

		$post_ids = $this->factory->post->create_many(
			5,
			array(
				'post_type' => MWF_Config::NAME,
			)
		);

		update_post_meta( current( $post_ids ), MWF_Config::NAME, array(
			'usedb' => 1,
		) );

		$this->assertEquals( 1, count( $Admin->get_forms_using_database() ) );
	}
}
