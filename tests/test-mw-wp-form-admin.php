<?php
class MW_WP_Form_Admim_Test extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
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
	}

	/**
	 * test_mwform_contact_data_post_types_add_invalid_post_type
	 */
	public function test_mwform_contact_data_post_types_add_invalid_post_type() {
		add_filter(
			'mwform_contact_data_post_types',
			array( $this, 'mwform_contact_data_post_types_invalid_post_type' )
		);
		$post_types = MW_WP_Form_Contact_Data_Setting::get_posts();
		$this->assertEquals( 5, count( $post_types ) );
	}
	public function mwform_contact_data_post_types_invalid_post_type( $post_types ) {
		$post_types[] = 'fugafuga';
		$post_types[] = 'hogehoge';
		return $post_types;
	}

	/**
	 * test_mwform_contact_data_post_types
	 */
	public function test_mwform_contact_data_post_types() {
		add_filter(
			'mwform_contact_data_post_types',
			array( $this, 'mwform_contact_data_post_types' )
		);
		$post_types = MW_WP_Form_Contact_Data_Setting::get_posts();
		$this->assertEquals( 4, count( $post_types ) );
	}
	public function mwform_contact_data_post_types( $post_types ) {
		unset( $post_types[0] );
		return $post_types;
	}

	/**
	 * test_get_forms_using_database
	 */
	public function test_get_forms_using_database() {
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