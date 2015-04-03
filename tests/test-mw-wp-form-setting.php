<?php
class MW_WP_Form_Setting_Test extends WP_UnitTestCase {

	/**
	 * @var MW_WP_Form_Setting
	 */
	protected $Setting;

	/**
	 * setUp
	 */
	public function setUp() {
		parent::setUp();
		$post_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		update_post_meta( $post_id, MWF_Config::NAME, array(
			'mail_subject' => 'mail_subject',
			'mail_from'    => 'mail_from',
			'mail_sender'  => 'mail_sender',
			'mail_content' => 'mail_content',
			'automatic_reply_email' => 'メールアドレス',
		) );
		$this->Setting = new MW_WP_Form_Setting( $post_id );
	}

	/**
	 * @group __construct
	 */
	public function test_初期化が成功しているか() {
		$this->assertEquals( 'mail_subject', $this->Setting->get( 'mail_subject' ) );
	}

	/**
	 * @group save
	 */
	public function test_save() {
		$this->Setting->set( 'mail_subject', 'new_mail_subject' );
		$this->Setting->save();
		$this->assertEquals( 'new_mail_subject', $this->Setting->get( 'mail_subject' ) );
	}

	/**
	 * @group get_posts
	 */
	public function test_get_posts() {
		$this->assertEquals( 1, count( $this->Setting->get_posts() ) );
	}

	/**
	 * @group get_tracking_number
	 */
	public function test_get_tracking_number_未登録の場合は1() {
		$this->assertEquals( 1, $this->Setting->get_tracking_number() );
	}

	/**
	 * @group get_tracking_number
	 */
	public function test_get_tracking_number_登録されている場合はそれを返す() {
		update_post_meta( $this->Setting->get( 'post_id' ), MWF_Config::TRACKINGNUMBER, 2 );
		$this->assertEquals( 2, $this->Setting->get_tracking_number() );
	}

	/**
	 * @group update_tracking_number
	 */
	public function test_update_tracking_number() {
		$this->Setting->update_tracking_number();
		$this->assertEquals( 2, $this->Setting->get_tracking_number() );
	}
}
