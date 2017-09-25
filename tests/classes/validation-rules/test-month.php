<?php
class MW_WP_Form_Validation_Rule_Month_Test extends WP_UnitTestCase {

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
		$Rule     = new MW_WP_Form_Validation_Rule_Month( $Data );

		$Data->set( 'month', '' );
		$this->assertNull( $Rule->rule( 'month' ) );

		$Data->set( 'month', '2015-01' );
		$this->assertNull( $Rule->rule( 'month' ) );
		$Data->set( 'month', '2015-1' );
		$this->assertNull( $Rule->rule( 'month' ) );

		$Data->set( 'month', '2015年01月' );
		$this->assertNull( $Rule->rule( 'month' ) );
		$Data->set( 'month', '2015年1月' );
		$this->assertNull( $Rule->rule( 'month' ) );

		$Data->set( 'month', 'aaa' );
		$this->assertNotNull( $Rule->rule( 'month' ) );
	}

	/**
	 * @test
	 * @group convert_jpdate_to_timestamp
	 */
	public function convert_jpdate_to_timestamp() {
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Data     = MW_WP_Form_Data::connect( $form_key );
		$Rule     = new MW_WP_Form_Validation_Rule_Month( $Data );

		$this->assertEquals(
			strtotime( '2015-01' ),
			$Rule->convert_jpdate_to_timestamp( '2015年01月' )
		);

		$this->assertEquals(
			strtotime( '2015-01' ),
			$Rule->convert_jpdate_to_timestamp( '2015年1月' )
		);
	}
}
