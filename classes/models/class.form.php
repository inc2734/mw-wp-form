<?php
/**
 * Name       : MW WP Form Form
 * Description: フォームヘルパー
 * Version    : 1.8.2
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : September 25, 2012
 * Modified   : March 26, 2016
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Form {

	/**
	 * データを返す
	 *
	 * @param string $key name属性値
	 * @return mixed
	 */
	public function get_raw( $key ) {
		MWF_Functions::deprecated_message(
			'MW_WP_Form_Form::get_raw()',
			'MW_WP_Form_Data::get_raw()'
		);
		$Data = MW_WP_Form_Data::getInstance();
		return $Data->get_raw( $key );
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
		$Data = MW_WP_Form_Data::getInstance();
		return $Data->get_raw_in_children( $key, $children );
	}

	/**
	 * データを返す ( 郵便番号用 )
	 *
	 * @param string $key name属性
	 * @return string データ
	 */
	public function get_zip_value( $key ) {
		MWF_Functions::deprecated_message(
			'MW_WP_Form_Form::get_zip_value()',
			'MW_WP_Form_Data::get_separated_value()'
		);
		$Data = MW_WP_Form_Data::getInstance();
		return $Data->get_separated_value( $key );
	}

	/**
	 * データを返す ( 電話番号用 )
	 *
	 * @param string $key name属性
	 * @return string データ
	 */
	public function get_tel_value( $key ) {
		MWF_Functions::deprecated_message(
			'MW_WP_Form_Form::get_tel_value()',
			'MW_WP_Form_Data::get_separated_value()'
		);
		$Data = MW_WP_Form_Data::getInstance();
		return $Data->get_separated_value( $key );
	}

	/**
	 * データを返す（ checkbox用 ）。$dataに含まれる値のみ返す
	 *
	 * @param string $key name属性
	 * @param array $data
	 * @return string データ
	 */
	public function get_checked_value( $key, array $data ) {
		MWF_Functions::deprecated_message(
			'MW_WP_Form_Form::get_checked_value()',
			'MW_WP_Form_Data::get_separated_value()'
		);
		$Data = MW_WP_Form_Data::getInstance();
		return $Data->get_separated_value( $key, $data );
	}

	/**
	 * データを返す（ radio用 ）。$dataに含まれる値のみ返す
	 *
	 * @param string name属性値
	 * @param array $data データ
	 * @return string
	 */
	public function get_radio_value( $key, array $data ) {
		MWF_Functions::deprecated_message(
			'MW_WP_Form_Form::get_radio_value()',
			'MW_WP_Form_Data::get_in_children()'
		);
		$Data = MW_WP_Form_Data::getInstance();
		return $Data->get_in_children( $key, $data );
	}

	/**
	 * データを返す（ selectbox用 ）。$dataに含まれる値のみ返す
	 *
	 * @param string $key name属性
	 * @param array $data データ
	 * @return string データ
	 */
	public function get_selected_value( $key, array $data ) {
		MWF_Functions::deprecated_message(
			'MW_WP_Form_Form::get_selected_value()',
			'MW_WP_Form_Data::get_in_children()'
		);
		$Data = MW_WP_Form_Data::getInstance();
		return $Data->get_in_children( $key, $data );
	}

	/**
	 * 配列データを整形して返す ( チェックボックス等用 )。配列の場合はpost値を連結して返す
	 *
	 * @param string $key name属性
	 * @param array $children 選択肢
	 * @return string データ
	 */
	public function get_separated_raw_value( $key, array $children = array() ) {
		MWF_Functions::deprecated_message(
			'MW_WP_Form_Form::get_separated_raw_value()',
			'MW_WP_Form_Data::get_separated_raw_value()'
		);
		$Data = MW_WP_Form_Data::getInstance();
		return $Data->get_separated_raw_value( $key, $children );
	}

	/**
	 * separator を設定するためのhiddenを返す
	 *
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
	 * 送られてきた separator を返す
	 *
	 * @param string $key name属性
	 * @return string
	 */
	public function get_separator_value( $key ) {
		MWF_Functions::deprecated_message(
			'MW_WP_Form_Form::get_separator_value()',
			'MW_WP_Form_Data::get_separator_value()'
		);
		return $Data->get_separator_value( $key );
	}

	/**
	 * children を設定するための hidden を返す
	 *
	 * @param string $key name属性
	 * @param array $children 選択肢の配列（必ず MW_WP_Form_Abstract_Form_Field::get_children の値 ）
	 * @return string HTML
	 */
	public function children( $key, array $children ) {
		$name = sprintf( '__children[%s][]', $key );
		return $this->hidden( $name, json_encode( $children ) );
	}

	/**
	 * フォームタグ生成
	 *
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
	 * トークンタグ、閉じタグ生成
	 *
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
			'id'          => null,
			'class'       => null,
			'size'        => 60,
			'maxlength'   => null,
			'value'       => '',
			'placeholder' => null,
			'conv-half-alphanumeric' => null,
		);
		$options = array_merge( $defaults, $options );
		$attributes = $this->generate_attributes( $options );

		return sprintf(
			'<input type="text" name="%s"%s />',
			esc_attr( $name ),
			$attributes
		);
	}

	/**
	 * input[type=email]タグ生成
	 *
	 * @param string $name name属性
	 * @param array
	 * @return string html
	 */
	public function email( $name, $options = array() ) {
		$defaults = array(
			'id'          => null,
			'class'       => null,
			'size'        => 60,
			'maxlength'   => null,
			'value'       => '',
			'placeholder' => null,
			'conv-half-alphanumeric' => null,
		);
		$options = array_merge( $defaults, $options );
		$attributes = $this->generate_attributes( $options );

		return sprintf(
			'<input type="email" name="%s"%s />',
			esc_attr( $name ),
			$attributes
		);
	}

	/**
	 * input[type=url]タグ生成
	 *
	 * @param string $name name属性
	 * @param array
	 * @return string html
	 */
	public function url( $name, $options = array() ) {
		$defaults = array(
			'id'          => null,
			'class'       => null,
			'size'        => 60,
			'maxlength'   => null,
			'value'       => '',
			'placeholder' => null,
			'conv-half-alphanumeric' => null,
		);
		$options = array_merge( $defaults, $options );
		$attributes = $this->generate_attributes( $options );

		return sprintf(
			'<input type="url" name="%s"%s />',
			esc_attr( $name ),
			$attributes
		);
	}

	/**
	 * input[type=range]タグ生成
	 *
	 * @param string $name name属性
	 * @param array
	 * @return string html
	 */
	public function range( $name, $options = array() ) {
		$defaults = array(
			'id'    => null,
			'class' => null,
			'value' => '',
			'min'   => 0,
			'max'   => 100,
			'step'  => 1,
		);
		$options = array_merge( $defaults, $options );
		$attributes = $this->generate_attributes( $options );

		return sprintf(
			'<input type="range" name="%s"%s />',
			esc_attr( $name ),
			$attributes
		);
	}

	/**
	 * input[type=number]タグ生成
	 *
	 * @param string $name name属性
	 * @param array
	 * @return string html
	 */
	public function number( $name, $options = array() ) {
		$defaults = array(
			'id'          => null,
			'class'       => null,
			'value'       => '',
			'min'         => null,
			'max'         => null,
			'step'        => 1,
			'placeholder' => null,
		);
		$options = array_merge( $defaults, $options );
		$attributes = $this->generate_attributes( $options );

		return sprintf(
			'<input type="number" name="%s"%s />',
			esc_attr( $name ),
			$attributes
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
			'id'          => null,
			'class'       => null,
			'size'        => 60,
			'maxlength'   => null,
			'value'       => '',
			'placeholder' => null,
		);
		$options = array_merge( $defaults, $options );
		$attributes = $this->generate_attributes( $options );

		return sprintf(
			'<input type="password" name="%s"%s />',
			esc_attr( $name ),
			$attributes
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
			'class' => null,
			'conv-half-alphanumeric' => null,
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
			'class'     => $options['class'],
			'size'      => 4,
			'maxlength' => 3,
			'value'     => $values[0],
			'conv-half-alphanumeric' => $options['conv-half-alphanumeric'],
		) );
		$_ret .= ' ' . $separator . ' ';
		$_ret .= $this->text( $name . '[data][1]', array(
			'class'     => $options['class'],
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
			'class' => null,
			'conv-half-alphanumeric' => null,
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
			'class'     => $options['class'],
			'size'      => 6,
			'maxlength' => 5,
			'value'     => $values[0],
			'conv-half-alphanumeric' => $options['conv-half-alphanumeric'],
		) );
		$_ret .= ' ' . $separator . ' ';
		$_ret .= $this->text( $name . '[data][1]', array(
			'class'     => $options['class'],
			'size'      => 5,
			'maxlength' => 4,
			'value'     => $values[1],
			'conv-half-alphanumeric' => $options['conv-half-alphanumeric'],
		) );
		$_ret .= ' ' . $separator . ' ';
		$_ret .= $this->text( $name . '[data][2]', array(
			'class'     => $options['class'],
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
			'id'          => null,
			'class'       => null,
			'cols'        => 50,
			'rows'        => 5,
			'value'       => '',
			'placeholder' => null,
		);
		$options = array_merge( $defaults, $options );
		$_options = $options;
		unset( $_options['value'] );
		$attributes = $this->generate_attributes( $_options );

		return sprintf(
			'<textarea name="%s"%s>%s</textarea>',
			esc_attr( $name ),
			$attributes,
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
			'class' => null,
			'id'    => null,
			'value' => '',
		);
		$options = array_merge( $defaults, $options );

		$_options = $options;
		unset( $_options['value'] );
		$attributes = $this->generate_attributes( $_options );
		$_ret = sprintf(
			'<select name="%s"%s>',
			esc_attr( $name ),
			$attributes
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
			'class'      => null,
			'id'         => '',
			'value'      => '',
			'vertically' => null,
		);
		$options = array_merge( $defaults, $options );

		$i    = 0;
		$_ret = '';
		foreach ( $children as $key => $_value ) {
			$i ++;
			$vertically = ( $options['vertically'] === 'true' ) ? 'vertical-item' : 'horizontal-item';
			$attributes_for_label = $this->generate_attributes( array(
				'for' => $this->get_attr_id( $options['id'], $i ),
			) );
			$attributes = $this->generate_attributes( array(
				'id'    => $this->get_attr_id( $options['id'], $i ),
				'class' => $options['class'],
			) );
			$_ret .= sprintf(
				'<span class="mwform-radio-field %s"><label%s><input type="radio" name="%s" value="%s"%s %s />%s</label></span>',
				$vertically,
				$attributes_for_label,
				esc_attr( $name ),
				esc_attr( $key ),
				checked( $key, $options['value'], false ),
				$attributes,
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
			'id'         => null,
			'class'      => null,
			'value'      => '',
			'vertically' => null,
		);
		$options = array_merge( $defaults, $options );

		$value = $options['value'];
		if ( !is_array( $options['value'] ) ) {
			$value = explode( $separator, $options['value'] );
		}

		$i    = 0;
		$_ret = '';
		foreach ( $children as $key => $_value ) {
			$i ++;
			$vertically = ( $options['vertically'] === 'true' ) ? 'vertical-item' : 'horizontal-item';
			$attributes_for_label = $this->generate_attributes( array(
				'for' => $this->get_attr_id( $options['id'], $i ),
			) );
			$attributes = $this->generate_attributes( array(
				'id'    => $this->get_attr_id( $options['id'], $i ),
				'class' => $options['class'],
			) );
			$_ret .= sprintf(
				'<span class="mwform-checkbox-field %s"><label%s><input type="checkbox" name="%s" value="%s"%s %s />%s</label></span>',
				$vertically,
				$attributes_for_label,
				esc_attr( $name . '[data][]' ),
				esc_attr( $key ),
				$attributes,
				checked( ( is_array( $value ) && in_array( $key, $value ) ), true, false ),
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
	 * @param array $options
	 * @return string submitボタン
	 */
	public function submit( $name, $value, $options = array() ) {
		$defaults = array(
			'class' => null,
		);
		$options = array_merge( $defaults, $options );
		$attributes = $this->generate_attributes( $options );
		return sprintf(
			'<input type="submit" name="%s" value="%s"%s />',
			esc_attr( $name ),
			esc_attr( $value ),
			$attributes
		);
	}

	/**
	 * ボタン生成
	 *
	 * @param string $name name属性
	 * @param string $value value属性
	 * @param array $options
	 * @return string ボタン
	 */
	public function button( $name, $value, $options = array() ) {
		$defaults = array(
			'class' => null,
		);
		$options = array_merge( $defaults, $options );
		$attributes = $this->generate_attributes( $options );
		return sprintf(
			'<input type="button" name="%s" value="%s"%s />',
			esc_attr( $name ),
			esc_attr( $value ),
			$attributes
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
			'id'          => null,
			'class'       => null,
			'size'        => 30,
			'js'          => '',
			'value'       => '',
			'placeholder' => null,
		);
		$options = array_merge( $defaults, $options );
		$_options = $options;
		unset( $_options['js'] );
		$attributes = $this->generate_attributes( $_options );

		$_ret = sprintf(
			'<input type="text" name="%s"%s />',
			esc_attr( $name ),
			$attributes
		);
		$_ret .= sprintf(
			'<script type="text/javascript">jQuery( function( $ ) { $("input[name=\'%s\']").datepicker( { %s } ); } );</script>',
			esc_js( $name ),
			trim( $options['js'], '{}' )
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
			'id'    => null,
			'class' => null,
		);
		$options = array_merge( $defaults, $options );
		$attributes = $this->generate_attributes( $options );

		return sprintf(
			'<input type="file" name="%1$s"%2$s /><span data-mwform-file-delete="%1$s" class="mwform-file-delete">&times;</span>',
			esc_attr( $name ),
			$attributes
		);
	}

	/**
	 * タグの属性を最適化して生成する
	 * ※テストしやすいようにアクセス修飾子を public に
	 *
	 * @param array $_attributes キーが属性名、要素が属性値の配列。要素が null のときは無視する
	 */
	public function generate_attributes( array $_attributes ) {
		$attributes = array();
		foreach ( $_attributes as $key => $value ) {
			if ( is_null( $value ) ) {
				continue;
			}
			if ( $key === 'conv-half-alphanumeric' ) {
				$key = 'data-conv-half-alphanumeric';
			}
			$attributes[] = sprintf( '%s="%s"', $key, esc_attr( $value ) );
		}
		$attributes = implode( ' ', $attributes );
		if ( $attributes ) {
			return ' ' . $attributes;
		}
	}

	/**
	 * id属性を返す
	 *
	 * @param string $id
	 * @param string $suffix
	 * @return string
	 */
	protected function get_attr_id( $id, $suffix = '' ) {
		if ( !MWF_Functions::is_empty( $id ) ) {
			if ( $suffix ) {
				$id .= '-' . $suffix;
			}
			return $id;
		}
	}
}
