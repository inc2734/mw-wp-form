<?php
class MW_WP_Form_Validation_Rule_Katakana_Test extends WP_UnitTestCase {

	/**
	 * @var MW_WP_Form_Validation_Rule_Katakana
	 */
	protected $Rule;

	/**
	 * setUp
	 */
	public function setUp() {
		parent::setUp();
		$form_key   = MWF_Config::NAME . '-1';
		$this->Data = MW_WP_Form_Data::getInstance( $form_key );
		$this->Rule = new MW_WP_Form_Validation_Rule_Katakana();
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

	public function test_カタカナのみならnull() {
		$this->Data->set( 'text', 'アイウエオ' );
		$this->assertNull( $this->Rule->rule( 'text' ) );
	}

	public function test_カタカナ以外が含まれていたらnotnull() {
		$this->Data->set( 'text', 'アイウエオ1' );
		$this->assertNotNull( $this->Rule->rule( 'text' ) );
	}
}
