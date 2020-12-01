<?php
/**
 * @package mw-wp-form
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * MW_WP_Form_Form
 */
class MW_WP_Form_Form {

	/**
	 * Return raw value.
	 *
	 * @param string $name Field name.
	 * @return mixed
	 */
	public function get_raw(
		// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$name
		// phpcs:enable
	) {
		MWF_Functions::deprecated_message(
			'MW_WP_Form_Form::get_raw()',
			'MW_WP_Form_Data::get_raw()'
		);
	}

	/**
	 * Return raw value if it is in $children.
	 *
	 * @param string $name     Field name.
	 * @param array  $children Children.
	 */
	public function get_raw_in_children(
		// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$name,
		array $children
		// phpcs:enable
	) {
		MWF_Functions::deprecated_message(
			'MW_WP_Form_Form::get_raw_in_children()',
			'MW_WP_Form_Data::get_raw_in_children()'
		);
	}

	/**
	 * Return value for zip.
	 *
	 * @param string $name Field name.
	 */
	public function get_zip_value(
		// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$name
		// phpcs:enable
	) {
		MWF_Functions::deprecated_message(
			'MW_WP_Form_Form::get_zip_value()',
			'MW_WP_Form_Data::get_separated_value()'
		);
	}

	/**
	 * Return value for tel.
	 *
	 * @param string $name Field name.
	 */
	public function get_tel_value(
		// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$name
		// phpcs:enable
	) {
		MWF_Functions::deprecated_message(
			'MW_WP_Form_Form::get_tel_value()',
			'MW_WP_Form_Data::get_separated_value()'
		);
	}

	/**
	 * Return value for checkbox.
	 *
	 * @param string $name Field name.
	 * @param array  $data Posted value.
	 */
	public function get_checked_value(
		// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$name,
		array $data
		// phpcs:enable
	) {
		MWF_Functions::deprecated_message(
			'MW_WP_Form_Form::get_checked_value()',
			'MW_WP_Form_Data::get_separated_value()'
		);
	}

	/**
	 * Return value for radio.
	 *
	 * @param string $name Field name.
	 * @param array  $data Posted value.
	 */
	public function get_radio_value(
		// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$name,
		array $data
		// phpcs:enable
	) {
		MWF_Functions::deprecated_message(
			'MW_WP_Form_Form::get_radio_value()',
			'MW_WP_Form_Data::get_in_children()'
		);
	}

	/**
	 * Return value for selectbox.
	 *
	 * @param string $name Field name.
	 * @param array  $data Posted value.
	 */
	public function get_selected_value(
		// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$name,
		array $data
		// phpcs:enable
	) {
		MWF_Functions::deprecated_message(
			'MW_WP_Form_Form::get_selected_value()',
			'MW_WP_Form_Data::get_in_children()'
		);
	}

	/**
	 * Return formatted raw value (e.g. for checkbox).
	 *
	 * @param string $name     Field name.
	 * @param array  $children Children.
	 */
	public function get_separated_raw_value(
		// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$name,
		array $children = array()
		// phpcs:enable
	) {
		MWF_Functions::deprecated_message(
			'MW_WP_Form_Form::get_separated_raw_value()',
			'MW_WP_Form_Data::get_separated_raw_value()'
		);
	}

	/**
	 * Return hidden field for separator.
	 *
	 * @param string $name      Field name.
	 * @param string $separator Separator.
	 * @return string
	 */
	public function separator( $name, $separator ) {
		if ( $separator ) {
			return $this->hidden( $name . '[separator]', $separator );
		}
	}

	/**
	 * Return separator value.
	 *
	 * @param string $name Field name.
	 */
	public function get_separator_value(
		// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$name
		// phpcs:enable
	) {
		MWF_Functions::deprecated_message(
			'MW_WP_Form_Form::get_separator_value()',
			'MW_WP_Form_Data::get_separator_value()'
		);
	}

	/**
	 * Return hidden field for setting children.
	 *
	 * @param string $name     Field name.
	 * @param array  $children Children.
	 * @return string
	 */
	public function children( $name, array $children ) {
		$name = sprintf( '__children[%s][]', $name );
		return $this->hidden( $name, json_encode( $children ) );
	}

	/**
	 * Render template.
	 *
	 * @param string $template directory/filename (no extension).
	 * @param array  $args Argments.
	 * @return string
	 */
	protected function _render( $template, array $args = array() ) {
		$template_path = locate_template( 'mw-wp-form/form-fields/' . $template . '.php' );
		if ( ! $template_path ) {
			$template_path = plugin_dir_path( __FILE__ ) . '../../templates/form-fields/' . $template . '.php';
			if ( ! file_exists( $template_path ) ) {
				return;
			}
		}

		foreach ( $args as $key => $val ) {
			unset( $args[ $key ] );
			$args[ str_replace( '-', '_', $key ) ] = $val;
		}

		// phpcs:disable WordPress.PHP.DontExtract.extract_extract
		extract( $args );
		// phpcs:enable

		ob_start();
		include( $template_path );
		return ob_get_clean();
	}

	/**
	 * Return form staring tag.
	 *
	 * @param array $options Start tag attributes.
	 * @return string
	 */
	public function start( $options = array() ) {
		$action  = '';
		$action  = apply_filters( 'mwform_form_start_attr_action', $action );
		$options = array_merge(
			array(
				'action'  => $action,
				'enctype' => 'multipart/form-data',
			),
			$options
		);

		return sprintf(
			'<form method="post" action="%s" enctype="%s">',
			esc_attr( $options['action'] ),
			esc_attr( $options['enctype'] )
		);
	}

	/**
	 * Return form ending tag.
	 *
	 * @return string
	 */
	public function end() {
		$html  = '';
		$html  = apply_filters( 'mwform_form_end_html', $html );
		$html .= '</form>';
		return $html;
	}

	/**
	 * Return input[type=text].
	 *
	 * @param string $name    Field name.
	 * @param array  $options Options.
	 * @return string
	 */
	public function text( $name, $options = array() ) {
		$defaults = array(
			'id'                     => null,
			'class'                  => null,
			'size'                   => 60,
			'maxlength'              => null,
			'value'                  => '',
			'placeholder'            => null,
			'conv-half-alphanumeric' => null,
		);

		$options = shortcode_atts( $defaults, $options );
		$options = array_merge(
			$options,
			array(
				'name' => $name,
			)
		);

		return $this->remove_newline_space(
			$this->_render( 'text', $options )
		);
	}

	/**
	 * Return input[type=email].
	 *
	 * @param string $name    Field name.
	 * @param array  $options Options.
	 * @return string
	 */
	public function email( $name, $options = array() ) {
		$defaults = array(
			'id'                     => null,
			'class'                  => null,
			'size'                   => 60,
			'maxlength'              => null,
			'value'                  => '',
			'placeholder'            => null,
			'conv-half-alphanumeric' => null,
		);
		$options  = shortcode_atts( $defaults, $options );
		$options  = array_merge(
			$options,
			array(
				'name' => $name,
			)
		);

		return $this->remove_newline_space(
			$this->_render( 'email', $options )
		);
	}

	/**
	 * Return input[type=url].
	 *
	 * @param string $name    Field name.
	 * @param array  $options Options.
	 * @return string
	 */
	public function url( $name, $options = array() ) {
		$defaults = array(
			'id'                     => null,
			'class'                  => null,
			'size'                   => 60,
			'maxlength'              => null,
			'value'                  => '',
			'placeholder'            => null,
			'conv-half-alphanumeric' => null,
		);
		$options  = shortcode_atts( $defaults, $options );
		$options  = array_merge(
			$options,
			array(
				'name' => $name,
			)
		);

		return $this->remove_newline_space(
			$this->_render( 'url', $options )
		);
	}

	/**
	 * Return input[type=range].
	 *
	 * @param string $name    Field name.
	 * @param array  $options Options.
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
		$options  = shortcode_atts( $defaults, $options );
		$options  = array_merge(
			$options,
			array(
				'name' => $name,
			)
		);

		return $this->remove_newline_space(
			$this->_render( 'range', $options )
		);
	}

	/**
	 * Return input[type=number].
	 *
	 * @param string $name    Field name.
	 * @param array  $options Options.
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
		$options  = shortcode_atts( $defaults, $options );
		$options  = array_merge(
			$options,
			array(
				'name' => $name,
			)
		);

		return $this->remove_newline_space(
			$this->_render( 'number', $options )
		);
	}

	/**
	 * Return input[type=hidden].
	 *
	 * @param string $name  Field name.
	 * @param array  $value Field value.
	 * @return string
	 */
	public function hidden( $name, $value ) {
		return $this->_render(
			'hidden',
			array(
				'name'  => $name,
				'value' => $value,
			)
		);
	}

	/**
	 * Return input[type=password].
	 *
	 * @param string $name    Field name.
	 * @param array  $options Options.
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
		$options  = shortcode_atts( $defaults, $options );
		$options  = array_merge(
			$options,
			array(
				'name' => $name,
			)
		);

		return $this->remove_newline_space(
			$this->_render( 'password', $options )
		);
	}

	/**
	 * Return zip field.
	 *
	 * @param string $name    Field name.
	 * @param array  $options Options.
	 * @return string
	 */
	public function zip( $name, $options = array() ) {
		$defaults = array(
			'class'                  => null,
			'conv-half-alphanumeric' => null,
			'value'                  => '',
		);
		$options  = shortcode_atts( $defaults, $options );

		$children  = array();
		$separator = '-';

		if ( is_array( $options['value'] ) ) {
			$children = $options['value'];
		} else {
			$children = explode( $separator, $options['value'] );
		}

		$values = array( '', '' );
		foreach ( $children as $key => $val ) {
			if ( 0 === $key || 1 === $key ) {
				$values[ $key ] = $val;
			}
		}

		return $this->remove_newline_space(
			$this->_render(
				'zip',
				array(
					'separator' => $separator,
					'fields'    => array(
						array(
							'name'                   => $name . '[data][0]',
							'class'                  => $options['class'],
							'size'                   => 4,
							'maxlength'              => 3,
							'value'                  => $values[0],
							'conv-half-alphanumeric' => $options['conv-half-alphanumeric'],
						),
						array(
							'name'                   => $name . '[data][1]',
							'class'                  => $options['class'],
							'size'                   => 5,
							'maxlength'              => 4,
							'value'                  => $values[1],
							'conv-half-alphanumeric' => $options['conv-half-alphanumeric'],
						),
					),
				)
			)
			. $this->separator( $name, $separator )
		);
	}

	/**
	 * Return tel field.
	 *
	 * @param string $name    Field name.
	 * @param array  $options Options.
	 * @return string
	 */
	public function tel( $name, $options = array() ) {
		$defaults = array(
			'class'                  => null,
			'conv-half-alphanumeric' => null,
			'value'                  => '',
		);
		$options  = shortcode_atts( $defaults, $options );

		$children  = array();
		$separator = '-';

		if ( is_array( $options['value'] ) ) {
			$children = $options['value'];
		} else {
			$children = explode( $separator, $options['value'] );
		}

		$values = array( '', '', '' );
		foreach ( $children as $key => $val ) {
			if ( 0 === $key || 1 === $key || 2 === $key ) {
				$values[ $key ] = $val;
			}
		}

		return $this->remove_newline_space(
			$this->_render(
				'tel',
				array(
					'separator' => $separator,
					'fields'    => array(
						array(
							'name'                   => $name . '[data][0]',
							'class'                  => $options['class'],
							'size'                   => 6,
							'maxlength'              => 5,
							'value'                  => $values[0],
							'conv-half-alphanumeric' => $options['conv-half-alphanumeric'],
						),
						array(
							'name'                   => $name . '[data][1]',
							'class'                  => $options['class'],
							'size'                   => 5,
							'maxlength'              => 4,
							'value'                  => $values[1],
							'conv-half-alphanumeric' => $options['conv-half-alphanumeric'],
						),
						array(
							'name'                   => $name . '[data][2]',
							'class'                  => $options['class'],
							'size'                   => 5,
							'maxlength'              => 4,
							'value'                  => $values[2],
							'conv-half-alphanumeric' => $options['conv-half-alphanumeric'],
						),
					),
				)
			)
			. $this->separator( $name, $separator )
		);
	}

	/**
	 * Return textarea.
	 *
	 * @param string $name    Field name.
	 * @param array  $options Options.
	 * @return string
	 */
	public function textarea( $name, $options = array() ) {
		$defaults = array(
			'id'          => null,
			'class'       => null,
			'maxlength'   => null,
			'cols'        => 50,
			'rows'        => 5,
			'value'       => '',
			'placeholder' => null,
		);
		$options  = shortcode_atts( $defaults, $options );
		$options  = array_merge(
			$options,
			array(
				'name' => $name,
			)
		);

		return $this->remove_newline_space(
			$this->_render( 'textarea', $options )
		);
	}

	/**
	 * Return selectbox.
	 *
	 * @param string $name     Field name.
	 * @param array  $children Children.
	 * @param array  $options  Options.
	 * @return string
	 */
	public function select( $name, $children = array(), $options = array() ) {
		$defaults = array(
			'class' => null,
			'id'    => null,
			'value' => '',
		);
		$options  = shortcode_atts( $defaults, $options );
		$options  = array_merge(
			$options,
			array(
				'name'     => $name,
				'children' => $children,
			)
		);

		return $this->remove_newline_space(
			$this->_render( 'select', $options )
		);
	}

	/**
	 * Return input[type=radio].
	 *
	 * @param string $name     Field name.
	 * @param array  $children Children.
	 * @param array  $options  Options.
	 * @return string
	 */
	public function radio( $name, $children = array(), $options = array() ) {
		$defaults = array(
			'class'      => null,
			'id'         => '',
			'value'      => '',
			'vertically' => null,
		);
		$options  = shortcode_atts( $defaults, $options );

		$i      = 0;
		$fields = array();
		foreach ( $children as $key => $label ) {
			$i ++;
			$fields[ $key ] = array(
				'name'  => $name,
				'label' => $label,
				'id'    => $this->_get_attr_id( $options['id'], $i ),
				'class' => $options['class'],
			);
		}

		$options = array_merge(
			$options,
			array(
				'fields' => $fields,
			)
		);

		return $this->remove_newline_space(
			$this->_render( 'radio', $options )
		);
	}

	/**
	 * Return input[checkbox].
	 *
	 * @param string $name      Field name.
	 * @param array  $children  Children.
	 * @param array  $options   Options.
	 * @param string $separator Separator.
	 * @return string
	 */
	public function checkbox( $name, $children = array(), $options = array(), $separator = ',' ) {
		$defaults = array(
			'id'         => null,
			'class'      => null,
			'value'      => '',
			'vertically' => null,
		);
		$options  = shortcode_atts( $defaults, $options );

		if ( ! is_array( $options['value'] ) ) {
			if ( MWF_Functions::is_empty( $options['value'] ) ) {
				$options['value'] = array();
			} else {
				$options['value'] = explode( $separator, $options['value'] );
			}
		}

		$i      = 0;
		$fields = array();
		foreach ( $children as $key => $label ) {
			$i ++;
			$fields[ $key ] = array(
				'name'  => $name . '[data][]',
				'label' => $label,
				'id'    => $this->_get_attr_id( $options['id'], $i ),
				'class' => $options['class'],
			);
		}

		$options = array_merge(
			$options,
			array(
				'fields' => $fields,
			)
		);

		return $this->remove_newline_space(
			$this->_render( 'checkbox', $options ) . $this->separator( $name, $separator )
		);
	}

	/**
	 * Return input[type=submit].
	 *
	 * @param string $name    Field name.
	 * @param string $value   Field value.
	 * @param array  $options Options.
	 * @return string
	 */
	public function submit( $name, $value, $options = array() ) {
		$defaults = array(
			'class' => null,
		);
		$options  = shortcode_atts( $defaults, $options );
		$options  = array_merge(
			$options,
			array(
				'name'  => $name,
				'value' => $value,
			)
		);

		return $this->remove_newline_space(
			$this->_render( 'submit', $options )
		);
	}

	/**
	 * Return button[type=submit].
	 *
	 * @param string $name            Field name.
	 * @param string $value           Field value.
	 * @param array  $options         Options.
	 * @param string $element_content Field content.
	 * @return string
	 */
	public function button_submit( $name, $value, $options = array(), $element_content = '' ) {
		$defaults = array(
			'class' => null,
		);
		$options  = shortcode_atts( $defaults, $options );
		$options  = array_merge(
			$options,
			array(
				'name'            => $name,
				'value'           => $value,
				'element_content' => $element_content,
			)
		);

		return $this->remove_newline_space(
			$this->_render( 'button_submit', $options )
		);
	}

	/**
	 * Return input[type=button].
	 *
	 * @param string $name    Field name.
	 * @param string $value   Field value.
	 * @param array  $options Options.
	 * @return string
	 */
	public function button( $name, $value, $options = array() ) {
		$defaults = array(
			'class' => null,
		);
		$options  = shortcode_atts( $defaults, $options );
		$options  = array_merge(
			$options,
			array(
				'name'  => $name,
				'value' => $value,
			)
		);

		return $this->remove_newline_space(
			$this->_render( 'button', $options )
		);
	}

	/**
	 * Return button.
	 *
	 * @param string $name            Field name.
	 * @param string $value           Field value.
	 * @param array  $options         Options.
	 * @param string $element_content Field content.
	 * @return string
	 */
	public function button_button( $name, $value, $options = array(), $element_content = '' ) {
		$defaults = array(
			'class' => null,
		);
		$options  = shortcode_atts( $defaults, $options );
		$options  = array_merge(
			$options,
			array(
				'name'            => $name,
				'value'           => $value,
				'element_content' => $element_content,
			)
		);

		return $this->remove_newline_space(
			$this->_render( 'button_button', $options )
		);
	}

	/**
	 * Return datepicker.
	 *
	 * @param string $name    Field name.
	 * @param string $options Options.
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
		$options  = shortcode_atts( $defaults, $options );
		$options  = array_merge(
			$options,
			array(
				'name' => $name,
			)
		);

		return $this->remove_newline_space(
			$this->_render( 'datepicker', $options )
		);
	}

	/**
	 * Return monthpicker.
	 *
	 * @param string $name    Field name.
	 * @param string $options Options.
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
		$options  = shortcode_atts( $defaults, $options );
		$options  = array_merge(
			$options,
			array(
				'name' => $name,
			)
		);

		return $this->remove_newline_space(
			$this->_render( 'monthpicker', $options )
		);
	}

	/**
	 * Return input[type=file].
	 *
	 * @param string $name    Field name.
	 * @param string $options Options.
	 * @return string
	 */
	public function file( $name, $options = array() ) {
		$defaults = array(
			'id'    => null,
			'class' => null,
		);
		$options  = shortcode_atts( $defaults, $options );
		$options  = array_merge(
			$options,
			array(
				'name' => $name,
			)
		);

		return $this->remove_newline_space(
			$this->_render( 'file', $options )
		);
	}

	/**
	 * タグの属性を最適化して生成する.
	 *
	 * @deprecated
	 *
	 * @param array $_attributes キーが属性名、要素が属性値の配列。要素が null のときは無視する.
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
	 * Return ID attribute.
	 *
	 * @param string $id     The id attribute.
	 * @param string $suffix Suffix.
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
	 * Removed linefeed codes, tabs and spaces.
	 *
	 * @param string $string Target string.
	 * @return string
	 */
	public function remove_newline_space( $string ) {
		return preg_replace_callback(
			'/<([^<>]+?)>/',
			array( $this, '_remove_newline_space_callback' ),
			$string
		);
	}

	/**
	 * Callback for remove_newline_space.
	 *
	 * @param array $matches $matches of remove_newline_space.
	 * @return string
	 */
	protected function _remove_newline_space_callback( $matches ) {
		$matches[0] = str_replace( array( "\r\n", "\r", "\n", "\t" ), ' ', $matches[0] );
		$matches[0] = preg_replace( '/[\t ]+/', ' ', $matches[0] );
		return $matches[0];
	}

	/**
	 * Removed linefeed codes, tabs and spaces.
	 *
	 * @param string $string Target string.
	 * @return string
	 */
	public static function remove_linefeed_space( $string ) {
		MWF_Functions::deprecated_message(
			'MW_WP_Form_Form::remove_linefeed_space()',
			'MW_WP_Form_Form::remove_newline_space()'
		);
		$Form = new MW_WP_Form_Form();
		return $Form->remove_newline_space( $string );
	}
}
