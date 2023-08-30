<?php
class MW_WP_Form_Validation_Rule_Tel_Test extends WP_UnitTestCase {

	public function tear_down() {
		parent::tear_down();
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
		$Rule     = new MW_WP_Form_Validation_Rule_Tel( $Data );

		$Data->set( 'tel', '' );
		$this->assertNull( $Rule->rule( 'tel' ) );

		$Data->set( 'tel', '00-0000-0000' );
		$this->assertNull( $Rule->rule( 'tel' ) );
		$Data->set( 'tel', '000-000-0000' );
		$this->assertNull( $Rule->rule( 'tel' ) );
		$Data->set( 'tel', '000-0000-0000' );
		$this->assertNull( $Rule->rule( 'tel' ) );
		$Data->set( 'tel', '0000-00-0000' );
		$this->assertNull( $Rule->rule( 'tel' ) );
		$Data->set( 'tel', '0000-000-000' );
		$this->assertNull( $Rule->rule( 'tel' ) );
		$Data->set( 'tel', '00000-0-0000' );
		$this->assertNull( $Rule->rule( 'tel' ) );

		$Data->set( 'tel', '0000000000' );
		$this->assertNull( $Rule->rule( 'tel' ) );
		$Data->set( 'tel', '00000000000' );
		$this->assertNull( $Rule->rule( 'tel' ) );

		$Data->set( 'tel', 'aaa' );
		$this->assertNotNull( $Rule->rule( 'tel' ) );
		$Data->set( 'tel', '000000000' );
		$this->assertNotNull( $Rule->rule( 'tel' ) );
		$Data->set( 'tel', '000000000000' );
		$this->assertNotNull( $Rule->rule( 'tel' ) );
	}
}
