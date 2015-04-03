<?php
class MW_WP_Form_Exec_Shortcode_Test extends WP_UnitTestCase {

	/**
	 * @var MW_WP_Form_Setting
	 */
	protected $Setting;

	/**
	 * setUp
	 */
	public function setUp() {
		parent::setUp();
		$post_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$this->Setting = new MW_WP_Form_Setting( $post_id );
		$this->Setting->set( 'input_url', '/contact/' );
		$this->Setting->save();
	}

	/**
	 * @group has_shortcode
	 */
	public function test_has_shortcode_投稿内の場合() {
		global $wp_query;
		$wp_query->is_singular = true;

		$post = $this->generate_page_has_mwform_formkey( $this->Setting );
		$ExecShortcode = new MW_WP_Form_Exec_Shortcode( $post, '' );
		$this->assertTrue( $ExecShortcode->has_shortcode() );
	}

	/**
	 * @group has_shortcode
	 */
	public function test_has_shortcode_投稿内かつ囲み型ショートコード内の場合() {
		global $wp_query;
		$wp_query->is_singular = true;

		$post_id = $this->factory->post->create( array(
			'post_type'    => 'paga',
			'post_content' => sprintf( '[gallery][mwform_formkey key="%d"][/gallery]', $this->Setting->get( 'post_id' ) ),
		) );
		$post = get_post( $post_id );

		$ExecShortcode = new MW_WP_Form_Exec_Shortcode( $post, '' );
		$this->assertTrue( $ExecShortcode->has_shortcode() );
	}

	/**
	 * @group has_shortcode
	 */
	public function test_has_shortcode_テンプレート内の場合() {
		$wp_upload_dir = wp_upload_dir();
		$page2_path = $wp_upload_dir['basedir'] . '/page2.php';
		file_put_contents( $page2_path, sprintf( '[mwform_formkey key="%d"]', $this->Setting->get( 'post_id' ) ) );
		$ExecShortcode = new MW_WP_Form_Exec_Shortcode( false, $page2_path );
		$this->assertTrue( $ExecShortcode->has_shortcode() );
		unlink( $page2_path );
	}

	/**
	 * @group get
	 */
	public function test_get_設定が存在すれば返す() {
		global $wp_query;
		$wp_query->is_singular = true;

		$post = $this->generate_page_has_mwform_formkey( $this->Setting );
		$ExecShortcode = new MW_WP_Form_Exec_Shortcode( $post, '' );

		$this->assertEquals(
			'/contact/',
			$ExecShortcode->get( 'input_url' )
		);
	}

	/**
	 * @group get
	 */
	public function test_get_設定が存在しなければnull() {
		global $wp_query;
		$wp_query->is_singular = true;

		$post = $this->generate_page_has_mwform_formkey( $this->Setting );
		$ExecShortcode = new MW_WP_Form_Exec_Shortcode( $post, '' );

		$this->assertNull( $ExecShortcode->get( 'hoge' ) );
	}

	/**
	 * @group set_settings_by_mwform
	 */
	public function test_set_settings_by_mwform() {
		global $wp_query;
		$wp_query->is_singular = true;

		$post = $this->generate_page_has_mwform_formkey( $this->Setting );
		$ExecShortcode = new MW_WP_Form_Exec_Shortcode( $post, '' );
		$ExecShortcode->set_settings_by_mwform( array(
			'input'            => '/input/',
			'confirm'          => '/confirm/',
			'complete'         => '/complete/',
			'validation_error' => '/error/',
		) );

		$this->assertEquals(
			'/input/',
			$ExecShortcode->get( 'input_url' )
		);
		$this->assertEquals(
			'/confirm/',
			$ExecShortcode->get( 'confirmation_url' )
		);
		$this->assertEquals(
			'/complete/',
			$ExecShortcode->get( 'complete_url' )
		);
		$this->assertEquals(
			'/error/',
			$ExecShortcode->get( 'validation_error_url' )
		);
	}

	/**
	 * @group set_settings_by_mwform_formkey
	 */
	public function test_set_settings_by_mwform_formkey() {
		global $wp_query;
		$wp_query->is_singular = true;

		$post_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$Setting = new MW_WP_Form_Setting( $post_id );
		$Setting->set( 'input_url', '/input/' );
		$Setting->save();

		$post = $this->generate_page_has_mwform_formkey( $Setting );
		$ExecShortcode = new MW_WP_Form_Exec_Shortcode( $post, '' );
		$ExecShortcode->set_settings_by_mwform_formkey( array(
			'key' => $Setting->get( 'post_id' ),
		) );

		$this->assertEquals(
			'/input/',
			$ExecShortcode->get( 'input_url' )
		);
	}

	/**
	 * @group is_generated_by_formkey
	 */
	public function test_is_generated_by_formkey_管理画面で作成されたフォームであればtrue() {
		global $wp_query;
		$wp_query->is_singular = true;

		$post = $this->generate_page_has_mwform_formkey( $this->Setting );
		$ExecShortcode = new MW_WP_Form_Exec_Shortcode( $post, '' );

		$this->assertTrue( $ExecShortcode->is_generated_by_formkey() );
	}

	/**
	 * @group is_generated_by_formkey
	 */
	public function test_is_generated_by_formkey_管理画面で作成されたフォームでなければfalse() {
		$ExecShortcode = new MW_WP_Form_Exec_Shortcode( false, '' );
		$this->assertFalse( $ExecShortcode->is_generated_by_formkey() );
	}

	/**
	 * @group get_form_id
	 */
	public function test_get_form_id() {
		global $wp_query;
		$wp_query->is_singular = true;

		$post = $this->generate_page_has_mwform_formkey( $this->Setting );
		$ExecShortcode = new MW_WP_Form_Exec_Shortcode( $post, '' );

		$this->assertEquals( $this->Setting->get( 'post_id' ), $ExecShortcode->get_form_id() );
	}

	/**
	 * @group add_shortcode
	 */
	public function test_add_shortcode() {
		global $wp_query;
		$wp_query->is_singular = true;

		$this->assertFalse( shortcode_exists( 'mwform_formkey' ) );
		$this->assertFalse( shortcode_exists( 'mwform' ) );
		$this->assertFalse( shortcode_exists( 'mwform_complete_message' ) );

		$post = $this->generate_page_has_mwform_formkey( $this->Setting );
		$ExecShortcode = new MW_WP_Form_Exec_Shortcode( $post, '' );
		$view_flg = 'input';
		$Form = new MW_WP_Form_Form();
		$Data = MW_WP_Form_Data::getInstance( $ExecShortcode->get( 'key' ), array(), array() );
		$ExecShortcode->add_shortcode( $view_flg, $this->Setting, $Form, $Data );

		$this->assertTrue( shortcode_exists( 'mwform_formkey' ) );
		$this->assertTrue( shortcode_exists( 'mwform' ) );
		$this->assertTrue( shortcode_exists( 'mwform_complete_message' ) );
	}

	/**
	 * @group mwform_formkey
	 */
	public function test_mwform_formkey_input() {
		$content = $this->get_page_content_has_mwform_formkey( 'input' );

		$this->assertEquals(
			'<div id="mw_wp_form_mw-wp-form-' . $this->Setting->get( 'post_id' ) . '" class="mw_wp_form mw_wp_form_input  ">
					<form method="post" action="" enctype="multipart/form-data"><p>Post content 1</p>
</form>
				<!-- end .mw_wp_form --></div>',
			$content
		);
	}

	/**
	 * @group mwform_formkey
	 */
	public function test_mwform_formkey_confirm() {
		$content = $this->get_page_content_has_mwform_formkey( 'confirm' );

		$this->assertEquals(
			'<div id="mw_wp_form_mw-wp-form-' . $this->Setting->get( 'post_id' ) . '" class="mw_wp_form mw_wp_form_confirm mw_wp_form_preview ">
					<form method="post" action="" enctype="multipart/form-data"><p>Post content 1</p>
</form>
				<!-- end .mw_wp_form --></div>',
			$content
		);
	}

	/**
	 * @group mwform_formkey
	 */
	public function test_mwform_formkey_complete() {
		$content = $this->get_page_content_has_mwform_formkey( 'complete' );

		$this->assertEquals(
			'<div id="mw_wp_form_mw-wp-form-' . $this->Setting->get( 'post_id' ) . '" class="mw_wp_form mw_wp_form_complete">
				
			<!-- end .mw_wp_form --></div>',
			$content
		);
	}

	/**
	 * @group mwform
	 */
	public function test_mwform_input() {
		$ExecShortcode = $this->get_ExecShortcode_after_add_shortcode( 'input' );
		$content = $ExecShortcode->mwform( '', '' );
		$this->assertEquals(
			'<div id="mw_wp_form_mw-wp-form-' . $this->Setting->get( 'post_id' ) . '" class="mw_wp_form mw_wp_form_input  ">
					<form method="post" action="" enctype="multipart/form-data"></form>
				<!-- end .mw_wp_form --></div>',
			$content
		);
	}

	/**
	 * @group mwform
	 */
	public function test_mwform_confirm() {
		$ExecShortcode = $this->get_ExecShortcode_after_add_shortcode( 'confirm' );
		$content = $ExecShortcode->mwform( '', '' );
		$this->assertEquals(
			'<div id="mw_wp_form_mw-wp-form-' . $this->Setting->get( 'post_id' ) . '" class="mw_wp_form mw_wp_form_confirm mw_wp_form_preview ">
					<form method="post" action="" enctype="multipart/form-data"></form>
				<!-- end .mw_wp_form --></div>',
			$content
		);
	}

	/**
	 * @group mwform
	 */
	public function test_mwform_ユーザー情報に置換() {
		global $current_user;
		$user_attributes = array(
			'user_login'   => 'user_login',
			'user_email'   => 'info@example.com',
			'user_url'     => 'http://example.com',
			'user_login'   => 'user_login',
			'display_name' => 'display_name',
		);
		$user_id = $this->factory->user->create( $user_attributes );
		$current_user = get_userdata( $user_id );
		$ExecShortcode = $this->get_ExecShortcode_after_add_shortcode( 'input' );

		$content  = "{user_id}\n";
		$content .= "{user_login}\n";
		$content .= "{user_email}\n";
		$content .= "{user_url}\n";
		$content .= "{user_registered}\n";
		$content .= "{display_name}";

		$this->assertEquals(
			"{$current_user->ID}
{$current_user->user_login}
{$current_user->user_email}
{$current_user->user_url}
{$current_user->user_registered}
{$current_user->display_name}",
			$ExecShortcode->get_the_content( $content )
		);
	}

	/**
	 * @group mwform
	 */
	public function test_mwform_投稿情報に置換() {
		global $wp_query, $post;
		$wp_query->is_singular = true;

		$post_id = $this->factory->post->create();
		update_post_meta( $post_id, 'meta', 'meta' );
		$ExecShortcode = $this->get_ExecShortcode_after_add_shortcode( 'input' );

		$content  = "{ID}\n";
		$content .= "{post_title}\n";
		$content .= "{post_content}\n";
		$content .= "{post_excerpt}\n";
		$content .= "{meta}";

		$post = get_post( $post_id );

		$this->assertEquals(
			"{$post->ID}
{$post->post_title}
{$post->post_content}
{$post->post_excerpt}
{$post->meta}",
			$ExecShortcode->get_the_content( $content )
		);
	}

	/**
	 * @group mwform
	 */
	public function test_mwform_querystringが有効な場合は引数で指定された投稿情報に置換() {
		global $wp_query, $post;
		$wp_query->is_singular = true;

		$this->Setting->set( 'querystring', 1 );
		$this->Setting->save();
		$post_id   = $this->factory->post->create();
		$post_id_2 = $this->factory->post->create();
		update_post_meta( $post_id_2, 'meta', 'meta' );
		$ExecShortcode = $this->get_ExecShortcode_after_add_shortcode( 'input' );

		$_GET['post_id'] = $post_id_2;

		$content  = "{ID}\n";
		$content .= "{post_title}\n";
		$content .= "{post_content}\n";
		$content .= "{post_excerpt}\n";
		$content .= "{meta}";

		$post = get_post( $post_id_2 );

		$this->assertEquals(
			"{$post->ID}
{$post->post_title}
{$post->post_content}
{$post->post_excerpt}
{$post->meta}",
			$ExecShortcode->get_the_content( $content )
		);
	}

	/**
	 * @group mwform_complete_message
	 */
	public function test_mwform_complete_message() {
		$ExecShortcode = $this->get_ExecShortcode_after_add_shortcode( 'complete' );
		$content = $ExecShortcode->mwform_complete_message( '', '' );
		$this->assertEquals(
			'<div id="mw_wp_form_mw-wp-form-' . $this->Setting->get( 'post_id' ) . '" class="mw_wp_form mw_wp_form_complete">
				
			<!-- end .mw_wp_form --></div>',
			$content
		);
	}

	/**
	 * ショートコード mwform_formkey を持つページを作成して返す
	 *
	 * @param MW_WP_Form_Setting $Setting
	 * @return WP_Post
	 */
	protected function generate_page_has_mwform_formkey( $Setting ) {
		$post_id = $this->factory->post->create( array(
			'post_type'    => 'paga',
			'post_content' => sprintf( '[mwform_formkey key="%d"]', $Setting->get( 'post_id' ) ),
		) );
		return get_post( $post_id );
	}

	/**
	 * ショートコード mwform_formkey をパースした本文を返す
	 *
	 * @param string $view_flg
	 * @return string
	 */
	protected function get_page_content_has_mwform_formkey( $view_flg ) {
		$ExecShortcode = $this->get_ExecShortcode_after_add_shortcode( $view_flg );
		$attributes = array(
			'key' => $this->Setting->get( 'post_id' ),
		);
		return $ExecShortcode->mwform_formkey( $attributes );
	}

	/**
	 * add_shortcode した後の ExecShortcode を返す
	 *
	 * @param string $view_flg
	 * @return MW_WP_Form_Exec_Shortcode
	 */
	protected function get_ExecShortcode_after_add_shortcode( $view_flg ) {
		$post = $this->generate_page_has_mwform_formkey( $this->Setting );
		$ExecShortcode = new MW_WP_Form_Exec_Shortcode( $post, '' );
		$Form = new MW_WP_Form_Form();
		$Data = MW_WP_Form_Data::getInstance( $ExecShortcode->get( 'key' ), array(), array() );
		$ExecShortcode->add_shortcode( $view_flg, $this->Setting, $Form, $Data );
		$attributes = array(
			'key' => $this->Setting->get( 'post_id' ),
		);
		$ExecShortcode->set_settings_by_mwform_formkey( $attributes );
		return $ExecShortcode;
	}
}
