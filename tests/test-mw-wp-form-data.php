<?php
class MW_WP_Form_Data_Test extends WP_UnitTestCase {

	/**
	 * @var MW_WP_Form_Data
	 */
	protected $Data;

	/**
	 * setUp
	 */
	public function setUp() {
		parent::setUp();
		$post_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$form_key   = MWF_Functions::get_form_key_from_form_id( $post_id );
		$this->Data = MW_WP_Form_Data::getInstance( $form_key );
	}

	/**
	 * tearDown
	 */
	public function tearDown() {
		parent::tearDown();
		$this->Data->clear_values();
	}

	/**
	 * @group get_in_children
	 */
	public function test_get_in_children_value値を送信したら表示値が返る() {
		$this->Data->set( 'radio', 'a' );
		$children = array(
			'a' => 'aaa',
			'b' => 'bbb',
			'c' => 'ccc',
		);
		$this->assertEquals( 'aaa', $this->Data->get_in_children( 'radio', $children ) );
	}

	/**
	 * @group get_raw_in_children
	 */
	public function test_get_raw_in_children_value値を送信したらvalue値が返る() {
		$this->Data->set( 'radio', 'a' );
		$children = array(
			'a' => 'aaa',
			'b' => 'bbb',
			'c' => 'ccc',
		);
		$this->assertEquals( 'a', $this->Data->get_raw_in_children( 'radio', $children ) );
	}

	/**
	 * @group get_post_condition
	 */
	public function test_get_post_condition_BACK_BUTTONが送信されたときはback() {
		$this->Data->set( MWF_Config::BACK_BUTTON, true );
		$this->assertEquals( 'back', $this->Data->get_post_condition( true ) );
	}

	/**
	 * @group get_post_condition
	 */
	public function test_get_post_condition_CONFIRM_BUTTONが送信されたときはconfirm() {
		$this->Data->set( MWF_Config::CONFIRM_BUTTON, true );
		$this->assertEquals( 'confirm', $this->Data->get_post_condition( true ) );
	}

	/**
	 * @group get_post_condition
	 */
	public function test_get_post_condition_tokeがtrueのときはcomplete() {
		$this->assertEquals( 'complete', $this->Data->get_post_condition( true ) );
	}

	/**
	 * @group get_post_condition
	 */
	public function test_get_post_condition_tokeがfalseのときはinput() {
		$this->assertEquals( 'input', $this->Data->get_post_condition( false ) );
	}

	/**
	 * @group get
	 */
	public function test_get_キーが一致するデータがなければnull() {
		$this->Data->set( 'test', 'a' );
		$this->assertNull( $this->Data->get( 'test-2' ) );
	}

	/**
	 * @group get
	 */
	public function test_get_送信データが文字列でchildrenがあるときは一致すれば表示値を返す() {
		$this->Data->set( 'test', 'a' );
		$this->Data->sets(
			array(
				'__children' => array(
					'test' => json_encode( array( 'a' => 'aaa' ) ),
				),
			)
		);
		$this->assertEquals( 'aaa', $this->Data->get( 'test' ) );
	}

	/**
	 * @group get
	 */
	public function test_get_送信データが文字列でchildrenがあるとき一致しなければ空文字() {
		$this->Data->set( 'test', 'a' );
		$this->Data->sets(
			array(
				'__children' => array(
					'test' => json_encode( array( 'b' => 'bbb' ) ),
				),
			)
		);
		$this->assertSame( '', $this->Data->get( 'test' ) );
	}

	/**
	 * @group get
	 */
	public function test_get_送信データが文字列でchildrenがないときは送信値を返す() {
		$this->Data->set( 'test', 'a' );
		$this->assertEquals( 'a', $this->Data->get( 'test' ) );
	}

	/**
	 * @group get
	 */
	public function test_get_送信データが複数値項目だけどキーdataがなければnull() {
		$this->Data->sets(
			array(
				'test' => array(
					'a' => 'aaa',
					'b' => 'bbb',
					'c' => 'ccc',
				),
			)
		);
		$this->assertNull( $this->Data->get( 'test' ) );
	}

	/**
	 * @group get
	 */
	public function test_get_送信データが複数値項目かつ配列でchildrenがあるときは一致すれば表示値を返す() {
		$this->Data->sets(
			array(
				'test' => array(
					'data'      => array( 'a', 'b', 'c' ),
					'separator' => ',',
				),
				'__children' => array(
					'test' => json_encode( array(
						'a' => 'aaa',
						'b' => 'bbb',
						'c' => 'ccc',
					) ),
				),
			)
		);
		$this->assertEquals( 'aaa,bbb,ccc', $this->Data->get( 'test' ) );
	}

	/**
	 * @group get
	 */
	public function test_get_送信データが複数値項目かつ配列でchildrenがないときは送信値を返す() {
		$this->Data->sets(
			array(
				'test' => array(
					'data'      => array( 'a', 'b', 'c' ),
					'separator' => ',',
				),
			)
		);
		$this->assertEquals( 'a,b,c', $this->Data->get( 'test' ) );
	}

	/**
	 * @group get
	 */
	public function test_get_送信データが複数値項目かつ配列でchildrenがあるときは一致しなければ空文字列() {
		$this->Data->sets(
			array(
				'test' => array(
					'data'      => array( 'a', 'b' ),
					'separator' => ',',
				),
				'__children' => array(
					'test' => json_encode( array(
						'c' => 'ccc',
						'e' => 'ddd',
						'e' => 'eee',
					) ),
				),
			)
		);
		$this->assertSame( '', $this->Data->get( 'test' ) );
	}

	/**
	 * @group get
	 */
	public function test_get_送信データが複数値項目かつ文字列でchildrenがあるときは一致すれば表示値を返す() {
		$this->Data->sets(
			array(
				'test' => array(
					'data'      => 'a,b,c',
					'separator' => ',',
				),
				'__children' => array(
					'test' => json_encode( array(
						'a' => 'aaa',
						'b' => 'bbb',
						'c' => 'ccc',
					) ),
				),
			)
		);
		$this->assertEquals( 'aaa,bbb,ccc', $this->Data->get( 'test' ) );
	}

	/**
	 * @group get
	 */
	public function test_get_送信データが複数値項目かつ文字列でchildrenがないときは送信値を返す() {
		$this->Data->sets(
			array(
				'test' => array(
					'data'      => 'a,b,c',
					'separator' => ',',
				),
			)
		);
		$this->assertEquals( 'a,b,c', $this->Data->get( 'test' ) );
	}

	/**
	 * @group get
	 */
	public function test_get_送信データが複数値項目かつ文字列でchildrenがあるときは一致しなければ空文字列() {
		$this->Data->sets(
			array(
				'test' => array(
					'data'      => 'a,b',
					'separator' => ',',
				),
				'__children' => array(
					'test' => json_encode( array(
						'c' => 'ccc',
						'e' => 'ddd',
						'e' => 'eee',
					) ),
				),
			)
		);
		$this->assertSame( '', $this->Data->get( 'test' ) );
	}

	/**
	 * @group get_raw
	 */
	public function test_get_raw_キーが一致するデータがなければnull() {
		$this->Data->set( 'test', 'a' );
		$this->assertNull( $this->Data->get_raw( 'test-2' ) );
	}

	/**
	 * @group get_raw
	 */
	public function test_get_raw_送信データが文字列でchildrenがあるときは一致すれば送信値を返す() {
		$this->Data->set( 'test', 'a' );
		$this->Data->sets(
			array(
				'__children' => array(
					'test' => json_encode( array( 'a' => 'aaa' ) ),
				),
			)
		);
		$this->assertEquals( 'a', $this->Data->get_raw( 'test' ) );
	}

	/**
	 * @group get_raw
	 */
	public function test_get_raw_送信データが文字列でchildrenがあるとき一致しなければ空文字() {
		$this->Data->set( 'test', 'a' );
		$this->Data->sets(
			array(
				'__children' => array(
					'test' => json_encode( array( 'b' => 'bbb' ) ),
				),
			)
		);
		$this->assertSame( '', $this->Data->get_raw( 'test' ) );
	}

	/**
	 * @group get_raw
	 */
	public function test_get_raw_送信データが文字列でchildrenがないときは送信値を返す() {
		$this->Data->set( 'test', 'a' );
		$this->assertEquals( 'a', $this->Data->get_raw( 'test' ) );
	}

	/**
	 * @group get_raw
	 */
	public function test_get_raw_送信データが複数値項目だけどキーdataがなければnull() {
		$this->Data->sets(
			array(
				'test' => array(
					'a' => 'aaa',
					'b' => 'bbb',
					'c' => 'ccc',
				),
			)
		);
		$this->assertNull( $this->Data->get_raw( 'test' ) );
	}

	/**
	 * @group get_raw
	 */
	public function test_get_raw_送信データが複数値項目かつ配列でchildrenがあるときは一致すれば送信値を返す() {
		$this->Data->sets(
			array(
				'test' => array(
					'data'      => array( 'a', 'b', 'c' ),
					'separator' => ',',
				),
				'__children' => array(
					'test' => json_encode( array(
						'a' => 'aaa',
						'b' => 'bbb',
						'c' => 'ccc',
					) ),
				),
			)
		);
		$this->assertEquals( 'a,b,c', $this->Data->get_raw( 'test' ) );
	}

	/**
	 * @group get_raw
	 */
	public function test_get_raw_送信データが複数値項目かつ配列でchildrenがないときは送信値を返す() {
		$this->Data->sets(
			array(
				'test' => array(
					'data'      => array( 'a', 'b', 'c' ),
					'separator' => ',',
				),
			)
		);
		$this->assertEquals( 'a,b,c', $this->Data->get_raw( 'test' ) );
	}

	/**
	 * @group get_raw
	 */
	public function test_get_raw_送信データが複数値項目かつ配列でchildrenがあるときは一致しなければ空文字列() {
		$this->Data->sets(
			array(
				'test' => array(
					'data'      => array( 'a', 'b' ),
					'separator' => ',',
				),
				'__children' => array(
					'test' => json_encode( array(
						'c' => 'ccc',
						'e' => 'ddd',
						'e' => 'eee',
					) ),
				),
			)
		);
		$this->assertSame( '', $this->Data->get_raw( 'test' ) );
	}

	/**
	 * @group get_raw
	 */
	public function test_get_raw_送信データが複数値項目かつ文字列でchildrenがあるときは一致すれば送信値を返す() {
		$this->Data->sets(
			array(
				'test' => array(
					'data'      => 'a,b,c',
					'separator' => ',',
				),
				'__children' => array(
					'test' => json_encode( array(
						'a' => 'aaa',
						'b' => 'bbb',
						'c' => 'ccc',
					) ),
				),
			)
		);
		$this->assertEquals( 'a,b,c', $this->Data->get_raw( 'test' ) );
	}

	/**
	 * @group get_raw
	 */
	public function test_get_raw_送信データが複数値項目かつ文字列でchildrenがないときは送信値を返す() {
		$this->Data->sets(
			array(
				'test' => array(
					'data'      => 'a,b,c',
					'separator' => ',',
				),
			)
		);
		$this->assertEquals( 'a,b,c', $this->Data->get_raw( 'test' ) );
	}

	/**
	 * @group get_raw
	 */
	public function test_get_raw_送信データが複数値項目かつ文字列でchildrenがあるときは一致しなければ空文字列() {
		$this->Data->sets(
			array(
				'test' => array(
					'data'      => 'a,b',
					'separator' => ',',
				),
				'__children' => array(
					'test' => json_encode( array(
						'c' => 'ccc',
						'e' => 'ddd',
						'e' => 'eee',
					) ),
				),
			)
		);
		$this->assertSame( '', $this->Data->get_raw( 'test' ) );
	}

	/**
	 * @group set_upload_file_keys
	 */
	public function test_set_upload_file_keys_ファイルがあればNotNull() {
		$wp_upload_dir = wp_upload_dir();
		system( "sudo chmod 777 " . $wp_upload_dir['basedir'] );
		system( "sudo mkdir -p " . $wp_upload_dir['path'] );
		file_put_contents( $wp_upload_dir['path'] . '/1.txt', 1 );
		$this->Data->set( 'file', $wp_upload_dir['url'] . '/1.txt' );
		$this->Data->push( MWF_Config::UPLOAD_FILE_KEYS, 'file' );
		$this->Data->set_upload_file_keys();
		$this->assertSame( array( 'file' ), $this->Data->get_post_value_by_key( MWF_Config::UPLOAD_FILE_KEYS ) );
		$this->assertNotNull( $this->Data->get_post_value_by_key( MWF_Config::UPLOAD_FILE_KEYS ) );
		unlink( $wp_upload_dir['path'] . '/1.txt' );
	}

	/**
	 * @group set_upload_file_keys
	 */
	public function test_set_upload_file_keys_ファイルがなければNull() {
		$wp_upload_dir = wp_upload_dir();
		$this->Data->set( 'file', $wp_upload_dir['url'] . '/1.txt' );
		$this->Data->push( MWF_Config::UPLOAD_FILE_KEYS, 'file' );
		$this->Data->set_upload_file_keys();
		$this->assertSame( array(), $this->Data->get_post_value_by_key( MWF_Config::UPLOAD_FILE_KEYS ) );
	}

	/**
	 * @group push_uploaded_file_keys
	 */
	public function test_push_uploaded_file_keys() {
		$this->Data->set( MWF_Config::UPLOAD_FILE_KEYS, array( 'file1' ) );
		$this->Data->push_uploaded_file_keys( array( 'file1' => 'http://exemple.com/dummy.txt' ) );
		$this->assertSame( array( 'file1' ), $this->Data->get_post_value_by_key( MWF_Config::UPLOAD_FILE_KEYS ) );

		$this->Data->set( MWF_Config::UPLOAD_FILE_KEYS, array( 'file1' ) );
		$this->Data->push_uploaded_file_keys( array( 'file2' => 'http://exemple.com/dummy.txt' ) );
		$this->assertSame( array( 'file1', 'file2' ), $this->Data->get_post_value_by_key( MWF_Config::UPLOAD_FILE_KEYS ) );
	}
}
