<?php
class MW_WP_Form_Form_Fields_Test extends WP_UnitTestCase {

	protected function _create_form() {
		return $this->factory->post->create(
			array(
				'post_type' => MWF_Config::NAME,
			)
		);
	}

	/**
	 * @test
	 * @group get_form_fields
	 */
	public function get_form_fields() {
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Form_Fields = MW_WP_Form_Form_Fields::instantiation( $form_key );
		$form_fields = $Form_Fields->get_form_fields();
		$this->assertTrue( ! empty( $form_fields ) );
	}
}
