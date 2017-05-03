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
	}

	/**
	 * tearDown
	 */
	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * @group set_mail_from
	 */
	public function test_set_mail_from() {
		$Mail = new MW_WP_Form_Mail();
		$Mail->from = 'new@example.com';
		$this->assertEquals(
			'new@example.com',
			$Mail->set_mail_from( 'example@example.com' )
		);

		$Mail->from = 'invalid.@example.com';
		$this->assertEquals(
			'example@example.com',
			$Mail->set_mail_from( 'example@example.com' )
		);
	}

	/**
	 * @group set_mail_from_name
	 */
	public function test_set_mail_from_name() {
		$Mail = new MW_WP_Form_Mail();
		$Mail->sender = 'new_sender';
		$this->assertEquals(
			'new_sender',
			$Mail->set_mail_from_name( 'old_sender' )
		);
	}

	/**
	 * @group set_return_path
	 */
	public function test_set_return_path() {
		$Mail = new MW_WP_Form_Mail();
		$phpmailer = new phpmailer();
		$Mail->from        = 'from';
		$Mail->to          = 'to';
		$Mail->return_path = 'return_path';
		$Mail->set_return_path( $phpmailer );
		$this->assertEquals(
			'return_path',
			$phpmailer->Sender
		);
	}

	/**
	 * @group set_admin_mail_raw_params
	 */
	public function test_set_admin_mail_raw_params_未設定() {
		$Mail = new MW_WP_Form_Mail();
		$post_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$Setting = new MW_WP_Form_Setting( $post_id );
		$Mail->set_admin_mail_raw_params( $Setting );

		$this->assertEquals( '', $Mail->subject );
		$this->assertEquals( '', $Mail->body );
		$this->assertEquals( get_bloginfo( 'admin_email' ), $Mail->to );
		$this->assertEquals( '', $Mail->cc );
		$this->assertEquals( '', $Mail->bcc );
		$this->assertEquals( get_bloginfo( 'admin_email' ), $Mail->return_path );
		$this->assertEquals( get_bloginfo( 'admin_email' ), $Mail->from );
		$this->assertEquals( get_bloginfo( 'name' ), $Mail->sender );
	}

	/**
	 * @group set_admin_mail_raw_params
	 */
	public function test_set_admin_mail_raw_params_設定あり() {
		$Mail = new MW_WP_Form_Mail();
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
		$Mail->set_admin_mail_raw_params( $Setting );

		$this->assertEquals( 'subject', $Mail->subject );
		$this->assertEquals( 'body', $Mail->body );
		$this->assertEquals( 'to@example.com', $Mail->to );
		$this->assertEquals( 'cc@example.com', $Mail->cc );
		$this->assertEquals( 'bcc@example.com', $Mail->bcc );
		$this->assertEquals( 'return_path@example.com', $Mail->return_path );
		$this->assertEquals( 'from@example.com', $Mail->from );
		$this->assertEquals( 'sender', $Mail->sender );
	}

	/**
	 * @group set_admin_mail_raw_params
	 */
	public function test_set_admin_mail_raw_params_未設定_自動返信設定あり() {
		$Mail = new MW_WP_Form_Mail();
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
		$Mail->set_admin_mail_raw_params( $Setting );

		$this->assertEquals( 'subject', $Mail->subject );
		$this->assertEquals( 'body', $Mail->body );
		$this->assertEquals( get_bloginfo( 'admin_email' ), $Mail->to );
		$this->assertEquals( '', $Mail->cc );
		$this->assertEquals( '', $Mail->bcc );
		$this->assertEquals( get_bloginfo( 'admin_email' ), $Mail->return_path );
		$this->assertEquals( 'from@example.com', $Mail->from );
		$this->assertEquals( 'sender', $Mail->sender );
	}

	/**
	 * @group set_reply_mail_raw_params
	 */
	public function test_set_reply_mail_raw_params_未設定() {
		$Mail = new MW_WP_Form_Mail();
		$post_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$Setting = new MW_WP_Form_Setting( $post_id );
		$Data = MW_WP_Form_Data::getInstance( MWF_Functions::get_form_key_from_form_id( $post_id ) );
		$Mail->set_reply_mail_raw_params( $Setting );

		$this->assertEquals( '', $Mail->to );
		$this->assertEquals( get_bloginfo( 'admin_email' ), $Mail->return_path );
		$this->assertEquals( get_bloginfo( 'admin_email' ), $Mail->from );
		$this->assertEquals( get_bloginfo( 'name' ), $Mail->sender );
		$this->assertEquals( '', $Mail->subject );
		$this->assertEquals( '', $Mail->body );
	}

	/**
	 * @group set_reply_mail_raw_params
	 */
	public function test_set_reply_mail_raw_params_設定あり() {
		$Mail = new MW_WP_Form_Mail();
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
		$Mail->set_reply_mail_raw_params( $Setting );

		$this->assertEquals( 'to@example.com', $Mail->to );
		$this->assertEquals( get_bloginfo( 'admin_email' ), $Mail->return_path );
		$this->assertEquals( 'from@example.com', $Mail->from );
		$this->assertEquals( 'sender', $Mail->sender );
		$this->assertEquals( 'subject', $Mail->subject );
		$this->assertEquals( 'body', $Mail->body );
	}

	/**
	 * @group set_reply_mail_raw_params
	 */
	public function test_set_reply_mail_raw_params_ToとCCとBCCとattachmentsは直接設定されてもに空値() {
		$Mail = new MW_WP_Form_Mail();
		$post_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$Setting = new MW_WP_Form_Setting( $post_id );
		$Data = MW_WP_Form_Data::getInstance( MWF_Functions::get_form_key_from_form_id( $post_id ) );
		$Mail->cc  = 'cc@example.com';
		$Mail->bcc = 'bcc@example.com';
		$Mail->set_reply_mail_raw_params( $Setting );

		$this->assertSame(
			array(),
			$Mail->attachments
		);

		$this->assertEquals(
			'',
			$Mail->cc
		);

		$this->assertEquals(
			'',
			$Mail->bcc
		);
	}

	/**
	 * @group set_admin_mail_reaquire_params
	 */
	public function test_set_admin_mail_reaquire_params_ToとFromとSenderが空なら上書き() {
		$Mail = new MW_WP_Form_Mail();
		$Mail->set_admin_mail_reaquire_params();

		$this->assertEquals(
			get_bloginfo( 'admin_email' ),
			$Mail->to
		);

		$this->assertEquals(
			get_bloginfo( 'admin_email' ),
			$Mail->from
		);

		$this->assertEquals(
			get_bloginfo( 'name' ),
			$Mail->sender
		);
	}

	/**
	 * @group set_admin_mail_reaquire_params
	 */
	public function test_set_admin_mail_reaquire_params_ToとFromとSenderが空でなければそのまま() {
		$Mail         = new MW_WP_Form_Mail();
		$Mail->to     = 'to@example.com';
		$Mail->from   = 'from@example.com';
		$Mail->sender = 'Sender';
		$Mail->set_admin_mail_reaquire_params();

		$this->assertEquals(
			'to@example.com',
			$Mail->to
		);

		$this->assertEquals(
			'from@example.com',
			$Mail->from
		);

		$this->assertEquals(
			'Sender',
			$Mail->sender
		);
	}

	/**
	 * @group set_reply_mail_reaquire_params
	 */
	public function test_set_reply_mail_reaquire_params_FromとSenderが空なら上書き() {
		$Mail = new MW_WP_Form_Mail();
		$Mail->set_reply_mail_reaquire_params();

		$this->assertEquals(
			get_bloginfo( 'admin_email' ),
			$Mail->from
		);

		$this->assertEquals(
			get_bloginfo( 'name' ),
			$Mail->sender
		);
	}

	/**
	 * @group set_reply_mail_reaquire_params
	 */
	public function test_set_reply_mail_reaquire_params_FromとSenderが空でなければそのまま() {
		$Mail = new MW_WP_Form_Mail();
		$Mail->from   = 'from@example.com';
		$Mail->sender = 'Sender';
		$Mail->set_reply_mail_reaquire_params();

		$this->assertEquals(
			'from@example.com',
			$Mail->from
		);

		$this->assertEquals(
			'Sender',
			$Mail->sender
		);
	}

	/**
	 * @group parse
	 */
	public function test_parse_ToとCCとBCCは上書きされない() {
		$Mail = new MW_WP_Form_Mail();
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
		$Mail->to     = '{to}';
		$Mail->cc     = '{cc}';
		$Mail->bcc    = '{bcc}';
		$Mail->from   = '{from}';
		$Mail->sender = '{sender}';
		$Mail->body   = '{body}';
		$Mail->parse( $Setting );

		$this->assertEquals( '', $Mail->to );
		$this->assertEquals( '', $Mail->cc );
		$this->assertEquals( '', $Mail->bcc );
		$this->assertEquals( 'from@example.com', $Mail->from );
		$this->assertEquals( 'Sender', $Mail->sender );
		$this->assertEquals( 'body', $Mail->body );
	}

	/**
	 * @group save
	 */
	public function test_save() {
		$Mail = new MW_WP_Form_Mail();
		$post_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$Setting = new MW_WP_Form_Setting( $post_id );
		$Data = MW_WP_Form_Data::getInstance( MWF_Functions::get_form_key_from_form_id( $post_id ) );
		$Data->set( 'example', 'example' );
		$Mail->body = '{example}';
		$Mail->save( $Setting );

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
		$Mail = new MW_WP_Form_Mail();
		$post_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$Setting = new MW_WP_Form_Setting( $post_id );
		$Data = MW_WP_Form_Data::getInstance( MWF_Functions::get_form_key_from_form_id( $post_id ) );
		$Data->set( 'example', 'example' );
		$Mail->body = '{example}';
		$Mail->save( $Setting );
		$this->assertNotEmpty( $Mail->get_saved_mail_id() );
	}

	/**
	 * @group get_saved_mail_id
	 */
	public function test_get_saved_mail_id__保存されなかったとき() {
		$Mail = new MW_WP_Form_Mail();
		$post_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$Setting = new MW_WP_Form_Setting( $post_id );
		$Data = MW_WP_Form_Data::getInstance( MWF_Functions::get_form_key_from_form_id( $post_id ) );
		$Data->set( 'example', 'example' );
		$Mail->body = '{example}';
		$this->assertNull( $Mail->get_saved_mail_id() );
	}
}
