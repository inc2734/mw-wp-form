<?php
class MW_WP_Form_Validation_Rule_Eq_Test extends WP_UnitTestCase {

	/**
	 * @var MW_WP_Form_Validation_Rule_Eq
	 */
	protected $Rule;

	/**
	 * setUp
	 */
	public function setUp() {
		parent::setUp();
		$form_key   = MWF_Config::NAME . '-1';
		$this->Data = MW_WP_Form_Data::getInstance( $form_key );
		$this->Rule = new MW_WP_Form_Validation_Rule_Eq();
		$this->Rule->set_Data( $this->Data );
	}

	/**
	 * tearDown
	 */
	public function tearDown() {
		parent::tearDown();
		$this->Data->clear_values();
	}

	public function test_存在しなければnull() {
		$this->Data->set( 'target-1', '' );
		$this->Data->set( 'target-2', 'aaa' );
		$this->assertNull( $this->Rule->rule( 'text', array( 'target' => 'target-2' ) ) );
	}

	public function test_同じならnull() {
		$this->Data->set( 'target-1', 'aaa' );
		$this->Data->set( 'target-2', 'aaa' );
		$this->assertNull( $this->Rule->rule( 'target-1', array( 'target' => 'target-2' ) ) );
	}

	public function test_型が異なっていてもnull() {
		$this->Data->set( 'target-1', 1 );
		$this->Data->set( 'target-2', '1' );
		$this->assertNull( $this->Rule->rule( 'target-1', array( 'target' => 'target-2' ) ) );
	}

	public function test_nullならnull() {
		$this->Data->set( 'target-1', null );
		$this->Data->set( 'target-2', 'aaa' );
		$this->assertNull( $this->Rule->rule( 'target-1', array( 'target' => 'target-2' ) ) );
	}

	public function test_異なっていればnotnull() {
		$this->Data->set( 'target-1', 'aaa' );
		$this->Data->set( 'target-2', 'bbb' );
		$this->assertNotNull( $this->Rule->rule( 'target-1', array( 'target' => 'target-2' ) ) );

		$this->Data->set( 'target-1', '' );
		$this->Data->set( 'target-2', 'aaa' );
		$this->assertNotNull( $this->Rule->rule( 'target-1', array( 'target' => 'target-2' ) ) );
	}
}
