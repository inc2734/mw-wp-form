<?php
class MW_WP_Form_Validation_Rule_Date_Test extends WP_UnitTestCase {

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
		$Rule     = new MW_WP_Form_Validation_Rule_Date( $Data );

		$Data->set( 'date', '' );
		$this->assertNull( $Rule->rule( 'date' ) );

		$Data->set( 'date', '2015-01-01' );
		$this->assertNull( $Rule->rule( 'date' ) );
		$Data->set( 'date', '2015-1-1' );
		$this->assertNull( $Rule->rule( 'date' ) );

		$Data->set( 'date', '2015年01月01日' );
		$this->assertNull( $Rule->rule( 'date' ) );
		$Data->set( 'date', '2015年1月1日' );
		$this->assertNull( $Rule->rule( 'date' ) );
	}

	/**
	 * @test
	 * @group convert_jpdate_to_timestamp
	 */
	public function convert_jpdate_to_timestamp() {
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Data     = MW_WP_Form_Data::connect( $form_key );
		$Rule     = new MW_WP_Form_Validation_Rule_Date( $Data );

		$this->assertEquals(
			$Rule->convert_jpdate_to_timestamp( '2015年01月01日' ),
			strtotime( '2015-01-01' )
		);

		$this->assertEquals(
			$Rule->convert_jpdate_to_timestamp( '2015年1月1日' ),
			strtotime( '2015-01-01' )
		);
	}
}
