<?php
class MW_WP_Form_Validation_Rule_FileType_Test extends WP_UnitTestCase {

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

		$Rule = new MW_WP_Form_Validation_Rule_FileType();
		$Rule->set_Data( $Data );

		$Data->set( 'text', '' );
		$this->assertNull( $Rule->rule( 'text', array( 'types' => 'jpg' ) ) );

		$Data->set( 'filetype', 'example.jpg' );
		$this->assertNull( $Rule->rule( 'filetype', array( 'types' => 'jpg' ) ) );

		$Data->set( 'filetype', 'example.jpg' );
		$this->assertNull( $Rule->rule( 'filetype', array( 'types' => 'jpg,png' ) ) );

		$Data->set( 'filetype', 'example.gif' );
		$this->assertNotNull( $Rule->rule( 'filetype', array( 'types' => 'jpg' ) ) );

		$Data->set( 'filetype', 'example.gif' );
		$this->assertNotNull( $Rule->rule( 'filetype', array( 'types' => 'jpg,png' ) ) );
	}
}
