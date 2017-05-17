<?php
class MW_WP_Form_Field_Checkbox_Test extends WP_UnitTestCase {

	/**
	 * @var string
	 */
	protected $form_key;

	/**
	 * @var MW_WP_Form_Field_Checkbox
	 */
	protected $Field;

	/**
	 * @var MW_WP_Form_Data
	 */
	protected $Data;

	/**
	 * setUp
	 */
	public function setUp() {
		parent::setUp();
		$this->Field    = new MW_WP_Form_Field_Checkbox();
		$this->form_key = MWF_Config::NAME . '-1';
		$this->Data     = NEW_MW_WP_Form_Data::connect( $this->form_key );
	}

	public function test_valueが未設定ならmwform_value_フックを実行() {
		$Form     = new MW_WP_Form_Form();
		$view_flg = 'input';
		$Error    = new MW_WP_Form_Error();

		$this->is_through = false;
		$self = $this;
		add_filter( 'mwform_value_' . $this->form_key, function( $value, $name ) use( $self ) {
			if ( $name === 'checkbox' ) {
				$self->is_through = true;
			}
			return $value;
		}, 10, 2 );
		$this->Field->add_shortcode( $Form, $view_flg, $Error, $this->form_key );

		$field = do_shortcode( '[mwform_checkbox name="checkbox" children="a,b"]' );
		$this->assertTrue( $this->is_through );
	}

	public function test_valueが設定されていたらmwform_value_フックを実行しない() {
		$Form     = new MW_WP_Form_Form();
		$view_flg = 'input';
		$Error    = new MW_WP_Form_Error();

		$this->is_through = false;
		$self = $this;
		add_filter( 'mwform_value_' . $this->form_key, function( $value, $name ) use( $self ) {
			if ( $name === 'checkbox' ) {
				$self->is_through = true;
			}
			return $value;
		}, 10, 2 );
		$this->Field->add_shortcode( $Form, $view_flg, $Error, $this->form_key );

		$field = do_shortcode( '[mwform_checkbox name="checkbox" children="a,b" value="a"]' );
		$this->assertFalse( $this->is_through );
	}
}
