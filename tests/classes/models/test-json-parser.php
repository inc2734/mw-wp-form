<?php
/**
 * キーと値は必ず同じクォーテーション
 * いずれかはクォーテーション無しの場合はある
 */
class MW_WP_Form_Json_Parser_Test extends WP_UnitTestCase {

	/**
	 * @group json_parser
	 */
	public function test__is_numeric() {
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
	public function test__is_string() {
		$js = '"minDate": "-1w"';
		$Json_Parser = new MW_WP_Form_Json_Parser( $js );
		$js = $Json_Parser->create_json();
		$this->assertEquals( $js, '{"minDate":"-1w"}' );
	}

	/**
	 * @group json_parser
	 */
	public function test__is_boolean() {
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
	public function test__is_null() {
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
	public function test__key_and_value_are_double_quotation() {
		$js = '"minDate": "+1"';
		$Json_Parser = new MW_WP_Form_Json_Parser( $js );
		$js = $Json_Parser->create_json();
		$this->assertEquals( $js, '{"minDate":1}' );
	}

	/**
	 * @group json_parser
	 */
	public function test__key_is_double_quotation_value_is_no_quotation() {
		$js = '"minDate": +1';
		$Json_Parser = new MW_WP_Form_Json_Parser( $js );
		$js = $Json_Parser->create_json();
		$this->assertEquals( $js, '{"minDate":1}' );
	}

	/**
	 * @group json_parser
	 */
	public function test__key_is_single_quotation_value_is_no_quotation() {
		$js = "'minDate': +1";
		$Json_Parser = new MW_WP_Form_Json_Parser( $js );
		$js = $Json_Parser->create_json();
		$this->assertEquals( $js, '{"minDate":1}' );
	}

	/**
	 * @group json_parser
	 */
	public function test__key_and_value_are_single_quotation() {
		$js = "'minDate': '+1'";
		$Json_Parser = new MW_WP_Form_Json_Parser( $js );
		$js = $Json_Parser->create_json();
		$this->assertEquals( $js, '{"minDate":1}' );
	}

	/**
	 * @group json_parser
	 */
	public function test__key_is_no_quotation_value_is_double_quotation() {
		$js = 'minDate: "+1"';
		$Json_Parser = new MW_WP_Form_Json_Parser( $js );
		$js = $Json_Parser->create_json();
		$this->assertEquals( $js, '{"minDate":1}' );
	}

	/**
	 * @group json_parser
	 */
	public function test__key_is_no_quotation_value_is_single_quotation() {
		$js = "minDate: '+1'";
		$Json_Parser = new MW_WP_Form_Json_Parser( $js );
		$js = $Json_Parser->create_json();
		$this->assertEquals( $js, '{"minDate":1}' );
	}

	/**
	 * @group json_parser
	 */
	public function test__key_and_value_are_no_quotation() {
		$js = "minDate: +1";
		$Json_Parser = new MW_WP_Form_Json_Parser( $js );
		$js = $Json_Parser->create_json();
		$this->assertEquals( $js, '{"minDate":1}' );
	}

	/**
	 * @group json_parser
	 */
	public function test__value_has_colon() {
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
