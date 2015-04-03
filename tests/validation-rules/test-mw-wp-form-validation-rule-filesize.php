<?php
class MW_WP_Form_Validation_Rule_FileSize_Test extends WP_UnitTestCase {

	/**
	 * @var MW_WP_Form_Validation_Rule_FileSize
	 */
	protected $Rule;
	
	/**
	 * setUp
	 */
	public function setUp() {
		parent::setUp();
		$form_key   = MWF_Config::NAME . '-1';
		$this->Data = MW_WP_Form_Data::getInstance( $form_key );
		$this->Rule = new MW_WP_Form_Validation_Rule_FileSize();
		$this->Rule->set_Data( $this->Data );
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_バイト数が同じならnull() {
		$this->Data->set( MWF_Config::UPLOAD_FILES, array(
			'filesize' => array( 'size' => 10 ),
		) );
		$this->assertNull( $this->Rule->rule( 'filesize', array( 'bytes' => 10 ) ) );
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_バイト数が小さければnull() {
		$this->Data->set( MWF_Config::UPLOAD_FILES, array(
			'filesize' => array( 'size' => 10 ),
		) );
		$this->assertNull( $this->Rule->rule( 'filesize', array( 'bytes' => 11 ) ) );
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_バイト数が大きければnotnull() {
		$this->Data->set( MWF_Config::UPLOAD_FILES, array(
			'filesize' => array( 'size' => 11 ),
		) );
		$this->assertNotNull( $this->Rule->rule( 'filesize', array( 'bytes' => 10 ) ) );
	}
}
