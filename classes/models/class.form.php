<?php
/**
 * Name       : MW WP Form Form
 * Description: フォームヘルパー
 * Version    : 1.11.0
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : September 25, 2012
 * Modified   : March 9, 2017
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
	 * テンプレートを読み込んで表示
	 *
	 * @todo テーマから上書きできるようにしたい
	 * @param string $template ディレクトリ名/ファイル名（拡張子無し）
	 */
	protected function _render( $template, array $args = array() ) {
		$template_path = locate_template( 'mw-wp-form/form-fields/' . $template );
		if ( ! $template_path ) {
			$template_path  = plugin_dir_path( __FILE__ ) . '../../templates/form-fields/' . $template . '.php';
			if ( ! file_exists( $template_path ) ) {
				return;
			}
		}

		extract( $args );
		ob_start();
		include( $template_path );
		return ob_get_clean();
	}

	/**
	 * フォームタグ生成
	 *
	 * @param array $options
	 * @return string form開始タグ
	 */
	public function start( $options = array() ) {
		$options = array_merge( array(
			'action'  => '',
			'enctype' => 'multipart/form-data',
		), $options );

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

		return $this->remove_linefeed_space(
			$this->_render( 'text', array(
				'name'       => $name,
				'attributes' => $attributes,
			) )
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

		return $this->remove_linefeed_space(
			$this->_render( 'email', array(
				'name'       => $name,
				'attributes' => $attributes,
			) )
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

		return $this->remove_linefeed_space(
			$this->_render( 'url', array(
				'name'       => $name,
				'attributes' => $attributes,
			) )
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

		return $this->remove_linefeed_space(
			$this->_render( 'range', array(
				'name'       => $name,
				'attributes' => $attributes,
			) )
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

		return $this->remove_linefeed_space(
			$this->_render( 'number', array(
				'name'       => $name,
				'attributes' => $attributes,
			) )
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
		return $this->remove_linefeed_space(
			$this->_render( 'hidden', array(
				'name'  => $name,
				'value' => $value,
			) )
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

		return $this->remove_linefeed_space(
			$this->_render( 'password', array(
				'name'       => $name,
				'attributes' => $attributes,
			) )
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

		return $this->remove_linefeed_space(
			$this->_render( 'zip', array(
				'name'      => $name,
				'separator' => $separator,
				'fields'    => array(
					array(
						'attributes' => $this->generate_attributes( array(
							'class'     => $options['class'],
							'size'      => 4,
							'maxlength' => 3,
							'value'     => $values[0],
							'conv-half-alphanumeric' => $options['conv-half-alphanumeric'],
						) ),
					),
					array(
						'attributes' => $this->generate_attributes( array(
							'class'     => $options['class'],
							'size'      => 5,
							'maxlength' => 4,
							'value'     => $values[1],
							'conv-half-alphanumeric' => $options['conv-half-alphanumeric'],
						) ),
					),
					array(
						'attributes' => $this->generate_attributes( array(
							'class'     => $options['class'],
							'size'      => 5,
							'maxlength' => 4,
							'value'     => $values[1],
							'conv-half-alphanumeric' => $options['conv-half-alphanumeric'],
						) ),
					),
				),
			) )
			. $this->separator( $name, $separator )
		);
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

		return $this->remove_linefeed_space(
			$this->_render( 'tel', array(
				'name'      => $name,
				'separator' => $separator,
				'fields'    => array(
					array(
						'attributes' => $this->generate_attributes( array(
							'class'     => $options['class'],
							'size'      => 6,
							'maxlength' => 5,
							'value'     => $values[0],
							'conv-half-alphanumeric' => $options['conv-half-alphanumeric'],
						) ),
					),
					array(
						'attributes' => $this->generate_attributes( array(
							'class'     => $options['class'],
							'size'      => 5,
							'maxlength' => 4,
							'value'     => $values[1],
							'conv-half-alphanumeric' => $options['conv-half-alphanumeric'],
						) ),
					),
					array(
						'attributes' => $this->generate_attributes( array(
							'class'     => $options['class'],
							'size'      => 5,
							'maxlength' => 4,
							'value'     => $values[2],
							'conv-half-alphanumeric' => $options['conv-half-alphanumeric'],
						) ),
					),
				),
			) )
			. $this->separator( $name, $separator )
		);
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
		$value   = $options['value'];
		unset( $options['value'] );
		$attributes = $this->generate_attributes( $options );

		return $this->remove_linefeed_space(
			$this->_render( 'textarea', array(
				'name'       => $name,
				'attributes' => $attributes,
				'value'      => $value,
			) )
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
		$value   = $options['value'];

		unset( $options['value'] );
		$attributes = $this->generate_attributes( $options );

		return $this->remove_linefeed_space(
			$this->_render( 'select', array(
				'name'       => $name,
				'value'      => $value,
				'attributes' => $attributes,
				'children'   => $children,
			) )
		);
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

		$i = 0;
		$fields = array();
		foreach ( $children as $key => $label ) {
			$i ++;
			$fields[ $key ] = array(
				'label' => $label,
				'attributes_for_label' => $this->generate_attributes( array(
					'for' => $this->_get_attr_id( $options['id'], $i ),
				) ),
				'attributes' => $this->generate_attributes( array(
					'for' => $this->_get_attr_id( $options['id'], $i ),
				) )
			);
		}

		return $this->remove_linefeed_space(
			$this->_render( 'radio', array(
				'name'       => $name,
				'vertically' => ( $options['vertically'] === 'true' ) ? 'vertical-item' : 'horizontal-item',
				'value'      => $options['value'],
				'fields'     => $fields,
			) )
		);
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

		$i = 0;
		$fields = array();
		foreach ( $children as $key => $label ) {
			$i ++;
			$fields[ $key ] = array(
				'label' => $label,
				'attributes_for_label' => $this->generate_attributes( array(
					'for' => $this->_get_attr_id( $options['id'], $i ),
				) ),
				'attributes' => $this->generate_attributes( array(
					'id'    => $this->_get_attr_id( $options['id'], $i ),
					'class' => $options['class'],
				) )
			);
		}

		return $this->remove_linefeed_space(
			$this->_render( 'checkbox', array(
				'name'       => $name,
				'vertically' => ( $options['vertically'] === 'true' ) ? 'vertical-item' : 'horizontal-item',
				'value'      => $value,
				'fields'     => $fields,
			) )
			. $this->separator( $name, $separator )
		);
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

		return $this->remove_linefeed_space(
			$this->_render( 'submit', array(
				'name'       => $name,
				'value'      => $value,
				'attributes' => $attributes,
			) )
		);
	}

	/**
	 * submitボタン(button)生成
	 *
	 * @param string $name name属性
	 * @param string $value value属性
	 * @param array $options
	 * @param string $element_content
	 * @return string submitボタン(button)
	 */
	public function button_submit( $name, $value, $options = array(), $element_content = '' ) {
		$defaults = array(
			'class' => null,
		);
		$options = array_merge( $defaults, $options );
		$attributes = $this->generate_attributes( $options );

		return $this->remove_linefeed_space(
			$this->_render( 'button_submit', array(
				'name'            => $name,
				'value'           => $value,
				'attributes'      => $attributes,
				'element_content' => $element_content,
			) )
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

		return $this->remove_linefeed_space(
			$this->_render( 'button', array(
				'name'       => $name,
				'value'      => $value,
				'attributes' => $attributes,
			) )
		);
	}

	/**
	 * ボタン(button)生成
	 *
	 * @param string $name name属性
	 * @param string $value value属性
	 * @param array $options
	 * @param string $element_content
	 * @return string ボタン(button)
	 */
	public function button_button( $name, $value, $options = array(), $element_content = '' ) {
		$defaults = array(
			'class' => null,
		);
		$options = array_merge( $defaults, $options );
		$attributes = $this->generate_attributes( $options );

		return $this->remove_linefeed_space(
			$this->_render( 'button_button', array(
				'name'            => $name,
				'value'           => $value,
				'attributes'      => $attributes,
				'element_content' => $element_content,
			) )
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
		$js = $options['js'];
		unset( $options['js'] );
		$attributes = $this->generate_attributes( $options );

		return $this->remove_linefeed_space(
			$this->_render( 'datepicker', array(
				'name'       => $name,
				'attributes' => $attributes,
				'js'         => $js,
			) )
		);
	}

	/**
	 * monthpicker生成
	 *
	 * @param string $name name属性
	 * @param string $options
	 * @return string HTML
	 */
	public function monthpicker( $name, $options = array() ) {
		$defaults = array(
			'id'          => null,
			'class'       => null,
			'size'        => 30,
			'js'          => '',
			'value'       => '',
			'placeholder' => null,
		);
		$options = array_merge( $defaults, $options );
		$js = $options['js'];
		unset( $options['js'] );
		$attributes = $this->generate_attributes( $options );

		return $this->remove_linefeed_space(
			$this->_render( 'monthpicker', array(
				'name'       => $name,
				'attributes' => $attributes,
				'js'         => $js,
			) )
		);
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

		return $this->remove_linefeed_space(
			$this->_render( 'file', array(
				'name'       => $name,
				'attributes' => $attributes,
			) )
		);
	}

	/**
	 * タグの属性を最適化して生成する
	 * ※テストしやすいようにアクセス修飾子を public に
	 *
	 * @todo このメソッドはなくしてテンプレート内で組み立てるようにする
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
	protected function _get_attr_id( $id, $suffix = '' ) {
		if ( MWF_Functions::is_empty( $id ) ) {
			return;
		}

		if ( $suffix ) {
			$id .= '-' . $suffix;
		}
		return $id;
	}

	/**
	 * Removed linefeed codes, tabs and spaces
	 *
	 * @param string $string
	 * @return string
	 */
	public function remove_linefeed_space( $string ) {
		$string = str_replace( array( "\r\n", "\r", "\n", "\t" ), '', $string );
		$string = preg_replace( '/\s+\/>/', ' />', $string );
		$string = preg_replace( '/"\s+?([^"\s])/', '" $1', $string );
		$string = preg_replace( '/>[\t\s]*?</', '><', $string );
		return $string;
	}
}
