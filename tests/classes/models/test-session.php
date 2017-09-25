<?php
class MW_WP_Form_Session_Test extends WP_UnitTestCase {

	public function tearDown() {
		parent::tearDown();
		_delete_all_data();
	}

	/**
	 * @test
	 * @group save
	 */
	public function save() {
		$Session = new MW_WP_Form_Session( 'session' );
		$Session->save( array(
			'name-1' => 'value-1',
			'name-2' => 'value-2',
		) );
		$this->assertEquals(
			array(
				'name-1' => 'value-1',
				'name-2' => 'value-2',
			),
			$Session->gets()
		);
	}

	/**
	 * @test
	 * @group set
	 */
	public function set() {
		$Session = new MW_WP_Form_Session( 'session' );
		$Session->set( 'name-1', 'value-1' );
		$this->assertEquals( 'value-1', $Session->get( 'name-1' ) );
	}

	/**
	 * @test
	 * @group push
	 */
	public function push() {
		$Session = new MW_WP_Form_Session( 'session' );
		$Session->save( array(
			'name-1' => array( 'value-1-1' ),
			'name-2' => 'value-2-1',
		) );
		$Session->push( 'name-1', 'value-1-2' );
		$Session->push( 'name-2', 'value-2-2' );
		$Session->push( 'name-3', 'value-3-1' );
		$this->assertEquals(
			array(
				'name-1' => array( 'value-1-1', 'value-1-2' ),
				'name-2' => array( 'value-2-1', 'value-2-2' ),
				'name-3' => array( 'value-3-1' ),
			),
			$Session->gets()
		);
	}

	/**
	 * @test
	 * @group get
	 */
	public function get() {
		$Session = new MW_WP_Form_Session( 'session' );
		$Session->set( 'name-1', 'value-1' );
		$this->assertEquals( 'value-1', $Session->get( 'name-1' ) );
	}

	/**
	 * @test
	 * @group gets
	 */
	public function gets() {
		$Session = new MW_WP_Form_Session( 'session' );
		$Session->save( array(
			'name-1' => 'value-1',
			'name-2' => 'value-2',
		) );
		$this->assertEquals(
			array(
				'name-1' => 'value-1',
				'name-2' => 'value-2',
			),
			$Session->gets()
		);
	}

	/**
	 * @test
	 * @group clear_value
	 */
	public function clear_value() {
		$Session = new MW_WP_Form_Session( 'session' );
		$Session->set( 'name-1', 'value-1' );
		$this->assertEquals( 'value-1', $Session->get( 'name-1' ) );
		$Session->clear_value( 'name-1' );
		$this->assertNull( $Session->get( 'name-1' ) );
	}

	/**
	 * @test
	 * @group clear_values
	 */
	public function clear_values() {
		$Session = new MW_WP_Form_Session( 'session' );
		$Session->save( array(
			'name-1' => array( 'value-1-1' ),
			'name-2' => array( 'value-2-1' ),
		) );
		$Session->clear_values();
		$this->assertNull( $Session->get( 'name-1' ) );
		$this->assertNull( $Session->get( 'name-2' ) );
		$this->assertSame( array(), $Session->gets() );
	}
}
