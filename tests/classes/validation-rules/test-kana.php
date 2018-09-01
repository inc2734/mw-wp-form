<?php
class MW_WP_Form_Validation_Rule_Kana_Test extends WP_UnitTestCase {

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
		$Rule     = new MW_WP_Form_Validation_Rule_Kana( $Data );

		$Data->set( 'kana', '' );
		$this->assertNull( $Rule->rule( 'kana' ) );

		$Data->set( 'kana', 'あいうえお' );
		$this->assertNull( $Rule->rule( 'kana' ) );

		$Data->set( 'kana', 'アイウエオ' );
		$this->assertNull( $Rule->rule( 'kana' ) );

		$Data->set( 'kana', 'あいうえおアイウエオ' );
		$this->assertNull( $Rule->rule( 'kana' ) );

		$Data->set( 'kana', 'あいうえお0アイウエオ' );
		$this->assertNotNull( $Rule->rule( 'kana' ) );
	}
}
