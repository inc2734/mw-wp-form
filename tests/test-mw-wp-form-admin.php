<?php
class MW_WP_Form_Admim_Test extends WP_UnitTestCase {

	/**
	 * MW_WP_Form_Admin::get_forms_using_database
	 */
	public function test_get_forms_using_database() {
		$post_ids = $this->factory->post->create_many(
			5,
			array(
				'post_type' => MWF_Config::NAME,
			)
		);
		foreach ( $post_ids as $post_id ) {
			$meta = array(
				'usedb' => 1,
			);
			update_post_meta( $post_id, MWF_Config::NAME, $meta );
		}

		$forms  = array();
		$Admin  = new MW_WP_Form_Admin();
		$_forms = $Admin->get_forms();
		foreach ( $_forms as $form ) {
			$Setting = new MW_WP_Form_Setting( $form->ID );
			if ( !$Setting->get( 'usedb' ) ) {
				continue;
			}
			$forms[] = $form;
		}
		$forms_using_database = $Admin->get_forms_using_database();
		$this->assertGreaterThan( 0, count( $forms_using_database ) );
		$this->assertEquals( count( $forms ), count( $forms_using_database ) );
	}
}