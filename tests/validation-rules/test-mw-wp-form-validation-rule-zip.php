<?php
class MW_WP_Form_Validation_Rule_Zip_Test extends WP_UnitTestCase {

	/**
	 * @var MW_WP_Form_Validation_Rule_Zip
	 */
	protected $Rule;

	/**
	 * setUp
	 */
	public function setUp() {
		parent::setUp();
		$form_key   = MWF_Config::NAME . '-1';
		$this->Data = MW_WP_Form_Data::connect( $form_key );
		$this->Rule = new MW_WP_Form_Validation_Rule_Zip();
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

	public function test_郵便番号の形式ならnull() {
		$this->Data->set( 'text', '000-0000' );
		$this->assertNull( $this->Rule->rule( 'text' ) );
		$this->Data->set( 'text', '0000000' );
		$this->assertNull( $this->Rule->rule( 'text' ) );
	}

	public function test_郵便番号の形式以外ならnotnull() {
		$this->Data->set( 'text', 'aaa' );
		$this->assertNotNull( $this->Rule->rule( 'text' ) );
	}
}
