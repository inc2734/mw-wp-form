<?php
/**
 * Name: MW Form Field
 * Description: フォームフィールドの抽象クラス
 * Version: 1.6.3
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : December 14, 2012
 * Modified: September 3, 2014
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
abstract class MW_Form_Field {

	/**
	 * string $shortcode_name
	 */
	protected $shortcode_name;

	/**
	 * string $display_name
	 */
	protected $display_name;

	/**
	 * Form $Form
	 */
	protected $Form;

	/**
	 * array $defaults 属性値等初期値
	 */
	protected $defaults = array();

	/**
	 * array $atts 属性値
	 */
	protected $atts = array();

	/**
	 * Error $Error エラーオブジェクト
	 */
	protected $Error;

	/**
	 * string $key フォーム識別子
	 */
	protected $key;

	/**
	 * string $type フォームタグの種類
	 * input, select, button, other
	 */
	protected $type = 'other';

	/**
	 * array $qtags qtagsの引数
	 */
	protected $qtags = array(
		'id' => '',
		'display' => '',
		'arg1' => '',
		'arg2' => '',
	);

	/**
	 * __construct
	 */
	public function __construct() {
		$this->_set_names();
		$this->defaults = $this->setDefaults();
		add_action( 'mwform_add_shortcode', array( $this, 'add_shortcode' ), 10, 4 );
		$this->_add_mwform_tag_generator();
	}

	/**
	 * set_names
	 * shortcode_name、display_nameを定義。各子クラスで上書きする。
	 * @return array shortcode_name, display_name
	 */
	protected function set_names() {
		return array(
			'shortcode_name' => $this->shortcode_name,
			'display_name' => $this->display_name,
		);
	}
	private function _set_names() {
		$args = $this->set_names();
		$this->shortcode_name = $args['shortcode_name'];
		$this->display_name = $args['display_name'];
	}

	/**
	 * set_qtags
	 * @param string $id
	 * @param string $display
	 * @param string $arg1 開始タグ（ショートコード）
	 * @param string $arg2 終了タグ（ショートコード）
	 */
	protected function set_qtags( $id, $display, $arg1, $arg2 = '' ) {
		MWF_Functions::deprecated_message( 'MW_Form_Field::set_qtags', 'MW_Form_Field::set_names' );
		$this->qtags = array(
			'id' => $id,
			'display' => $display,
			'arg1' => $arg1,
			'arg2' => $arg2,
		);
	}

	/**
	 * getError
	 * @param  string $key name属性
	 * @return string エラーHTML
	 */
	protected function getError( $key ) {
		$_ret = '';
		if ( is_array( $this->Error->getError( $key ) ) ) {
			$start_tag = '<span class="error">';
			$end_tag   = '</span>';
			foreach ( $this->Error->getError( $key ) as $rule => $error ) {
				$rule = strtolower( $rule );
				$error = apply_filters( 'mwform_error_message_' . $this->key, $error, $key, $rule );
				$error_html = apply_filters( 'mwform_error_message_html',
					$start_tag . esc_html( $error ) . $end_tag,
					$error,
					$start_tag,
					$end_tag,
					$this->key,
					$key,
					$rule
				);
				$_ret .= $error_html;
			}
		}
		if ( $_ret )
			return apply_filters( 'mwform_error_message_wrapper', $_ret, $this->key );
	}

	/**
	 * setDefaults
	 * $this->defaultsを設定し返す
	 * @return array defaults
	 */
	abstract protected function setDefaults();

	/**
	 * inputPage
	 * 入力ページでのフォーム項目を返す
	 * @param array $atts
	 * @return string HTML
	 */
	abstract protected function inputPage();
	public function _inputPage( $atts ) {
		if ( isset( $this->defaults['value'], $atts['name'] ) && !isset( $atts['value'] ) ) {
			$atts['value'] = apply_filters( 'mwform_value_' . $this->key, $this->defaults['value'], $atts['name'] );
		}
		$this->atts = shortcode_atts( $this->defaults, $atts );
		return $this->inputPage();
	}

	/**
	 * confirmPage
	 * 確認ページでのフォーム項目を返す
	 * @param array $atts
	 * @return string HTML
	 */
	abstract protected function confirmPage();
	public function _confirmPage( $atts ) {
		$this->atts = shortcode_atts( $this->defaults, $atts );
		return $this->confirmPage();
	}

	/**
	 * add_short_code
	 * フォーム項目を返す
	 * @param MW_Form $Form
	 * @param string $viewFlg
	 * @param MW_Error $Error
	 * @param string $key
	 */
	public function add_shortcode( MW_Form $Form, $viewFlg, MW_Error $Error, $key ) {
		if ( !empty( $this->shortcode_name ) ) {
			$this->Form = $Form;
			$this->Error = $Error;
			$this->key = $key;
			switch( $viewFlg ) {
				case 'input' :
					add_shortcode( $this->shortcode_name, array( $this, '_inputPage' ) );
					break;
				case 'confirm' :
					add_shortcode( $this->shortcode_name, array( $this, '_confirmPage' ) );
					break;
				default :
					exit( '$viewFlg is not right value.' );
			}
		}
	}

	/**
	 * getChildren
	 * 選択肢の配列を返す
	 * @param string $_children
	 * @return array $children
	 */
	protected function getChildren( $_children ) {
		$children = array();
		if ( !empty( $_children ) && !is_array( $_children ) ) {
			$_children = explode( ',', $_children );
		}
		if ( is_array( $_children ) ) {
			foreach ( $_children as $child ) {
				$children[$child] = $child;
			}
		}
		if ( $this->key ) {
			$children = apply_filters( 'mwform_choices_' . $this->key, $children, $this->atts );
		}
		return $children;
	}

	/**
	 * _add_mwform_tag_generator
	 * フォームタグジェネレータのタグ選択肢とダイアログを設定
	 */
	protected function _add_mwform_tag_generator() {
		add_action( 'mwform_tag_generator_dialog', array( $this, 'add_mwform_tag_generator' ) );
		if ( $this->type !== 'other' ) {
			$tag = 'mwform_tag_generator_' . $this->type . '_option';
		} else {
			$tag = 'mwform_tag_generator_option';
		}
		add_action( $tag, array( $this, 'mwform_tag_generator_option' ) );
	}

	/**
	 * add_mwform_tag_generator
	 * タグジェネレータのダイアログ枠を出力
	 */
	public function add_mwform_tag_generator() {
		?>
		<div id="dialog-<?php echo esc_attr( $this->shortcode_name ); ?>" class="mwform-dialog" title="<?php echo esc_attr( $this->shortcode_name ); ?>">
			<div class="form">
				<?php $this->mwform_tag_generator_dialog(); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * add_mwform_tag_generator
	 * タグジェネレータのダイアログを出力。各フォーム項目クラスでオーバーライド
	 */
	protected function mwform_tag_generator_dialog() {}

	/**
	 * mwform_tag_generator_option
	 * フォームタグ挿入ボタンのセレクトボックスに選択項目を追加
	 */
	public function mwform_tag_generator_option() {
		$display_name = $this->qtags['display'];
		if ( $this->display_name )
			$display_name = $this->display_name;
		?>
		<option value="<?php echo esc_attr( $this->shortcode_name ); ?>"><?php echo esc_html( $display_name ); ?></option>
		<?php
	}
}
