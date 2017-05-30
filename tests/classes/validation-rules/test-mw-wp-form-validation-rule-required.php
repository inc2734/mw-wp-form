<?php
class MW_WP_Form_Validation_Rule_Required_Test extends WP_UnitTestCase {

	public function tearDown() {
		parent::tearDown();
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
	 * @group rule
	 */
	public function rule() {
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Data     = MW_WP_Form_Data::connect( $form_key );

		$Rule = new MW_WP_Form_Validation_Rule_Required();
		$Rule->set_Data( $Data );

		$Data->set( 'required', '' );
		$this->assertNull( $Rule->rule( 'required' ) );

		$Data->set( 'required', 'aaa' );
		$this->assertNull( $Rule->rule( 'required' ) );

		$Data->set( 'required', null );
		$this->assertNotNull( $Rule->rule( 'required' ) );
	}
}
