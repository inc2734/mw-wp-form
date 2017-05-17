<?php
class MW_WP_Form_Validation_Rule_Between_Test extends WP_UnitTestCase {

	/**
	 * @var MW_WP_Form_Validation_Rule_Between
	 */
	protected $Rule;

	/**
	 * setUp
	 */
	public function setUp() {
		parent::setUp();
		$form_key   = MWF_Config::NAME . '-1';
		$this->Data = MW_WP_Form_Data::connect( $form_key );
		$this->Rule = new MW_WP_Form_Validation_Rule_Between();
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
		$this->assertNull( $this->Rule->rule( 'text', array( 'min' => 2, 'max' => 5 ) ) );
	}

	public function test_2文字未満ならnotnull() {
		$this->Data->set( 'text', '1' );
		$this->assertNotNull( $this->Rule->rule( 'text', array( 'min' => 2, 'max' => 5 ) ) );
	}

	public function test_5文字より大きければnotnull() {
		$this->Data->set( 'text', '123456' );
		$this->assertNotNull( $this->Rule->rule( 'text', array( 'min' => 2, 'max' => 5 ) ) );
	}

	public function test_2文字以上5文字以下ならnull() {
		$this->Data->set( 'text-1', '12' );
		$this->Data->set( 'text-2', '12345' );
		$this->assertNull( $this->Rule->rule( 'text-1', array( 'min' => 2, 'max' => 5 ) ) );
		$this->assertNull( $this->Rule->rule( 'text-2', array( 'min' => 2, 'max' => 5 ) ) );
	}
}
