<?php
class MW_WP_Form_Form_Test extends WP_UnitTestCase {

	/**
	 * MW_WP_Form_Field::get_children() のテスト
	 */
	public function test_get_children() {
		$Field = new MW_WP_Form_Field_Checkbox();

		$children = 'あいうえお,かきくけこ';
		$this->assertEquals( array(
			'あいうえお' => 'あいうえお',
			'かきくけこ' => 'かきくけこ',
		), $Field->get_children( $children ) );
		
		$children = 'あいうえお, かきくけこ';
		$this->assertEquals( array(
			'あいうえお' => 'あいうえお',
			'かきくけこ' => 'かきくけこ',
		), $Field->get_children( $children ) );

		$children = array(
			'あいうえお',
			'かきくけこ',
		);
		$this->assertEquals( array(
			'あいうえお' => 'あいうえお',
			'かきくけこ' => 'かきくけこ',
		), $Field->get_children( $children ) );

		$children = 'abc:あいうえお,def:かきくけこ';
		$this->assertEquals( array(
			'abc' => 'あいうえお',
			'def' => 'かきくけこ',
		), $Field->get_children( $children ) );
		
		$children = 'abc : あいうえお, def : かきくけこ';
		$this->assertEquals( array(
			'abc' => 'あいうえお',
			'def' => 'かきくけこ',
		), $Field->get_children( $children ) );

		$children = array(
			'abc:あいうえお',
			'def:かきくけこ',
		);
		$this->assertEquals( array(
			'abc' => 'あいうえお',
			'def' => 'かきくけこ',
		), $Field->get_children( $children ) );

		$children = 'abc:あいうえお:！,def:かきくけこ:！';
		$this->assertEquals( array(
			'abc' => 'あいうえお:！',
			'def' => 'かきくけこ:！',
		), $Field->get_children( $children ) );
	}
}