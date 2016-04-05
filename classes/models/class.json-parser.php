<?php
/**
 * Name       : MW WP Form Json Parser
 * Description: ショートコードから渡される json を正しい形式に変換
 * Version    : 1.0.1
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : April 3, 2016
 * Modified   : April 5, 2016
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Json_Parser {

	/**
	 * ショートコードから渡される json
	 * @var string
	 */
	protected $maybe_json;

	/**
	 * シングルクォーテーションの待ち状態
	 * @var bool
	 */
	protected $s_quote_stay = false;

	/**
	 * ダブルクォーテーションの待ち状態
	 * @var bool
	 */
	protected $d_quote_stay = false;

	/**
	 * コロンの待ち状態
	 * @var bool
	 */
	protected $colon_stay = true;

	/**
	 * 正しい json を生成するための配列の添字
	 * @var int
	 */
	protected $index = 0;

	/**
	 * セットする文字が json のキーにあたるのか値にあたるのかを識別するフラグ
	 * @var string key|value
	 */
	protected $key = 'key';

	/**
	 * json 生成用の配列
	 * @var array
	 */
	protected $temp = array();

	/**
	 * @param  string $maybe_json json
	 */
	public function __construct( $maybe_json ) {
		$this->maybe_json = $maybe_json;
		$this->set_default_params();
	}

	/**
	 * 各プロパティの初期値を設定
	 */
	protected function set_default_params() {
		$this->s_quote_stay = false;
		$this->d_quote_stay = false;
		$this->colon_stay   = true;
		$this->index        = 0;
		$this->key          = 'key';
		$this->temp         = array();
	}

	/**
	 * 文字を json 生成用の配列に追加する
	 *
	 * @param string $character
	 */
	public function push_character( $character ) {
		if ( !isset( $this->temp[$this->index][$this->key] ) ) {
			$this->temp[$this->index][$this->key] = '';
		}
		$this->temp[$this->index][$this->key] .= $character;
	}

	/**
	 * シングルクォーテーション用の処理
	 */
	protected function proccess_single_quote() {
		if ( !$this->d_quote_stay ) {
			if ( !$this->s_quote_stay ) {
				$this->s_quote_stay = true;
			} else {
				$this->s_quote_stay = false;
			}
		} else {
			$this->push_character( "'" );
		}
	}

	/**
	 * ダブルクォーテーション用の処理
	 */
	protected function proccess_double_quote() {
		if ( !$this->s_quote_stay ) {
			if ( !$this->d_quote_stay ) {
				$this->d_quote_stay = true;
			} else {
				$this->d_quote_stay = false;
			}
		} else {
			$this->push_character( '"' );
		}
	}

	/**
	 * カンマ用の処理
	 */
	protected function proccess_comma() {
		if ( !$this->s_quote_stay || !$this->d_quote_stay ) {
			$this->index ++;
			$this->colon_stay = true;
			$this->key = 'key';
		} else {
			$this->push_character( ':' );
		}
	}

	/**
	 * コロン用の処理
	 *
	 * @param string $character
	 */
	protected function proccess_colon() {
		if ( $this->colon_stay ) {
			$this->colon_stay = false;
			$this->key = 'value';
		} else {
			$this->push_character( ':' );
		}
	}

	/**
	 * json 生成用の配列を元に json を返す
	 *
	 * @return json
	 */
	public function json_encode() {
		$js = array();
		foreach ( $this->temp as $param ) {
			if ( !isset( $param['key'] ) ) {
				continue;
			}
			$key   = $param['key'];
			$value = '';
			if ( isset( $param['value'] ) ) {
				$value = $param['value'];
			}
			$value = trim( $value );
			if ( preg_match( '/^[\-\+]?[\d]+$/', $value ) ) {
				$value = (int) $value;
			} elseif ( $value === mb_strtolower( 'true' ) ) {
				$value = true;
			} elseif ( $value === mb_strtolower( 'false' ) ) {
				$value = false;
			} elseif ( $value === mb_strtolower( 'null' ) ) {
				$value = null;
			}
			$js[ trim( $key ) ] = $value;
		}
		return json_encode( $js );
	}

	/**
	 * @return json
	 */
	public function create_json() {
		$_js = trim( $this->maybe_json, '{}' );
		$_js = preg_split( "//u", $_js, -1, PREG_SPLIT_NO_EMPTY );

		foreach ( $_js as $character ) {
			// シングルクォーテーション
			if ( $character === "'" ) {
				$this->proccess_single_quote();
			}
			// ダブルクォーテーション
			elseif ( $character === '"' ) {
				$this->proccess_double_quote();
			}
			// カンマ
			elseif ( $character === ',' ) {
				$this->proccess_comma();
			}
			// コロン
			elseif ( $character === ':' ) {
				$this->proccess_colon();
			}
			// その他の文字
			else {
				$this->push_character( $character );
			}
		}

		$json = $this->json_encode();
		$this->set_default_params();
		return $json;
	}
}
