<?php
class MW_WP_Form_Validation_Rule_Url_Test extends WP_UnitTestCase {

	/**
	 * @var MW_WP_Form_Validation_Rule_Url
	 */
	protected $Rule;

	/**
	 * setUp
	 */
	public function setUp() {
		parent::setUp();
		$form_key   = MWF_Config::NAME . '-1';
		$this->Data = MW_WP_Form_Data::connect( $form_key );
		$this->Rule = new MW_WP_Form_Validation_Rule_Url();
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

	public function test_URLの形式ならnull() {
		$this->Data->set( 'text', 'http://example.com' );
		$this->assertNull( $this->Rule->rule( 'text' ) );
		$this->Data->set( 'text', 'https://example.com' );
		$this->assertNull( $this->Rule->rule( 'text' ) );
		$this->Data->set( 'text', 'https://www.e' );
		$this->assertNull( $this->Rule->rule( 'text' ) );
	}

	public function test_URLの形式以外ならnotnull() {
		$this->Data->set( 'text', 'htt://example.com' );
		$this->assertNotNull( $this->Rule->rule( 'text' ) );
		$this->Data->set( 'text', 'http:/example.com' );
		$this->assertNotNull( $this->Rule->rule( 'text' ) );
		$this->Data->set( 'text', 'https://www.' );
		$this->assertNotNull( $this->Rule->rule( 'text' ) );
	}
}
