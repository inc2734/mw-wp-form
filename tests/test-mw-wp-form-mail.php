<?php
class MW_WP_Form_Mail_Test extends WP_UnitTestCase {

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_clone_data_for_setted_auto_replay_mail() {
		$post_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$form_key = MWF_Config::NAME . '-' . $post_id;

		add_filter( 'mwform_auto_mail_raw_' . $form_key,
			array( $this, 'clone_data_for_auto_replay_mail_mwform_auto_mail_raw' ),
			10, 3
		);

		add_filter( 'mwform_auto_mail_' . $form_key,
			array( $this, 'clone_data_for_auto_replay_mail_mwform_auto_mail' ),
			10, 3
		);

		$Mail = new MW_WP_Form_Mail();
		$Data = MW_WP_Form_Data::getInstance( $form_key );
		$Data->set( 'メールアドレス', 'inc@2inc.org' );
		$Validation_Rule_Mail = new MW_WP_Form_Validation_Rule_Mail();
		$Validation_Rule_Mail->set_Data( $Data );
		$validation_rules = array(
			'mail' => $Validation_Rule_Mail,
		);
		$Setting = new MW_WP_Form_Setting( $post_id );
		$Setting->set( 'automatic_reply_email', 'メールアドレス' );

		$Mail_Service = new MW_WP_Form_Mail_Service(
			$Mail, $Data, $form_key, $validation_rules, $Setting
		);
		$Mail_Service->send_admin_mail();
		$Mail_Service->send_reply_mail();
	}
	public function clone_data_for_auto_replay_mail_mwform_auto_mail_raw( $Mail, $values, $Data ) {
		$this->assertEquals( $Data->get( 'メールアドレス' ), 'inc@2inc.org' );
		$Data->set( 'メールアドレス', 'hoge' );
		return $Mail;
	}
	public function clone_data_for_auto_replay_mail_mwform_auto_mail( $Mail, $values, $Data ) {
		$this->assertEquals( $Data->get( 'メールアドレス' ), 'inc@2inc.org' );
		return $Mail;
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_clone_data_for_setted_admin_mail() {
		$post_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$form_key = MWF_Config::NAME . '-' . $post_id;

		add_filter( 'mwform_admin_mail_raw_' . $form_key,
			array( $this, 'clone_data_for_auto_replay_mail_mwform_admin_mail_raw' ),
			10, 3
		);

		add_filter( 'mwform_admin_mail_' . $form_key,
			array( $this, 'clone_data_for_auto_replay_mail_mwform_admin_mail' ),
			10, 3
		);

		$Mail = new MW_WP_Form_Mail();
		$Data = MW_WP_Form_Data::getInstance( $form_key );
		$Data->set( 'メールアドレス', 'inc@2inc.org' );
		$Validation_Rule_Mail = new MW_WP_Form_Validation_Rule_Mail();
		$Validation_Rule_Mail->set_Data( $Data );
		$validation_rules = array(
			'mail' => $Validation_Rule_Mail,
		);
		$Setting = new MW_WP_Form_Setting( $post_id );
		$Setting->set( 'automatic_reply_email', 'メールアドレス' );

		$Mail_Service = new MW_WP_Form_Mail_Service(
			$Mail, $Data, $form_key, $validation_rules, $Setting
		);
		$Mail_Service->send_admin_mail();
		$Mail_Service->send_reply_mail();
	}
	public function clone_data_for_auto_replay_mail_mwform_admin_mail_raw( $Mail, $values, $Data ) {
		$this->assertEquals( $Data->get( 'メールアドレス' ), 'inc@2inc.org' );
		$Data->set( 'メールアドレス', 'hoge' );
		return $Mail;
	}
	public function clone_data_for_auto_replay_mail_mwform_admin_mail( $Mail, $values, $Data ) {
		$this->assertEquals( $Data->get( 'メールアドレス' ), 'inc@2inc.org' );
		return $Mail;
	}

	/**
	 * メール関連のフックのテスト（自動返信設定あり）
	 * @backupStaticAttributes enabled
	 */
	public function test_mail_hooks_for_setted_auto_replay_mail() {
		$post_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$form_key = MWF_Config::NAME . '-' . $post_id;

		add_filter(
			'mwform_admin_mail_raw_' . $form_key,
			array( $this, 'for_setted_auto_replay_mail_mwform_admin_mail_raw' ),
			10, 2
		);

		add_filter(
			'mwform_mail_' . $form_key,
			array( $this, 'for_setted_auto_replay_mail_mwform_mail' ),
			10, 2
		);

		add_filter( 'mwform_admin_mail_' . $form_key,
			array( $this, 'for_setted_auto_replay_mail_mwform_admin_mail' ),
			10, 2
		);

		add_filter( 'mwform_auto_mail_raw_' . $form_key,
			array( $this, 'for_setted_auto_replay_mail_mwform_auto_mail_raw' ),
			10, 2
		);

		add_filter( 'mwform_auto_mail_' . $form_key,
			array( $this, 'for_setted_auto_replay_mail_mwform_auto_mail' ),
			10, 2
		);

		$Mail = new MW_WP_Form_Mail();
		$Data = MW_WP_Form_Data::getInstance( $form_key );
		$Data->set( 'メールアドレス', 'inc@2inc.org' );

		$Validation_Rule_Mail = new MW_WP_Form_Validation_Rule_Mail();
		$Validation_Rule_Mail->set_Data( $Data );
		$validation_rules = array(
			'mail' => $Validation_Rule_Mail,
		);

		$Setting = new MW_WP_Form_Setting( $post_id );
		$Setting->set( 'automatic_reply_email', 'メールアドレス' );

		$Mail_Service = new MW_WP_Form_Mail_Service(
			$Mail, $Data, $form_key, $validation_rules, $Setting
		);
		$Mail_Service->send_admin_mail();
		$Mail_Service->send_reply_mail();
	}
	public function for_setted_auto_replay_mail_mwform_admin_mail_raw( $Mail, $values ) {
		$Mail->to   = 'hoge1@example.com';
		$Mail->from = 'from1@example.com';
		return $Mail;
	}
	public function for_setted_auto_replay_mail_mwform_mail( $Mail, $values ) {
		$this->assertEquals( $Mail->to, 'hoge1@example.com' );
		$Mail->to = 'hoge2@example.com';
		return $Mail;
	}
	public function for_setted_auto_replay_mail_mwform_admin_mail( $Mail, $values ) {
		$this->assertEquals( $Mail->to, 'hoge2@example.com' );
		return $Mail;
	}
	public function for_setted_auto_replay_mail_mwform_auto_mail_raw( $Mail, $values ) {
		$this->assertEquals( $Mail->to, 'inc@2inc.org' );
		$this->assertEquals( $Mail->from, get_bloginfo( 'admin_email' ) );
		$Mail->from = 'from2@example.com';
		return $Mail;
	}
	public function for_setted_auto_replay_mail_mwform_auto_mail( $Mail, $values ) {
		$this->assertEquals( $Mail->from, 'from2@example.com' );
		return $Mail;
	}

	/**
	 * メール関連のフックのテスト（自動返信設定なし）
	 * @backupStaticAttributes enabled
	 */
	public function test_mail_hooks_for_no_set_auto_replay_mail() {
		$post_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$form_key = MWF_Config::NAME . '-' . $post_id;

		add_filter(
			'mwform_admin_mail_raw_' . $form_key,
			array( $this, 'for_no_set_auto_replay_mail_mwform_admin_mail_raw' ),
			10, 2
		);

		add_filter(
			'mwform_mail_' . $form_key,
			array( $this, 'for_no_set_auto_replay_mail_mwform_mail' ),
			10, 2
		);

		add_filter(
			'mwform_admin_mail_' . $form_key,
			array( $this, 'for_no_set_auto_replay_mail_mwform_admin_mail' ),
			10, 2
		);

		add_filter(
			'mwform_auto_mail_raw_' . $form_key,
			array( $this, 'for_no_set_auto_replay_mail_mwform_auto_mail_raw' ),
			10, 2
		);

		add_filter(
			'mwform_auto_mail_' . $form_key,
			array( $this, 'for_no_set_auto_replay_mail_mwform_auto_mail' ),
			10, 2
		);

		$Mail = new MW_WP_Form_Mail();
		$Data = MW_WP_Form_Data::getInstance( $form_key );

		$Validation_Rule_Mail = new MW_WP_Form_Validation_Rule_Mail();
		$Validation_Rule_Mail->set_Data( $Data );
		$validation_rules = array(
			'mail' => $Validation_Rule_Mail,
		);

		$Setting = new MW_WP_Form_Setting( $post_id );

		$Mail_Service = new MW_WP_Form_Mail_Service(
			$Mail, $Data, $form_key, $validation_rules, $Setting
		);
		$Mail_Service->send_admin_mail();
		$Mail_Service->send_reply_mail();
	}
	public function for_no_set_auto_replay_mail_mwform_admin_mail_raw( $Mail, $values ) {
		$Mail->to   = 'hoge1@example.com';
		$Mail->from = 'from1@example.com';
		return $Mail;
	}
	public function for_no_set_auto_replay_mail_mwform_mail( $Mail, $values ) {
		$this->assertEquals( $Mail->to, 'hoge1@example.com' );
		$Mail->to = 'hoge2@example.com';
		return $Mail;
	}
	public function for_no_set_auto_replay_mail_mwform_admin_mail( $Mail, $values ) {
		$this->assertEquals( $Mail->to, 'hoge2@example.com' );
		return $Mail;
	}
	public function for_no_set_auto_replay_mail_mwform_auto_mail_raw( $Mail, $values ) {
		$this->assertEquals( $Mail->to, '' );
		$this->assertEquals( $Mail->from, get_bloginfo( 'admin_email' ) );
		$Mail->from = 'from2@example.com';
		return $Mail;
	}
	public function for_no_set_auto_replay_mail_mwform_auto_mail( $Mail, $values ) {
		$this->assertEquals( $Mail->from, 'from2@example.com' );
		return $Mail;
	}

	/**
	 * メール関連のフックのテスト（送信内容に応じてメール設定を書き換える）
	 * @backupStaticAttributes enabled
	 */
	public function test_mail_hooks_for_parse_post_content() {
		$post_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$form_key = MWF_Config::NAME . '-' . $post_id;

		add_filter(
			'mwform_admin_mail_raw_' . $form_key,
			array( $this, 'for_parse_post_content_mwform_admin_mail_raw' ),
			10, 2
		);

		add_filter(
			'mwform_admin_mail_' . $form_key,
			array( $this, 'for_parse_post_content_mwform_admin_mail' ),
			10, 2
		);

		$Mail = new MW_WP_Form_Mail();
		$Data = MW_WP_Form_Data::getInstance( $form_key );
		$Data->set( 'メールアドレス', 'customer@example.com' );

		$Validation_Rule_Mail = new MW_WP_Form_Validation_Rule_Mail();
		$Validation_Rule_Mail->set_Data( $Data );
		$validation_rules = array(
			'mail' => $Validation_Rule_Mail,
		);

		$Setting = new MW_WP_Form_Setting( $post_id );

		$Mail_Service = new MW_WP_Form_Mail_Service(
			$Mail, $Data, $form_key, $validation_rules, $Setting
		);
		$Mail_Service->send_admin_mail();
		$Mail_Service->send_reply_mail();
	}
	public function for_parse_post_content_mwform_admin_mail_raw( $Mail, $values ) {
		$Mail->from = '{メールアドレス}';
		return $Mail;
	}
	public function for_parse_post_content_mwform_admin_mail( $Mail, $values ) {
		$this->assertEquals( $Mail->from, 'customer@example.com' );
		return $Mail;
	}
}