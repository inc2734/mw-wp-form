<?php
class MW_WP_Form_Validation_Rule_Between_Test extends WP_UnitTestCase {

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

		$Rule = new MW_WP_Form_Validation_Rule_Between();
		$Rule->set_Data( $Data );

		$Data->set( 'between', '' );
		$this->assertNull( $Rule->rule( 'between', array( 'min' => 2, 'max' => 5 ) ) );

		$Data->set( 'between', '1' );
		$this->assertNotNull( $Rule->rule( 'between', array( 'min' => 2, 'max' => 5 ) ) );

		$Data->set( 'between', '123456' );
		$this->assertNotNull( $Rule->rule( 'between', array( 'min' => 2, 'max' => 5 ) ) );

		$Data->set( 'between', '12' );
		$this->assertNull( $Rule->rule( 'between', array( 'min' => 2, 'max' => 5 ) ) );

		$Data->set( 'between', '12345' );
		$this->assertNull( $Rule->rule( 'between', array( 'min' => 2, 'max' => 5 ) ) );
	}
}
