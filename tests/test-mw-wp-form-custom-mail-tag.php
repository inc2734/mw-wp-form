<?php
/**
 * mwform_custom_mail_tag フックのテスト
 */
class MW_WP_Form_Custom_Mail_Tag_Test extends WP_UnitTestCase {

	protected $Mail_Service;
	protected $form_id;
	protected $form_key;
	protected $dummy;
	protected $cnt = 0;

	public function setUp() {
		parent::setUp();
		$this->form_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$this->form_key = MWF_Config::NAME . '-' . $this->form_id;
		update_post_meta( $this->form_id, 'mw-wp-form', array(
			'admin_mail_content' => '{custom_tag}',
			'usedb'              => 1,
		) );

		$Mail = new MW_WP_Form_Mail();
		$Data = MW_WP_Form_Data::getInstance( $this->form_key );
		$Data->set( 'メールアドレス', 'inc@2inc.org' );
		$Validation_Rule_Mail = new MW_WP_Form_Validation_Rule_Mail();
		$Validation_Rule_Mail->set_Data( $Data );
		$validation_rules = array(
			'mail' => $Validation_Rule_Mail,
		);
		$Setting = new MW_WP_Form_Setting( $this->form_id );
		$this->Mail_Service = new MW_WP_Form_Mail_Service(
			$Mail, $Data, $this->form_key, $validation_rules, $Setting
		);
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_mwform_custom_mail_tag() {
		add_filter(
			'mwform_custom_mail_tag_' . $this->form_key,
			array( $this, 'custom_tag' ),
			10,
			3
		);
		$this->Mail_Service->send_admin_mail();
		$this->Mail_Service->send_reply_mail();
		$this->assertEquals( 'hoge', $this->dummy );
	}
	public function custom_tag( $value, $key, $insert_id ) {
		if ( $key === 'custom_tag' ) {
			$this->dummy = 'hoge';
			return $this->dummy;
		}
		return $value;
	}

	/**
	 * @backupStaticAttributes enabled
	 */
	public function test_mwform_custom_mail_tag_データベース保存使用時に1回だけ実行ならtrue() {
		add_filter(
			'mwform_custom_mail_tag_' . $this->form_key,
			array( $this, 'count_up_custom_tag' ),
			10,
			3
		);
		$this->Mail_Service->send_admin_mail();
		$this->Mail_Service->send_reply_mail();
		$this->assertEquals( $this->cnt, 1 );
		// ついでにちゃんと保存されているかもテスト
		$this->assertEquals( count( get_posts( array(
			'post_type'      => MWF_Config::DBDATA . $this->form_id,
			'posts_per_page' => -1,
		) ) ), 1 );
	}
	public function count_up_custom_tag( $value, $key, $insert_id ) {
		if ( $key === 'custom_tag' ) {
			$this->cnt ++;
		}
		return $value;
	}
}
