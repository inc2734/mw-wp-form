<?php
class MW_WP_Form_Validation_Rule_In_Test extends WP_UnitTestCase {

	/**
	 * @var MW_WP_Form_Validation_Rule_In
	 */
	protected $Rule;
	
	/**
	 * setUp
	 */
	public function setUp() {
		parent::setUp();
		$form_key   = MWF_Config::NAME . '-1';
		$this->Data = MW_WP_Form_Data::getInstance( $form_key );
		$this->Rule = new MW_WP_Form_Validation_Rule_In();
		$this->Rule->set_Data( $this->Data );
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_空文字列ならnull() {
		$this->Data->set( 'text', '' );
		$this->assertNull( $this->Rule->rule( 'text', array( 'options' => array( 'aaa' ) ) ) );
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_一致すればnull() {
		$this->Data->set( 'text', 'aaa' );
		$this->assertNull( $this->Rule->rule( 'text', array( 'options' => array( 'aaa' ) ) ) );
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_型が一致しなくてもnull() {
		$this->Data->set( 'text', 0 );
		$this->assertNull( $this->Rule->rule( 'text', array( 'options' => array( '0' ) ) ) );
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_含まれていればnull() {
		$this->Data->set( 'text', 'aaa' );
		$this->assertNull( $this->Rule->rule( 'text', array( 'options' => array( 'aaa', 'bbb' ) ) ) );
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_一致しなければnotnull() {
		$this->Data->set( 'text', 'aaa' );
		$this->assertNotNull( $this->Rule->rule( 'text', array( 'options' => array( 'bbb' ) ) ) );
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_含まれていなければnull() {
		$this->Data->set( 'text', 'aaa' );
		$this->assertNotNull( $this->Rule->rule( 'text', array( 'options' => array( 'bbb', 'ccc' ) ) ) );
	}
}
