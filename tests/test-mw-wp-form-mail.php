<?php
class MW_WP_Form_Mail_Test extends WP_UnitTestCase {

	/**
	 * @var MW_WP_Form_Mail
	 */
	protected $Mail;

	/**
	 * setUp
	 */
	public function setUp() {
		parent::setUp();
		$this->Mail = new MW_WP_Form_Mail();
	}

	/**
	 * @group set_mail_from
	 */
	public function test_set_mail_from() {
		$this->Mail->from = 'from';
		$this->assertEquals(
			'from',
			$this->Mail->set_mail_from( 'example@example.com' )
		);
	}

	/**
	 * @group set_mail_from_name
	 */
	public function test_set_mail_from_name() {
		$this->Mail->sender = 'new_sender';
		$this->assertEquals(
			'new_sender',
			$this->Mail->set_mail_from_name( 'old_sender' )
		);
	}

	/**
	 * @group set_return_path
	 */
	public function test_set_return_path() {
		$phpmailer = new phpmailer();
		$this->Mail->from = 'from';
		$this->Mail->set_return_path( $phpmailer );
		$this->assertEquals(
			'from',
			$phpmailer->Sender
		);
	}
}
