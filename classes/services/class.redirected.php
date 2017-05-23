<?php
/**
 * Name       : MW WP Form Redirected
 * Version    : 2.0.0
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : December 31, 2014
 * Modified   : May 21, 2017
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Redirected {

	/**
	 * @var string
	 */
	protected $form_key;

	/**
	 * @var MW_WP_Form_Setting
	 */
	protected $Setting;

	/**
	 * @var string input|confirm|complete
	 */
	protected $view_flg = 'input';

	/**
	 * @var string
	 */
	protected $url;

	public function __construct( $form_key, $Setting, $is_valid, $post_condition ) {
		$this->form_key = $form_key;
		$this->Setting  = $Setting;

		$input        = $this->_parse_url( $this->Setting->get( 'input_url' ) );
		$confirm      = $this->_parse_url( $this->Setting->get( 'confirmation_url' ) );
		$complete     = $this->_parse_url( $this->Setting->get( 'complete_url' ) );
		$error        = $this->_parse_url( $this->Setting->get( 'validation_error_url' ) );
		$REQUEST_URI  = $this->_parse_url( $this->get_request_uri() );

		if ( 'back' === $post_condition ) {
			$this->url = $input;
			return;
		}

		if ( 'confirm' === $post_condition ) {
			if ( $is_valid ) {
				$this->view_flg = 'confirm';
				$this->url      = $confirm;
			} else {
				if ( $error ) {
					$this->url = $error;
				} else {
					$this->url = $input;
				}
			}
			return;
		}

		if ( 'complete' === $post_condition ) {
			if ( $is_valid ) {
				$this->view_flg = 'complete';
				$this->url      = $complete;
			} else {
				if ( $error ) {
					$this->url = $error;
				} else {
					$this->url = $input;
				}
			}
			return;
		}

		$this->url = ( $input ) ? $input : home_url();
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
	protected function _parse_url( $url ) {
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
		if ( $this->Setting->get( 'querystring' ) ) {
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

	/**
	 * 現在のURLと引数で渡されたリダイレクトURLが同じであればリダイレクトしない
	 */
	public function redirect() {
		$Data        = MW_WP_Form_Data::connect( $this->form_key );
		$url         = ( $this->get_url() ) ? $this->get_url() : $this->get_request_uri();
		$redirect    = apply_filters( 'mwform_redirect_url_' . $this->form_key, $url, $Data );
		$REQUEST_URI = $this->get_request_uri();

		if ( empty( $_POST ) && $redirect === $REQUEST_URI ) {
			return;
		}

		do_action( 'mwform_before_redirect_' . $this->form_key );

		$redirect = wp_sanitize_redirect( $redirect );
		$redirect = wp_validate_redirect( $redirect, home_url() );
		wp_safe_redirect( $redirect );
		exit();
	}
}
