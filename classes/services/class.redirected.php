<?php
/**
 * Name       : MW WP Form Redirected
 * Version    : 1.0.1
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : December 31, 2014
 * Modified   : April 3, 2015
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Redirected {

	/**
	 * 現在のモード
	 * @var string input|confirm|complete|back
	 */
	protected $mode_check = 'input';

	/**
	 * @var string input|confirm|complete
	 */
	protected $view_flg = 'input';

	/**
	 * @var string
	 */
	protected $url;

	/**
	 * @var bool
	 */
	protected $querystring;
	
	/**
	 * __construct
	 *
	 * @param string $input
	 * @param string $confirm
	 * @param string $complete
	 * @param string $valdation_error
	 * @param bool $is_valid
	 * @param bool $post_condition
	 * @param bool $querystring
	 */
	public function __construct( $input, $confirm, $complete, $valdation_error, $is_valid, $post_condition, $querystring ) {
		$this->querystring = $querystring; // parse_url

		$this->initialize( $input, $confirm, $complete, $valdation_error, $is_valid, $post_condition );
	}
	
	/**
	 * initialize
	 *
	 * @param string $input
	 * @param string $confirm
	 * @param string $complete
	 * @param string $valdation_error
	 * @param bool $is_valid
	 * @param bool $post_condition
	 */
	protected function initialize( $input, $confirm, $complete, $valdation_error, $is_valid, $post_condition ) {
		$input            = $this->parse_url( $input );
		$confirm          = $this->parse_url( $confirm );
		$complete         = $this->parse_url( $complete );
		$validation_error = $this->parse_url( $valdation_error );
		$REQUEST_URI      = $this->parse_url( $this->get_request_uri() );

		// 入力画面（戻る）のとき
		if ( $post_condition === 'back' ) {
			$this->url = $input;
			return;
		}
		// 確認画面のとき
		elseif ( $post_condition === 'confirm' ) {
			if ( $is_valid ) {
				$this->view_flg = 'confirm';
				$this->url      = $confirm;
				return;
			} else {
				if ( $validation_error ) {
					$this->url = $validation_error;
					return;
				} else {
					$this->url = $input;
					return;
				}
			}
		}
		// 完了画面のとき
		elseif ( $post_condition === 'complete' ) {
			if ( $is_valid ) {
				$this->view_flg = 'complete';
				$this->url      = $complete;
				return;
			} else {
				if ( $validation_error ) {
					$this->url = $validation_error;
					return;
				} else {
					$this->url = $input;
					return;
				}
			}
		}
		// 完了 or 確認画面 or エラーURLが設定済みで
		// 完了 or 確認画面 or エラーに直接アクセスした場合、
		// 入力画面に戻れれば戻る。戻れない場合はトップに戻す
		else {
			$check_urls = array(
				$confirm, $complete,
			);
			$back_url = ( $input ) ? $input : home_url();
			foreach ( $check_urls as $check_url ) {
				if ( $REQUEST_URI === $check_url ) {
					$this->url = $back_url;
					return;
				}
			}
			$this->url = $input;
			return;

			if ( $is_valid && $REQUEST_URI == $validation_error ) {
				$this->url = $back_url;
				return;
			}
		}
	}

	/**
	 * リダイレクト先の URL を返す
	 *
	 * @return string
	 */
	public function get_url() {
		return $this->url;
	}

	/**
	 * 表示すべき画面を示すフラグを返す
	 *
	 * @return string $this->view_flg
	 */
	public function get_view_flg() {
		return $this->view_flg;
	}

	/**
	 * $_SERVER['REQUEST_URI'] を http:// からはじまるURLに変換する
	 *
	 * @return string URL
	 */
	public function get_request_uri() {
		$_REQUEST_URI = $_SERVER['REQUEST_URI'];
		if ( !preg_match( '/^https?:\/\//', $_REQUEST_URI ) ) {
			$REQUEST_URI = home_url() . $_REQUEST_URI;
			$parse_url = parse_url( home_url() );
			// サブディレクトリ型の場合
			if ( !empty( $parse_url['path'] ) ) {
				$pettern = preg_quote( $parse_url['path'], '/' );
				if ( preg_match( '/^' . $pettern . '/', $_REQUEST_URI ) ) {
					$REQUEST_URI = preg_replace( '/' . $pettern . '$/', $_REQUEST_URI, home_url() );
				}
			}
		} else {
			$REQUEST_URI = $_REQUEST_URI;
		}
		return $REQUEST_URI;
	}

	/**
	 * http:// からはじまるURLに変換する
	 *
	 * @param string URL
	 * @return string URL
	 */
	protected function parse_url( $url ) {
		if ( empty( $url ) ) {
			return '';
		}

		$query_string = array();
		preg_match( '/\?(.*)$/', $url, $reg );
		if ( !empty( $reg[1] ) ) {
			$url = str_replace( '?' . $reg[1], '', $url );
			parse_str( $reg[1], $query_string );
		}
		if ( !preg_match( '/^https?:\/\//', $url ) ) {
			$home_url = home_url();
			$url = $home_url . $url;
		}
		$url = preg_replace( '/([^:])\/+/', '$1/', $url );

		// URL設定でURL引数が使用されている場合はそれを使う。
		// 「URL引数を有効にする」が有効の場合は $_GET を利用する（重複するURL引数はURL設定のものが優先される ※post_id除く）
		if ( $this->querystring ) {
			$query_string = array_merge( $_GET, $query_string );
			if ( isset( $_GET['post_id'] ) && MWF_Functions::is_numeric( $_GET['post_id'] ) ) {
				$query_string['post_id'] = $_GET['post_id'];
			}
		}

		if ( !empty( $query_string ) ) {
			$url = $url . '?' . http_build_query( $query_string, null, '&' );
		}
		return $url;
	}
}
