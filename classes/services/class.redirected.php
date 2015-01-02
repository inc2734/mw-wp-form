<?php
/**
 * Name       : MW WP Form Redirected
 * Version    : 1.0.0
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : December 31, 2014
 * Modified   : 
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Redirected {

	/**
	 * $mode_check
	 * 現在のモード
	 * @var string input|confirm|complete|back
	 */
	protected $mode_check = 'input';

	/**
	 * $view_flg
	 * @var string input|confirm|complete
	 */
	protected $view_flg = 'input';

	/**
	 * $url
	 * @var string
	 */
	protected $url;

	/**
	 * $ExecShortcode
	 * @var MW_WP_Form_Exec_Shortcode
	 */
	protected $ExecShortcode;

	/**
	 * $is_valid
	 * @var bool
	 */
	protected $is_valid = false;

	/**
	 * $Token_Check
	 * @var MW_WP_Form_Token_Check
	 */
	protected $Token_Check;

	/**
	 * $Setting
	 * @var MW_WP_Form_Setting
	 */
	protected $Setting;

	/**
	 * $Data
	 * @var MW_WP_Form_Data
	 */
	protected $Data;
	
	/**
	 * __construct
	 * @param MW_WP_Form_Exec_Shortcode $ExecShortcode
	 * @param bool $is_valid
	 * @param MW_WP_Form_Token_Check $Token_Check
	 * @param MW_WP_Form_Setting $Setting
	 */
	public function __construct( MW_WP_Form_Exec_Shortcode $ExecShortcode, $is_valid, MW_WP_Form_Token_Check $Token_Check, MW_WP_Form_Setting $Setting, MW_WP_Form_Data $Data ) {
		$this->ExecShortcode = $ExecShortcode;
		$this->is_valid      = $is_valid;
		$this->Token_Check   = $Token_Check;
		$this->Setting       = $Setting;
		$this->Data          = $Data;

		$this->initialize();
	}

	protected function initialize() {
		$input            = $this->parse_url( $this->ExecShortcode->get( 'input' ) );
		$confirm          = $this->parse_url( $this->ExecShortcode->get( 'confirm' ) );
		$complete         = $this->parse_url( $this->ExecShortcode->get( 'complete' ) );
		$validation_error = $this->parse_url( $this->ExecShortcode->get( 'validation_error' ) );
		$REQUEST_URI      = $this->parse_url( $this->get_request_uri() );
		$post_condition   = $this->get_post_condition();

		// 入力画面（戻る）のとき
		if ( $post_condition === 'back' ) {
			$this->url = $input;
			return;
		}
		// 確認画面のとき
		elseif ( $post_condition === 'confirm' ) {
			if ( $this->is_valid ) {
				$this->view_flg = 'confirm';
				$this->url      = $confirm;
				return;
			} else {
				if ( $this->ExecShortcode->get( 'validation_error' ) ) {
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
			if ( $this->is_valid ) {
				$this->view_flg = 'complete';
				$this->url      = $complete;
				return;
			} else {
				if ( $this->ExecShortcode->get( 'validation_error' ) ) {
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

			if ( $this->is_valid && $REQUEST_URI == $validation_error ) {
				$this->url = $back_url;
				return;
			}
		}
	}

	/**
	 * get_url
	 * リダイレクト先の URL を返す
	 */
	public function get_url() {
		return $this->url;
	}

	/**
	 * get_view_flg
	 * 表示すべき画面を示すフラグを返す
	 * @return string $this->view_flg
	 */
	public function get_view_flg() {
		return $this->view_flg;
	}

	/**
	 * mode_check
	 * @return string back|confirm|complete|input
	 */
	protected function mode_check() {
		$backButton    = $this->Data->get_raw( MWF_Config::BACK_BUTTON );
		$confirmButton = $this->Data->get_raw( MWF_Config::CONFIRM_BUTTON );
		if ( $backButton ) {
			return 'back';
		} elseif ( $confirmButton ) {
			return 'confirm';
		} elseif ( !$confirmButton && !$backButton && $this->Token_Check->check() ) {
			return 'complete';
		}
		return 'input';
	}

	/**
	 * get_post_condition
	 * 送信データからどのページを表示すべきかの状態を判定して返す
	 * ただし実際に表示するページと同じとは限らない（バリデーション通らないとかあるので）
	 * @return string back|confirm|complete|input
	 */
	public function get_post_condition() {
		$mode = $this->mode_check();
		$data = $this->Data->gets();
		if ( $mode === 'back' ) {
			return 'back';
		} elseif ( !empty( $data ) && $mode === 'confirm' ) {
			return 'confirm';
		} elseif ( !empty( $data ) && $mode === 'complete' ) {
			return 'complete';
		}
		return 'input';
	}

	/**
	 * get_request_uri
	 * $_SERVER['REQUEST_URI'] を http:// からはじまるURLに変換する
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
	 * parse_url
	 * http:// からはじまるURLに変換する
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
			$url = str_replace( '?', '', $url );
			$url = str_replace( $reg[1], '', $url );
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
}