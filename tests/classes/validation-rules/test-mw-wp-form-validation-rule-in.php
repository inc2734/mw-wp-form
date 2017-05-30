<?php
class MW_WP_Form_Validation_Rule_In_Test extends WP_UnitTestCase {

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

		$Rule = new MW_WP_Form_Validation_Rule_In();
		$Rule->set_Data( $Data );

		$Data->set( 'in', '' );
		$this->assertNull( $Rule->rule( 'in', array( 'options' => array( 'aaa' ) ) ) );

		$Data->set( 'in', 'aaa' );
		$this->assertNull( $Rule->rule( 'in', array( 'options' => array( 'aaa' ) ) ) );

		$Data->set( 'in', 0 );
		$this->assertNull( $Rule->rule( 'in', array( 'options' => array( '0' ) ) ) );

		$Data->set( 'in', 'aaa' );
		$this->assertNull( $Rule->rule( 'in', array( 'options' => array( 'aaa', 'bbb' ) ) ) );

		$Data->set( 'in', 'aaa' );
		$this->assertNotNull( $Rule->rule( 'in', array( 'options' => array( 'bbb' ) ) ) );

		$Data->set( 'in', 'aaa' );
		$this->assertNotNull( $Rule->rule( 'in', array( 'options' => array( 'bbb', 'ccc' ) ) ) );
	}
}
