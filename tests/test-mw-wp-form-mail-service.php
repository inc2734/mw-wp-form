<?php
class MW_WP_Form_Mail_Service_Test extends WP_UnitTestCase {

	/**
	 * @var string
	 */
	protected $form_key;

	/**
	 * @var MW_WP_Form_Data
	 */
	protected $Data;

	/**
	 * @var int
	 */
	protected $post_id;

	/**
	 * setUp
	 */
	public function setUp() {
		parent::setUp();

		$this->post_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$this->form_key = MWF_Config::NAME . '-' . $this->post_id;
		$this->Data = MW_WP_Form_Data::getInstance( $this->form_key );
		$Validation_Rule_Mail = new MW_WP_Form_Validation_Rule_Mail();
		$Validation_Rule_Mail->set_Data( $this->Data );
	}

	/**
	 * tearDown
	 */
	public function tearDown() {
		parent::tearDown();
		$this->Data->clear_values();
	}

	/**
	 */
	public function test_自動返信メール関連フックのテスト_raw_でDataを変更しても影響されない() {
		$self = $this;
		$Setting = new MW_WP_Form_Setting( $this->post_id );
		$Mail = new MW_WP_Form_Mail();

		add_filter( 'mwform_auto_mail_raw_' . $this->form_key,
			function( $Mail, $values, $Data ) use( $self ) {
				$self->assertEquals( 'info@example.com', $Data->get( 'メールアドレス' ) );
				$Data->set( 'メールアドレス', 'hoge' );
				return $Mail;
			},
			10, 3
		);
		add_filter( 'mwform_auto_mail_' . $this->form_key,
			function( $Mail, $values, $Data ) use( $self ) {
				// mwform_auto_mail_raw で Data を書き換えても影響されない
				$self->assertEquals( 'info@example.com', $Data->get( 'メールアドレス' ) );
				return $Mail;
			},
			10, 3
		);

		$this->Data->set( 'メールアドレス', 'info@example.com' );
		$Setting->set( 'automatic_reply_email', 'メールアドレス' );
		$Mail_Service = new MW_WP_Form_Mail_Service(
			$Mail, $this->form_key, $Setting
		);
		$Mail_Service->send_admin_mail();
		$Mail_Service->send_reply_mail();

		remove_all_filters( 'mwform_auto_mail_raw_' . $this->form_key );
		remove_all_filters( 'mwform_auto_mail_' . $this->form_key );
	}

	/**
	 */
	public function test_管理者宛メール関連フックのテスト_raw_でDataを変更しても影響されない() {
		$self = $this;
		$Setting = new MW_WP_Form_Setting( $this->post_id );
		$Mail = new MW_WP_Form_Mail();

		add_filter( 'mwform_admin_mail_raw_' . $this->form_key,
			function( $Mail, $values, $Data ) use( $self ) {
				$self->assertEquals( $Data->get( 'メールアドレス' ), 'info@example.com' );
				$Data->set( 'メールアドレス', 'hoge' );
				return $Mail;
			},
			10, 3
		);
		add_filter( 'mwform_admin_mail_' . $this->form_key,
			function( $Mail, $values, $Data ) use( $self ) {
				// mwform_admin_mail_raw で Data を書き換えても影響されない
				$self->assertEquals( $Data->get( 'メールアドレス' ), 'info@example.com' );
				return $Mail;
			},
			10, 3
		);

		$this->Data->set( 'メールアドレス', 'info@example.com' );
		$Setting->set( 'automatic_reply_email', 'メールアドレス' );
		$Mail_Service = new MW_WP_Form_Mail_Service(
			$Mail, $this->form_key, $Setting
		);
		$Mail_Service->send_admin_mail();
		$Mail_Service->send_reply_mail();

		remove_all_filters( 'mwform_admin_mail_raw_' . $this->form_key );
		remove_all_filters( 'mwform_admin_mail_' . $this->form_key );
	}

	/**
	 */
	public function test_全メール関連フックのテスト_自動返信設定あり() {
		$self = $this;
		$Setting = new MW_WP_Form_Setting( $this->post_id );
		$Mail = new MW_WP_Form_Mail();

		add_filter(
			'mwform_admin_mail_raw_' . $this->form_key,
			function( $Mail, $values ) use( $self ) {
				$Mail->to = 'admin_mail_raw_to@example.com';
				return $Mail;
			},
			10, 2
		);
		add_filter( 'mwform_auto_mail_raw_' . $this->form_key,
			function( $Mail, $values ) use( $self ) {
				// admin、mail での Mail の変更はひきつがない
				$self->assertEquals( $Mail->to, 'info@example.com' );
				$Mail->to = 'mwform_auto_mail_raw_to@example.com';
				return $Mail;
			},
			10, 2
		);
		add_filter(
			'mwform_mail_' . $this->form_key,
			function( $Mail, $values ) use( $self ) {
				// raw での Mail の変更はひきつぐ
				$self->assertEquals( $Mail->to, 'admin_mail_raw_to@example.com' );
				$Mail->to = 'mwform_mail_to@example.com';
				return $Mail;
			},
			10, 2
		);
		add_filter( 'mwform_admin_mail_' . $this->form_key,
			function( $Mail, $values ) use( $self ) {
				// mail での Mail の変更はひきつぐ
				$self->assertEquals( $Mail->to, 'mwform_mail_to@example.com' );
				return $Mail;
			},
			10, 2
		);
		add_filter( 'mwform_auto_mail_' . $this->form_key,
			function( $Mail, $values ) use( $self ) {
				// raw での Mail の変更はひきつぐ
				$self->assertEquals( $Mail->to, 'mwform_auto_mail_raw_to@example.com' );
				return $Mail;
			},
			10, 2
		);

		$this->Data->set( 'メールアドレス', 'info@example.com' );
		$Setting->set( 'automatic_reply_email', 'メールアドレス' );
		$Mail_Service = new MW_WP_Form_Mail_Service(
			$Mail, $this->form_key, $Setting
		);
		$Mail_Service->send_admin_mail();
		$Mail_Service->send_reply_mail();

		remove_all_filters( 'mwform_admin_mail_raw_' . $this->form_key );
		remove_all_filters( 'mwform_auto_mail_raw_' . $this->form_key );
		remove_all_filters( 'mwform_mail_' . $this->form_key );
		remove_all_filters( 'mwform_admin_mail_' . $this->form_key );
		remove_all_filters( 'mwform_auto_mail_' . $this->form_key );
	}

	/**
	 */
	public function test_全メール関連フックのテスト_自動返信設定なし() {
		$self = $this;
		$Setting = new MW_WP_Form_Setting( $this->post_id );
		$Mail = new MW_WP_Form_Mail();

		add_filter(
			'mwform_admin_mail_raw_' . $this->form_key,
			function( $Mail, $values ) use( $self ) {
				$Mail->to = 'mwform_admin_mail_raw_to@example.com';
				return $Mail;
			},
			10, 2
		);
		add_filter(
			'mwform_auto_mail_raw_' . $this->form_key,
			function( $Mail, $values ) use( $self ) {
				$self->assertEquals( $Mail->to, '' );
				$Mail->to = 'mwform_auto_mail_raw_to@example.com';
				return $Mail;
			},
			10, 2
		);
		add_filter(
			'mwform_mail_' . $this->form_key,
			function( $Mail, $values ) use( $self ) {
				$self->assertEquals( $Mail->to, 'mwform_admin_mail_raw_to@example.com' );
				$Mail->to = 'mwform_mail_to@example.com';
				return $Mail;
			},
			10, 2
		);
		add_filter(
			'mwform_admin_mail_' . $this->form_key,
			function( $Mail, $values ) use( $self ) {
				$self->assertEquals( $Mail->to, 'mwform_mail_to@example.com' );
				return $Mail;
			},
			10, 2
		);
		add_filter(
			'mwform_auto_mail_' . $this->form_key,
			function( $Mail, $values ) use( $self ) {
				$self->assertEquals( $Mail->to, 'mwform_auto_mail_raw_to@example.com' );
				return $Mail;
			},
			10, 2
		);

		$Mail_Service = new MW_WP_Form_Mail_Service(
			$Mail, $this->form_key, $Setting
		);
		$Mail_Service->send_admin_mail();
		$Mail_Service->send_reply_mail();

		remove_all_filters( 'mwform_admin_mail_raw_' . $this->form_key );
		remove_all_filters( 'mwform_auto_mail_raw_' . $this->form_key );
		remove_all_filters( 'mwform_mail_' . $this->form_key );
		remove_all_filters( 'mwform_admin_mail_' . $this->form_key );
		remove_all_filters( 'mwform_auto_mail_' . $this->form_key );
	}

	/**
	 */
	public function test_管理者宛メール関連フックのテスト_送信内容に応じてメール設定を書き換える() {
		$self = $this;
		$Setting = new MW_WP_Form_Setting( $this->post_id );
		$Mail = new MW_WP_Form_Mail();

		add_filter(
			'mwform_admin_mail_raw_' . $this->form_key,
			function( $Mail, $values ) use( $self ) {
				$Mail->from = '{メールアドレス}';
				return $Mail;
			},
			10, 2
		);
		add_filter(
			'mwform_admin_mail_' . $this->form_key,
			function( $Mail, $values ) use( $self ) {
				$self->assertEquals( $Mail->from, 'customer@example.com' );
				return $Mail;
			},
			10, 2
		);

		$this->Data->set( 'メールアドレス', 'customer@example.com' );
		$Mail_Service = new MW_WP_Form_Mail_Service(
			$Mail, $this->form_key, $Setting
		);
		$Mail_Service->send_admin_mail();
		$Mail_Service->send_reply_mail();

		remove_all_filters( 'mwform_admin_mail_raw_' . $this->form_key );
		remove_all_filters( 'mwform_admin_mail_' . $this->form_key );
	}

	/**
	 * @group tracking_number
	 */
	public function test_tracking_number() {
		$Setting = new MW_WP_Form_Setting( $this->post_id );
		$Mail = new MW_WP_Form_Mail();

		$Setting->set( 'admin_mail_content', '{' . MWF_Config::TRACKINGNUMBER . '}' );
		$Mail_Service = new MW_WP_Form_Mail_Service(
			$Mail, $this->form_key, $Setting
		);

		$this->assertEquals( 1, $Setting->get_tracking_number() );
		$Mail_Service->update_tracking_number();
		$this->assertEquals( 2, $Setting->get_tracking_number() );
	}

	/**
	 */
	public function test_データベースに保存() {
		$Setting = new MW_WP_Form_Setting( $this->post_id );
		$Mail = new MW_WP_Form_Mail();

		add_filter( 'mwform_is_mail_sended', function() {
			return true;
		} );
		$Setting->set( 'usedb', 1 );
		$Mail_Service = new MW_WP_Form_Mail_Service(
			$Mail, $this->form_key, $Setting
		);
		$Mail_Service->send_admin_mail();
		$Mail_Service->send_reply_mail();

		$posts = get_posts( array(
			'post_type'      => MWF_Functions::get_contact_data_post_type_from_form_id( $Setting->get( 'post_id' ) ),
			'posts_per_page' => -1,
		) );
		$this->assertEquals( 1, count( $posts ) );

		$meta_data = get_post_meta( $posts[0]->ID, MWF_config::CONTACT_DATA_NAME, true );
		$this->assertNotEmpty( $meta_data );
	}

	public function test_メール内容をフックで変更してもDBには送信したデータが保存される() {
		$self = $this;
		$Setting = new MW_WP_Form_Setting( $this->post_id );
		$Mail = new MW_WP_Form_Mail();

		$this->Data->set( 'お名前', 'foo' );
		$Setting->set( 'usedb', 1 );
		$Setting->set( 'admin_mail_content', '{お名前}' );

		add_filter( 'mwform_is_mail_sended', function() {
			return true;
		} );

		add_filter( 'mwform_admin_mail_' . $this->form_key, function( $Mail, $values, $Data ) use ( $self ) {
			$Mail->body = 'お問い合わせがありました。';
			return $Mail;
		}, 10, 3 );

		$Mail_Service = new MW_WP_Form_Mail_Service(
			$Mail, $this->form_key, $Setting
		);
		$Mail_Service->send_admin_mail();

		$posts = get_posts( array(
			'post_type'      => MWF_Functions::get_contact_data_post_type_from_form_id( $Setting->get( 'post_id' ) ),
			'posts_per_page' => -1,
		) );

		$this->assertEquals( 'foo', get_post_meta( $posts[0]->ID, 'お名前', true ) );

		remove_all_filters( 'mwform_admin_mail_' . $this->form_key );
	}
}
