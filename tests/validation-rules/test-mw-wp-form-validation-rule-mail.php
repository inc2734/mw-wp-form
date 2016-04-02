<?php
class MW_WP_Form_Validation_Rule_Mail_Test extends WP_UnitTestCase {

	/**
	 * @var MW_WP_Form_Validation_Rule_Mail
	 */
	protected $Rule;

	/**
	 * setUp
	 */
	public function setUp() {
		parent::setUp();
		$form_key   = MWF_Config::NAME . '-1';
		$this->Data = MW_WP_Form_Data::getInstance( $form_key );
		$this->Rule = new MW_WP_Form_Validation_Rule_Mail();
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

	public function test_メールアドレスの形式ならnull() {
		$this->Data->set( 'text', 'info@example.com' );
		$this->assertNull( $this->Rule->rule( 'text' ) );
	}

	public function test_atより前がなければnotnull() {
		$this->Data->set( 'text', '@example.com' );
		$this->assertNotNull( $this->Rule->rule( 'text' ) );
	}

	public function test_atより後ろがなければnotnull() {
		$this->Data->set( 'text', 'into@' );
		$this->assertNotNull( $this->Rule->rule( 'text' ) );
	}

	public function test_メールアドレスの形式でなければnotnull() {
		$this->Data->set( 'text', 'aaa' );
		$this->assertNotNull( $this->Rule->rule( 'text' ) );
	}
}
