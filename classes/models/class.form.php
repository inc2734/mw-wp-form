<?php
/**
 * Name       : MW WP Form Form
 * Version    : 2.0.0
 * Author     : Takashi Kitajima
 * Author URI : https://2inc.org
 * Created    : September 25, 2012
 * Modified   : June 1, 2017
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Form {

	/**
	 * Return raw value
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function get_raw( $name ) {
		MWF_Functions::deprecated_message(
			'MW_WP_Form_Form::get_raw()',
			'MW_WP_Form_Data::get_raw()'
		);
	}

	/**
	 * Return raw value if it is in $children
	 *
	 * @param string $name
	 * @param array $children
	 * @return string
	 */
	public function get_raw_in_children( $name, array $children ) {
		MWF_Functions::deprecated_message(
			'MW_WP_Form_Form::get_raw_in_children()',
			'MW_WP_Form_Data::get_raw_in_children()'
		);
	}

	/**
	 * Return value for zip
	 *
	 * @param string $name
	 * @return string
	 */
	public function get_zip_value( $name ) {
		MWF_Functions::deprecated_message(
			'MW_WP_Form_Form::get_zip_value()',
			'MW_WP_Form_Data::get_separated_value()'
		);
	}

	/**
	 * Return value for tel
	 *
	 * @param string $name
	 * @return string
	 */
	public function get_tel_value( $name ) {
		MWF_Functions::deprecated_message(
			'MW_WP_Form_Form::get_tel_value()',
			'MW_WP_Form_Data::get_separated_value()'
		);
	}

	/**
	 * Return value for checkbox
	 *
	 * @param string $name
	 * @param array $data
	 * @return string
	 */
	public function get_checked_value( $name, array $data ) {
		MWF_Functions::deprecated_message(
			'MW_WP_Form_Form::get_checked_value()',
			'MW_WP_Form_Data::get_separated_value()'
		);
	}

	/**
	 * Return value for radio
	 *
	 * @param string $name
	 * @param array $data
	 * @return string
	 */
	public function get_radio_value( $name, array $data ) {
		MWF_Functions::deprecated_message(
			'MW_WP_Form_Form::get_radio_value()',
			'MW_WP_Form_Data::get_in_children()'
		);
	}

	/**
 	 * Return value for selectbox
	 *
	 * @param string $name
	 * @param array $data
	 * @return string
	 */
	public function get_selected_value( $name, array $data ) {
		MWF_Functions::deprecated_message(
			'MW_WP_Form_Form::get_selected_value()',
			'MW_WP_Form_Data::get_in_children()'
		);
	}

	/**
	 * Return formatted raw value (e.g. for checkbox)
	 *
	 * @param string $name
	 * @param array $children
	 * @return string
	 */
	public function get_separated_raw_value( $name, array $children = array() ) {
		MWF_Functions::deprecated_message(
			'MW_WP_Form_Form::get_separated_raw_value()',
			'MW_WP_Form_Data::get_separated_raw_value()'
		);
	}

	/**
	 * Return hidden field for separator
	 *
	 * @param string $name
	 * @param string $separator
	 * @return string
	 */
	public function separator( $name, $separator ) {
		if ( $separator ) {
			return $this->hidden( $name . '[separator]', $separator );
		}
	}

	/**
	 * Return separator value
	 *
	 * @param string $name
	 * @return string
	 */
	public function get_separator_value( $name ) {
		MWF_Functions::deprecated_message(
			'MW_WP_Form_Form::get_separator_value()',
			'MW_WP_Form_Data::get_separator_value()'
		);
	}

	/**
	 * Return hidden field for setting children
	 *
	 * @param string $name
	 * @param array $children
	 * @return string
	 */
	public function children( $name, array $children ) {
		$name = sprintf( '__children[%s][]', $name );
		return $this->hidden( $name, json_encode( $children ) );
	}

	/**
	 * Render template
	 *
	 * @param string $template directory/filename (no extension)
	 * @return string
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
	 * Return form staring tag
	 *
	 * @param array $options
	 * @return string
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
	 * Return form ending tag
	 *
	 * @return string
	 */
	public function end() {
		$html = '';
		$html = apply_filters( 'mwform_form_end_html', $html );
		$html .= '</form>';
		return $html;
	}

	/**
	 * Return input[type=text]
	 *
	 * @param string $name
	 * @param array $options
	 * @return string
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

		return $this->remove_newline_space(
			$this->_render( 'text', array(
				'name'       => $name,
				'attributes' => $attributes,
			) )
		);
	}

	/**
	 * Return input[type=email]
	 *
	 * @param string $name
	 * @param array $options
	 * @return string
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

		return $this->remove_newline_space(
			$this->_render( 'email', array(
				'name'       => $name,
				'attributes' => $attributes,
			) )
		);
	}

	/**
	 * Return input[type=url]
	 *
	 * @param string $name
	 * @param array $options
	 * @return string
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

		return $this->remove_newline_space(
			$this->_render( 'url', array(
				'name'       => $name,
				'attributes' => $attributes,
			) )
		);
	}

	/**
	 * Return input[type=range]
	 *
	 * @param string $name
	 * @param array $options
	 * @return string
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

		return $this->remove_newline_space(
			$this->_render( 'range', array(
				'name'       => $name,
				'attributes' => $attributes,
			) )
		);
	}

	/**
	 * Return input[type=number]
	 *
	 * @param string $name
	 * @param array $options
	 * @return string
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

		return $this->remove_newline_space(
			$this->_render( 'number', array(
				'name'       => $name,
				'attributes' => $attributes,
			) )
		);
	}

	/**
	 * Return input[type=hidden]
	 *
	 * @param string $name
	 * @param string $value
	 * @return string
	 */
	public function hidden( $name, $value ) {
		return $this->remove_newline_space(
			$this->_render( 'hidden', array(
				'name'  => $name,
				'value' => $value,
			) )
		);
	}

	/**
	 * Return input[type=password]
	 *
	 * @param string $name
	 * @param array $options
	 * @return string
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

		return $this->remove_newline_space(
			$this->_render( 'password', array(
				'name'       => $name,
				'attributes' => $attributes,
			) )
		);
	}

	/**
	 * Return zip field
	 *
	 * @param string $name
	 * @param array $options
	 * @return string
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
				$values[ $key ] = $val;
			}
		}

		return $this->remove_newline_space(
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
	 * Return tel field
	 *
	 * @param string $name
	 * @param array $options
	 * @return string
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
				$values[ $key ] = $val;
			}
		}

		return $this->remove_newline_space(
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
	 * Return textarea
	 *
	 * @param string $name
	 * @param array $options
	 * @return string
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

		return $this->remove_newline_space(
			$this->_render( 'textarea', array(
				'name'       => $name,
				'attributes' => $attributes,
				'value'      => $value,
			) )
		);
	}

	/**
	 * Return selectbox
	 *
	 * @param string $name
	 * @param array $children
	 * @param array $options
	 * @return string
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

		return $this->remove_newline_space(
			$this->_render( 'select', array(
				'name'       => $name,
				'value'      => $value,
				'attributes' => $attributes,
				'children'   => $children,
			) )
		);
	}

	/**
	 * Return input[type=radio]
	 *
	 * @param string $name
	 * @param array $children
	 * @param array $options
	 * @return string
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

		return $this->remove_newline_space(
			$this->_render( 'radio', array(
				'name'       => $name,
				'vertically' => ( 'true' === $options['vertically'] ) ? 'vertical-item' : 'horizontal-item',
				'value'      => $options['value'],
				'fields'     => $fields,
			) )
		);
	}

	/**
	 * Return input[checkbox]
	 *
	 * @param string $name
	 * @param array $children
	 * @param array $options
	 * @param string $separator
	 * @return string
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
		if ( ! is_array( $options['value'] ) ) {
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

		return $this->remove_newline_space(
			$this->_render( 'checkbox', array(
				'name'       => $name,
				'vertically' => ( 'true' === $options['vertically'] ) ? 'vertical-item' : 'horizontal-item',
				'value'      => $value,
				'fields'     => $fields,
			) )
			. $this->separator( $name, $separator )
		);
	}

	/**
	 * Return input[type=submit]
	 *
	 * @param string $name
	 * @param string $value
	 * @param array $options
	 * @return string
	 */
	public function submit( $name, $value, $options = array() ) {
		$defaults = array(
			'class' => null,
		);
		$options = array_merge( $defaults, $options );
		$attributes = $this->generate_attributes( $options );

		return $this->remove_newline_space(
			$this->_render( 'submit', array(
				'name'       => $name,
				'value'      => $value,
				'attributes' => $attributes,
			) )
		);
	}

	/**
	 * Return button[type=submit]
	 *
	 * @param string $name
	 * @param string $value
	 * @param array $options
	 * @param string $element_content
	 * @return string
	 */
	public function button_submit( $name, $value, $options = array(), $element_content = '' ) {
		$defaults = array(
			'class' => null,
		);
		$options = array_merge( $defaults, $options );
		$attributes = $this->generate_attributes( $options );

		return $this->remove_newline_space(
			$this->_render( 'button_submit', array(
				'name'            => $name,
				'value'           => $value,
				'attributes'      => $attributes,
				'element_content' => $element_content,
			) )
		);
	}

	/**
	 * Return input[type=button]
	 *
	 * @param string $name
	 * @param string $value
	 * @param array $options
	 * @return string
	 */
	public function button( $name, $value, $options = array() ) {
		$defaults = array(
			'class' => null,
		);
		$options = array_merge( $defaults, $options );
		$attributes = $this->generate_attributes( $options );

		return $this->remove_newline_space(
			$this->_render( 'button', array(
				'name'       => $name,
				'value'      => $value,
				'attributes' => $attributes,
			) )
		);
	}

	/**
	 * Return button
	 *
	 * @param string $name
	 * @param string $value
	 * @param array $options
	 * @param string $element_content
	 * @return string
	 */
	public function button_button( $name, $value, $options = array(), $element_content = '' ) {
		$defaults = array(
			'class' => null,
		);
		$options = array_merge( $defaults, $options );
		$attributes = $this->generate_attributes( $options );

		return $this->remove_newline_space(
			$this->_render( 'button_button', array(
				'name'            => $name,
				'value'           => $value,
				'attributes'      => $attributes,
				'element_content' => $element_content,
			) )
		);
	}

	/**
	 * Return datepicker
	 *
	 * @param string $name
	 * @param string $options
	 * @return string
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

		return $this->remove_newline_space(
			$this->_render( 'datepicker', array(
				'name'       => $name,
				'attributes' => $attributes,
				'js'         => $js,
			) )
		);
	}

	/**
	 * Return monthpicker
	 *
	 * @param string $name
	 * @param string $options
	 * @return string
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

		return $this->remove_newline_space(
			$this->_render( 'monthpicker', array(
				'name'       => $name,
				'attributes' => $attributes,
				'js'         => $js,
			) )
		);
	}

	/**
	 * Return input[type=file]
	 *
	 * @param string $name
	 * @param array $options
	 * @return string
	 */
	public function file( $name, $options = array() ) {
		$defaults = array(
			'id'    => null,
			'class' => null,
		);
		$options = array_merge( $defaults, $options );
		$attributes = $this->generate_attributes( $options );

		return $this->remove_newline_space(
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
			if ( 'conv-half-alphanumeric' === $key ) {
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
	 * Return ID attribute
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
	public function remove_newline_space( $string ) {
		$string = str_replace( array( "\r\n", "\r", "\n", "\t" ), '', $string );
		$string = preg_replace( '/\s+\/>/', ' />', $string );
		$string = preg_replace( '/"\s+?([^"\s])/', '" $1', $string );
		$string = preg_replace( '/>[\t\s]*?</', '><', $string );
		return $string;
	}
	public static function remove_linefeed_space( $string ) {
		MWF_Functions::deprecated_message(
			'MW_WP_Form_Form::remove_linefeed_space()',
			'MW_WP_Form_Form::remove_newline_space()'
		);
		$Form = new MW_WP_Form_Form();
		return $Form->remove_newline_space( $string );
	}
}
