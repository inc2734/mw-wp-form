<?php
class MW_WP_Form_Validation_Rule_Month_Test extends WP_UnitTestCase {

	/**
	 * @var MW_WP_Form_Validation_Rule_Month
	 */
	protected $Rule;

	/**
	 * setUp
	 */
	public function setUp() {
		parent::setUp();
		$form_key   = MWF_Config::NAME . '-1';
		$this->Data = MW_WP_Form_Data::connect( $form_key );
		$this->Rule = new MW_WP_Form_Validation_Rule_Month();
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

	public function test_日付ならnull() {
		$this->Data->set( 'date', '2015-01' );
		$this->assertNull( $this->Rule->rule( 'date' ) );
		$this->Data->set( 'date', '2015-1' );
		$this->assertNull( $this->Rule->rule( 'date' ) );
	}

	public function test_日本語日付ならnull() {
		$this->Data->set( 'date', '2015年01月' );
		$this->assertNull( $this->Rule->rule( 'date' ) );
		$this->Data->set( 'date', '2015年1月' );
		$this->assertNull( $this->Rule->rule( 'date' ) );
	}

	public function test_日本語日付ならタイムスタンプ() {
		$this->assertEquals(
			$this->Rule->convert_jpdate_to_timestamp( '2015年01月' ),
			strtotime( '2015-01' )
		);
		$this->assertEquals(
			$this->Rule->convert_jpdate_to_timestamp( '2015年1月' ),
			strtotime( '2015-01' )
		);
	}

	public function test_日付以外ならnotnull() {
		$this->Data->set( 'text', 'aaa' );
		$this->assertNotNull( $this->Rule->rule( 'text' ) );
	}
}
