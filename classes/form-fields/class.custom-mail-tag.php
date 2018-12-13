<?php
/**
 * Name       : MW WP Form Field Custom Mail Tag
 * Version    : 2.0.0
 * Author     : Takashi Kitajima
 * Author URI : https://2inc.org
 * Created    : April 3, 2016
 * Modified   : May 30, 2017
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Field_Custom_Mail_Tag extends MW_WP_Form_Abstract_Form_Field {

	/**
	 * Types of form type.
	 * input|select|button|input_button|error|other
	 * @var string
	 */
	public $type = 'other';

	/**
	 * Set shortcode_name and display_name
	 * Overwrite required for each child class
	 *
	 * @return array(shortcode_name, display_name)
	 */
	protected function set_names() {
		return array(
			'shortcode_name' => 'mwform_custom_mail_tag',
			'display_name'   => __( 'Custom Mail Tag', 'mw-wp-form' ),
		);
	}

	/**
	 * Set default attributes
	 *
	 * @return array defaults
	 */
	protected function set_defaults() {
		return array(
			'name'  => '',
			'id'    => null,
			'class' => null,
			'echo'  => 'true',
		);
	}

	/**
	 * Callback of add shortcode for input page
	 *
	 * @param array $atts
	 * @param string $element_content
	 * @return string HTML
	 */
	protected function input_page() {
		$_ret = '';
		if ( 'true' === $this->atts['echo'] ) {
			$_ret .= $this->custom_mail_tag_field( $this->atts['name'], array(
				'id'    => $this->atts['id'],
				'class' => $this->atts['class'],
			) );
		}
		$_ret .= $this->Form->hidden( MWF_Config::CUSTOM_MAIL_TAG_KEYS . '[]', $this->atts['name'] );
		if ( 'false' !== $this->atts['show_error'] ) {
			$_ret .= $this->get_error( $this->atts['name'] );
		}
		return $_ret;
	}

	/**
	 * Callback of add shortcode for confirm page
	 *
	 * @param array $atts
	 * @param string $element_content
	 * @return string HTML
	 */
	protected function confirm_page() {
		$_ret = '';
		if ( 'true' === $this->atts['echo'] ) {
			$_ret .= $this->custom_mail_tag_field( $this->atts['name'], array(
				'id'    => $this->atts['id'],
				'class' => $this->atts['class'],
			) );
		}
		$_ret .= $this->Form->hidden( MWF_Config::CUSTOM_MAIL_TAG_KEYS . '[]', $this->atts['name'] );
		return $_ret;
	}

	/**
	 * Display tag generator dialog
	 * Overwrite required for each child class
	 *
	 * @param array $options
	 * @return void
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
			<strong><?php esc_html_e( 'Display', 'mw-wp-form' ); ?></strong>
			<?php $echo = $this->get_value_for_generator( 'echo', $options ); ?>
			<input type="checkbox" name="echo" value="false" <?php checked( 'false', $echo ); ?> /> <?php esc_html_e( 'Don\'t display.', 'mw-wp-form' ); ?>
		</p>
		<?php
	}

	/**
	 * 任意のデータを表示する要素を生成
	 *
	 * @param string $name name属性
	 * @param array
	 * @return string html
	 */
	public function custom_mail_tag_field( $name, $options = array() ) {
		$defaults = array(
			'id'    => null,
			'class' => null,
		);
		$options = array_merge( $defaults, $options );
		$class   = $options['class'];
		unset( $options['class'] );
		$Form = new MW_WP_Form_Form();
		$attributes = $Form->generate_attributes( $options );

		return sprintf(
			'<span class="mwform-custom-mail-tag-field %s" %s>%s</span>',
			esc_attr( $class ),
			$attributes,
			esc_html( MW_WP_Form_Parser::apply_filters_mwform_custom_mail_tag( $this->form_key, '', $name ) )
		);
	}
}
