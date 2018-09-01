<?php
class MW_WP_Form_Validation_Rule_Mail_Test extends WP_UnitTestCase {

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
		$Rule     = new MW_WP_Form_Validation_Rule_Mail( $Data );

		$Data->set( 'mail', '' );
		$this->assertNull( $Rule->rule( 'mail' ) );

		$Data->set( 'mail', 'info@example.com' );
		$this->assertNull( $Rule->rule( 'mail' ) );

		$Data->set( 'mail', '@example.com' );
		$this->assertNotNull( $Rule->rule( 'mail' ) );

		$Data->set( 'mail', 'into@' );
		$this->assertNotNull( $Rule->rule( 'mail' ) );

		$Data->set( 'mail', 'aaa' );
		$this->assertNotNull( $Rule->rule( 'mail' ) );
	}
}
