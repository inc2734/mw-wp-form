<?php
class MW_WP_Form_URL_Test extends WP_UnitTestCase {

	/**
	 * リダイレクトURL決定のテスト
	 */
	public function test_parse_url() {
		// バリデーションエラーが無ければtrue
		$is_valid       = true;
		// どの画面を表示しようとしているか
		$post_condition = 'input';
		// URL引数を使用するならtrue
		$querystring    = false;

		$Redirected = new MW_WP_Form_Redirected(
			'/contact/', '/contact/confirm/', '/contact/complete/', '',
			$is_valid, $post_condition, $querystring
		);
		$this->assertEquals( home_url( '/contact/' ), $Redirected->get_url() );

		$is_valid       = true;
		$post_condition = 'confirm';
		$querystring    = false;
		$Redirected = new MW_WP_Form_Redirected(
			'/contact/', '/contact/confirm/', '/contact/complete/', '',
			$is_valid, $post_condition, $querystring
		);
		$this->assertEquals( home_url( '/contact/confirm/' ), $Redirected->get_url() );

		$is_valid       = true;
		$post_condition = 'complete';
		$querystring    = false;
		$Redirected = new MW_WP_Form_Redirected(
			'/contact/', '/contact/confirm/', '/contact/complete/', '',
			$is_valid, $post_condition, $querystring
		);
		$this->assertEquals( home_url( '/contact/complete/' ), $Redirected->get_url() );

		$post_condition = 'confirm';
		$is_valid       = false;
		$querystring    = false;
		$Redirected = new MW_WP_Form_Redirected(
			'/contact/', '/contact/confirm/', '/contact/complete/', '',
			$is_valid, $post_condition, $querystring
		);
		$this->assertEquals( home_url( '/contact/' ), $Redirected->get_url() );

		$post_condition = 'complete';
		$is_valid       = false;
		$querystring    = false;
		$Redirected = new MW_WP_Form_Redirected(
			'/contact/', '/contact/confirm/', '/contact/complete/', '',
			$is_valid, $post_condition, $querystring
		);
		$this->assertEquals( home_url( '/contact/' ), $Redirected->get_url() );

		$post_condition = 'input';
		$is_valid       = true;
		$querystring    = false;
		$Redirected = new MW_WP_Form_Redirected(
			'/contact/', '/contact/confirm/', '/contact/complete/', '/contact/error/',
			$is_valid, $post_condition, $querystring
		);
		$this->assertEquals( home_url( '/contact/' ), $Redirected->get_url() );

		$post_condition = 'confirm';
		$is_valid       = true;
		$querystring    = false;
		$Redirected = new MW_WP_Form_Redirected(
			'/contact/', '/contact/confirm/', '/contact/complete/', '/contact/error/',
			$is_valid, $post_condition, $querystring
		);
		$this->assertEquals( home_url( '/contact/confirm/' ), $Redirected->get_url() );

		$post_condition = 'complete';
		$is_valid       = true;
		$querystring    = false;
		$Redirected = new MW_WP_Form_Redirected(
			'/contact/', '/contact/confirm/', '/contact/complete/', '/contact/error/',
			$is_valid, $post_condition, $querystring
		);
		$this->assertEquals( home_url( '/contact/complete/' ), $Redirected->get_url() );

		$post_condition = 'confirm';
		$is_valid       = false;
		$querystring    = false;
		$Redirected = new MW_WP_Form_Redirected(
			'/contact/', '/contact/confirm/', '/contact/complete/', '/contact/error/',
			$is_valid, $post_condition, $querystring
		);
		$this->assertEquals( home_url( '/contact/error/' ), $Redirected->get_url() );

		$post_condition = 'complete';
		$is_valid       = false;
		$querystring    = false;
		$Redirected = new MW_WP_Form_Redirected(
			'/contact/', '/contact/confirm/', '/contact/complete/', '/contact/error/',
			$is_valid, $post_condition, $querystring
		);
		$this->assertEquals( home_url( '/contact/error/' ), $Redirected->get_url() );

		$Redirected = new MW_WP_Form_Redirected(
			home_url( '/contact/' ), home_url( '/contact/confirm/' ), home_url( '/contact/complete/' ), '',
			$is_valid, $post_condition, $querystring
		);
		$this->assertEquals( home_url( '/contact/' ), $Redirected->get_url() );

		$is_valid       = true;
		$post_condition = 'confirm';
		$querystring    = false;
		$Redirected = new MW_WP_Form_Redirected(
			home_url( '/contact/' ), home_url( '/contact/confirm/' ), home_url( '/contact/complete/' ), '',
			$is_valid, $post_condition, $querystring
		);
		$this->assertEquals( home_url( '/contact/confirm/' ), $Redirected->get_url() );

		$is_valid       = true;
		$post_condition = 'complete';
		$querystring    = false;
		$Redirected = new MW_WP_Form_Redirected(
			home_url( '/contact/' ), home_url( '/contact/confirm/' ), home_url( '/contact/complete/' ), '',
			$is_valid, $post_condition, $querystring
		);
		$this->assertEquals( home_url( '/contact/complete/' ), $Redirected->get_url() );

		$post_condition = 'confirm';
		$is_valid       = false;
		$querystring    = false;
		$Redirected = new MW_WP_Form_Redirected(
			home_url( '/contact/' ), home_url( '/contact/confirm/' ), home_url( '/contact/complete/' ), '',
			$is_valid, $post_condition, $querystring
		);
		$this->assertEquals( home_url( '/contact/' ), $Redirected->get_url() );

		$post_condition = 'complete';
		$is_valid       = false;
		$querystring    = false;
		$Redirected = new MW_WP_Form_Redirected(
			home_url( '/contact/' ), home_url( '/contact/confirm/' ), home_url( '/contact/complete/' ), '',
			$is_valid, $post_condition, $querystring
		);
		$this->assertEquals( home_url( '/contact/' ), $Redirected->get_url() );

		$post_condition = 'input';
		$is_valid       = true;
		$querystring    = false;
		$Redirected = new MW_WP_Form_Redirected(
			home_url( '/contact/' ), home_url( '/contact/confirm/' ),
			home_url( '/contact/complete/' ), home_url( '/contact/error/' ),
			$is_valid, $post_condition, $querystring
		);
		$this->assertEquals( home_url( '/contact/' ), $Redirected->get_url() );

		$post_condition = 'confirm';
		$is_valid       = true;
		$querystring    = false;
		$Redirected = new MW_WP_Form_Redirected(
			home_url( '/contact/' ), home_url( '/contact/confirm/' ),
			home_url( '/contact/complete/' ), home_url( '/contact/error/' ),
			$is_valid, $post_condition, $querystring
		);
		$this->assertEquals( home_url( '/contact/confirm/' ), $Redirected->get_url() );

		$post_condition = 'complete';
		$is_valid       = true;
		$querystring    = false;
		$Redirected = new MW_WP_Form_Redirected(
			home_url( '/contact/' ), home_url( '/contact/confirm/' ),
			home_url( '/contact/complete/' ), home_url( '/contact/error/' ),
			$is_valid, $post_condition, $querystring
		);
		$this->assertEquals( home_url( '/contact/complete/' ), $Redirected->get_url() );

		$post_condition = 'confirm';
		$is_valid       = false;
		$querystring    = false;
		$Redirected = new MW_WP_Form_Redirected(
			home_url( '/contact/' ), home_url( '/contact/confirm/' ),
			home_url( '/contact/complete/' ), home_url( '/contact/error/' ),
			$is_valid, $post_condition, $querystring
		);
		$this->assertEquals( home_url( '/contact/error/' ), $Redirected->get_url() );

		$post_condition = 'complete';
		$is_valid       = false;
		$querystring    = false;
		$Redirected = new MW_WP_Form_Redirected(
			home_url( '/contact/' ), home_url( '/contact/confirm/' ),
			home_url( '/contact/complete/' ), home_url( '/contact/error/' ),
			$is_valid, $post_condition, $querystring
		);
		$this->assertEquals( home_url( '/contact/error/' ), $Redirected->get_url() );

		$post_condition = 'input';
		$is_valid       = true;
		$querystring    = true;
		$_GET           = array();
		$_GET['hoge']   = 'fuga';
		$Redirected = new MW_WP_Form_Redirected(
			home_url( '/contact/' ), home_url( '/contact/confirm/' ), home_url( '/contact/complete/' ), '',
			$is_valid, $post_condition, $querystring
		);
		$this->assertEquals( home_url( '/contact/?hoge=fuga' ), $Redirected->get_url() );

		$post_condition = 'input';
		$is_valid       = true;
		$querystring    = true;
		$_GET           = array();
		$_GET['hoge']   = 'fuga';
		$Redirected = new MW_WP_Form_Redirected(
			home_url( '/contact/?poge=puga' ), home_url( '/contact/confirm/' ), home_url( '/contact/complete/' ), '',
			$is_valid, $post_condition, $querystring
		);
		$this->assertEquals( home_url( '/contact/?hoge=fuga&poge=puga' ), $Redirected->get_url() );

		$post_condition = 'input';
		$is_valid       = true;
		$querystring    = true;
		$_GET           = array();
		$_GET['hoge']   = 'fuga';
		$Redirected = new MW_WP_Form_Redirected(
			home_url( '/contact/?hoge=puga' ), home_url( '/contact/confirm/' ), home_url( '/contact/complete/' ), '',
			$is_valid, $post_condition, $querystring
		);
		$this->assertEquals( home_url( '/contact/?hoge=puga' ), $Redirected->get_url() );

		$post_condition  = 'input';
		$is_valid        = true;
		$querystring     = true;
		$_GET            = array();
		$_GET['post_id'] = '1';
		$Redirected = new MW_WP_Form_Redirected(
			home_url( '/contact/?post_id=2' ), home_url( '/contact/confirm/' ), home_url( '/contact/complete/' ), '',
			$is_valid, $post_condition, $querystring
		);
		$this->assertEquals( home_url( '/contact/?post_id=1' ), $Redirected->get_url() );
	}
}