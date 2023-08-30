<?php
class MW_WP_Form_Validation_Rule_Eq_Test extends WP_UnitTestCase {

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
		$Rule     = new MW_WP_Form_Validation_Rule_Eq( $Data );

		$Data->set( 'target-1', '' );
		$Data->set( 'target-2', 'aaa' );
		$this->assertNull( $Rule->rule( 'text', array( 'target' => 'target-2' ) ) );

		$Data->set( 'target-1', 'aaa' );
		$Data->set( 'target-2', 'aaa' );
		$this->assertNull( $Rule->rule( 'target-1', array( 'target' => 'target-2' ) ) );

		$Data->set( 'target-1', 1 );
		$Data->set( 'target-2', '1' );
		$this->assertNull( $Rule->rule( 'target-1', array( 'target' => 'target-2' ) ) );

		$Data->set( 'target-1', null );
		$Data->set( 'target-2', 'aaa' );
		$this->assertNull( $Rule->rule( 'target-1', array( 'target' => 'target-2' ) ) );

		$Data->set( 'target-1', 'aaa' );
		$Data->set( 'target-2', 'bbb' );
		$this->assertNotNull( $Rule->rule( 'target-1', array( 'target' => 'target-2' ) ) );

		$Data->set( 'target-1', '' );
		$Data->set( 'target-2', 'aaa' );
		$this->assertNotNull( $Rule->rule( 'target-1', array( 'target' => 'target-2' ) ) );
	}
}
