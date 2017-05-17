<?php
class MW_WP_Form_Validation_Rule_AlphaNumeric_Test extends WP_UnitTestCase {

	/**
	 * @var MW_WP_Form_Validation_Rule_AlphaNumeric
	 */
	protected $Rule;

	/**
	 * setUp
	 */
	public function setUp() {
		parent::setUp();
		$form_key   = MWF_Config::NAME . '-1';
		$this->Data = MW_WP_Form_Data::connect( $form_key );
		$this->Rule = new MW_WP_Form_Validation_Rule_AlphaNumeric();
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
		$this->assertNull( $this->Rule->rule( 'text' ) );
	}

	public function test_数字アルファベットのみならnull() {
		$this->Data->set( 'text', 'abc123' );
		$this->assertNull( $this->Rule->rule( 'text' ) );
	}

	public function test_数字アルファベット以外が含まれていたらnotnull() {
		$this->Data->set( 'text', 'abc123-' );
		$this->assertNotNull( $this->Rule->rule( 'text' ) );
	}
}
