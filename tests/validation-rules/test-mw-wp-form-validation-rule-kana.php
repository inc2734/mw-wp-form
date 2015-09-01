<?php
class MW_WP_Form_Validation_Rule_Kana_Test extends WP_UnitTestCase {

	/**
	 * @var MW_WP_Form_Validation_Rule_Alpha
	 */
	protected $Rule;

	/**
	 * setUp
	 */
	public function setUp() {
		parent::setUp();
		$form_key   = MWF_Config::NAME . '-1';
		$this->Data = MW_WP_Form_Data::getInstance( $form_key );
		$this->Rule = new MW_WP_Form_Validation_Rule_Kana();
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
	public function test_ひらがなのみならnull() {
		$this->Data->set( 'text', 'あいうえお' );
		$this->assertNull( $this->Rule->rule( 'text' ) );
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_カタカナのみならnull() {
		$this->Data->set( 'text', 'アイウエオ' );
		$this->assertNull( $this->Rule->rule( 'text' ) );
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_ひらがなとカタカナのみならnull() {
		$this->Data->set( 'text', 'あいうえおアイウエオ' );
		$this->assertNull( $this->Rule->rule( 'text' ) );
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_ひらがなとカタカナ以外が含まれていたらnotnull() {
		$this->Data->set( 'text', 'あいうえお0アイウエオ' );
		$this->assertNotNull( $this->Rule->rule( 'text' ) );
	}
}
