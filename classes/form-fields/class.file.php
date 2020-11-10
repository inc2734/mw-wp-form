<?php
/**
 * @package mw-wp-form
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * MW_WP_Form_Field_File
 */
class MW_WP_Form_Field_File extends MW_WP_Form_Abstract_Form_Field {

	/**
	 * Types of form type.
	 * input|select|button|input_button|error|other.
	 *
	 * @var string
	 */
	public $type = 'input';

	/**
	 * Set shortcode_name and display_name.
	 * Overwrite required for each child class.
	 *
	 * @return array
	 */
	protected function set_names() {
		return array(
			'shortcode_name' => 'mwform_file',
			'display_name'   => __( 'File', 'mw-wp-form' ),
		);
	}

	/**
	 * Set default attributes.
	 *
	 * @return array
	 */
	protected function set_defaults() {
		return array(
			'name'       => '',
			'id'         => null,
			'class'      => null,
			'show_error' => 'true',
		);
	}

	/**
	 * Callback of add shortcode for input page.
	 *
	 * @return string
	 */
	protected function input_page() {
		$_ret  = $this->Form->file(
			$this->atts['name'],
			array(
				'id'    => $this->atts['id'],
				'class' => $this->atts['class'],
			)
		);
		$value = $this->Data->get_raw( $this->atts['name'] );

		$upload_file_keys = $this->Data->get_post_value_by_key( MWF_Config::UPLOAD_FILE_KEYS );
		if (
			! empty( $value )
			&& is_array( $upload_file_keys )
			&& in_array( $this->atts['name'], $upload_file_keys, true )
		) {
			$filepath = MWF_Functions::fileurl_to_path( $value );
			if ( file_exists( $filepath ) ) {
				$_ret .= sprintf(
					'<div class="%s_file">
						<a href="%s" target="_blank">%s</a>
						%s
					</div>',
					esc_attr( MWF_Config::NAME ),
					esc_attr( $value ),
					esc_html__( 'Uploaded.', 'mw-wp-form' ),
					$this->Form->hidden( $this->atts['name'], $value )
				);
			}
		}
		if ( 'false' !== $this->atts['show_error'] ) {
			$_ret .= $this->get_error( $this->atts['name'] );
		}
		return $_ret;
	}

	/**
	 * Callback of add shortcode for confirm page.
	 *
	 * @return string
	 */
	protected function confirm_page() {
		$value = $this->Data->get_raw( $this->atts['name'] );
		if ( $value ) {
			$filepath = MWF_Functions::fileurl_to_path( $value );
			if ( file_exists( $filepath ) ) {
				$_ret  = '<div class="' . MWF_Config::NAME . '_file">';
				$_ret .= '<a href="' . esc_attr( $value ) . '" target="_blank">' . __( 'Uploaded.', 'mw-wp-form' ) . '</a>';
				$_ret .= '</div>';
				$_ret .= $this->Form->hidden( $this->atts['name'], $value );
				return $_ret;
			}
		}
	}

	/**
	 * Display tag generator dialog.
	 * Overwrite required for each child class.
	 *
	 * @param array $options Options.
	 */
	public function mwform_tag_generator_dialog( array $options = array() ) {
		?>
		<p>
			<strong>name<span class="mwf_require">*</span></strong>
			<?php $name = $this->get_value_for_generator( 'name', $options ); ?>
			<input type="text" name="name" value="<?php echo esc_attr( $name ); ?>" />
		</p>
		<p>
			<strong>id</strong>
			<?php $id = $this->get_value_for_generator( 'id', $options ); ?>
			<input type="text" name="id" value="<?php echo esc_attr( $id ); ?>" />
		</p>
		<p>
			<strong>class</strong>
			<?php $class = $this->get_value_for_generator( 'class', $options ); ?>
			<input type="text" name="class" value="<?php echo esc_attr( $class ); ?>" />
		</p>
		<p>
			<strong><?php esc_html_e( 'Display error', 'mw-wp-form' ); ?></strong>
			<?php $show_error = $this->get_value_for_generator( 'show_error', $options ); ?>
			<label><input type="checkbox" name="show_error" value="false" <?php checked( 'false', $show_error ); ?> /> <?php esc_html_e( 'Don\'t display error.', 'mw-wp-form' ); ?></label>
		</p>
		<?php
	}
}
