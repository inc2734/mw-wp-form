<?php
/**
 * Name: MW Form
 * Description: フォームクラス
 * Version: 1.4.1
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : September 25, 2012
 * Modified: July 23, 2014
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_Form {

	/**
	 * 識別子
	 */
	protected $key = 'form_token';

	/**
	 * トークンタグ用のトークン名
	 */
	public $tokenName = 'token';

	/**
	 * トークンの値
	 */
	protected $token;

	/**
	 * データ
	 */
	protected $Data;

	/**
	 * 確認ボタンの名前
	 */
	protected $confirmButton = 'submitConfirm';

	/**
	 * 戻るボタンの名前
	 */
	protected $backButton = 'submitBack';

	/**
	 * 現在のモード
	 */
	protected $modeCheck = 'input';

	/**
	 * フォームの method
	 */
	protected $method = 'post';

	/**
	 * 完了画面の判定用
	 */
	const COMPLETE_TWICE = '__complete_twice_flg';

	/**
	 * __construct
	 * 取得データを保存
	 * @param string $key 識別子
	 */
	public function __construct( $key = '' ) {
		$this->Data = MW_WP_Form_Data::getInstance( $key );
		if ( $key ) {
			$this->key = $key . '_key';
		}
		$this->modeCheck = $this->modeCheck();
	}

	/**
	 * getTokenName
	 * nonce用のキーを返す
	 * @return string
	 */
	public function getTokenName() {
		return $this->tokenName;
	}

	/**
	 * isComplete
	 * 完了画面かどうか
	 * @return bool
	 */
	public function isComplete() {
		$data = $this->Data->getValues();
		if ( !empty( $data ) && $this->modeCheck === 'complete' ) {
			return true;
		}
		return false;
	}

	/**
	 * isConfirm
	 * 確認画面かどうか
	 * @return bool
	 */
	public function isConfirm() {
		$data = $this->Data->getValues();
		if ( !empty( $data ) && $this->modeCheck === 'confirm' )
			return true;
		return false;
	}

	/**
	 * isInput
	 * 入力画面かどうか
	 * @return bool
	 */
	public function isInput() {
		if ( $this->modeCheck === 'input' )
			return true;
		return false;
	}

	/**
	 * isBack
	 * 入力画面（戻る）かどうか
	 * @return bool
	 */
	public function isBack() {
		if ( $this->modeCheck === 'back' )
			return true;
		return false;
	}

	/**
	 * modeCheck
	 * 表示画面判定
	 * @return string input || confirm || complete
	 */
	protected function modeCheck() {
		$backButton = $this->getValue( $this->backButton );
		$confirmButton = $this->getValue( $this->confirmButton );
		if ( isset( $backButton ) ) {
			return 'back';
		} elseif ( isset( $confirmButton ) ) {
			return 'confirm';
		} elseif ( !isset( $confirmButton ) && !isset( $backButton ) && $this->check() ) {
			return 'complete';
		}
		return 'input';
	}

	/**
	 * check
	 * トークンチェック
	 * @return bool
	 */
	protected function check() {
		if ( isset( $_POST[$this->tokenName] ) )
			$requestToken = $_POST[$this->tokenName];

		$data = $this->Data->getValues();
		if ( isset( $requestToken ) && wp_verify_nonce( $requestToken, $this->key ) ) {
			$this->Data->setValue( self::COMPLETE_TWICE, true );
			return true;
		} elseif ( empty( $_POST ) && !empty( $data ) && $this->Data->getValue( self::COMPLETE_TWICE ) ) {
			return true;
		}
		return false;
	}

	/**
	 * getConfirmButtonName
	 * 確認画面への変遷用ボタンのname属性値を返す
	 * @return	String	name属性値
	 */
	public function getConfirmButtonName() {
		return $this->confirmButton;
	}

	/**
	 * getBackButtonName
	 * 戻る用ボタンのname属性値を返す
	 * @return	String
	 */
	public function getBackButtonName() {
		return $this->backButton;
	}

	/**
	 * getValue
	 * データを返す
	 * @param string $key name属性値
	 * @return mixed
	 */
	public function getValue( $key ) {
		return $this->Data->getValue( $key );
	}

	/**
	 * getZipValue
	 * データを返す ( 郵便番号用 )
	 * @param string $key name属性
	 * @return string データ
	 */
	public function getZipValue( $key ) {
		return $this->Data->getSeparatedValue( $key );
	}

	/**
	 * getTelValue
	 * データを返す ( 電話番号用 )
	 * @param string $key name属性
	 * @return string データ
	 */
	public function getTelValue( $key ) {
		return $this->getZipValue( $key );
	}

	/**
	 * getCheckedValue
	 * データを返す（ checkbox用 ）。$dataに含まれる値のみ返す
	 * @param string $key name属性
	 * @param array $data
	 * @return string データ
	 */
	public function getCheckedValue( $key, Array $data ) {
		return $this->Data->getSeparatedValue( $key, $data );
	}

	/**
	 * getRadioValue
	 * データを返す（ radio用 ）。$dataに含まれる値のみ返す
	 * @param string name属性値
	 * @param array $data データ
	 * @return string
	 */
	public function getRadioValue( $key, Array $data ) {
		$value = $this->getValue( $key );
		if ( !is_null( $value ) && !is_array( $value ) ) {
			if ( isset( $data[$value] ) ) {
				return $data[$value];
			}
		}
	}

	/**
	 * getSelectedValue
	 * データを返す（ selectbox用 ）。$dataに含まれる値のみ返す
	 * @param string $key name属性
	 * @param array $data データ
	 * @return string データ
	 */
	public function getSelectedValue( $key, Array $data ) {
		return $this->getRadioValue( $key, $data );
	}

	/**
	 * separator
	 * separatorを設定するためのhiddenを返す
	 * @param string $key name属性
	 * @param string $separator 区切り文字
	 * @return string HTML
	 */
	public function separator( $key, $separator = '' ) {
		$post_separator = $this->getSeparatorValue( $key );
		if ( !$separator && $post_separator ) {
			$separator = $post_separator;
		}
		if ( $separator ) {
			return $this->hidden( $key . '[separator]', $separator );
		}
	}

	/**
	 * getSeparatorValue
	 * 送られてきたseparatorを返す
	 * @param string $key name属性
	 * @return string
	 */
	public function getSeparatorValue( $key ) {
		return $this->Data->getSeparatorValue( $key );
	}

	/**
	 * start
	 * フォームタグ生成
	 * @param array $options
	 * @return string form開始タグ
	 */
	public function start( $options = array() ) {
		$defaults = array(
			'action' => '',
			//'enctype' => 'application/x-www-form-urlencoded',
			'enctype' => 'multipart/form-data',
		);
		$options = array_merge( $defaults, $options );
		return sprintf( '<form method="%s" action="%s" enctype="%s">',
				$this->method, esc_attr( $options['action'] ), esc_attr( $options['enctype'] ) );
	}

	/**
	 * end
	 * トークンタグ、閉じタグ生成
	 * @return string input[type=hidden]
	 */
	public function end() {
		$html = '';
		if ( $this->method === 'post' ) {
			$html .= wp_nonce_field( $this->key, $this->tokenName, true, false );
		}
		$html .= '</form>';
		return $html;
	}

	/**
	 * text
	 * input[type=text]タグ生成
	 * @param string $name name属性
	 * @param array
	 * @return string html
	 */
	public function text( $name, $options = array() ) {
		$defaults = array(
			'id' => '',
			'size' => 60,
			'maxlength' => 255,
			'value' => '',
			'conv-half-alphanumeric' => false,
			'placeholder' => '',
		);
		$options = array_merge( $defaults, $options );
		$value = $options['value'];
		if ( !is_null( $value ) ) {
			$value = $this->getValue( $name );
			if ( is_null( $value ) ) {
				$value = $options['value'];
			}
		}
		$placeholder = $this->get_attr_placeholder( $options['placeholder'] );
		$dataConvHalfAlphanumeric = null;
		if ( $options['conv-half-alphanumeric'] === true ) {
			$dataConvHalfAlphanumeric = 'data-conv-half-alphanumeric="true"';
		}
		$id = $this->get_attr_id( $options['id'] );
		return sprintf( '<input type="text" name="%s" value="%s" size="%d" maxlength="%d" %s %s %s />',
			esc_attr( $name ),
			esc_attr( $value ),
			esc_attr( $options['size'] ),
			esc_attr( $options['maxlength'] ),
			$placeholder,
			$dataConvHalfAlphanumeric,
			$id
		);
	}

	/**
	 * hidden
	 * input[type=hidden]タグ生成
	 * @param string $name name属性
	 * @param string $value 値
	 * @return string HTML
	 */
	public function hidden( $name, $value ) {
		if ( is_array( $value ) ) {
			$value = $this->getZipValue( $name );
		}
		return sprintf( '<input type="hidden" name="%s" value="%s" />', esc_attr( $name ), esc_attr( $value ) );
	}

	/**
	 * password
	 * input[type=password]タグ生成
	 * @param string $name name属性
	 * @param array $options
	 * @return string HTML
	 */
	public function password( $name, $options = array() ) {
		$defaults = array(
			'id' => '',
			'size' => 60,
			'maxlength' => 255,
			'value' => '',
			'placeholder' => '',
		);
		$options = array_merge( $defaults, $options );
		$value = $options['value'];
		if ( !is_null( $value ) ) {
			$value = $this->getValue( $name );
			if ( is_null( $value ) ) {
				$value = $options['value'];
			}
		}
		$placeholder = $this->get_attr_placeholder( $options['placeholder'] );
		$id = $this->get_attr_id( $options['id'] );
		return sprintf( '<input type="password" name="%s" value="%s" size="%d" maxlength="%d" %s %s />',
			esc_attr( $name ),
			esc_attr( $value ),
			esc_attr( $options['size'] ),
			esc_attr( $options['maxlength'] ),
			$placeholder,
			$id
		);
	}

	/**
	 * zip
	 * 郵便番号フィールド生成
	 * @param string $name name属性
	 * @param array $options
	 * @return string HTML
	 */
	public function zip( $name, $options = array() ) {
		$defaults = array(
			'conv-half-alphanumeric' => false,
		);
		$options = array_merge( $defaults, $options );

		$children = array();
		$separator = '-';
		$value = $this->getValue( $name );
		if ( !is_null( $value ) && is_array( $value ) && isset( $value['data'] ) ) {
			if ( is_array( $value['data'] ) ) {
				$children = $value['data'];
			} else {
				$children = explode( $separator, $value['data'] );
			}
		}

		$values = array( '', '' );
		foreach ( $children as $key => $val ) {
			if ( $key === 0 || $key === 1 ) {
				$values[$key] = $val;
			}
		}

		$_ret  = '〒';
		$_ret .= $this->text( $name . '[data][0]', array(
			'size' => 4,
			'maxlength' => 3,
			'value' => $values[0],
			'conv-half-alphanumeric' => $options['conv-half-alphanumeric'],
		) );
		$_ret .= ' ' . $separator . ' ';
		$_ret .= $this->text( $name . '[data][1]', array(
			'size' => 5,
			'maxlength' => 4,
			'value' => $values[1],
			'conv-half-alphanumeric' => $options['conv-half-alphanumeric'],
		) );
		$_ret .= $this->separator( $name, $separator );
		return $_ret;
	}

	/**
	 * tel
	 * 電話番号フィールド生成
	 * @param string $name name属性
	 * @param array $options
	 * @return string HTML
	 */
	public function tel( $name, $options = array() ) {
		$defaults = array(
			'conv-half-alphanumeric' => false,
		);
		$options = array_merge( $defaults, $options );

		$children = array();
		$separator = '-';
		$value = $this->getValue( $name );
		if ( !is_null( $value ) && is_array( $value ) && isset( $value['data'] ) ) {
			if ( is_array( $value['data'] ) ) {
				$children = $value['data'];
			} else {
				$children = explode( $separator, $value['data'] );
			}
		}

		$values = array( '', '', '' );
		foreach ( $children as $key => $val ) {
			if ( $key === 0 || $key === 1 || $key === 2 ) {
				$values[$key] = $val;
			}
		}

		$_ret = '';
		$_ret .= $this->text( $name . '[data][0]', array(
			'size' => 6,
			'maxlength' => 5,
			'value' => $values[0],
			'conv-half-alphanumeric' => $options['conv-half-alphanumeric'],
		) );
		$_ret .= ' ' . $separator . ' ';
		$_ret .= $this->text( $name . '[data][1]', array(
			'size' => 5,
			'maxlength' => 4,
			'value' => $values[1],
			'conv-half-alphanumeric' => $options['conv-half-alphanumeric'],
		) );
		$_ret .= ' ' . $separator . ' ';
		$_ret .= $this->text( $name . '[data][2]', array(
			'size' => 5,
			'maxlength' => 4,
			'value' => $values[2],
			'conv-half-alphanumeric' => $options['conv-half-alphanumeric'],
		) );
		$_ret .= $this->separator( $name, $separator );
		return $_ret;
	}

	/**
	 * textarea
	 * textareaタグ生成
	 * @param string $name name属性
	 * @param array $options
	 * @return string html
	 */
	public function textarea( $name, $options = array() ) {
		$defaults = array(
			'id' => '',
			'cols' => 50,
			'rows' => 5,
			'value' => '',
			'placeholder' => '',
		);
		$options = array_merge( $defaults, $options );
		$value = $options['value'];
		if ( !is_null( $value ) ) {
			$value = $this->getValue( $name );
			if ( is_null( $value ) ) {
				$value = $options['value'];
			}
		}
		$placeholder = $this->get_attr_placeholder( $options['placeholder'] );
		$id = $this->get_attr_id( $options['id'] );
		return sprintf( '<textarea name="%s" cols="%d" rows="%d" %s %s>%s</textarea>',
			esc_attr( $name ),
			esc_attr( $options['cols'] ),
			esc_attr( $options['rows'] ),
			$placeholder,
			$id,
			esc_html( $value )
		);
	}

	/**
	 * select
	 * selectタグ生成
	 * @param string $name name属性
	 * @param array $children
	 * @param array $options
	 * @return string HTML
	 */
	public function select( $name, $children = array(), $options = array() ) {
		$defaults = array(
			'id' => '',
			'value' => ''
		);
		$options = array_merge( $defaults, $options );
		$value = $options['value'];
		if ( !is_null( $value ) ) {
			$value = $this->getValue( $name );
			if ( is_null( $value ) ) {
				$value = $options['value'];
			}
		}
		$id = $this->get_attr_id( $options['id'] );
		$_ret = sprintf( '<select name="%s" %s>', esc_attr( $name ), $id );
		foreach ( $children as $key => $_value ) {
			$selected = ( $key == $value )? ' selected="selected"' : '';
			$_ret .= sprintf( '<option value="%s"%s>%s</option>',
				esc_attr( $key ), $selected, esc_html( $_value )
			);
		}
		$_ret .= '</select>';
		return $_ret;
	}

	/**
	 * radio
	 * radioタグ生成
	 * @param string $name name属性
	 * @param array $children
	 * @param array $options
	 * @return string HTML
	 */
	public function radio( $name, $children = array(), $options = array() ) {
		$defaults = array(
			'id' => '',
			'value' => ''
		);
		$options = array_merge( $defaults, $options );
		$value = $options['value'];
		if ( !is_null( $value ) ) {
			$value = $this->getValue( $name );
			if ( is_null( $value ) ) {
				$value = $options['value'];
			}
		}

		$i = 0;
		$_ret = '';
		foreach ( $children as $key => $_value ) {
			$i ++;
			$id = $this->get_attr_id( $options['id'], $i );
			$for = $this->get_attr_for( $options['id'], $i );
			$checked = ( $key == $value )? ' checked="checked"' : '';
			$_ret .= sprintf( '<label %s><input type="radio" name="%s" value="%s"%s %s />%s</label>',
				$for,
				esc_attr( $name ),
				esc_attr( $key ),
				$checked,
				$id,
				esc_html( $_value )
			);
		}
		return $_ret;
	}

	/**
	 * checkbox
	 * checkboxタグ生成
	 * @param string $name name属性
	 * @param array $children
	 * @param array $options
	 * @param string $separator 区切り文字
	 * @return string HTML
	 */
	public function checkbox( $name, $children = array(), $options = array(), $separator = ',' ) {
		$defaults = array(
			'id' => '',
			'value' => array()
		);
		$options = array_merge( $defaults, $options );
		$value = $options['value'];
		if ( !is_null( $value ) ) {
			$value = $this->getValue( $name );
			if ( is_array( $value ) && isset( $value['data'] ) ) {
				$value = $value['data'];
			} else {
				$value = $options['value'];
			}
			if ( !is_array( $value ) ) {
				$value = explode( $separator, $value );
			}
		}

		$i = 0;
		$_ret = '';
		foreach ( $children as $key => $_value ) {
			$i ++;
			$id = $this->get_attr_id( $options['id'], $i );
			$for = $this->get_attr_for( $options['id'], $i );
			$checked = ( is_array( $value ) && in_array( $key, $value ) )? ' checked="checked"' : '';
			$_ret .= sprintf( '<label %s><input type="checkbox" name="%s" value="%s"%s %s />%s</label>',
				$for,
				esc_attr( $name.'[data][]' ),
				esc_attr( $key ),
				$checked,
				$id,
				esc_html( $_value )
			);
		}
		$_ret .= $this->separator( $name, $separator );
		return $_ret;
	}

	/**
	 * submit
	 * submitボタン生成
	 * @param string $name name属性
	 * @param string $value value属性
	 * @return string submitボタン
	 */
	public function submit( $name, $value ) {
		return sprintf( '<input type="submit" name="%s" value="%s" />', esc_attr( $name ), esc_attr( $value ) );
	}

	/**
	 * button
	 * ボタン生成
	 * @param string $name name属性
	 * @param string $value value属性
	 * @return string ボタン
	 */
	public function button( $name, $value ) {
		return sprintf( '<input type="button" name="%s" value="%s" />', esc_attr( $name ), esc_attr( $value ) );
	}

	/**
	 * datepicker
	 * datepicker生成
	 * @param string $name name属性
	 * @param string $options
	 * @return string HTML
	 */
	public function datepicker( $name, $options = array() ) {
		$defaults = array(
			'id' => '',
			'size' => 30,
			'js' => '',
			'value' => '',
		);
		$options = array_merge( $defaults, $options );
		$value = $options['value'];
		if ( !is_null( $value ) ) {
			$value = $this->getValue( $name );
			if ( is_null( $value ) ) {
				$value = $options['value'];
			}
		}
		$id = $this->get_attr_id( $options['id'] );
		$_ret = sprintf( '<input type="text" name="%s" value="%s" size="%d" %s />',
			esc_attr( $name ), esc_attr( $value ), esc_attr( $options['size'] ), $id
		);
		$_ret .= sprintf(
			'<script type="text/javascript">
			jQuery( function( $ ) {
				$("input[name=\'%s\']").datepicker({%s});
			} );
			</script>'
		, esc_html( $name ), $options['js'] );
		return $_ret;
	}

	/**
	 * file
	 * input[type=file]タグ生成
	 * @param string $name name属性
	 * @param $options array
	 * @return string HTML
	 */
	public function file( $name, $options = array() ) {
		$defaults = array(
			'id' => '',
		);
		$id = $this->get_attr_id( $options['id'] );
		$options = array_merge( $defaults, $options );
		return sprintf( '<input type="file" name="%s" %s /><span data-mwform-file-delete="%1$s" class="mwform-file-delete">&times;</span>',
			esc_attr( $name ), $id
		);
	}

	/**
	 * get_attr_id
	 * ID属性を返す
	 * @param string $id
	 * @param string $suffix
	 * @return string id="hoge"
	 */
	protected function get_attr_id( $id, $suffix = '' ) {
		if ( !empty( $id ) ) {
			if ( $suffix ) {
				$id .= '-' . $suffix;
			}
			return 'id="' . esc_attr( $id ) . '"';
		}
	}

	/**
	 * get_attr_for
	 * for属性を返す
	 * @param string $id
	 * @param string $suffix
	 * @return string for="hoge"
	 */
	protected function get_attr_for( $id, $suffix = '' ) {
		if ( !empty( $id ) ) {
			if ( $suffix ) {
				$id .= '-' . $suffix;
			}
			return 'for="' . esc_attr( $id ) . '"';
		}
	}

	/**
	 * get_attr_placeholder
	 * placeholder属性を返す
	 * @param string $placeholder
	 * @return string placeholder="hoge"
	 */
	protected function get_attr_placeholder( $placeholder ) {
		if ( !empty( $placeholder ) ) {
			return 'placeholder="' . esc_attr( $placeholder ) . '"';
		}
	}
}