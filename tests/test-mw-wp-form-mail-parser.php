<?php
class MW_WP_Form_Mail_Parser_Test extends WP_UnitTestCase {

	/**
	 * @var MW_WP_Form_Mail
	 */
	protected $Mail;

	/**
	 * @var MW_WP_Form_Data
	 */
	protected $Data;

	/**
	 * @var MW_WP_Form_Setting
	 */
	protected $Setting;

	public function setUp() {
		parent::setUp();
		$post_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$this->Mail    = new MW_WP_Form_Mail();
		$this->Setting = new MW_WP_Form_Setting( $post_id );
		$this->Data    = MW_WP_Form_Data::getInstance( MWF_Functions::get_form_key_from_form_id( $post_id ), array(), array() );
	}

	/**
	 * @group get_parsed_mail_object
	 * @backupStaticAttributes enabled
	 */
	public function test_get_parsed_mail_object_ToとCCとBCCは上書きされない() {
		$this->Data->set( 'to'    , 'to@example.com' );
		$this->Data->set( 'cc'    , 'cc@example.com' );
		$this->Data->set( 'bcc'   , 'bcc@example.com' );
		$this->Data->set( 'from'  , 'from@example.com' );
		$this->Data->set( 'sender', 'Sender' );
		$this->Data->set( 'body'  , 'body' );
		$this->Mail->to     = '{to}';
		$this->Mail->cc     = '{cc}';
		$this->Mail->bcc    = '{bcc}';
		$this->Mail->from   = '{from}';
		$this->Mail->sender = '{sender}';
		$this->Mail->body   = '{body}';
		$Mail_Parser = new MW_WP_Form_Mail_Parser( $this->Mail, $this->Data, $this->Setting );
		$Mail_Parser->get_parsed_mail_object( false );

		$this->assertEquals(
			'{to}',
			$this->Mail->to
		);

		$this->assertEquals(
			'{cc}',
			$this->Mail->cc
		);

		$this->assertEquals(
			'{bcc}',
			$this->Mail->bcc
		);

		$this->assertEquals(
			'from@example.com',
			$this->Mail->from
		);

		$this->assertEquals(
			'Sender',
			$this->Mail->sender
		);

		$this->assertEquals(
			'body',
			$this->Mail->body
		);
	}

	/**
	 * @group get_parsed_mail_object
	 * @backupStaticAttributes enabled
	 */
	public function test_get_parsed_mail_object_データベースに保存() {
		$this->Data->set( 'example', 'example' );
		$this->Mail->body = '{example}';
		$Mail_Parser = new MW_WP_Form_Mail_Parser( $this->Mail, $this->Data, $this->Setting );
		$Mail_Parser->get_parsed_mail_object( true );

		$posts = get_posts( array(
			'post_type' => MWF_Config::DBDATA . $this->Setting->get( 'post_id' ),
		) );
		foreach ( $posts as $post ) {
			$this->assertEquals(
				'example',
				get_post_meta( $post->ID, 'example', true )
			);
			break;
		}
	}
}
