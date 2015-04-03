<?php
class MW_WP_Form_Admin_Test extends WP_UnitTestCase {

	/**
	 * @var MW_WP_Form_Admin
	 */
	protected $Admin;

	/**
	 * setUp
	 */
	public function setUp() {
		parent::setUp();

		// 問い合わせデータをデータベースに保存する設定のフォームを作成
		$post_ids = $this->factory->post->create_many(
			5,
			array(
				'post_type' => MWF_Config::NAME,
			)
		);
		foreach ( $post_ids as $post_id ) {
			$meta = array(
				'usedb' => 1,
			);
			update_post_meta( $post_id, MWF_Config::NAME, $meta );
			break;
		}
		$this->Admin = new MW_WP_Form_Admin();
	}

	/**
	 * @group get_forms
	 */
	public function test_get_forms() {
		$forms = $this->Admin->get_forms();
		$this->assertEquals( 5, count( $forms ) );
	}

	/**
	 * @group get_forms_using_database
	 */
	public function test_get_forms_using_database() {
		$forms  = array();
		$_forms = $this->Admin->get_forms();
		foreach ( $_forms as $form ) {
			$Setting = new MW_WP_Form_Setting( $form->ID );
			if ( !$Setting->get( 'usedb' ) ) {
				continue;
			}
			$forms[] = $form;
		}
		$forms_using_database = $this->Admin->get_forms_using_database();
		$this->assertEquals( 1, count( $forms_using_database ) );
		$this->assertEquals( count( $forms ), count( $forms_using_database ) );
	}
}