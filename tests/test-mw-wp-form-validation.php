<?php
class MW_WP_Form_Validation_Test extends WP_UnitTestCase {
	
	/**
	 * @var MW_WP_Form_Validation
	 */
	protected $Validation;

	/**
	 * setUp
	 */
	public function setUp() {
		$this->Validation = new MW_WP_Form_Validation( new MW_WP_Form_Error() );
	}

	/**
	 * @group set_validation_rules
	 */
	public function test_set_validation_rules() {
		$validations = array(
			'alpha' => new MW_WP_Form_Validation_Rule_Alpha(),
		);
		$this->Validation->set_validation_rules( $validations );
		$this->assertEquals( $validations, $this->Validation->get_validation_rules() );
	}
}
