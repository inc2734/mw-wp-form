<?php
class MW_WP_Form_Validation_Rule_Numeric_Test extends WP_UnitTestCase {

	/**
	 * @var MW_WP_Form_Validation_Rule_Numeric
	 */
	protected $Rule;

	/**
	 * setUp
	 */
	public function setUp() {
		parent::setUp();
		$form_key   = MWF_Config::NAME . '-1';
		$this->Data = NEW_MW_WP_Form_Data::connect( $form_key );
		$this->Rule = new MW_WP_Form_Validation_Rule_Numeric();
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

	public function test_数字_文字列型のみならnull() {
		$this->Data->set( 'text', '111' );
		$this->assertNull( $this->Rule->rule( 'text' ) );
	}

	public function test_数字_数値型のみならnull() {
		$this->Data->set( 'text', 111 );
		$this->assertNull( $this->Rule->rule( 'text' ) );
	}

	public function test_数字以外が含まれていたらnotnull() {
		$this->Data->set( 'text', 'abc123' );
		$this->assertNotNull( $this->Rule->rule( 'text' ) );
	}
}
