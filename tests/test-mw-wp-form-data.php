<?php
class MW_WP_Form_Data_Test extends WP_UnitTestCase {

	/**
	 * MW_WP_Form_Data::getSeparatedValue() のテスト
	 */
	public function test_getSeparatedValue() {
		$post_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$form_key = MWF_Config::NAME . '-' . $post_id;
		$Data = MW_WP_Form_Data::getInstance( $form_key );
		$Data->clear_values();

		$Data->set( '郵便番号', array(
			'separator' => '-',
			'data'      => array( '123', '1234' ),
		) );
		$this->assertEquals( '123-1234', $Data->getSeparatedValue( '郵便番号' ) );

		$Data->set( '郵便番号', array(
			'separator' => '-',
			'data'      => '123-1234',
		) );
		$this->assertNull( $Data->getSeparatedValue( '郵便番号' ) );

		$Data->set( '郵便番号', array(
			'separator' => '-',
			'data'      => array( '', '' ),
		) );
		$this->assertEquals( '', $Data->getSeparatedValue( '郵便番号' ) );
	}
}