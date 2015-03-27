<?php
/**
 * Name       : MW WP Form Form
 * Description: フォームヘルパー
 * Version    : 1.6.0
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : September 25, 2012
 * Modified   : March 26, 2015
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Form {

	/**
	 * get_raw
	 * データを返す
	 * @param string $key name属性値
	 * @return mixed
	 */
	public function get_raw( $key ) {
		MWF_Functions::deprecated_message(
			'MW_WP_Form_Form::get_raw()',
			'MW_WP_Form_Data::get_raw()'
		);
		return $this->Data->get_raw( $key );
	}

	/**
	 * $children の中に値が含まれているときだけ返す
	 *
	 * @param string $key name属性
	 * @param array $children
	 * @return string
	 */
	public function get_raw_in_children( $key, array $children ) {
		MWF_Functions::deprecated_message(
			'MW_WP_Form_Form::get_raw_in_children()',
			'MW_WP_Form_Data::get_raw_in_children()'
		);
		return $this->Data->get_raw_in_children( $key, $children );
	}

	/**
	 * get_zip_value
	 * データを返す ( 郵便番号用 )
	 * @param string $key name属性
	 * @return string データ
	 */
	public function get_zip_value( $key ) {
		MWF_Functions::deprecated_message(
			'MW_WP_Form_Form::get_zip_value()',
			'MW_WP_Form_Data::get_separated_value()'
		);
		return $this->Data->get_separated_value( $key );
	}

	/**
	 * get_tel_value
	 * データを返す ( 電話番号用 )
	 * @param string $key name属性
	 * @return string データ
	 */
	public function get_tel_value( $key ) {
		MWF_Functions::deprecated_message(
			'MW_WP_Form_Form::get_zip_value()',
			'MW_WP_Form_Data::get_separated_value()'
		);
		return $this->Data->get_separated_value( $key );
	}

	/**
	 * get_checked_value
	 * データを返す（ checkbox用 ）。$dataに含まれる値のみ返す
	 * @param string $key name属性
	 * @param array $data
	 * @return string データ
	 */
	public function get_checked_value( $key, array $data ) {
		MWF_Functions::deprecated_message(
			'MW_WP_Form_Form::get_checked_value()',
			'MW_WP_Form_Data::get_separated_value()'
		);
		return $this->Data->get_separated_value( $key, $data );
	}

	/**
	 * get_radio_value
	 * データを返す（ radio用 ）。$dataに含まれる値のみ返す
	 * @param string name属性値
	 * @param array $data データ
	 * @return string
	 */
	public function get_radio_value( $key, array $data ) {
		MWF_Functions::deprecated_message(
			'MW_WP_Form_Form::get_radio_value()',
			'MW_WP_Form_Data::get_in_children()'
		);
		return $this->Data->get_in_children( $key, $data );
	}

	/**
	 * get_selected_value
	 * データを返す（ selectbox用 ）。$dataに含まれる値のみ返す
	 * @param string $key name属性
	 * @param array $data データ
	 * @return string データ
	 */
	public function get_selected_value( $key, array $data ) {
		MWF_Functions::deprecated_message(
			'MW_WP_Form_Form::get_selected_value()',
			'MW_WP_Form_Data::get_in_children()'
		);
		return $this->Data->get_in_children( $key, $data );
	}

	/**
	 * get_separated_raw_value
	 * 配列データを整形して返す ( チェックボックス等用 )。配列の場合はpost値を連結して返す
	 * @param string $key name属性
	 * @param array $children 選択肢
	 * @return string データ
	 */
	public function get_separated_raw_value( $key, array $children = array() ) {
		MWF_Functions::deprecated_message(
			'MW_WP_Form_Form::get_separated_raw_value()',
			'MW_WP_Form_Data::get_separated_raw_value()'
		);
		return $this->Data->get_separated_raw_value( $key, $children );
	}

	/**
	 * separator
	 * separatorを設定するためのhiddenを返す
	 * @param string $key name属性
	 * @param string $separator 区切り文字
	 * @return string HTML
	 */
	public function separator( $key, $separator ) {
		if ( $separator ) {
			return $this->hidden( $key . '[separator]', $separator );
		}
	}

	/**
	 * get_separator_value
	 * 送られてきたseparatorを返す
	 * @param string $key name属性
	 * @return string
	 */
	public function get_separator_value( $key ) {
		MWF_Functions::deprecated_message(
			'MW_WP_Form_Form::get_separator_value()',
			'MW_WP_Form_Data::get_separator_value()'
		);
		return $this->Data->get_separator_value( $key );
	}

	/**
	 * children
	 * childrenを設定するためのhiddenを返す
	 * @param string $key name属性
	 * @param array $children 選択肢の配列（必ず MW_WP_Form_Abstract_Form_Field::get_children の値 ）
	 * @return string HTML
	 */
	public function children( $key, array $children ) {
		$name = sprintf( '__children[%s]', $key );
		return $this->hidden( $name, json_encode( $children ) );
	}

	/**
	 * start
	 * フォームタグ生成
	 * @param array $options
	 * @return string form開始タグ
	 */
	public function start( $options = array() ) {
		$defaults = array(
			'action'  => '',
			'enctype' => 'multipart/form-data',
		);
		$options = array_merge( $defaults, $options );
		return sprintf(
			'<form method="post" action="%s" enctype="%s">',
			esc_attr( $options['action'] ),
			esc_attr( $options['enctype'] )
		);
	}

	/**
	 * end
	 * トークンタグ、閉じタグ生成
	 * @return string input[type=hidden]
	 */
	public function end() {
		$html = '';
		$html = apply_filters( 'mwform_form_end_html', $html );
		$html .= '</form>';
		return $html;
	}

	/**
	 * input[type=text]タグ生成
	 *
	 * @param string $name name属性
	 * @param array
	 * @return string html
	 */
	public function text( $name, $options = array() ) {
		$defaults = array(
			'id'          => '',
			'size'        => 60,
			'maxlength'   => 255,
			'value'       => '',
			'conv-half-alphanumeric' => false,
			'placeholder' => '',
		);
		$options = array_merge( $defaults, $options );
		$placeholder = $this->get_attr_placeholder( $options['placeholder'] );
		$data_conv_half_alphanumeric = null;
		if ( $options['conv-half-alphanumeric'] === true ) {
			$data_conv_half_alphanumeric = 'data-conv-half-alphanumeric="true"';
		}
		$id = $this->get_attr_id( $options['id'] );
		return sprintf(
			'<input type="text" name="%s" value="%s" size="%d" maxlength="%d" %s %s %s />',
			esc_attr( $name ),
			esc_attr( $options['value'] ),
			esc_attr( $options['size'] ),
			esc_attr( $options['maxlength'] ),
			$placeholder,
			$data_conv_half_alphanumeric,
			$id
		);
	}

	/**
	 * input[type=hidden]タグ生成
	 *
	 * @param string $name name属性
	 * @param string $value 値
	 * @return string HTML
	 */
	public function hidden( $name, $value ) {
		// todo
		if ( is_null( $value ) ) {
			var_dump( 'this is null !' );
			var_dump( $name );
		}
		if ( is_array( $value ) ) {
			var_dump( 'this is array !' );
			var_dump( $name );
		}
		/*
		if ( is_null( $value ) ) {
			$value = $this->get_raw( $name );
		}
		if ( is_array( $value ) ) {
			$value = $this->get_zip_value( $name );
		}
		*/
		return sprintf(
			'<input type="hidden" name="%s" value="%s" />',
			esc_attr( $name ),
			esc_attr( $value )
		);
	}

	/**
	 * input[type=password]タグ生成
	 *
	 * @param string $name name属性
	 * @param array $options
	 * @return string HTML
	 */
	public function password( $name, $options = array() ) {
		$defaults = array(
			'id'          => '',
			'size'        => 60,
			'maxlength'   => 255,
			'value'       => '',
			'placeholder' => '',
		);
		$options = array_merge( $defaults, $options );
		$placeholder = $this->get_attr_placeholder( $options['placeholder'] );
		$id = $this->get_attr_id( $options['id'] );
		return sprintf(
			'<input type="password" name="%s" value="%s" size="%d" maxlength="%d" %s %s />',
			esc_attr( $name ),
			esc_attr( $options['value'] ),
			esc_attr( $options['size'] ),
			esc_attr( $options['maxlength'] ),
			$placeholder,
			$id
		);
	}

	/**
	 * 郵便番号フィールド生成
	 *
	 * @param string $name name属性
	 * @param array $options
	 * @return string HTML
	 */
	public function zip( $name, $options = array() ) {
		$defaults = array(
			'conv-half-alphanumeric' => false,
			'value' => '',
		);
		$options = array_merge( $defaults, $options );

		$children  = array();
		$separator = '-';

		if ( is_array( $options['value'] ) ) {
			$children = $options['value'];
		} else {
			$children = explode( $separator, $options['value'] );
		}

		$values = array( '', '' );
		foreach ( $children as $key => $val ) {
			if ( $key === 0 || $key === 1 ) {
				$values[$key] = $val;
			}
		}

		$_ret  = '<span class="mwform-zip-field">';
		$_ret .= '〒';
		$_ret .= $this->text( $name . '[data][0]', array(
			'size'      => 4,
			'maxlength' => 3,
			'value'     => $values[0],
			'conv-half-alphanumeric' => $options['conv-half-alphanumeric'],
		) );
		$_ret .= ' ' . $separator . ' ';
		$_ret .= $this->text( $name . '[data][1]', array(
			'size'      => 5,
			'maxlength' => 4,
			'value'     => $values[1],
			'conv-half-alphanumeric' => $options['conv-half-alphanumeric'],
		) );
		$_ret .= $this->separator( $name, $separator );
		$_ret .= '</span>';
		return $_ret;
	}

	/**
	 * 電話番号フィールド生成
	 *
	 * @param string $name name属性
	 * @param array $options
	 * @return string HTML
	 */
	public function tel( $name, $options = array() ) {
		$defaults = array(
			'conv-half-alphanumeric' => false,
			'value' => '',
		);
		$options = array_merge( $defaults, $options );

		$children  = array();
		$separator = '-';

		if ( is_array( $options['value'] ) ) {
			$children = $options['value'];
		} else {
			$children = explode( $separator, $options['value'] );
		}

		$values = array( '', '', '' );
		foreach ( $children as $key => $val ) {
			if ( $key === 0 || $key === 1 || $key === 2 ) {
				$values[$key] = $val;
			}
		}

		$_ret  = '<span class="mwform-tel-field">';
		$_ret .= $this->text( $name . '[data][0]', array(
			'size'      => 6,
			'maxlength' => 5,
			'value'     => $values[0],
			'conv-half-alphanumeric' => $options['conv-half-alphanumeric'],
		) );
		$_ret .= ' ' . $separator . ' ';
		$_ret .= $this->text( $name . '[data][1]', array(
			'size'      => 5,
			'maxlength' => 4,
			'value'     => $values[1],
			'conv-half-alphanumeric' => $options['conv-half-alphanumeric'],
		) );
		$_ret .= ' ' . $separator . ' ';
		$_ret .= $this->text( $name . '[data][2]', array(
			'size'      => 5,
			'maxlength' => 4,
			'value'     => $values[2],
			'conv-half-alphanumeric' => $options['conv-half-alphanumeric'],
		) );
		$_ret .= $this->separator( $name, $separator );
		$_ret .= '</span>';
		return $_ret;
	}

	/**
	 * textareaタグ生成
	 *
	 * @param string $name name属性
	 * @param array $options
	 * @return string html
	 */
	public function textarea( $name, $options = array() ) {
		$defaults = array(
			'id'          => '',
			'cols'        => 50,
			'rows'        => 5,
			'value'       => '',
			'placeholder' => '',
		);
		$options = array_merge( $defaults, $options );
		return sprintf(
			'<textarea name="%s" cols="%d" rows="%d" %s %s>%s</textarea>',
			esc_attr( $name ),
			esc_attr( $options['cols'] ),
			esc_attr( $options['rows'] ),
			$this->get_attr_placeholder( $options['placeholder'] ),
			$this->get_attr_id( $options['id'] ),
			esc_html( $options['value'] )
		);
	}

	/**
	 * selectタグ生成
	 *
	 * @param string $name name属性
	 * @param array $children
	 * @param array $options
	 * @return string HTML
	 */
	public function select( $name, $children = array(), $options = array() ) {
		$defaults = array(
			'id'    => '',
			'value' => '',
		);
		$options = array_merge( $defaults, $options );
		$_ret = sprintf(
			'<select name="%s" %s>',
			esc_attr( $name ),
			$this->get_attr_id( $options['id'] )
		);
		foreach ( $children as $key => $_value ) {
			$_ret .= sprintf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $key ),
				selected( $key, $options['value'], false ),
				esc_html( $_value )
			);
		}
		$_ret .= '</select>';
		return $_ret;
	}

	/**
	 * radioタグ生成
	 *
	 * @param string $name name属性
	 * @param array $children
	 * @param array $options
	 * @return string HTML
	 */
	public function radio( $name, $children = array(), $options = array() ) {
		$defaults = array(
			'id'         => '',
			'value'      => '',
			'vertically' => '',
		);
		$options = array_merge( $defaults, $options );

		$i    = 0;
		$_ret = '';
		foreach ( $children as $key => $_value ) {
			$i ++;
			$vertically = ( $options['vertically'] === 'true' ) ? 'vertical-item' : '';
			$_ret .= sprintf(
				'<span class="%s"><label %s><input type="radio" name="%s" value="%s" %s %s />%s</label></span>',
				$vertically,
				$this->get_attr_for( $options['id'], $i ),
				esc_attr( $name ),
				esc_attr( $key ),
				checked( $key, $options['value'], false ),
				$this->get_attr_id( $options['id'], $i ),
				esc_html( $_value )
			);
		}
		return $_ret;
	}

	/**
	 * checkboxタグ生成
	 *
	 * @param string $name name属性
	 * @param array $children
	 * @param array $options
	 * @param string $separator 区切り文字
	 * @return string HTML
	 */
	public function checkbox( $name, $children = array(), $options = array(), $separator = ',' ) {
		$defaults = array(
			'id'         => '',
			'value'      => '',
			'vertically' => '',
		);
		$options = array_merge( $defaults, $options );

		$value = array();
		if ( !is_array( $options['value'] ) ) {
			$value = explode( $separator, $options['value'] );
		}

		/*
		$value = $this->get_raw( $name );
		if ( is_array( $value ) && isset( $value['data'] ) ) {
			$value = $value['data'];
			// 送信された後の画面の場合は、送信された separator で区切る
			if ( !is_array( $value ) ) {
				$value = explode( $separator, $value );
			}
		} else {
			$value = $options['value'];
			// 最初の画面（post無し）の場合は、管理画面上で children が,区切りとなっている
			if ( !is_array( $value ) ) {
				$value = explode( ',', $value );
			}
		}
		*/

		$i    = 0;
		$_ret = '';
		foreach ( $children as $key => $_value ) {
			$i ++;
			$vertically = ( $options['vertically'] === 'true' ) ? 'vertical-item' : '';
			$checked = ( is_array( $value ) && in_array( $key, $value ) )? ' checked="checked"' : '';
			$_ret .= sprintf(
				'<span class="%s"><label %s><input type="checkbox" name="%s" value="%s"%s %s />%s</label></span>',
				$vertically,
				$this->get_attr_for( $options['id'], $i ),
				esc_attr( $name . '[data][]' ),
				esc_attr( $key ),
				checked( ( is_array( $value ) && in_array( $key, $value ) ), true, false ),
				$this->get_attr_id( $options['id'], $i ),
				esc_html( $_value )
			);
		}
		$_ret .= $this->separator( $name, $separator );
		return $_ret;
	}

	/**
	 * submitボタン生成
	 *
	 * @param string $name name属性
	 * @param string $value value属性
	 * @return string submitボタン
	 */
	public function submit( $name, $value ) {
		return sprintf(
			'<input type="submit" name="%s" value="%s" />',
			esc_attr( $name ),
			esc_attr( $value )
		);
	}

	/**
	 * ボタン生成
	 *
	 * @param string $name name属性
	 * @param string $value value属性
	 * @return string ボタン
	 */
	public function button( $name, $value ) {
		return sprintf(
			'<input type="button" name="%s" value="%s" />',
			esc_attr( $name ),
			esc_attr( $value )
		);
	}

	/**
	 * datepicker生成
	 *
	 * @param string $name name属性
	 * @param string $options
	 * @return string HTML
	 */
	public function datepicker( $name, $options = array() ) {
		$defaults = array(
			'id'    => '',
			'size'  => 30,
			'js'    => '',
			'value' => '',
		);
		$options = array_merge( $defaults, $options );

		$_ret = sprintf(
			'<input type="text" name="%s" value="%s" size="%d" %s />',
			esc_attr( $name ),
			esc_attr( $options['value'] ),
			esc_attr( $options['size'] ),
			$this->get_attr_id( $options['id'] )
		);
		$_ret .= sprintf(
			'<script type="text/javascript">
			jQuery( function( $ ) {
				$("input[name=\'%s\']").datepicker( { %s } );
			} );
			</script>',
			esc_js( $name ),
			$options['js']
		);
		return $_ret;
	}

	/**
	 * input[type=file]タグ生成
	 *
	 * @param string $name name属性
	 * @param $options array
	 * @return string HTML
	 */
	public function file( $name, $options = array() ) {
		$defaults = array(
			'id' => '',
		);
		$options = array_merge( $defaults, $options );
		return sprintf(
			'<input type="file" name="%s" %s />
			<span data-mwform-file-delete="%1$s" class="mwform-file-delete">&times;</span>',
			esc_attr( $name ),
			$this->get_attr_id( $options['id'] )
		);
	}

	/**
	 * ID属性を返す
	 *
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
	 * for属性を返す
	 *
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
	 * placeholder属性を返す
	 *
	 * @param string $placeholder
	 * @return string placeholder="hoge"
	 */
	protected function get_attr_placeholder( $placeholder ) {
		if ( !empty( $placeholder ) ) {
			return 'placeholder="' . esc_attr( $placeholder ) . '"';
		}
	}
}