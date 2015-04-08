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

	/**
	 * @group set_rule
	 */
	public function test_set_rule_MW_WP_Form_Validationオブジェクトを返す() {
		// set_rule はフック経由で使用されることがあるので必ず public である必要がある
		$this->assertTrue(
			is_a(
				$this->Validation->set_rule( 'text', 'noEmpty' ),
				'MW_WP_Form_Validation'
			)
		);
	}
}
