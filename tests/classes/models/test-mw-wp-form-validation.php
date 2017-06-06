<?php
class MW_WP_Form_Validation_Test extends WP_UnitTestCase {

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
	 * @group set_rule
	 */
	public function set_rule() {
		$form_id    = $this->_create_form();
		$form_key   = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Validation = new MW_WP_Form_Validation( $form_key );

		$Validation->set_rule( 'name-1', 'noempty', array( 'message' => 'message' ) );
		$this->assertTrue( $Validation->is_valid_validation_settings() );
	}

	/**
	 * @test
	 * @group is_valid
	 */
	public function is_valid__no_post() {
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Validation = new MW_WP_Form_Validation( $form_key );
		$Validation->set_rule( 'name-1', 'noempty', array( 'message' => 'message' ) );
		$Validation->set_rule( 'name-1', 'numeric', array( 'message' => 'message' ) );
		$Validation->set_rule( 'name-2', 'alpha', array( 'message' => 'message' ) );
		$Validation->set_rule( 'name-3', 'numeric', array( 'message' => 'message' ) );
		//$this->assertTrue( $Validation->is_valid() );
	}

	/**
	 * @test
	 * @group is_valid
	 */
	public function is_valid__all_correct() {
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Data = MW_WP_Form_Data::connect( $form_key, array(
			'name-1' => '12345',
			'name-2' => 'abcde',
			'name-3' => '',
		) );
		$Validation = new MW_WP_Form_Validation( $form_key );
		$Validation->set_rule( 'name-1', 'noempty', array( 'message' => 'message' ) );
		//$Validation->set_rule( 'name-1', 'numeric', array( 'message' => 'message' ) );
		$Validation->set_rule( 'name-2', 'alpha', array( 'message' => 'message' ) );
		//$Validation->set_rule( 'name-3', 'numeric', array( 'message' => 'message' ) );
		$this->assertTrue( $Validation->is_valid() );
	}

	/**
	 * @test
	 * @group is_valid
	 */
	public function is_valid__incorrect() {
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Data = MW_WP_Form_Data::connect( $form_key, array(
			'name-1' => '12345',
			'name-2' => 'abcde',
			'name-3' => 'abcde',
		) );
		$Validation = new MW_WP_Form_Validation( $form_key );
		$Validation->set_rule( 'name-1', 'noempty', array( 'message' => 'message' ) );
		$Validation->set_rule( 'name-1', 'numeric', array( 'message' => 'message' ) );
		$Validation->set_rule( 'name-2', 'alpha', array( 'message' => 'message' ) );
		$Validation->set_rule( 'name-3', 'numeric', array( 'message' => 'message' ) );
		$this->assertFalse( $Validation->is_valid() );
	}

	/**
	 * @test
	 * @group is_valid_field
	 */
	public function is_valid_field__no_post() {
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Validation = new MW_WP_Form_Validation( $form_key );
		$Validation->set_rule( 'name-1', 'noempty', array( 'message' => 'message' ) );
		$Validation->set_rule( 'name-1', 'numeric', array( 'message' => 'message' ) );
		$this->assertTrue( $Validation->is_valid_field( 'name-1' ) );
	}

	/**
	 * @test
	 * @group is_valid_field
	 */
	public function is_valid_field() {
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Data = MW_WP_Form_Data::connect( $form_key, array(
			'name-1' => '12345',
			'name-2' => 'abcde',
			'name-3' => 'fghij',
		) );
		$Validation = new MW_WP_Form_Validation( $form_key );
		$Validation->set_rule( 'name-1', 'noempty', array( 'message' => 'message' ) );
		$Validation->set_rule( 'name-1', 'numeric', array( 'message' => 'message' ) );
		$Validation->set_rule( 'name-2', 'alpha', array( 'message' => 'message' ) );
		$Validation->set_rule( 'name-3', 'numeric', array( 'message' => 'message' ) );
		$this->assertTrue( $Validation->is_valid_field( 'name-1' ) );
		$this->assertTrue( $Validation->is_valid_field( 'name-2' ) );
		$this->assertFalse( $Validation->is_valid_field( 'name-3' ) );
	}
}
