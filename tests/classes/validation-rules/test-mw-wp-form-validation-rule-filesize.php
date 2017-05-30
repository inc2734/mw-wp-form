<?php
class MW_WP_Form_Validation_Rule_FileSize_Test extends WP_UnitTestCase {

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

		$Rule = new MW_WP_Form_Validation_Rule_FileSize();
		$Rule->set_Data( $Data );

		$Data->set( MWF_Config::UPLOAD_FILES, array(
			'filesize' => array( 'size' => 10 ),
		) );
		$this->assertNull( $Rule->rule( 'filesize', array( 'bytes' => 10 ) ) );

		$Data->set( MWF_Config::UPLOAD_FILES, array(
			'filesize' => array( 'size' => 10 ),
		) );
		$this->assertNull( $Rule->rule( 'filesize', array( 'bytes' => 11 ) ) );

		$Data->set( MWF_Config::UPLOAD_FILES, array(
			'filesize' => array( 'size' => 11 ),
		) );
		$this->assertNotNull( $Rule->rule( 'filesize', array( 'bytes' => 10 ) ) );

		$Data->set( MWF_Config::UPLOAD_FILES, array(
			'filesize' => array( 'error' => 1 ),
		) );
		$this->assertNotNull( $Rule->rule( 'filesize', array( 'bytes' => 0 ) ) );
	}
}
