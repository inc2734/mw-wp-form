<?php
class MW_WP_Form_Validation_Rule_FileType_Test extends WP_UnitTestCase {

	/**
	 * @var MW_WP_Form_Validation_Rule_FileType
	 */
	protected $Rule;
	
	/**
	 * setUp
	 */
	public function setUp() {
		parent::setUp();
		$form_key   = MWF_Config::NAME . '-1';
		$this->Data = MW_WP_Form_Data::getInstance( $form_key );
		$this->Rule = new MW_WP_Form_Validation_Rule_FileType();
		$this->Rule->set_Data( $this->Data );
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_空文字列ならnull() {
		$this->Data->set( 'text', '' );
		$this->assertNull( $this->Rule->rule( 'text', array( 'types' => 'jpg' ) ) );
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_拡張子が同じならnull() {
		$this->Data->set( 'filetype', 'example.jpg' );
		$this->assertNull( $this->Rule->rule( 'filetype', array( 'types' => 'jpg' ) ) );
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_拡張子が含まれていればnull() {
		$this->Data->set( 'filetype', 'example.jpg' );
		$this->assertNull( $this->Rule->rule( 'filetype', array( 'types' => 'jpg,png' ) ) );
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_拡張子が異なればnotnull() {
		$this->Data->set( 'filetype', 'example.gif' );
		$this->assertNotNull( $this->Rule->rule( 'filetype', array( 'types' => 'jpg' ) ) );
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_拡張子が含まれていなければnotnull() {
		$this->Data->set( 'filetype', 'example.gif' );
		$this->assertNotNull( $this->Rule->rule( 'filetype', array( 'types' => 'jpg,png' ) ) );
	}
}
