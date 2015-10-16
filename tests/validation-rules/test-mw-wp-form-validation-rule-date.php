<?php
class MW_WP_Form_Validation_Rule_Date_Test extends WP_UnitTestCase {

	/**
	 * @var MW_WP_Form_Validation_Rule_Date
	 */
	protected $Rule;
	
	/**
	 * setUp
	 */
	public function setUp() {
		parent::setUp();
		$form_key   = MWF_Config::NAME . '-1';
		$this->Data = MW_WP_Form_Data::getInstance( $form_key );
		$this->Rule = new MW_WP_Form_Validation_Rule_Date();
		$this->Rule->set_Data( $this->Data );
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_空文字列ならnull() {
		$this->Data->set( 'text', '' );
		$this->assertNull( $this->Rule->rule( 'text' ) );
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_日付ならnull() {
		$this->Data->set( 'date', '2015-01-01' );
		$this->assertNull( $this->Rule->rule( 'date' ) );
		$this->Data->set( 'date', '2015-1-1' );
		$this->assertNull( $this->Rule->rule( 'date' ) );
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_日本語日付ならnull() {
		$this->Data->set( 'date', '2015年01月01日' );
		$this->assertNull( $this->Rule->rule( 'date' ) );
		$this->Data->set( 'date', '2015年1月1日' );
		$this->assertNull( $this->Rule->rule( 'date' ) );
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_日本語日付ならタイムスタンプ() {
		$this->assertEquals(
			$this->Rule->convert_jpdate_to_timestamp( '2015年01月01日' ),
			strtotime( '2015-01-01' )
		);
		$this->assertEquals(
			$this->Rule->convert_jpdate_to_timestamp( '2015年1月1日' ),
			strtotime( '2015-01-01' )
		);
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_日付以外ならnotnull() {
		$this->Data->set( 'text', 'aaa' );
		$this->assertNotNull( $this->Rule->rule( 'text' ) );
	}
}
