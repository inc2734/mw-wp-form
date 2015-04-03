<?php
class MW_WP_Form_Contact_Data_Setting_Test extends WP_UnitTestCase {

	/**
	 * MW_WP_Form_Contact_Data_Setting の配列
	 * @var array
	 */
	protected $settings = array();

	/**
	 * 1つめの MW_WP_Form_Contact_Data_Setting の投稿ID
	 * @var int
	 */
	protected $post_id;

	/**
	 * setUp
	 */
	public function setUp() {
		parent::setUp();

		// 問い合わせデータをデータベースに保存する設定のフォームを作成
		$post_ids = $this->factory->post->create_many(
			5,
			array(
				'post_type' => MWF_Config::NAME,
			)
		);
		foreach ( $post_ids as $post_id ) {
			$meta = array(
				'usedb' => 1,
			);
			update_post_meta( $post_id, MWF_Config::NAME, $meta );
		}

		// 問い合わせデータを保存
		foreach ( $post_ids as $post_id ) {
			$post_ids = $this->factory->post->create_many(
				5,
				array(
					'post_type' => MWF_Config::DBDATA,
				)
			);
			break;
		}
		foreach ( $post_ids as $post_id ) {
			update_post_meta( $post_id, MWF_config::CONTACT_DATA_NAME, array(
				'test-1' => 'aaa',
				'test-2' => 'bbb',
			) );
			$Setting = new MW_WP_Form_Contact_Data_Setting( $post_id );
			$this->settings[] = $Setting;
			$this->post_id = $post_id;
			break;
		}
	}

	/**
	 * @backupStaticAttributes enabled
	 * @group get_posts
	 */
	public function test_mwform_get_posts_データベースに保存が有効でない投稿タイプは追加されない() {
		add_filter(
			'mwform_contact_data_post_types',
			function( $post_types ) {
				$post_types[] = 'fugafuga';
				$post_types[] = 'hogehoge';
				return $post_types;
			}
		);
		$post_types = MW_WP_Form_Contact_Data_Setting::get_posts();
		$this->assertEquals( 5, count( $post_types ) );
	}

	/**
	 * @backupStaticAttributes enabled
	 * @group get_posts
	 */
	public function test_mwform_get_posts_投稿タイプを一つ消す() {
		add_filter(
			'mwform_contact_data_post_types',
			function( $post_types ) {
				unset( $post_types[0] );
				return $post_types;
			}
		);
		$post_types = MW_WP_Form_Contact_Data_Setting::get_posts();
		$this->assertEquals( 4, count( $post_types ) );
	}

	/**
	 * @group get_response_statuses
	 */
	public function test_get_response_statuses() {
		$this->assertEquals(
			array(
				'not-supported' => 'Not supported',
				'reservation'   => 'Reservation',
				'supported'     => 'Supported',
			),
			$this->settings[0]->get_response_statuses()
		);
	}

	/**
	 * @group get_permit_keys
	 */
	public function test_get_permit_keys() {
		$this->assertEquals(
			array(
				'response_status',
				'memo',
			),
			$this->settings[0]->get_permit_keys()
		);
	}

	/**
	 * @group gets
	 */
	public function test_gets() {
		$this->assertEquals(
			array_merge(
				get_post_meta( $this->post_id, MWF_config::CONTACT_DATA_NAME, true ),
				array(
					'response_status' => 'not-supported',
					'memo'            => '',
				)
			),
			$this->settings[0]->gets()
		);
	}

	/**
	 * @group get
	 */
	public function test_get_response_statusの場合() {
		$this->assertEquals(
			'not-supported',
			$this->settings[0]->get( 'response_status' )
		);
	}

	/**
	 * @group get
	 */
	public function test_get_memo_の場合() {
		$this->assertSame( '', $this->settings[0]->get( 'memo' ) );
	}

	/**
	 * @group get
	 */
	public function test_get_保存済みデータの場合() {
		$this->assertEquals( 'aaa', $this->settings[0]->get( 'test-1' ) );
	}

	/**
	 * @group get
	 */
	public function test_get_保存されていないデータの場合() {
		$this->assertNull( $this->settings[0]->get( 'hoge' ) );
	}

	/**
	 * @group set
	 */
	public function test_set_respnse_statusの場合() {
		$this->settings[0]->set( 'response_status', 'hoge' );
		$this->assertEquals( 'hoge', $this->settings[0]->get( 'response_status' ) );
	}

	/**
	 * @group set
	 */
	public function test_set_permit_keys以外の場合() {
		$this->settings[0]->set( 'test-3', 'hoge' );
		$this->assertEquals( 'hoge', $this->settings[0]->get( 'test-3' ) );
	}

	/**
	 * @group sets
	 */
	public function test_sets() {
		$this->settings[0]->sets( array(
			'response_status' => 'hoge',
			'test-3'          => 'hoge',
		) );
		$this->assertEquals( 'hoge', $this->settings[0]->get( 'response_status' ) );
		$this->assertEquals( 'hoge', $this->settings[0]->get( 'test-3' ) );
	}

	/**
	 * @group save
	 */
	public function test_save_permit_keyは保存しない() {
		$this->settings[0]->sets( array(
			'response_status' => 'hoge',
			'test-3'          => 'hoge',
		) );
		$this->settings[0]->save();
		$this->assertEquals(
			array(
				'response_status' => 'hoge',
				'memo'            => '',
			),
			get_post_meta( $this->post_id, MWF_config::CONTACT_DATA_NAME, true )
		);
		$this->assertSame( '', get_post_meta( $this->post_id, 'test-3', true ) );
	}

	/**
	 * @group save
	 */
	public function test_save_permit_keyも保存する() {
		$this->settings[0]->sets( array(
			'response_status' => 'hoge',
			'test-3'          => 'hoge',
		) );
		$this->settings[0]->save( true );
		$this->assertEquals(
			array(
				'response_status' => 'hoge',
				'memo'            => '',
			),
			get_post_meta( $this->post_id, MWF_config::CONTACT_DATA_NAME, true )
		);
		$this->assertEquals( 'hoge', get_post_meta( $this->post_id, 'test-3', true ) );
	}
}