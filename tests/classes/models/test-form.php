<?php
class MW_WP_Form_Form_Test extends WP_UnitTestCase {

	public function tear_down() {
		parent::tear_down();
		_delete_all_data();
	}

	/**
	 * @test
	 * @group separator
	 */
	public function separator() {
		$Form = new MW_WP_Form_Form();
		$this->assertEquals(
			$Form->hidden( 'name-1[separator]', ',' ),
			$Form->separator( 'name-1', ',' )
		);
	}

	/**
	 * @test
	 * @group children
	 */
	public function children() {
		$Form = new MW_WP_Form_Form();
		$children = array(
			'value-1' => 'value-1-label',
		);
		$this->assertEquals(
			$Form->hidden( '__children[name-1][]', json_encode( $children ) ),
			$Form->children( 'name-1', $children )
		);
	}

	/**
	 * @test
	 * @group generate_attributes
	 */
	public function generate_attributes() {
		$Form = new MW_WP_Form_Form();
		// Pattern: conv-half-alphanumeric
		$this->assertEquals(
			'data-conv-half-alphanumeric="true"',
			trim( $Form->generate_attributes( array(
				'conv-half-alphanumeric' => 'true',
			) ) )
		);

		// Pattern: null
		$this->assertEquals(
			'',
			trim( $Form->generate_attributes( array(
				'conv-half-alphanumeric' => null,
			) ) )
		);

		// Pattern: other
		$this->assertEquals(
			'size="10" maxlength="10"',
			trim( $Form->generate_attributes( array(
				'size'      => 10,
				'maxlength' => 10,
			) ) )
		);
	}

	/**
	 * @test
	 * @group remove_newline_space
	 */
	public function remove_newline_space() {
		$Form = new MW_WP_Form_Form();
		$this->assertEquals(
			'<input type="text" name="name-1" />' . "\n" . '     <input type="text" name="name-1" />',
			$Form->remove_newline_space(
				'<input type="text"      name="name-1" />' . "\n" . '     <input type="text" name="name-1"      />'
			)
		);
	}
}
