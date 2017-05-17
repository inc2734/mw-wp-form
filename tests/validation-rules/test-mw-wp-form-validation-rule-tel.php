<?php
class MW_WP_Form_Validation_Rule_Tel_Test extends WP_UnitTestCase {

	/**
	 * @var MW_WP_Form_Validation_Rule_Tel
	 */
	protected $Rule;

	/**
	 * setUp
	 */
	public function setUp() {
		parent::setUp();
		$form_key   = MWF_Config::NAME . '-1';
		$this->Data = MW_WP_Form_Data::connect( $form_key );
		$this->Rule = new MW_WP_Form_Validation_Rule_Tel();
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

	public function test_電話番号の形式ならnull() {
		$this->Data->set( 'text', '00-0000-0000' );
		$this->assertNull( $this->Rule->rule( 'text' ) );
		$this->Data->set( 'text', '000-000-0000' );
		$this->assertNull( $this->Rule->rule( 'text' ) );
		$this->Data->set( 'text', '000-0000-0000' );
		$this->assertNull( $this->Rule->rule( 'text' ) );
		$this->Data->set( 'text', '0000-00-0000' );
		$this->assertNull( $this->Rule->rule( 'text' ) );
		$this->Data->set( 'text', '0000-000-000' );
		$this->assertNull( $this->Rule->rule( 'text' ) );
		$this->Data->set( 'text', '00000-0-0000' );
		$this->assertNull( $this->Rule->rule( 'text' ) );
	}

	public function test_電話番号の形式_ハイフンなしでもnull() {
		$this->Data->set( 'text', '0000000000' );
		$this->assertNull( $this->Rule->rule( 'text' ) );
		$this->Data->set( 'text', '00000000000' );
		$this->assertNull( $this->Rule->rule( 'text' ) );
	}

	public function test_電話番号の形式以外ならnotnull() {
		$this->Data->set( 'text', 'aaa' );
		$this->assertNotNull( $this->Rule->rule( 'text' ) );
		$this->Data->set( 'text', '000000000' );
		$this->assertNotNull( $this->Rule->rule( 'text' ) );
		$this->Data->set( 'text', '000000000000' );
		$this->assertNotNull( $this->Rule->rule( 'text' ) );
	}
}
