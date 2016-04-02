<?php
class MW_WP_Form_Mail_Service_Test extends WP_UnitTestCase {

	/**
	 * @var string
	 */
	protected $form_key;

	/**
	 * @var MW_WP_Form_Setting
	 */
	protected $Setting;

	/**
	 * @var MW_WP_Form_Mail
	 */
	protected $Mail;

	/**
	 * @var MW_WP_Form_Data
	 */
	protected $Data;

	/**
	 * setUp
	 */
	public function setUp() {
		parent::setUp();

		$post_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$this->form_key = MWF_Config::NAME . '-' . $post_id;
		$this->Mail = new MW_WP_Form_Mail();
		$this->Data = MW_WP_Form_Data::getInstance( $this->form_key );
		$Validation_Rule_Mail = new MW_WP_Form_Validation_Rule_Mail();
		$Validation_Rule_Mail->set_Data( $this->Data );
		$this->Setting = new MW_WP_Form_Setting( $post_id );
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
		$this->Setting->set( 'automatic_reply_email', 'メールアドレス' );
		$Mail_Service = new MW_WP_Form_Mail_Service(
			$this->Mail, $this->form_key, $this->Setting
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
		$this->Setting->set( 'automatic_reply_email', 'メールアドレス' );
		$Mail_Service = new MW_WP_Form_Mail_Service(
			$this->Mail, $this->form_key, $this->Setting
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
				$self->assertEquals( $Mail->to , 'info@example.com' );
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
		$this->Setting->set( 'automatic_reply_email', 'メールアドレス' );
		$Mail_Service = new MW_WP_Form_Mail_Service(
			$this->Mail, $this->form_key, $this->Setting
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
			$this->Mail, $this->form_key, $this->Setting
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
			$this->Mail, $this->form_key, $this->Setting
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
		$this->Setting->set( 'admin_mail_content', '{' . MWF_Config::TRACKINGNUMBER . '}' );
		$Mail_Service = new MW_WP_Form_Mail_Service(
			$this->Mail, $this->form_key, $this->Setting
		);

		$this->assertEquals( 1, $this->Setting->get_tracking_number() );
		$Mail_Service->update_tracking_number();
		$this->assertEquals( 2, $this->Setting->get_tracking_number() );
	}

	/**
	 */
	public function test_データベースに保存() {
		$this->Setting->set( 'usedb', 1 );
		$Mail_Service = new MW_WP_Form_Mail_Service(
			$this->Mail, $this->form_key, $this->Setting
		);
		$Mail_Service->send_admin_mail();
		$Mail_Service->send_reply_mail();

		$posts = get_posts( array(
			'post_type'      => MWF_Functions::get_contact_data_post_type_from_form_id( $this->Setting->get( 'post_id' ) ),
			'posts_per_page' => -1,
		) );
		$this->assertEquals( 1, count( $posts ) );

		$meta_data = get_post_meta( $posts[0]->ID, MWF_config::CONTACT_DATA_NAME, true );
		$this->assertNotEmpty( $meta_data );
	}
}
