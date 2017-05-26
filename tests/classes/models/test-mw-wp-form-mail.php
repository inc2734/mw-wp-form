<?php
class MW_WP_Form_Mail_Test extends WP_UnitTestCase {

	public function tearDown() {
		parent::tearDown();
		_delete_all_data();
	}

	protected function _instantiation_Setting() {
		$post_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		return new MW_WP_Form_Setting( $post_id );
	}

	/**
	 * @test
	 * @group set_admin_mail_raw_params
	 */
	public function set_admin_mail_raw_params() {
		// Pattern: no settings
		$Mail = new MW_WP_Form_Mail();
		$Mail->set_admin_mail_raw_params( $this->_instantiation_Setting() );
		$this->assertEquals( '', $Mail->subject );
		$this->assertEquals( '', $Mail->body );
		$this->assertEquals( get_bloginfo( 'admin_email' ), $Mail->to );
		$this->assertEquals( '', $Mail->cc );
		$this->assertEquals( '', $Mail->bcc );
		$this->assertEquals( get_bloginfo( 'admin_email' ), $Mail->return_path );
		$this->assertEquals( get_bloginfo( 'admin_email' ), $Mail->from );
		$this->assertEquals( get_bloginfo( 'name' ), $Mail->sender );

		// Pattern: has settings
		$Mail = new MW_WP_Form_Mail();
		$Setting = $this->_instantiation_Setting();
		$Setting->sets( array(
			'admin_mail_subject' => 'subject',
			'admin_mail_content' => 'body',
			'mail_to'            => 'to@example.com',
			'mail_cc'            => 'cc@example.com',
			'mail_bcc'           => 'bcc@example.com',
			'mail_return_path'   => 'return_path@example.com',
			'admin_mail_from'    => 'from@example.com',
			'admin_mail_sender'  => 'sender',
		) );
		$Mail->set_admin_mail_raw_params( $Setting );
		$this->assertEquals( 'subject', $Mail->subject );
		$this->assertEquals( 'body', $Mail->body );
		$this->assertEquals( 'to@example.com', $Mail->to );
		$this->assertEquals( 'cc@example.com', $Mail->cc );
		$this->assertEquals( 'bcc@example.com', $Mail->bcc );
		$this->assertEquals( 'return_path@example.com', $Mail->return_path );
		$this->assertEquals( 'from@example.com', $Mail->from );
		$this->assertEquals( 'sender', $Mail->sender );

		// Pattern: has reply mail settings
		$Mail = new MW_WP_Form_Mail();
		$Setting = $this->_instantiation_Setting();
		$Setting->sets( array(
			'mail_from'    => 'from@example.com',
			'mail_sender'  => 'sender',
			'mail_subject' => 'subject',
			'mail_content' => 'body',
		) );
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
	 * @test
	 * @group set_reply_mail_raw_params
	 */
	public function set_reply_mail_raw_params() {
		// Pattern: no settings
		$Mail = new MW_WP_Form_Mail();
		$Mail->set_reply_mail_raw_params( $this->_instantiation_Setting() );
		$this->assertEquals( '', $Mail->to );
		$this->assertEquals( get_bloginfo( 'admin_email' ), $Mail->return_path );
		$this->assertEquals( get_bloginfo( 'admin_email' ), $Mail->from );
		$this->assertEquals( get_bloginfo( 'name' ), $Mail->sender );
		$this->assertEquals( '', $Mail->subject );
		$this->assertEquals( '', $Mail->body );

		// Pattern: has settings
		$Mail = new MW_WP_Form_Mail();
		$Setting = $this->_instantiation_Setting();
		$Setting->sets( array(
			'automatic_reply_email' => 'メールアドレス',
			'mail_from'             => 'from@example.com',
			'mail_sender'           => 'sender',
			'mail_subject'          => 'subject',
			'mail_content'          => 'body',
		) );
		$Data = MW_WP_Form_Data::connect( MWF_Functions::get_form_key_from_form_id( $Setting->get( 'post_id' ) ) );
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
	 * @test
	 * @group set_admin_mail_reaquire_params
	 */
	public function set_admin_mail_reaquire_params() {
		// Pattern: overwrite when to, from and sender are blank
		$Mail = new MW_WP_Form_Mail();
		$Mail->set_admin_mail_reaquire_params();
		$this->assertEquals( get_bloginfo( 'admin_email' ), $Mail->to );
		$this->assertEquals( get_bloginfo( 'admin_email' ), $Mail->from );
		$this->assertEquals( get_bloginfo( 'name' ), $Mail->sender );

		// Pattern: to, from and sender aren't blank
		$Mail         = new MW_WP_Form_Mail();
		$Mail->to     = 'to@example.com';
		$Mail->from   = 'from@example.com';
		$Mail->sender = 'Sender';
		$Mail->set_admin_mail_reaquire_params();
		$this->assertEquals( 'to@example.com', $Mail->to );
		$this->assertEquals( 'from@example.com', $Mail->from );
		$this->assertEquals( 'Sender', $Mail->sender );
	}

	/**
	 * @test
	 * @group set_reply_mail_reaquire_params
	 */
	public function set_reply_mail_reaquire_params() {
		// Pattern: overwrite when from and sender are blank
		$Mail = new MW_WP_Form_Mail();
		$Mail->set_reply_mail_reaquire_params();
		$this->assertEquals( get_bloginfo( 'admin_email' ), $Mail->from );
		$this->assertEquals( get_bloginfo( 'name' ), $Mail->sender );

		// Pattern: to, from and sender aren't blank
		$Mail = new MW_WP_Form_Mail();
		$Mail->from   = 'from@example.com';
		$Mail->sender = 'Sender';
		$Mail->set_reply_mail_reaquire_params();
		$this->assertEquals( 'from@example.com', $Mail->from );
		$this->assertEquals( 'Sender', $Mail->sender );
	}

	/**
	 * @test
	 * @group parse
	 */
	public function parse() {
			$Mail = new MW_WP_Form_Mail();
			$Setting = $this->_instantiation_Setting();

			$Data = MW_WP_Form_Data::connect( MWF_Functions::get_form_key_from_form_id( $Setting->get( 'post_id' ) ) );
			$Data->sets( array(
				'to'     => 'to@example.com',
				'cc'     => 'cc@example.com',
				'bcc'    => 'bcc@example.com',
				'from'   => 'from@example.com',
				'sender' => 'Sender',
				'body'   => 'body',
			) );

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
	 * @test
	 * @group save
	 */
	public function save() {
		$Mail = new MW_WP_Form_Mail();
		$Setting = $this->_instantiation_Setting();
		$Data = MW_WP_Form_Data::connect( MWF_Functions::get_form_key_from_form_id( $Setting->get( 'post_id' ) ) );
		$Data->set( 'example', 'example' );
		$Mail->body = '{example}';
		$Mail->save( $Setting );

		$posts = get_posts( array(
			'post_type' => MWF_Functions::get_contact_data_post_type_from_form_id( $Setting->get( 'post_id' ) )
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
	 * @test
	 * @group get_saved_mail_id
	 */
	public function get_saved_mail_id() {
		// Pattern: saved
		$Mail = new MW_WP_Form_Mail();
		$Setting = $this->_instantiation_Setting();
		$Data = MW_WP_Form_Data::connect( MWF_Functions::get_form_key_from_form_id( $Setting->get( 'post_id' ) ) );
		$Data->set( 'example', 'example' );
		$Mail->body = '{example}';
		$Mail->save( $Setting );
		$this->assertNotEmpty( $Mail->get_saved_mail_id() );

		// Pattern: no saved
		$Mail = new MW_WP_Form_Mail();
		$Setting = $this->_instantiation_Setting();
		$Data = MW_WP_Form_Data::connect( MWF_Functions::get_form_key_from_form_id( $Setting->get( 'post_id' ) ) );
		$Data->set( 'example', 'example' );
		$Mail->body = '{example}';
		$this->assertNull( $Mail->get_saved_mail_id() );
	}
}
