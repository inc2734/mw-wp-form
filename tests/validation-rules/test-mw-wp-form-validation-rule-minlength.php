<?php
class MW_WP_Form_Validation_Rule_MinLength_Test extends WP_UnitTestCase {

	/**
	 * @var MW_WP_Form_Validation_Rule_MinLength
	 */
	protected $Rule;

	/**
	 * setUp
	 */
	public function setUp() {
		parent::setUp();
		$form_key   = MWF_Config::NAME . '-1';
		$this->Data = MW_WP_Form_Data::getInstance( $form_key );
		$this->Rule = new MW_WP_Form_Validation_Rule_MinLength();
		$this->Rule->set_Data( $this->Data );
	}

	/**
	 * tearDown
	 */
	public function tearDown() {
		parent::tearDown();
		$this->Data->clear_values();
	}

	public function test_空文字列ならnull() {
		$this->Data->set( 'text', '' );
		$this->assertNull( $this->Rule->rule( 'text', array( 'min' => 2 ) ) );
	}

	public function test_2文字未満ならnotnull() {
		$this->Data->set( 'text', '1' );
		$this->assertNotNull( $this->Rule->rule( 'text', array( 'min' => 2 ) ) );
	}

	public function test_2文字以上ならnull() {
		$this->Data->set( 'text', '12' );
		$this->assertNull( $this->Rule->rule( 'text', array( 'min' => 2 ) ) );
	}
}
