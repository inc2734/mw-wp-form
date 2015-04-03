<?php
class MW_WP_Form_Error_Test extends WP_UnitTestCase {

	/**
	 * @var MW_WP_Form_Error
	 */
	protected $Error;

	/**
	 * setUp
	 */
	public function setUp() {
		parent::setUp();
		$this->Error = new MW_WP_Form_Error();
	}

	/**
	 * @group set_error
	 */
	public function test_set_error() {
		$this->Error->set_error( 'test', 'alpha', 'message' );
		$this->assertEquals(
			array(
				'test' => array(
					'alpha' => 'message',
				)
			),
			$this->Error->get_errors()
		);
	}

	/**
	 * @group get_error
	 */
	public function test_get_error_指定したキーにエラーがある() {
		$this->Error->set_error( 'test', 'alpha', 'message' );
		$this->assertEquals(
			array(
				'alpha' => 'message',
			),
			$this->Error->get_error( 'test' )
		);
	}

	/**
	 * @group get_error
	 */
	public function test_get_error_指定したキーにエラーがない() {
		$this->Error->set_error( 'test-2', 'alpha', 'message' );
		$this->assertSame( array(), $this->Error->get_error( 'test' ) );
	}
}
