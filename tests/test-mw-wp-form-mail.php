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
	 * tearDown
	 */
	public function tearDown() {
		parent::tearDown();
		$this->Mail = new MW_WP_Form_Mail();
	}

	/**
	 * @group set_mail_from
	 */
	public function test_set_mail_from() {
		$this->Mail->from = 'new@example.com';
		$this->assertEquals(
			'new@example.com',
			$this->Mail->set_mail_from( 'example@example.com' )
		);

		$this->Mail->from = 'invalid.@example.com';
		$this->assertEquals(
			'example@example.com',
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
		$this->Mail->from        = 'from';
		$this->Mail->to          = 'to';
		$this->Mail->return_path = 'return_path';
		$this->Mail->set_return_path( $phpmailer );
		$this->assertEquals(
			'return_path',
			$phpmailer->Sender
		);
	}

	/**
	 * @group set_admin_mail_raw_params
	 */
	public function test_set_admin_mail_raw_params_未設定() {
		$post_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$Setting = new MW_WP_Form_Setting( $post_id );
		$this->Mail->set_admin_mail_raw_params( $Setting );

		$this->assertEquals( '', $this->Mail->subject );
		$this->assertEquals( '', $this->Mail->body );
		$this->assertEquals( get_bloginfo( 'admin_email' ), $this->Mail->to );
		$this->assertEquals( '', $this->Mail->cc );
		$this->assertEquals( '', $this->Mail->bcc );
		$this->assertEquals( get_bloginfo( 'admin_email' ), $this->Mail->return_path );
		$this->assertEquals( get_bloginfo( 'admin_email' ), $this->Mail->from );
		$this->assertEquals( get_bloginfo( 'name' ), $this->Mail->sender );
	}

	/**
	 * @group set_admin_mail_raw_params
	 */
	public function test_set_admin_mail_raw_params_設定あり() {
		$post_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$Setting = new MW_WP_Form_Setting( $post_id );
		$Setting->set( 'admin_mail_subject', 'subject' );
		$Setting->set( 'mail_content'      , 'body' );
		$Setting->set( 'mail_to'           , 'to@example.com' );
		$Setting->set( 'mail_cc'           , 'cc@example.com' );
		$Setting->set( 'mail_bcc'          , 'bcc@example.com' );
		$Setting->set( 'mail_return_path'  , 'return_path@example.com' );
		$Setting->set( 'admin_mail_from'   , 'from@example.com' );
		$Setting->set( 'admin_mail_sender' , 'sender' );
		$this->Mail->set_admin_mail_raw_params( $Setting );

		$this->assertEquals( 'subject', $this->Mail->subject );
		$this->assertEquals( 'body', $this->Mail->body );
		$this->assertEquals( 'to@example.com', $this->Mail->to );
		$this->assertEquals( 'cc@example.com', $this->Mail->cc );
		$this->assertEquals( 'bcc@example.com', $this->Mail->bcc );
		$this->assertEquals( 'return_path@example.com', $this->Mail->return_path );
		$this->assertEquals( 'from@example.com', $this->Mail->from );
		$this->assertEquals( 'sender', $this->Mail->sender );
	}

	/**
	 * @group set_admin_mail_raw_params
	 */
	public function test_set_admin_mail_raw_params_未設定_自動返信設定あり() {
		$post_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$Setting = new MW_WP_Form_Setting( $post_id );
		$Setting->set( 'automatic_reply_email', 'メールアドレス' );
		$Setting->set( 'mail_from'            , 'from@example.com' );
		$Setting->set( 'mail_sender'          , 'sender' );
		$Setting->set( 'mail_subject'         , 'subject' );
		$Setting->set( 'mail_content'         , 'body' );
		$Data = MW_WP_Form_Data::getInstance( MWF_Functions::get_form_key_from_form_id( $post_id ) );
		$Data->set( 'メールアドレス', 'to@example.com' );
		$this->Mail->set_admin_mail_raw_params( $Setting );

		$this->assertEquals( 'subject', $this->Mail->subject );
		$this->assertEquals( 'body', $this->Mail->body );
		$this->assertEquals( get_bloginfo( 'admin_email' ), $this->Mail->to );
		$this->assertEquals( '', $this->Mail->cc );
		$this->assertEquals( '', $this->Mail->bcc );
		$this->assertEquals( get_bloginfo( 'admin_email' ), $this->Mail->return_path );
		$this->assertEquals( 'from@example.com', $this->Mail->from );
		$this->assertEquals( 'sender', $this->Mail->sender );
	}

	/**
	 * @group set_reply_mail_raw_params
	 */
	public function test_set_reply_mail_raw_params_未設定() {
		$post_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$Setting = new MW_WP_Form_Setting( $post_id );
		$Data = MW_WP_Form_Data::getInstance( MWF_Functions::get_form_key_from_form_id( $post_id ) );
		$this->Mail->set_reply_mail_raw_params( $Setting );

		$this->assertEquals( '', $this->Mail->to );
		$this->assertEquals( get_bloginfo( 'admin_email' ), $this->Mail->return_path );
		$this->assertEquals( get_bloginfo( 'admin_email' ), $this->Mail->from );
		$this->assertEquals( get_bloginfo( 'name' ), $this->Mail->sender );
		$this->assertEquals( '', $this->Mail->subject );
		$this->assertEquals( '', $this->Mail->body );
	}

	/**
	 * @group set_reply_mail_raw_params
	 */
	public function test_set_reply_mail_raw_params_設定あり() {
		$post_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$Setting = new MW_WP_Form_Setting( $post_id );
		$Setting->set( 'automatic_reply_email', 'メールアドレス' );
		$Setting->set( 'mail_from'            , 'from@example.com' );
		$Setting->set( 'mail_sender'          , 'sender' );
		$Setting->set( 'mail_subject'         , 'subject' );
		$Setting->set( 'mail_content'         , 'body' );
		$Data = MW_WP_Form_Data::getInstance( MWF_Functions::get_form_key_from_form_id( $post_id ) );
		$Data->set( 'メールアドレス', 'to@example.com' );
		$this->Mail->set_reply_mail_raw_params( $Setting );

		$this->assertEquals( 'to@example.com', $this->Mail->to );
		$this->assertEquals( get_bloginfo( 'admin_email' ), $this->Mail->return_path );
		$this->assertEquals( 'from@example.com', $this->Mail->from );
		$this->assertEquals( 'sender', $this->Mail->sender );
		$this->assertEquals( 'subject', $this->Mail->subject );
		$this->assertEquals( 'body', $this->Mail->body );
	}

	/**
	 * @group set_reply_mail_raw_params
	 */
	public function test_set_reply_mail_raw_params_ToとCCとBCCとattachmentsは直接設定されてもに空値() {
		$post_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$Setting = new MW_WP_Form_Setting( $post_id );
		$Data = MW_WP_Form_Data::getInstance( MWF_Functions::get_form_key_from_form_id( $post_id ) );
		$this->Mail->cc  = 'cc@example.com';
		$this->Mail->bcc = 'bcc@example.com';
		$this->Mail->set_reply_mail_raw_params( $Setting );

		$this->assertSame(
			array(),
			$this->Mail->attachments
		);

		$this->assertEquals(
			'',
			$this->Mail->cc
		);

		$this->assertEquals(
			'',
			$this->Mail->bcc
		);
	}

	/**
	 * @group set_admin_mail_reaquire_params
	 */
	public function test_set_admin_mail_reaquire_params_ToとFromとSenderが空なら上書き() {
		$this->Mail->set_admin_mail_reaquire_params();

		$this->assertEquals(
			get_bloginfo( 'admin_email' ),
			$this->Mail->to
		);

		$this->assertEquals(
			get_bloginfo( 'admin_email' ),
			$this->Mail->from
		);

		$this->assertEquals(
			get_bloginfo( 'name' ),
			$this->Mail->sender
		);
	}

	/**
	 * @group set_admin_mail_reaquire_params
	 */
	public function test_set_admin_mail_reaquire_params_ToとFromとSenderが空でなければそのまま() {
		$this->Mail->to     = 'to@example.com';
		$this->Mail->from   = 'from@example.com';
		$this->Mail->sender = 'Sender';
		$this->Mail->set_admin_mail_reaquire_params();

		$this->assertEquals(
			'to@example.com',
			$this->Mail->to
		);

		$this->assertEquals(
			'from@example.com',
			$this->Mail->from
		);

		$this->assertEquals(
			'Sender',
			$this->Mail->sender
		);
	}

	/**
	 * @group set_reply_mail_reaquire_params
	 */
	public function test_set_reply_mail_reaquire_params_FromとSenderが空なら上書き() {
		$this->Mail->set_reply_mail_reaquire_params();

		$this->assertEquals(
			get_bloginfo( 'admin_email' ),
			$this->Mail->from
		);

		$this->assertEquals(
			get_bloginfo( 'name' ),
			$this->Mail->sender
		);
	}

	/**
	 * @group set_reply_mail_reaquire_params
	 */
	public function test_set_reply_mail_reaquire_params_FromとSenderが空でなければそのまま() {
		$this->Mail->from   = 'from@example.com';
		$this->Mail->sender = 'Sender';
		$this->Mail->set_reply_mail_reaquire_params();

		$this->assertEquals(
			'from@example.com',
			$this->Mail->from
		);

		$this->assertEquals(
			'Sender',
			$this->Mail->sender
		);
	}

	/**
	 * @group parse
	 */
	public function test_parse_ToとCCとBCCは上書きされない() {
		$post_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$Setting = new MW_WP_Form_Setting( $post_id );
		$Data = MW_WP_Form_Data::getInstance( MWF_Functions::get_form_key_from_form_id( $post_id ) );
		$Data->set( 'to'    , 'to@example.com' );
		$Data->set( 'cc'    , 'cc@example.com' );
		$Data->set( 'bcc'   , 'bcc@example.com' );
		$Data->set( 'from'  , 'from@example.com' );
		$Data->set( 'sender', 'Sender' );
		$Data->set( 'body'  , 'body' );
		$this->Mail->to     = '{to}';
		$this->Mail->cc     = '{cc}';
		$this->Mail->bcc    = '{bcc}';
		$this->Mail->from   = '{from}';
		$this->Mail->sender = '{sender}';
		$this->Mail->body   = '{body}';
		$this->Mail->parse( $Setting, false );

		$this->assertEquals( '', $this->Mail->to );
		$this->assertEquals( '', $this->Mail->cc );
		$this->assertEquals( '', $this->Mail->bcc );
		$this->assertEquals( 'from@example.com', $this->Mail->from );
		$this->assertEquals( 'Sender', $this->Mail->sender );
		$this->assertEquals( 'body', $this->Mail->body );
	}

	/**
	 * @group parse
	 */
	public function test_parse_データベースに保存() {
		$post_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$Setting = new MW_WP_Form_Setting( $post_id );
		$Data = MW_WP_Form_Data::getInstance( MWF_Functions::get_form_key_from_form_id( $post_id ) );
		$Data->set( 'example', 'example' );
		$this->Mail->body = '{example}';
		$this->Mail->parse( $Setting, true );

		$posts = get_posts( array(
			'post_type' => MWF_Functions::get_contact_data_post_type_from_form_id( $post_id ),
		) );
		foreach ( $posts as $post ) {
			$this->assertEquals(
				'example',
				get_post_meta( $post->ID, 'example', true )
			);
			break;
		}
	}

	/**
	 * @group get_saved_mail_id
	 */
	public function test_get_saved_mail_id__保存されたとき() {
		$post_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$Setting = new MW_WP_Form_Setting( $post_id );
		$Data = MW_WP_Form_Data::getInstance( MWF_Functions::get_form_key_from_form_id( $post_id ) );
		$Data->set( 'example', 'example' );
		$this->Mail->body = '{example}';
		$this->Mail->parse( $Setting, true );
		$this->assertNotEmpty( $this->Mail->get_saved_mail_id() );
	}

	/**
	 * @group get_saved_mail_id
	 */
	public function test_get_saved_mail_id__保存されなかったとき() {
		$post_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$Setting = new MW_WP_Form_Setting( $post_id );
		$Data = MW_WP_Form_Data::getInstance( MWF_Functions::get_form_key_from_form_id( $post_id ) );
		$Data->set( 'example', 'example' );
		$this->Mail->body = '{example}';
		$this->Mail->parse( $Setting, false );
		$this->assertNull( $this->Mail->get_saved_mail_id() );
	}
}
