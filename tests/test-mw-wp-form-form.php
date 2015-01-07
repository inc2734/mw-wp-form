<?php
class MW_WP_Form_Form_Test extends WP_UnitTestCase {

	/**
	 * MW_WP_Form_Field::getChildren() のテスト
	 */
	public function test_getChildren() {
		$Field = new MW_WP_Form_Field_Checkbox();

		$children = 'あいうえお,かきくけこ';
		$this->assertEquals( array(
			'あいうえお' => 'あいうえお',
			'かきくけこ' => 'かきくけこ',
		), $Field->getChildren( $children ) );
		
		$children = 'あいうえお, かきくけこ';
		$this->assertEquals( array(
			'あいうえお' => 'あいうえお',
			'かきくけこ' => 'かきくけこ',
		), $Field->getChildren( $children ) );

		$children = array(
			'あいうえお',
			'かきくけこ',
		);
		$this->assertEquals( array(
			'あいうえお' => 'あいうえお',
			'かきくけこ' => 'かきくけこ',
		), $Field->getChildren( $children ) );

		$children = 'abc:あいうえお,def:かきくけこ';
		$this->assertEquals( array(
			'abc' => 'あいうえお',
			'def' => 'かきくけこ',
		), $Field->getChildren( $children ) );
		
		$children = 'abc : あいうえお, def : かきくけこ';
		$this->assertEquals( array(
			'abc' => 'あいうえお',
			'def' => 'かきくけこ',
		), $Field->getChildren( $children ) );

		$children = array(
			'abc:あいうえお',
			'def:かきくけこ',
		);
		$this->assertEquals( array(
			'abc' => 'あいうえお',
			'def' => 'かきくけこ',
		), $Field->getChildren( $children ) );

		$children = 'abc:あいうえお:！,def:かきくけこ:！';
		$this->assertEquals( array(
			'abc' => 'あいうえお:！',
			'def' => 'かきくけこ:！',
		), $Field->getChildren( $children ) );
	}
}