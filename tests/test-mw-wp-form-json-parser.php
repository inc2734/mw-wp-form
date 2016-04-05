<?php
/**
 * キーと値は必ず同じクォーテーション
 * いずれかはクォーテーション無しの場合はある
 */
class MW_WP_Form_Json_Parser_Test extends WP_UnitTestCase {

	/**
	 * @group json_parser
	 */
	public function test__値が数値() {
		$js = '"minDate": "+1"';
		$Json_Parser = new MW_WP_Form_Json_Parser( $js );
		$js = $Json_Parser->create_json();
		$this->assertEquals( $js, '{"minDate":1}' );

		$js = '"minDate": "-1"';
		$Json_Parser = new MW_WP_Form_Json_Parser( $js );
		$js = $Json_Parser->create_json();
		$this->assertEquals( $js, '{"minDate":-1}' );

		$js = '"minDate": -1';
		$Json_Parser = new MW_WP_Form_Json_Parser( $js );
		$js = $Json_Parser->create_json();
		$this->assertEquals( $js, '{"minDate":-1}' );
	}

	/**
	 * @group json_parser
	 */
	public function test__値が文字列() {
		$js = '"minDate": "-1w"';
		$Json_Parser = new MW_WP_Form_Json_Parser( $js );
		$js = $Json_Parser->create_json();
		$this->assertEquals( $js, '{"minDate":"-1w"}' );
	}

	/**
	 * @group json_parser
	 */
	public function test__値が真偽値() {
		$js = '"autoSize": true';
		$Json_Parser = new MW_WP_Form_Json_Parser( $js );
		$js = $Json_Parser->create_json();
		$this->assertEquals( $js, '{"autoSize":true}' );

		$js = '"autoSize": false';
		$Json_Parser = new MW_WP_Form_Json_Parser( $js );
		$js = $Json_Parser->create_json();
		$this->assertEquals( $js, '{"autoSize":false}' );

		$js = '"autoSize": "true"';
		$Json_Parser = new MW_WP_Form_Json_Parser( $js );
		$js = $Json_Parser->create_json();
		$this->assertEquals( $js, '{"autoSize":true}' );
	}

	/**
	 * @group json_parser
	 */
	public function test__値がnull() {
		$js = '"minDate": null';
		$Json_Parser = new MW_WP_Form_Json_Parser( $js );
		$js = $Json_Parser->create_json();
		$this->assertEquals( $js, '{"minDate":null}' );

		$js = '"minDate": "null"';
		$Json_Parser = new MW_WP_Form_Json_Parser( $js );
		$js = $Json_Parser->create_json();
		$this->assertEquals( $js, '{"minDate":null}' );
	}

	/**
	 * @group json_parser
	 */
	public function test__キーがダブル値もダブル() {
		$js = '"minDate": "+1"';
		$Json_Parser = new MW_WP_Form_Json_Parser( $js );
		$js = $Json_Parser->create_json();
		$this->assertEquals( $js, '{"minDate":1}' );
	}

	/**
	 * @group json_parser
	 */
	public function test__キーがダブル値がクォート無し() {
		$js = '"minDate": +1';
		$Json_Parser = new MW_WP_Form_Json_Parser( $js );
		$js = $Json_Parser->create_json();
		$this->assertEquals( $js, '{"minDate":1}' );
	}

	/**
	 * @group json_parser
	 */
	public function test__キーがシングル値がクォート無し() {
		$js = "'minDate': +1";
		$Json_Parser = new MW_WP_Form_Json_Parser( $js );
		$js = $Json_Parser->create_json();
		$this->assertEquals( $js, '{"minDate":1}' );
	}

	/**
	 * @group json_parser
	 */
	public function test__キーがシングル値もシングル() {
		$js = "'minDate': '+1'";
		$Json_Parser = new MW_WP_Form_Json_Parser( $js );
		$js = $Json_Parser->create_json();
		$this->assertEquals( $js, '{"minDate":1}' );
	}

	/**
	 * @group json_parser
	 */
	public function test__キーがクォート無し値がダブル() {
		$js = 'minDate: "+1"';
		$Json_Parser = new MW_WP_Form_Json_Parser( $js );
		$js = $Json_Parser->create_json();
		$this->assertEquals( $js, '{"minDate":1}' );
	}

	/**
	 * @group json_parser
	 */
	public function test__キーがクォート無し値がシングル() {
		$js = "minDate: '+1'";
		$Json_Parser = new MW_WP_Form_Json_Parser( $js );
		$js = $Json_Parser->create_json();
		$this->assertEquals( $js, '{"minDate":1}' );
	}

	/**
	 * @group json_parser
	 */
	public function test__キーがクォート無し値もクォート無し() {
		$js = "minDate: +1";
		$Json_Parser = new MW_WP_Form_Json_Parser( $js );
		$js = $Json_Parser->create_json();
		$this->assertEquals( $js, '{"minDate":1}' );
	}

	/**
	 * @group json_parser
	 */
	public function test__値にコロンを含む() {
		$js = "yearRange: '-nn:+nn'";
		$Json_Parser = new MW_WP_Form_Json_Parser( $js );
		$js = $Json_Parser->create_json();
		$this->assertEquals( $js, '{"yearRange":"-nn:+nn"}' );

		$js = 'yearRange: "-nn:+nn"';
		$Json_Parser = new MW_WP_Form_Json_Parser( $js );
		$js = $Json_Parser->create_json();
		$this->assertEquals( $js, '{"yearRange":"-nn:+nn"}' );
	}

	/**
	 * @group json_parser
	 */
	public function test__multi_options() {
		$js = "minDate: '+1m +7d', maxDate: '+1m +8d'";
		$Json_Parser = new MW_WP_Form_Json_Parser( $js );
		$js = $Json_Parser->create_json();
		$this->assertEquals( $js, '{"minDate":"+1m +7d","maxDate":"+1m +8d"}' );
	}
}
