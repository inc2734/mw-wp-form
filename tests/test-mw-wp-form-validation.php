<?php
class MW_WP_Form_Validation_Test extends WP_UnitTestCase {

	protected $Data;

	public function setUp() {
		parent::setUp();
		$form_key = MWF_Config::NAME . '-1';
		$this->Data = MW_WP_Form_Data::getInstance( $form_key );
		$this->Data->set( 'numeric', '111' );
		$this->Data->set( 'alpha', 'aaa' );
		$this->Data->set( 'alphanumeric', 'aaa111' );
		$this->Data->set( 'date', '2015-1-6' );
		$this->Data->set( 'jp-numeric', '１１１' );
		$this->Data->set( 'jp-string', 'あああ' );
		$this->Data->set( 'jp-katakana', 'アアア' );
		$this->Data->set( 'jp-hiragana-katakana', 'アアあ' );
		$this->Data->set( 'jp-date', '2015年1月6日' );
		$this->Data->set( 'mail', 'inc@2inc.org' );
		$this->Data->set( 'jp-mail', 'inc@エグザンプル.com' );
		$this->Data->set( 'break-mail', 'inc@' );
		$this->Data->set( 'break-mail2', '@2inc.org' );
		$this->Data->set( 'empty', '' );
		$this->Data->set( 'zero', 0 );
		$this->Data->set( 'tel1', '00-0000-0000' );
		$this->Data->set( 'tel2', '000-000-0000' );
		$this->Data->set( 'tel3', '000-0000-0000' );
		$this->Data->set( 'tel4', '0000-00-0000' );
		$this->Data->set( 'tel5', '0000-000-000' );
		$this->Data->set( 'tel6', '00000-0-0000' );
		$this->Data->set( 'zip', '000-0000' );
		$this->Data->set( 'http', 'http://example.com' );
		$this->Data->set( 'https', 'https://example.com' );
		$this->Data->set( 'break-http', 'http:example.com' );
		$this->Data->set( 'break-http2', 'http://example' );
		$this->Data->set( 'break-http3', 'http://' );
		$this->Data->set( MWF_Config::UPLOAD_FILES, array(
			'file-size-10'  => array( 'size' => 10 ),
			'file-size-100' => array( 'size' => 100 ),
		) );
		$this->Data->set( 'jpg', 'hoge.jpg' );
		$this->Data->set( 'png', 'hoge.png' );
	}

	/**
	 * 複数
	 */
	public function test_multi_validations() {
		$post_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$Rule1 = new MW_WP_Form_Validation_Rule_Alpha();
		$Rule2 = new MW_WP_Form_Validation_Rule_Date();
		$Rule1->set_Data( $this->Data );
		$Rule2->set_Data( $this->Data );
		$Error = new MW_WP_Form_Error();
		$Validation = new MW_WP_Form_Validation( $Error );
		$Validation->set_validation_rules( array(
			$Rule1, $Rule2,
		) );
		$Validation->set_rule( 'tel1', 'alpha' );
		$Validation->set_rule( 'tel1', 'date' );
		$Validation->check();
		$errors = $Error->get_errors();
		$this->assertTrue( isset( $errors['tel1'] ) );
		if ( isset( $errors['tel1'] ) ) {
			$this->assertEquals( count( $errors['tel1'] ), 2 );
		}
	}

	/**
	 * 半角英字のテスト
	 */
	public function test_alpha() {
		$Rule = new MW_WP_Form_Validation_Rule_Alpha();
		$Rule->set_Data( $this->Data );
		$message = $Rule->rule( 'numeric' );
		$this->assertNotNull( $message );
		$message = $Rule->rule( 'alpha' );
		$this->assertNull( $message );
		$message = $Rule->rule( 'alphanumeric' );
		$this->assertNotNull( $message );
		$message = $Rule->rule( 'jp-numeric' );
		$this->assertNotNull( $message );
		$message = $Rule->rule( 'jp-string' );
		$this->assertNotNull( $message );
	}

	/**
	 * 半角数字のテスト
	 */
	public function test_numeric() {
		$Rule = new MW_WP_Form_Validation_Rule_Numeric();
		$Rule->set_Data( $this->Data );
		$message = $Rule->rule( 'numeric' );
		$this->assertNull( $message );
		$message = $Rule->rule( 'alpha' );
		$this->assertNotNull( $message );
		$message = $Rule->rule( 'alphanumeric' );
		$this->assertNotNull( $message );
		$message = $Rule->rule( 'jp-numeric' );
		$this->assertNotNull( $message );
		$message = $Rule->rule( 'jp-string' );
		$this->assertNotNull( $message );
	}

	/**
	 * 半角英数字のテスト
	 */
	public function test_alphanumeric() {
		$Rule = new MW_WP_Form_Validation_Rule_Alphanumeric();
		$Rule->set_Data( $this->Data );
		$message = $Rule->rule( 'numeric' );
		$this->assertNull( $message );
		$message = $Rule->rule( 'alpha' );
		$this->assertNull( $message );
		$message = $Rule->rule( 'alphanumeric' );
		$this->assertNull( $message );
		$message = $Rule->rule( 'jp-numeric' );
		$this->assertNotNull( $message );
		$message = $Rule->rule( 'jp-string' );
		$this->assertNotNull( $message );
	}

	/**
	 * 範囲のテスト
	 */
	public function test_between() {
		$Rule = new MW_WP_Form_Validation_Rule_Between();
		$Rule->set_Data( $this->Data );
		$message = $Rule->rule( 'numeric', array( 'min' => 1, 'max' => 2 ) );
		$this->assertNotNull( $message );
		$message = $Rule->rule( 'numeric', array( 'min' => 1, 'max' => 3 ) );
		$this->assertNull( $message );
		$message = $Rule->rule( 'numeric', array( 'min' => 1, 'max' => 4 ) );
		$this->assertNull( $message );
		$message = $Rule->rule( 'jp-numeric', array( 'min' => 1, 'max' => 2 ) );
		$this->assertNotNull( $message );
		$message = $Rule->rule( 'jp-numeric', array( 'min' => 1, 'max' => 3 ) );
		$this->assertNull( $message );
		$message = $Rule->rule( 'jp-numeric', array( 'min' => 1, 'max' => 4 ) );
		$this->assertNull( $message );
	}

	/**
	 * 日付のテスト
	 */
	public function test_date() {
		$Rule = new MW_WP_Form_Validation_Rule_Date();
		$Rule->set_Data( $this->Data );
		$message = $Rule->rule( 'numeric' );
		$this->assertNotNull( $message );
		$message = $Rule->rule( 'alpha' );
		$this->assertNotNull( $message );
		$message = $Rule->rule( 'alphanumeric' );
		$this->assertNotNull( $message );
		$message = $Rule->rule( 'jp-numeric' );
		$this->assertNotNull( $message );
		$message = $Rule->rule( 'jp-string' );
		$this->assertNotNull( $message );
		$message = $Rule->rule( 'date' );
		$this->assertNull( $message );
		$message = $Rule->rule( 'jp-date' );
		$this->assertNull( $message );
	}

	/**
	 * 一致のテスト
	 */
	public function test_eq() {
		$Rule = new MW_WP_Form_Validation_Rule_Eq();
		$Rule->set_Data( $this->Data );
		$message = $Rule->rule( 'numeric', array( 'target' => 'numeric' ) );
		$this->assertNull( $message );
		$message = $Rule->rule( 'numeric', array( 'target' => 'alpha' ) );
		$this->assertNotNull( $message );
	}

	/**
	 * ひらがなのテスト
	 */
	public function test_hiragana() {
		$Rule = new MW_WP_Form_Validation_Rule_Hiragana();
		$Rule->set_Data( $this->Data );
		$message = $Rule->rule( 'numeric' );
		$this->assertNotNull( $message );
		$message = $Rule->rule( 'alpha' );
		$this->assertNotNull( $message );
		$message = $Rule->rule( 'alphanumeric' );
		$this->assertNotNull( $message );
		$message = $Rule->rule( 'jp-numeric' );
		$this->assertNotNull( $message );
		$message = $Rule->rule( 'jp-string' );
		$this->assertNull( $message );
		$message = $Rule->rule( 'jp-katakana' );
		$this->assertNotNull( $message );
		$message = $Rule->rule( 'jp-hiragana-katakana' );
		$this->assertNotNull( $message );
	}

	/**
	 * カタカナのテスト
	 */
	public function test_katakana() {
		$Rule = new MW_WP_Form_Validation_Rule_Katakana();
		$Rule->set_Data( $this->Data );
		$message = $Rule->rule( 'numeric' );
		$this->assertNotNull( $message );
		$message = $Rule->rule( 'alpha' );
		$this->assertNotNull( $message );
		$message = $Rule->rule( 'alphanumeric' );
		$this->assertNotNull( $message );
		$message = $Rule->rule( 'jp-numeric' );
		$this->assertNotNull( $message );
		$message = $Rule->rule( 'jp-string' );
		$this->assertNotNull( $message );
		$message = $Rule->rule( 'jp-katakana' );
		$this->assertNull( $message );
		$message = $Rule->rule( 'jp-hiragana-katakana' );
		$this->assertNotNull( $message );
	}

	/**
	 * メールアドレスのテスト
	 */
	public function test_mail() {
		$Rule = new MW_WP_Form_Validation_Rule_Mail();
		$Rule->set_Data( $this->Data );
		$message = $Rule->rule( 'mail' );
		$this->assertNull( $message );
		$message = $Rule->rule( 'jp-mail' );
		$this->assertNull( $message );
		$message = $Rule->rule( 'break-mail' );
		$this->assertNotNull( $message );
		$message = $Rule->rule( 'break-mail2' );
		$this->assertNotNull( $message );
		$message = $Rule->rule( 'alpha' );
		$this->assertNotNull( $message );
	}

	/**
	 * 最小文字数のテスト
	 */
	public function test_minlength() {
		$Rule = new MW_WP_Form_Validation_Rule_Minlength();
		$Rule->set_Data( $this->Data );
		$message = $Rule->rule( 'numeric', array( 'min' => 1 ) );
		$this->assertNull( $message );
		$message = $Rule->rule( 'jp-numeric', array( 'min' => 1 ) );
		$this->assertNull( $message );
		$message = $Rule->rule( 'numeric', array( 'min' => 3 ) );
		$this->assertNull( $message );
		$message = $Rule->rule( 'jp-numeric', array( 'min' => 3 ) );
		$this->assertNull( $message );
		$message = $Rule->rule( 'numeric', array( 'min' => 4 ) );
		$this->assertNotNull( $message );
		$message = $Rule->rule( 'jp-numeric', array( 'min' => 4 ) );
		$this->assertNotNull( $message );
	}

	/**
	 * 必須のテスト
	 */
	public function test_noempty() {
		$Rule = new MW_WP_Form_Validation_Rule_Noempty();
		$Rule->set_Data( $this->Data );
		$message = $Rule->rule( 'empty' );
		$this->assertNotNull( $message );
		$message = $Rule->rule( 'alpha' );
		$this->assertNull( $message );
		$message = $Rule->rule( 'zero' );
		$this->assertNull( $message );
	}

	/**
	 * falseのテスト
	 */
	public function test_nofalse() {
		$Rule = new MW_WP_Form_Validation_Rule_Nofalse();
		$Rule->set_Data( $this->Data );
		$message = $Rule->rule( 'empty' );
		$this->assertNotNull( $message );
		$message = $Rule->rule( 'alpha' );
		$this->assertNull( $message );
		$message = $Rule->rule( 'zero' );
		$this->assertNotNull( $message );
	}

	/**
	 * required（存在）のテスト
	 */
	public function test_required() {
		$Rule = new MW_WP_Form_Validation_Rule_Required();
		$Rule->set_Data( $this->Data );
		$message = $Rule->rule( 'empty' );
		$this->assertNull( $message );
		$message = $Rule->rule( 'hogehoge-fugafuga' );
		$this->assertNotNull( $message );
		$message = $Rule->rule( 'zero' );
		$this->assertNull( $message );
	}

	/**
	 * 電話番号のテスト
	 */
	public function test_tel() {
		$Rule = new MW_WP_Form_Validation_Rule_Tel();
		$Rule->set_Data( $this->Data );
		$message = $Rule->rule( 'empty' );
		$this->assertNull( $message );
		$message = $Rule->rule( 'tel1' );
		$this->assertNull( $message );
		$message = $Rule->rule( 'tel2' );
		$this->assertNull( $message );
		$message = $Rule->rule( 'tel3' );
		$this->assertNull( $message );
		$message = $Rule->rule( 'tel4' );
		$this->assertNull( $message );
		$message = $Rule->rule( 'tel5' );
		$this->assertNull( $message );
		$message = $Rule->rule( 'tel6' );
		$this->assertNull( $message );
		$message = $Rule->rule( 'alpha' );
		$this->assertNotNull( $message );
		$message = $Rule->rule( 'zero' );
		$this->assertNotNull( $message );
	}

	/**
	 * 郵便番号のテスト
	 */
	public function test_zip() {
		$Rule = new MW_WP_Form_Validation_Rule_Zip();
		$Rule->set_Data( $this->Data );
		$message = $Rule->rule( 'zip' );
		$this->assertNull( $message );
		$message = $Rule->rule( 'tel1' );
		$this->assertNotNull( $message );
		$message = $Rule->rule( 'zero' );
		$this->assertNotNull( $message );
	}

	/**
	 * URLのテスト
	 */
	public function test_url() {
		$Rule = new MW_WP_Form_Validation_Rule_Url();
		$Rule->set_Data( $this->Data );
		$message = $Rule->rule( 'http' );
		$this->assertNull( $message );
		$message = $Rule->rule( 'https' );
		$this->assertNull( $message );
		$message = $Rule->rule( 'break-http' );
		$this->assertNotNull( $message );
		$message = $Rule->rule( 'break-http2' );
		$this->assertNotNull( $message );
		$message = $Rule->rule( 'break-http3' );
		$this->assertNotNull( $message );
		$message = $Rule->rule( 'zero' );
		$this->assertNotNull( $message );
	}

	/**
	 * inのテスト
	 */
	public function test_in() {
		$Rule = new MW_WP_Form_Validation_Rule_In();
		$Rule->set_Data( $this->Data );
		$message = $Rule->rule( 'alpha', array( 'options' => array( 'aaa' ) ) );
		$this->assertNull( $message );
		$message = $Rule->rule( 'alpha', array( 'options' => array( 'aaa', 'bbb' ) ) );
		$this->assertNull( $message );
		$message = $Rule->rule( 'alpha', array( 'options' => array( 'aa' ) ) );
		$this->assertNotNull( $message );
		$message = $Rule->rule( 'alpha', array( 'options' => array( 'aaaa' ) ) );
		$this->assertNotNull( $message );
		$message = $Rule->rule( 'zero', array( 'options' => array( '0' ) ) );
		$this->assertNull( $message );
		$message = $Rule->rule( 'zero', array( 'options' => array( 'aaa' ) ) );
		$this->assertNotNull( $message );
	}

	/**
	 * ファイルサイズのテスト
	 */
	public function test_filesize() {
		$Rule = new MW_WP_Form_Validation_Rule_Filesize();
		$Rule->set_Data( $this->Data );
		$message = $Rule->rule( 'file-size-10', array( 'bytes' => 10 ) );
		$this->assertNull( $message );
		$message = $Rule->rule( 'file-size-10', array( 'bytes' => 9 ) );
		$this->assertNotNull( $message );
		$message = $Rule->rule( 'file-size-100', array( 'bytes' => 10 ) );
		$this->assertNotNull( $message );
	}

	/**
	 * ファイルタイプのテスト
	 */
	public function test_filetype() {
		$Rule = new MW_WP_Form_Validation_Rule_Filetype();
		$Rule->set_Data( $this->Data );
		$message = $Rule->rule( 'jpg', array( 'types' => 'jpg' ) );
		$this->assertNull( $message );
		$message = $Rule->rule( 'jpg', array( 'types' => 'png' ) );
		$this->assertNotNull( $message );
		$message = $Rule->rule( 'jpg', array( 'types' => 'jpg,png' ) );
		$this->assertNull( $message );
		$message = $Rule->rule( 'jpg', array( 'types' => 'jpg, png' ) );
		$this->assertNull( $message );
	}
}