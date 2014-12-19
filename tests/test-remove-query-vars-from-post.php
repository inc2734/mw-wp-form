<?php

class Remove_Query_Vars_From_Post_Test extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
		$this->MW_WP_Form_Model = new MW_WP_Form_Model();
	}

	public function test_remove_query_vars_from_post_include_name() {
		$post = array(
			'name'    => 'inc2734',
			'address' => 'japan',
			'content' => 'test',
			'token'   => 'jofiajnfpaeia',
		);
		$query_vars= array(
			'name' => 'inc2734',
		);
		$query_vars = $this->MW_WP_Form_Model->remove_query_vars_from_post( $query_vars, $post );
		$this->assertEquals( $query_vars['name'], '' );
	}

	public function test_remove_query_vars_from_post_not_include_name() {
		$post = array(
			'name2'    => 'inc2734',
			'address' => 'japan',
			'content' => 'test',
			'token'   => 'jofiajnfpaeia',
		);
		$query_vars= array(
			'name' => 'inc2734',
		);
		$query_vars = $this->MW_WP_Form_Model->remove_query_vars_from_post( $query_vars, $post );
		$this->assertEquals( $query_vars['name'], 'inc2734' );
	}
}

