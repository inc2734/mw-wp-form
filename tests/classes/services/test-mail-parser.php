<?php
class MW_WP_Form_Mail_Parser_Test extends WP_UnitTestCase {

	protected function _create_form() {
		return $this->factory->post->create(
			array(
				'post_type' => MWF_Config::NAME,
			)
		);
	}

	/**
	 * tear_down
	 */
	public function tear_down() {
		parent::tear_down();
		_delete_all_data();
	}

	/**
	 * @test
	 * @group get_parsed_mail_object
	 */
	public function get_parsed_mail_object__To_and_CC_and_BCC_are_not_overwritten() {
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Setting  = new MW_WP_Form_Setting( $form_id );
		$Data     = MW_WP_Form_Data::connect( $form_key, array(
			'to'     => 'to@example.com',
			'cc'     => 'cc@example.com',
			'bcc'    => 'bcc@example.com',
			'from'   => 'from@example.com',
			'sender' => 'Sender',
			'body'   => 'body',
		) );

		$Mail = new MW_WP_Form_Mail();
		$Mail->to     = '{to}';
		$Mail->cc     = '{cc}';
		$Mail->bcc    = '{bcc}';
		$Mail->from   = '{from}';
		$Mail->sender = '{sender}';
		$Mail->body   = '{body}';

		$Mail_Parser = new MW_WP_Form_Mail_Parser( $Mail, $Setting );
		$Parsed_Mail = $Mail_Parser->get_parsed_mail_object();

		$this->assertEquals( '', $Parsed_Mail->to );
		$this->assertEquals( '', $Parsed_Mail->cc );
		$this->assertEquals( '', $Parsed_Mail->bcc );
		$this->assertEquals( 'from@example.com', $Parsed_Mail->from );
		$this->assertEquals( 'Sender', $Parsed_Mail->sender );
		$this->assertEquals( 'body', $Parsed_Mail->body );
	}

	/**
	 * @test
	 * @group save
	 */
	public function save() {
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Setting  = new MW_WP_Form_Setting( $form_id );
		$Data     = MW_WP_Form_Data::connect( $form_key, array(
			'example' => 'example',
		) );

		$Mail = new MW_WP_Form_Mail();
		$Mail->body = '{example}';

		$Mail_Parser = new MW_WP_Form_Mail_Parser( $Mail, $Setting );
		$Mail_Parser->save();

		$posts = get_posts( array(
			'post_type' => MWF_Functions::get_contact_data_post_type_from_form_id( $form_id ),
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
	 * @group save
	 */
	public function save__saved_when_null() {
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Setting  = new MW_WP_Form_Setting( $form_id );
		$Data     = MW_WP_Form_Data::connect( $form_key, array(
			'example' => null,
		) );

		$Mail = new MW_WP_Form_Mail();
		$Mail->body = '{example}';

		$Mail_Parser = new MW_WP_Form_Mail_Parser( $Mail, $Setting );
		$Mail_Parser->save();

		$posts = get_posts( array(
			'post_type' => MWF_Functions::get_contact_data_post_type_from_form_id( $form_id ),
		) );
		foreach ( $posts as $post ) {
			$post_metas = get_post_meta( $post->ID );
			$this->assertTrue( isset( $post_metas['example'] ) );
			break;
		}
	}

	/**
	 * @test
	 * @group save
	 */
	public function save__saved_when_null_but_attachement_files_are_not_saved() {

		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Setting  = new MW_WP_Form_Setting( $form_id );
		$Data     = MW_WP_Form_Data::connect( $form_key, array(
			'attachment_1' => null,
		) );

		$temp_dir_1 = MW_WP_Form_Directory::generate_user_file_dirpath( $form_id, 'attachment_1' );
		$temp_dir_2 = MW_WP_Form_Directory::generate_user_file_dirpath( $form_id, 'attachment_2' );
		wp_mkdir_p( $temp_dir_1 );
		wp_mkdir_p( $temp_dir_2 );
		file_put_contents( $temp_dir_1 . '/attachment_1.txt', 'hoge' );
		file_put_contents( $temp_dir_2 . '/attachment_2.txt', 'fuga' );

		$Mail = new MW_WP_Form_Mail();
		$Mail->body = '{attachment_1}';
		$Mail->attachments = array(
			'attachment_1' => $temp_dir_1 . '/attachment_1.txt',
			'attachment_2' => $temp_dir_2 . '/attachment_2.txt',
		);

		$Mail_Parser = new MW_WP_Form_Mail_Parser( $Mail, $Setting );
		$Mail_Parser->save();

		$posts = get_posts( array(
			'post_type' => MWF_Functions::get_contact_data_post_type_from_form_id( $form_id ),
		) );
		foreach ( $posts as $post ) {
			$post_metas = get_post_meta( $post->ID );
			$this->assertFalse( isset( $post_metas['attachment_1'] ) );
			break;
		}
	}

	/**
	 * @test
	 * @group get_parsed_mail_oabject
	 * @group tracking_number
	 */
	public function get_parsed_mail_object__tracking_number() {
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Setting  = new MW_WP_Form_Setting( $form_id );

		$Mail = new MW_WP_Form_Mail();
		$Mail->body = '{' . MWF_Config::TRACKINGNUMBER . '}';

		$Mail_Parser = new MW_WP_Form_Mail_Parser( $Mail, $Setting );

		$Parsed_Mail = $Mail_Parser->get_parsed_mail_object();
		$this->assertEquals( 1, $Parsed_Mail->body );
	}

	/**
	 * @test
	 * @group get_parsed_mail_object
	 * @group custom_mail_tag
	 *
	 */
	public function get_parsed_mail_object__custom_mail_tag() {
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Setting  = new MW_WP_Form_Setting( $form_id );

		$self = $this;
		add_filter(
			'mwform_custom_mail_tag_' . $form_key,
			function( $value, $key, $insert_id ) use( $self ) {
				if ( 'custom_tag' === $key ) {
					return 'hoge';
				}
				return $value;
			},
			10,
			3
		);

		$Mail = new MW_WP_Form_Mail();
		$Mail->body = '{custom_tag}';

		$Mail_Parser = new MW_WP_Form_Mail_Parser( $Mail, $Setting );
		$Parsed_Mail = $Mail_Parser->get_parsed_mail_object();

		$this->assertEquals( 'hoge', $Parsed_Mail->body );

		remove_all_filters( 'mwform_custom_mail_tag_' . $form_key );
	}

	/**
	 * @test
	 * @group get_parsed_mail_object
	 * @group custom_mail_tag
	 */
	public function get_parsed_mail_object__custom_mail_tag__TO_CC_BCC() {
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Setting  = new MW_WP_Form_Setting( $form_id );

		$self = $this;
		add_filter(
			'mwform_custom_mail_tag_' . $form_key,
			function( $value, $key, $insert_id ) use( $self ) {
				if ( 'to' === $key ) {
					return 'to@example.com';
				} elseif ( 'cc' === $key ) {
					return 'cc@example.com';
				} elseif ( 'bcc' === $key ) {
					return 'bcc@example.com';
				}
				return $value;
			},
			10,
			3
		);

		$Mail = new MW_WP_Form_Mail();
		$Mail->to  = '{to}';
		$Mail->cc  = '{cc}';
		$Mail->bcc = '{bcc}';
		$Mail_Parser = new MW_WP_Form_Mail_Parser( $Mail, $Setting );
		$Parsed_Mail = $Mail_Parser->get_parsed_mail_object();

		$this->assertEquals( 'to@example.com', $Parsed_Mail->to );
		$this->assertEquals( 'cc@example.com', $Parsed_Mail->cc );
		$this->assertEquals( 'bcc@example.com', $Parsed_Mail->bcc );

		remove_all_filters( 'mwform_custom_mail_tag_' . $form_key );
	}

	/**
	 * @test
	 * @group get_saved_mail_id
	 */
	public function get_saved_mail_id__saved() {
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Setting  = new MW_WP_Form_Setting( $form_id );
		$Data     = MW_WP_Form_Data::connect( $form_key, array(
			'example' => 'example',
		) );

		$Mail = new MW_WP_Form_Mail();
		$Mail->body = '{example}';
		$Mail_Parser = new MW_WP_Form_Mail_Parser( $Mail, $Setting );
		$Mail_Parser->save();

		$this->assertNotEmpty( $Mail_Parser->get_saved_mail_id() );
	}

	/**
	 * @test
	 * @group get_saved_mail_id
	 */
	public function get_saved_mail_id__not_saved() {
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Setting  = new MW_WP_Form_Setting( $form_id );
		$Data     = MW_WP_Form_Data::connect( $form_key, array(
			'example' => 'example',
		) );

		$Mail = new MW_WP_Form_Mail();
		$Mail->body = '{example}';
		$Mail_Parser = new MW_WP_Form_Mail_Parser( $Mail, $Setting );

		$this->assertNull( $Mail_Parser->get_saved_mail_id() );
	}
}
