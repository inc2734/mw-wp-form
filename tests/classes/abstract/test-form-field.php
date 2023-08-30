<?php
class MW_WP_Form_Abstract_Form_field_Test extends WP_UnitTestCase {

	public function tear_down() {
		parent::tear_down();
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
	 * @group mwform_value_{$form_key}
	 */
	public function hook_mwform_value() {
		$self       = $this;
		$form_id    = $this->_create_form();
		$form_key   = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Form_Field = new Dummy_Form_field();
		$Form_Field->initialize( new MW_WP_Form_Form(), $form_key, 'input' );

		add_filter( 'mwform_value_' . $form_key, function( $value, $name ) use( $self ) {
			if ( 'dummy' === $name ) {
				return 'dummy-value';
			}
			return $value;
		}, 10, 2 );

		$field = do_shortcode( '[mwform_dummy name="dummy"]' );
		$this->assertNotFalse( strpos( $field, 'value="dummy-value"' ) );

		$field = do_shortcode( '[mwform_dummy name="dummy" value="value"]' );
		$this->assertNotFalse( strpos( $field, 'value="value"' ) );
	}

	/**
	 * @test
	 * @group get_children
	 */
	public function get_children() {
		$form_id    = $this->_create_form();
		$form_key   = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Form_Field = new Dummy_Form_field();
		$Form_Field->initialize( new MW_WP_Form_Form(), $form_key, 'input' );

		// Pattern: array normal
		$this->assertEquals(
			array(
				'value-1' => 'value-1',
				'value-2' => 'value-2',
			),
			$Form_Field->get_children( array(
				'value-1',
				'value-2'
			) )
		);

		// Pattern: array separated
		$this->assertEquals(
			array(
				'name-1' => 'value-1',
				'name-2' => 'value-2',
			),
			$Form_Field->get_children( array(
				'name-1:value-1',
				'name-2:value-2'
			) )
		);

		// Pattern: array escaped
		$this->assertEquals(
			array(
				'name-1:value-1' => 'name-1:value-1',
				'name-2'         => 'value-2',
			),
			$Form_Field->get_children( array(
				'name-1::value-1',
				'name-2:value-2'
			) )
		);

		// Pattern: string normal
		$this->assertEquals(
			array(
				'value-1' => 'value-1',
				'value-2' => 'value-2',
			),
			$Form_Field->get_children( 'value-1,value-2' )
		);

		// Pattern: string separated
		$this->assertEquals(
			array(
				'name-1' => 'value-1',
				'name-2' => 'value-2',
			),
			$Form_Field->get_children( 'name-1:value-1,name-2:value-2' )
		);

		// Pattern: string escaped
		$this->assertEquals(
			array(
				'name-1:value-1' => 'name-1:value-1',
				'name-2'         => 'value-2',
			),
			$Form_Field->get_children( 'name-1::value-1,name-2:value-2' )
		);
	}

	/**
	 * @test
	 * @group get_display_name
	 */
	public function get_display_name() {
		$form_id    = $this->_create_form();
		$form_key   = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Form_Field = new Dummy_Form_field();
		$Form_Field->initialize( new MW_WP_Form_Form(), $form_key, 'input' );

		$this->assertEquals( 'dummy', $Form_Field->get_display_name() );
	}

	/**
	 * @test
	 * @group get_shortcode_name
	 */
	public function get_shortcode_name() {
		$form_id    = $this->_create_form();
		$form_key   = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Form_Field = new Dummy_Form_field();
		$Form_Field->initialize( new MW_WP_Form_Form(), $form_key, 'input' );

		$this->assertEquals( 'mwform_dummy', $Form_Field->get_shortcode_name() );
	}

	/**
	 * @test
	 * @group get_value_for_generator
	 */
	public function get_value_for_generator() {
		$form_id    = $this->_create_form();
		$form_key   = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Form_Field = new Dummy_Form_field();
		$Form_Field->initialize( new MW_WP_Form_Form(), $form_key, 'input' );

		$this->assertNull(
			$Form_Field->get_value_for_generator( 'foo', array( 'foo' => 'bar', 'value' => 'value-1' ) )
		);

		$this->assertSame(
			'',
			$Form_Field->get_value_for_generator( 'children', array( 'foo' => 'bar', 'value' => 'value-1' ) )
		);

		$this->assertEquals(
			'value-1',
			$Form_Field->get_value_for_generator( 'value', array( 'foo' => 'bar', 'value' => 'value-1' ) )
		);
	}
}

class Dummy_Form_field extends MW_WP_Form_Abstract_Form_Field {
	protected function set_names() {
		return array(
			'shortcode_name' => 'mwform_dummy',
			'display_name'   => __( 'dummy', 'mw-wp-form' ),
		);
	}

	protected function set_defaults() {
		return array(
			'value'    => '',
			'children' => '',
		);
	}

	protected function input_page() {
		$value = $this->Data->get_raw( 'dummy' );
		if ( is_null( $value ) ) {
			$value = $this->atts['value'];
		}
		return sprintf(
			'<input type="dummy" name="dummy" value="%1$s">',
			esc_attr( $value )
		);
	}

	protected function confirm_page() {}
}
