<?php
class MW_WP_Form_Form_Test extends WP_UnitTestCase {

	/**
	 * test_get_raw
	 * @backupStaticAttributes enabled
	 */
	public function test_get_raw() {
		$post_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$form_key = MWF_Config::NAME . '-' . $post_id;
		$Data = MW_WP_Form_Data::getInstance( $form_key );
		$Form = new MW_WP_Form_Form( $Data );
		$name = 'raw';

		$Data->set( $name, '000-0000' );
		$this->assertEquals( $Form->get_raw( $name ), '000-0000' );
		
		$Data->set( $name, array( '000', '0000' ) );
		$this->assertEquals( $Form->get_raw( $name ), array( '000', '0000' ) );
	}

	/**
	 * test_get_zip_value
	 * @backupStaticAttributes enabled
	 */
	public function test_get_zip_value() {
		$post_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$form_key = MWF_Config::NAME . '-' . $post_id;
		$Data = MW_WP_Form_Data::getInstance( $form_key );
		$Form = new MW_WP_Form_Form( $Data );
		$name = 'zip';

		$Data->set( $name, '000-0000' );
		$this->assertEquals( '000-0000', $Form->get_zip_value( $name ) );

		$Data->set( $name, array( 'data' => array( '000', '0000' ), 'separator' => '-' ) );
		$this->assertEquals( '000-0000', $Form->get_zip_value( $name ) );
	}

	/**
	 * test_get_tel_value
	 * @backupStaticAttributes enabled
	 */
	public function test_get_tel_value() {
		$post_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$form_key = MWF_Config::NAME . '-' . $post_id;
		$Data = MW_WP_Form_Data::getInstance( $form_key );
		$Form = new MW_WP_Form_Form( $Data );
		$name = 'tel';

		$Data->set( $name, '000-0000-0000' );
		$this->assertEquals( '000-0000-0000', $Form->get_tel_value( $name ) );

		$Data->set( $name, array( 'data' => array( '000', '0000', '0000' ), 'separator' => '-' ) );
		$this->assertEquals( '000-0000-0000', $Form->get_tel_value( $name ) );
	}

	/**
	 * test_get_checked_value
	 * @backupStaticAttributes enabled
	 */
	public function test_get_checked_value() {
		$post_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$form_key = MWF_Config::NAME . '-' . $post_id;
		$Data = MW_WP_Form_Data::getInstance( $form_key );
		$Form = new MW_WP_Form_Form( $Data );
		$name = 'check';

		$arr1 = array(
			'AAA' => 'AAA',
			'BBB' => 'BBB',
			'CCC' => 'CCC',
		);

		$Data->set( $name, 'AAA' );
		$this->assertEquals( 'AAA', $Form->get_checked_value( $name, $arr1 ) );

		$Data->set( $name, array( 'data' => array( 'AAA', 'BBB' ), 'separator' => ',' ) );
		$this->assertEquals( 'AAA,BBB', $Form->get_checked_value( $name, $arr1 ) );

		$Data->set( $name, array( 'data' => array( 'AAA', 'BBB', 'DDD' ), 'separator' => ',' ) );
		$this->assertEquals( 'AAA,BBB', $Form->get_checked_value( $name, $arr1 ) );

		$arr2 = array(
			'a' => 'AAA',
			'b' => 'BBB',
			'c' => 'CCC',
		);

		$Data->set( $name, 'AAA' );
		$this->assertNull( $Form->get_checked_value( $name, $arr2 ) );

		$Data->set( $name, array( 'data' => array( 'AAA', 'BBB' ), 'separator' => ',' ) );
		$this->assertEquals( $Form->get_checked_value( $name, $arr2 ), '' );

		$Data->set( $name, array( 'data' => array( 'AAA', 'BBB', 'DDD' ), 'separator' => ',' ) );
		$this->assertSame( '', $Form->get_checked_value( $name, $arr2 ) );

		$Data->set( $name, 'a' );
		$this->assertEquals( 'AAA', $Form->get_checked_value( $name, $arr2 ) );

		$Data->set( $name, array( 'data' => array( 'a', 'b' ), 'separator' => ',' ) );
		$this->assertEquals( 'AAA,BBB', $Form->get_checked_value( $name, $arr2 ) );

		$Data->set( $name, array( 'data' => array( 'a', 'b', 'd' ), 'separator' => ',' ) );
		$this->assertEquals( 'AAA,BBB', $Form->get_checked_value( $name, $arr2 ) );
	}

	/**
	 * test_get_radio_value
	 * @backupStaticAttributes enabled
	 */
	public function test_get_radio_value() {
		$post_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$form_key = MWF_Config::NAME . '-' . $post_id;
		$Data = MW_WP_Form_Data::getInstance( $form_key );
		$Form = new MW_WP_Form_Form( $Data );
		$name = 'radio';

		$arr1 = array(
			'AAA' => 'AAA',
			'BBB' => 'BBB',
			'CCC' => 'CCC',
		);

		$Data->set( $name, 'AAA' );
		$this->assertEquals( $Form->get_radio_value( $name, $arr1 ), 'AAA' );

		$Data->set( $name, 'BBB' );
		$this->assertEquals( $Form->get_radio_value( $name, $arr1 ), 'BBB' );

		$Data->set( $name, 'DDD' );
		$this->assertEquals( $Form->get_radio_value( $name, $arr1 ), '' );

		$arr2 = array(
			'a' => 'AAA',
			'b' => 'BBB',
			'c' => 'CCC',
		);

		$Data->set( $name, 'a' );
		$this->assertEquals( $Form->get_radio_value( $name, $arr2 ), 'AAA' );

		$Data->set( $name, 'b' );
		$this->assertEquals( $Form->get_radio_value( $name, $arr2 ), 'BBB' );

		$Data->set( $name, 'd' );
		$this->assertEquals( $Form->get_radio_value( $name, $arr2 ), '' );
	}

	/**
	 * test_get_selected_value
	 * @backupStaticAttributes enabled
	 */
	public function test_get_selected_value() {
		$post_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$form_key = MWF_Config::NAME . '-' . $post_id;
		$Data = MW_WP_Form_Data::getInstance( $form_key );
		$Form = new MW_WP_Form_Form( $Data );
		$name = 'radio';

		$arr1 = array(
			'AAA' => 'AAA',
			'BBB' => 'BBB',
			'CCC' => 'CCC',
		);

		$Data->set( $name, 'AAA' );
		$this->assertEquals( $Form->get_selected_value( $name, $arr1 ), 'AAA' );

		$Data->set( $name, 'BBB' );
		$this->assertEquals( $Form->get_selected_value( $name, $arr1 ), 'BBB' );

		$Data->set( $name, 'DDD' );
		$this->assertEquals( $Form->get_selected_value( $name, $arr1 ), '' );

		$arr2 = array(
			'a' => 'AAA',
			'b' => 'BBB',
			'c' => 'CCC',
		);

		$Data->set( $name, 'a' );
		$this->assertEquals( $Form->get_selected_value( $name, $arr2 ), 'AAA' );

		$Data->set( $name, 'b' );
		$this->assertEquals( $Form->get_selected_value( $name, $arr2 ), 'BBB' );

		$Data->set( $name, 'd' );
		$this->assertEquals( $Form->get_selected_value( $name, $arr2 ), '' );
	}

	/**
	 * test_get_separated_raw_value
	 * @backupStaticAttributes enabled
	 */
	public function test_get_separated_raw_value() {
		$post_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$form_key = MWF_Config::NAME . '-' . $post_id;
		$Data = MW_WP_Form_Data::getInstance( $form_key );
		$Form = new MW_WP_Form_Form( $Data );
		$name = 'check';

		$arr1 = array(
			'AAA' => 'AAA',
			'BBB' => 'BBB',
			'CCC' => 'CCC',
		);

		$Data->set( $name, 'AAA' );
		$this->assertEquals( 'AAA', $Form->get_separated_raw_value( $name, $arr1 ) );

		$Data->set( $name, array( 'data' => array( 'AAA', 'BBB' ), 'separator' => ',' ) );
		$this->assertEquals( 'AAA,BBB', $Form->get_separated_raw_value( $name, $arr1 ) );

		$Data->set( $name, array( 'data' => array( 'AAA', 'BBB', 'DDD' ), 'separator' => ',' ) );
		$this->assertEquals( 'AAA,BBB', $Form->get_separated_raw_value( $name, $arr1 ) );

		$arr2 = array(
			'a' => 'AAA',
			'b' => 'BBB',
			'c' => 'CCC',
		);

		$Data->set( $name, 'AAA' );
		$this->assertSame( '', $Form->get_separated_raw_value( $name, $arr2 ) );

		$Data->set( $name, array( 'data' => array( 'AAA', 'BBB' ), 'separator' => ',' ) );
		$this->assertSame( '', $Form->get_separated_raw_value( $name, $arr2 ) );

		$Data->set( $name, array( 'data' => array( 'AAA', 'BBB', 'DDD' ), 'separator' => ',' ) );
		$this->assertSame( '', $Form->get_separated_raw_value( $name, $arr2 ) );

		$Data->set( $name, 'a' );
		$this->assertEquals( 'a', $Form->get_separated_raw_value( $name, $arr2 ) );

		$Data->set( $name, array( 'data' => array( 'a', 'b' ), 'separator' => ',' ) );
		$this->assertEquals( 'a,b', $Form->get_separated_raw_value( $name, $arr2 ) );

		$Data->set( $name, array( 'data' => array( 'a', 'b', 'd' ), 'separator' => ',' ) );
		$this->assertEquals( 'a,b', $Form->get_separated_raw_value( $name, $arr2 ) );
	}

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
			' かきくけこ' => ' かきくけこ',
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
			'abc ' => ' あいうえお',
			' def ' => ' かきくけこ',
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

		$children = array(
			'abc::あいうえお',
			'abc:::あいうえお',
			'def:def::かきくけこ',
			'::ghi:さしすせそ',
		);
		$this->assertEquals( array(
			'abc:あいうえお'  => 'abc:あいうえお',
			'abc::あいうえお' => 'abc::あいうえお',
			'def'   => 'def:かきくけこ',
			':ghi' => 'さしすせそ',
		), $Field->get_children( $children ) );
	}
}