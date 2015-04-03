<?php
class MW_WP_Form_Redirected_Test extends WP_UnitTestCase {

	/**
	 * @var string
	 */
	protected $input_url = '/contact/';

	/**
	 * @var string
	 */
	protected $confirm_url = '/contact/confirm/';

	/**
	 * @var string
	 */
	protected $complete_url = '/contact/complete/';

	/**
	 * @var string
	 */
	protected $error_url = '/contact/error/';

	/**
	 * @group get_url
	 */
	public function test_get_url_入力画面_エラーなし_URL引数を使用しない() {
		$post_condition = 'input';
		$is_valid       = true;
		$querystring    = false;

		$Redirected = new MW_WP_Form_Redirected(
			$this->input_url, $this->confirm_url, $this->complete_url, $this->error_url,
			$is_valid, $post_condition, $querystring
		);

		$this->assertEquals( home_url( $this->input_url ), $Redirected->get_url() );
	}

	/**
	 * @group get_url
	 */
	public function test_get_url_確認画面_エラーなし_URL引数を使用しない() {
		$post_condition = 'confirm';
		$is_valid       = true;
		$querystring    = false;

		$Redirected = new MW_WP_Form_Redirected(
			$this->input_url, $this->confirm_url, $this->complete_url, $this->error_url,
			$is_valid, $post_condition, $querystring
		);

		$this->assertEquals( home_url( $this->confirm_url ), $Redirected->get_url() );
	}

	/**
	 * @group get_url
	 */
	public function test_get_url_完了画面_エラーなし_URL引数を使用しない() {
		$post_condition = 'complete';
		$is_valid       = true;
		$querystring    = false;

		$Redirected = new MW_WP_Form_Redirected(
			$this->input_url, $this->confirm_url, $this->complete_url, $this->error_url,
			$is_valid, $post_condition, $querystring
		);

		$this->assertEquals( home_url( $this->complete_url ), $Redirected->get_url() );
	}

	/**
	 * @group get_url
	 */
	public function test_get_url_確認画面_エラーあり_URL引数を使用しない() {
		$post_condition = 'confirm';
		$is_valid       = false;
		$querystring    = false;

		$Redirected = new MW_WP_Form_Redirected(
			$this->input_url, $this->confirm_url, $this->complete_url, $this->error_url,
			$is_valid, $post_condition, $querystring
		);

		$this->assertEquals( home_url( $this->error_url ), $Redirected->get_url() );
	}

	/**
	 * @group get_url
	 */
	public function test_get_url_確認画面_エラーあり_URL引数を使用しない_エラーURL設定なし() {
		$post_condition = 'confirm';
		$is_valid       = false;
		$querystring    = false;

		$Redirected = new MW_WP_Form_Redirected(
			$this->input_url, $this->confirm_url, $this->complete_url, '',
			$is_valid, $post_condition, $querystring
		);

		$this->assertEquals( home_url( $this->input_url ), $Redirected->get_url() );
	}

	/**
	 * @group get_url
	 */
	public function test_get_url_完了画面_エラーあり_URL引数を使用しない() {
		$post_condition = 'complete';
		$is_valid       = false;
		$querystring    = false;

		$Redirected = new MW_WP_Form_Redirected(
			$this->input_url, $this->confirm_url, $this->complete_url, $this->error_url,
			$is_valid, $post_condition, $querystring
		);

		$this->assertEquals( home_url( $this->error_url ), $Redirected->get_url() );
	}

	/**
	 * @group get_url
	 */
	public function test_get_url_完了画面_エラーあり_URL引数を使用しない_エラーURL設定なし() {
		$post_condition = 'complete';
		$is_valid       = false;
		$querystring    = false;

		$Redirected = new MW_WP_Form_Redirected(
			$this->input_url, $this->confirm_url, $this->complete_url, '',
			$is_valid, $post_condition, $querystring
		);

		$this->assertEquals( home_url( $this->input_url ), $Redirected->get_url() );
	}

	/**
	 * @group get_url
	 */
	public function test_get_url_入力画面_エラーなし_URL引数を使用する() {
		$post_condition = 'input';
		$is_valid       = true;
		$querystring    = true;
		$_GET           = array();
		$_GET['hoge']   = 'fuga';

		$Redirected = new MW_WP_Form_Redirected(
			$this->input_url, $this->confirm_url, $this->complete_url, $this->error_url,
			$is_valid, $post_condition, $querystring
		);

		$this->assertEquals( home_url( $this->input_url . '?hoge=fuga' ), $Redirected->get_url() );
	}

	/**
	 * @group get_url
	 */
	public function test_get_url_入力画面_エラーなし_URL引数を使用する_入力画面URL設定に重複する引数あり() {
		$post_condition = 'input';
		$is_valid       = true;
		$querystring    = true;
		$_GET           = array();
		$_GET['hoge']   = 'fuga';

		$Redirected = new MW_WP_Form_Redirected(
			$this->input_url . '?hoge=piyo', $this->confirm_url, $this->complete_url, $this->error_url,
			$is_valid, $post_condition, $querystring
		);

		$this->assertEquals( home_url( $this->input_url . '?hoge=piyo' ), $Redirected->get_url() );
	}

	/**
	 * @group get_url
	 */
	public function test_get_url_入力画面_エラーなし_URL引数を使用する_入力画面URL設定に重複しない引数あり() {
		$post_condition = 'input';
		$is_valid       = true;
		$querystring    = true;
		$_GET           = array();
		$_GET['hoge']   = 'fuga';

		$Redirected = new MW_WP_Form_Redirected(
			$this->input_url . '?hoge2=piyo2', $this->confirm_url, $this->complete_url, $this->error_url,
			$is_valid, $post_condition, $querystring
		);

		$this->assertEquals( home_url( $this->input_url . '?hoge=fuga&hoge2=piyo2' ), $Redirected->get_url() );
	}

	/**
	 * @group get_url
	 */
	public function test_get_url_入力画面_エラーなし_URL引数を使用する_入力画面URL設定に引数post_idあり() {
		$post_condition  = 'input';
		$is_valid        = true;
		$querystring     = true;
		$_GET            = array();
		$_GET['post_id'] = 1;

		$Redirected = new MW_WP_Form_Redirected(
			$this->input_url . '?post_id=2', $this->confirm_url, $this->complete_url, $this->error_url,
			$is_valid, $post_condition, $querystring
		);

		$this->assertEquals( home_url( $this->input_url . '?post_id=1' ), $Redirected->get_url() );
	}
}
