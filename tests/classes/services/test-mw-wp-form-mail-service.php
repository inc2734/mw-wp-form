<?php
class MW_WP_Form_Mail_Service_Test extends WP_UnitTestCase {

	/**
	 * tearDown
	 */
	public function tearDown() {
		parent::tearDown();
		_delete_all_data();
	}

	protected function _create_form() {
		return $this->factory->post->create(
			array(
				'post_type' => MWF_Config::NAME,
			)
		);
	}

	/**
	 * @test
	 * @group mwform_auto_mail_raw_{$form_key}
	 * @group mwform_auto_mail_{$form_key}
	 */
	public function hook_mwform_auto_mail_raw__hook_mwform_auto_mail__Data_in_callback_is_independent() {
		$self     = $this;
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Mail     = new MW_WP_Form_Mail();
		$Data     = MW_WP_Form_Data::connect( $form_key, array(
			'email' => 'info@example.com',
		) );

		$Setting = new MW_WP_Form_Setting( $form_id );
		$Setting->set( 'automatic_reply_email', 'email' );

		$this->through_hook_count = 0;

		add_filter( 'mwform_auto_mail_raw_' . $form_key,
			function( $Mail, $values, $Data ) use( $self ) {
				$this->through_hook_count ++;
				$self->assertEquals( 'info@example.com', $Data->get( 'email' ) );
				$Data->set( 'email', 'dummy' );
				return $Mail;
			},
			10, 3
		);

		add_filter( 'mwform_auto_mail_' . $form_key,
			function( $Mail, $values, $Data ) use( $self ) {
				$this->through_hook_count ++;
				$self->assertEquals( 'info@example.com', $Data->get( 'email' ) );
				return $Mail;
			},
			10, 3
		);

		$Mail_Service = new MW_WP_Form_Mail_Service(
			$Mail, $form_key, $Setting
		);
		$Mail_Service->send_reply_mail();
		$self->assertEquals( 2, $this->through_hook_count );

		remove_all_filters( 'mwform_auto_mail_raw_' . $form_key );
		remove_all_filters( 'mwform_auto_mail_' . $form_key );
	}

	/**
	 * @test
	 * @group mwform_admin_mail_raw_{$form_key}
	 * @group mwform_admin_mail_{$form_key}
	 */
	public function hook_mwform_admin_mail_raw__hook_mwform_admin_mail__Data_in_callback_is_independent() {
		$self     = $this;
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Mail     = new MW_WP_Form_Mail();
		$Data     = MW_WP_Form_Data::connect( $form_key, array(
			'email' => 'info@example.com',
		) );

		$Setting = new MW_WP_Form_Setting( $form_id );

		$this->through_hook_count = 0;

		add_filter( 'mwform_admin_mail_raw_' . $form_key,
			function( $Mail, $values, $Data ) use( $self ) {
				$this->through_hook_count ++;
				$self->assertEquals( 'info@example.com', $Data->get( 'email' ) );
				$Data->set( 'email', 'dummy' );
				return $Mail;
			},
			10, 3
		);

		add_filter( 'mwform_admin_mail_' . $form_key,
			function( $Mail, $values, $Data ) use( $self ) {
				$this->through_hook_count ++;
				$self->assertEquals( 'info@example.com', $Data->get( 'email' ) );
				return $Mail;
			},
			10, 3
		);

		$Mail_Service = new MW_WP_Form_Mail_Service(
			$Mail, $form_key, $Setting
		);
		$Mail_Service->send_admin_mail();
		$self->assertEquals( 2, $this->through_hook_count );

		remove_all_filters( 'mwform_admin_mail_raw_' . $form_key );
		remove_all_filters( 'mwform_admin_mail_' . $form_key );
	}

	/**
	 * @test
	 * @group mwform_admin_mail_raw_{$form_key}
	 * @group mwform_admin_mail_{$form_key}
	 */
	public function hook_mwform_admin_mail_raw__hook_mwform_admin_mail() {
		$self     = $this;
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Mail     = new MW_WP_Form_Mail();
		$Data     = MW_WP_Form_Data::connect( $form_key, array(
			'email' => 'info@example.com',
		) );

		$Setting = new MW_WP_Form_Setting( $form_id );

		$this->through_hook_count = 0;

		add_filter(
			'mwform_admin_mail_raw_' . $form_key,
			function( $Mail, $values ) use( $self ) {
				$this->through_hook_count ++;
				$Mail->to = 'admin_mail_raw_to@example.com';
				return $Mail;
			},
			10, 3
		);

		add_filter( 'mwform_admin_mail_' . $form_key,
			function( $Mail, $values ) use( $self ) {
				$this->through_hook_count ++;
				$self->assertEquals( $Mail->to, 'admin_mail_raw_to@example.com' );
				return $Mail;
			},
			10, 3
		);

		$Mail_Service = new MW_WP_Form_Mail_Service(
			$Mail, $form_key, $Setting
		);
		$Mail_Service->send_admin_mail();
		$self->assertEquals( 2, $this->through_hook_count );

		remove_all_filters( 'mwform_admin_mail_raw_' . $form_key );
		remove_all_filters( 'mwform_admin_mail_' . $form_key );
	}

	/**
	 * @test
	 * @group mwform_auto_mail_raw_{$form_key}
	 * @group mwform_auto_mail_{$form_key}
	 */
	public function hook_mwform_auto_mail_raw__hook_mwform_auto_mail() {
		$self     = $this;
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Mail     = new MW_WP_Form_Mail();
		$Data     = MW_WP_Form_Data::connect( $form_key, array(
			'email' => 'info@example.com',
		) );

		$Setting = new MW_WP_Form_Setting( $form_id );
		$Setting->set( 'automatic_reply_email', 'email' );

		$this->through_hook_count = 0;

		add_filter(
			'mwform_auto_mail_raw_' . $form_key,
			function( $Mail, $values ) use( $self ) {
				$this->through_hook_count ++;
				$Mail->to = 'auto_mail_raw_to@example.com';
				return $Mail;
			},
			10, 3
		);

		add_filter( 'mwform_auto_mail_' . $form_key,
			function( $Mail, $values ) use( $self ) {
				$this->through_hook_count ++;
				$self->assertEquals( $Mail->to, 'auto_mail_raw_to@example.com' );
				return $Mail;
			},
			10, 3
		);

		$Mail_Service = new MW_WP_Form_Mail_Service(
			$Mail, $form_key, $Setting
		);
		$Mail_Service->send_reply_mail();
		$self->assertEquals( 2, $this->through_hook_count );

		remove_all_filters( 'mwform_auto_mail_raw_' . $form_key );
		remove_all_filters( 'mwform_auto_mail_' . $form_key );
	}

	/**
	 * @test
	 * @group mwform_admin_mail_raw_{$form_key}
	 * @group mwform_admin_mail_{$form_key}
	 */
	public function hook_mwform_admin_mail_raw__hook_mwform_admin_mail__replace() {
		$self     = $this;
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Mail     = new MW_WP_Form_Mail();
		$Data     = MW_WP_Form_Data::connect( $form_key, array(
			'email'  => 'info@example.com',
			'to'     => 'admin_mail_raw_to@example.com',
			'cc'     => 'admin_mail_raw_cc@example.com',
			'bcc'    => 'admin_mail_raw_bcc@example.com',
			'name-1' => 'value-1',
		) );

		$Setting = new MW_WP_Form_Setting( $form_id );

		$this->through_hook_count = 0;

		add_filter(
			'mwform_admin_mail_raw_' . $form_key,
			function( $Mail, $values ) use( $self ) {
				$this->through_hook_count ++;
				$Mail->to   = '{to}';
				$Mail->cc   = '{cc}';
				$Mail->bcc  = '{bcc}';
				$Mail->body = '{name-1}';
				return $Mail;
			},
			10, 3
		);

		add_filter( 'mwform_admin_mail_' . $form_key,
			function( $Mail, $values ) use( $self ) {
				$this->through_hook_count ++;
				$self->assertEquals( get_bloginfo( 'admin_email' ), $Mail->to );
				$self->assertEquals( '', $Mail->cc );
				$self->assertEquals( '', $Mail->bcc );
				$self->assertEquals( 'value-1', $Mail->body );
				return $Mail;
			},
			10, 3
		);

		$Mail_Service = new MW_WP_Form_Mail_Service(
			$Mail, $form_key, $Setting
		);
		$Mail_Service->send_admin_mail();
		$self->assertEquals( 2, $this->through_hook_count );

		remove_all_filters( 'mwform_admin_mail_raw_' . $form_key );
		remove_all_filters( 'mwform_admin_mail_' . $form_key );
	}

	/**
	 * @test
	 * @group mwform_auto_mail_raw_{$form_key}
	 * @group mwform_auto_mail_{$form_key}
	 */
	public function hook_mwform_auto_mail_raw__hook_mwform_auto_mail__replace() {
		$self     = $this;
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Mail     = new MW_WP_Form_Mail();
		$Data     = MW_WP_Form_Data::connect( $form_key, array(
			'email'  => 'info@example.com',
			'to'     => 'auto_mail_raw_to@example.com',
			'cc'     => 'auto_mail_raw_cc@example.com',
			'bcc'    => 'auto_mail_raw_bcc@example.com',
			'name-1' => 'value-1',
		) );

		$Setting = new MW_WP_Form_Setting( $form_id );
		$Setting->set( 'automatic_reply_email', 'email' );

		$this->through_hook_count = 0;

		add_filter(
			'mwform_auto_mail_raw_' . $form_key,
			function( $Mail, $values ) use( $self ) {
				$this->through_hook_count ++;
				$Mail->to   = '{to}';
				$Mail->cc   = '{cc}';
				$Mail->bcc  = '{bcc}';
				$Mail->body = '{name-1}';
				return $Mail;
			},
			10, 3
		);

		add_filter( 'mwform_auto_mail_' . $form_key,
			function( $Mail, $values ) use( $self ) {
				$this->through_hook_count ++;
				$self->assertEquals( '', $Mail->to );
				$self->assertEquals( '', $Mail->cc );
				$self->assertEquals( '', $Mail->bcc );
				$self->assertEquals( 'value-1', $Mail->body );
				return $Mail;
			},
			10, 3
		);

		$Mail_Service = new MW_WP_Form_Mail_Service(
			$Mail, $form_key, $Setting
		);
		$Mail_Service->send_reply_mail();
		$self->assertEquals( 2, $this->through_hook_count );

		remove_all_filters( 'mwform_auto_mail_raw_' . $form_key );
		remove_all_filters( 'mwform_auto_mail_' . $form_key );
	}

	/**
	 * @test
	 * @group tracking_number
	 */
	public function tracking_number() {
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Mail     = new MW_WP_Form_Mail();

		$Setting = new MW_WP_Form_Setting( $form_id );
		$Setting->set( 'admin_mail_content', '{' . MWF_Config::TRACKINGNUMBER . '}' );

		$Mail_Service = new MW_WP_Form_Mail_Service(
			$Mail, $form_key, $Setting
		);

		$this->assertEquals( 1, $Setting->get_tracking_number() );
		$Mail_Service->update_tracking_number();
		$this->assertEquals( 2, $Setting->get_tracking_number() );
	}

	/**
	 * @test
	 * @group send_admin_mail
	 */
	public function send_admin_mail__save() {
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Mail     = new MW_WP_Form_Mail();

		$Setting = new MW_WP_Form_Setting( $form_id );
		$Setting->set( 'usedb', 1 );

		add_filter( 'mwform_is_mail_sended', function() {
			return true;
		} );

		$Mail_Service = new MW_WP_Form_Mail_Service(
			$Mail, $form_key, $Setting
		);
		$Mail_Service->send_admin_mail();

		$posts = get_posts( array(
			'post_type'      => MWF_Functions::get_contact_data_post_type_from_form_id( $form_id ),
			'posts_per_page' => -1,
		) );
		$this->assertEquals( 1, count( $posts ) );

		$meta = get_post_meta( $posts[0]->ID, MWF_config::CONTACT_DATA_NAME, true );
		$this->assertNotEmpty( $meta );
	}

	/**
	 * @test
	 * @group send_admin_mail
	 * @group mwform_admin_mail_{$form_key}
	 */
	public function send_admin_mail__save__replace_by_hook_mwform_admin_mail() {
		$self     = $this;
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Mail     = new MW_WP_Form_Mail();
		$Data     = MW_WP_Form_Data::connect( $form_key, array(
			'name-1' => 'value-1',
		) );

		$Setting = new MW_WP_Form_Setting( $form_id );
		$Setting->set( 'usedb', 1 );
		$Setting->set( 'admin_mail_content', '{name-1}' );

		add_filter( 'mwform_is_mail_sended', function() {
			return true;
		} );

		add_filter( 'mwform_admin_mail_' . $form_key, function( $Mail, $values, $Data ) use ( $self ) {
			$Mail->body = 'This is dummy message.';
			return $Mail;
		}, 10, 3 );

		$Mail_Service = new MW_WP_Form_Mail_Service(
			$Mail, $form_key, $Setting
		);
		$Mail_Service->send_admin_mail();

		$posts = get_posts( array(
			'post_type'      => MWF_Functions::get_contact_data_post_type_from_form_id( $form_id ),
			'posts_per_page' => -1,
		) );

		$this->assertEquals( 'value-1', get_post_meta( $posts[0]->ID, 'name-1', true ) );

		remove_all_filters( 'mwform_admin_mail_' . $form_key );
	}

	/**
	 * @test
	 * @group mwform_is_mail_sended
	 * @group send_admin_mail
	 */
	public function send_admin_mail__when_mail_to_is_false() {
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Mail     = new MW_WP_Form_Mail();

		$Setting = new MW_WP_Form_Setting( $form_id );

		$Mail_Service = new MW_WP_Form_Mail_Service(
			$Mail, $form_key, $Setting
		);

		add_filter( 'mwform_admin_mail_' . $form_key, function( $Mail, $values, $Data ) {
			$Mail->to = false;
			return $Mail;
		}, 10, 3 );

		$this->assertTrue( $Mail_Service->send_admin_mail() );
	}

	/**
	 * @test
	 * @group mwform_is_mail_sended
	 * @group send_admin_mail
	 */
	public function send_admin_mail__save__when_mail_to_is_false() {
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Mail     = new MW_WP_Form_Mail();

		$Setting = new MW_WP_Form_Setting( $form_id );
		$Setting->set( 'usedb', 1 );

		add_filter( 'mwform_is_mail_sended', function( $is_mail_sended ) {
			return false;
		} );

		add_filter( 'mwform_admin_mail_' . $form_key, function( $Mail, $values, $Data ) {
			$Mail->to = false;
			return $Mail;
		}, 10, 3 );

		$Mail_Service = new MW_WP_Form_Mail_Service(
			$Mail, $form_key, $Setting
		);
		$Mail_Service->send_admin_mail();

		$posts = get_posts( array(
			'post_type'      => MWF_Functions::get_contact_data_post_type_from_form_id( $form_id ),
			'posts_per_page' => -1,
		) );
		$this->assertEquals( 1, count( $posts ) );
	}
}
