<?php
class MW_WP_Form_Setting_Test extends WP_UnitTestCase {

	public function tear_down() {
		parent::tear_down();
		_delete_all_data();
	}

	protected function _create_form() {
		$form_id = $this->factory->post->create(
			array(
				'post_type' => MWF_Config::NAME,
			)
		);

		update_post_meta( $form_id, MWF_Config::NAME, array(
			'mail_subject' => 'mail_subject',
			'mail_from'    => 'mail_from',
			'mail_sender'  => 'mail_sender',
			'mail_content' => 'mail_content',
		) );

		return $form_id;
	}

	/**
	 * @test
	 * @group get
	 */
	public function get() {
		$form_id = $this->_create_form();
		$Setting = new MW_WP_Form_Setting( $form_id );
		$this->assertEquals( $form_id, $Setting->get( 'post_id' ) );
		$this->assertEquals( 'mail_subject', $Setting->get( 'mail_subject' ) );
	}

	/**
	 * @test
	 * @group set
	 */
	public function set() {
		$form_id = $this->_create_form();
		$Setting = new MW_WP_Form_Setting( $form_id );
		$Setting->set( 'mail_subject', 'mail_subject_2' );
		$Setting->set( 'dummy', 'dummy' );
		$this->assertEquals( 'mail_subject_2', $Setting->get( 'mail_subject' ) );
		$this->assertNull( $Setting->get( 'dummy' ) );
	}

	/**
	 * @test
	 * @group sets
	 */
	public function sets() {
		$form_id = $this->_create_form();
		$Setting = new MW_WP_Form_Setting( $form_id );
		$Setting->sets( array(
			'mail_subject' => 'mail_subject_2',
			'dummy'        => 'dummy',
		) );
		$this->assertEquals( 'mail_subject_2', $Setting->get( 'mail_subject' ) );
		$this->assertNull( $Setting->get( 'dummy' ) );
	}

	/**
	 * @test
	 * @group save
	 */
	public function save() {
		$form_id = $this->_create_form();
		$Setting = new MW_WP_Form_Setting( $form_id );
		$Setting->set( 'mail_subject', 'mail_subject_2' );
		$Setting->set( 'dummy', 'dummy' );

		$meta = get_post_meta( $form_id, MWF_Config::NAME, true );
		$this->assertEquals( 'mail_subject', $meta['mail_subject'] );
		$this->assertTrue( ! isset( $meta['dummy'] ) );

		$Setting->save();
		$meta = get_post_meta( $form_id, MWF_Config::NAME, true );
		$this->assertEquals( 'mail_subject_2', $meta['mail_subject'] );
		$this->assertTrue( ! isset( $meta['dummy'] ) );
	}

	/**
	 * @test
	 * @group get_posts
	 */
	public function get_posts() {
		// Pattern: don't have form
		$Setting = new MW_WP_Form_Setting( 'dummy' );
		$this->assertSame( array(), $Setting->get_posts() );

		// Pattern: has forms
		$form_id = $this->_create_form();
		$Setting = new MW_WP_Form_Setting( $form_id );
		$this->assertEquals( get_posts( array( 'post_type' => MWF_Config::NAME ) ), $Setting->get_posts() );
	}

	/**
	 * @test
	 * @group get_tracking_number
	 */
	public function get_tracking_number() {
		$form_id = $this->_create_form();
		$Setting = new MW_WP_Form_Setting( $form_id );

		// Pattern: not set
		$this->assertEquals( 1, $Setting->get_tracking_number() );

		// Pattern: be set
		update_post_meta( $form_id, MWF_Config::TRACKINGNUMBER, 2 );
		$this->assertEquals( 2, $Setting->get_tracking_number() );
	}

	/**
	 * @test
	 * @group update_tracking_number
	 */
	public function update_tracking_number() {
		$form_id = $this->_create_form();
		$Setting = new MW_WP_Form_Setting( $form_id );

		// Pattern: don't have arg
		$Setting->update_tracking_number();
		$this->assertEquals( 2, $Setting->get_tracking_number() );

		// Pattern: arg is numeric
		$Setting->update_tracking_number( 100 );
		$this->assertEquals( 100, $Setting->get_tracking_number() );

		// Pattern: arg is string
		$Setting->update_tracking_number( 'dummy' );
		$this->assertEquals( 100, $Setting->get_tracking_number() );
	}
}
