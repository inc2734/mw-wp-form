<?php
class MW_WP_Form_Form_Fields_Test extends WP_UnitTestCase {

	/**
	 * @test
	 * @group get_form_fields
	 */
	public function get_form_fields() {
		$Form_Fields = MW_WP_Form_Form_Fields::instantiation();
		$form_fields = $Form_Fields->get_form_fields();
		$this->assertTrue( ! empty( $form_fields ) );
	}
}
