<?php
/**
 * Name       : MW WP Form Field Error
 * Version    : 2.0.0
 * Author     : Takashi Kitajima
 * Author URI : https://2inc.org
 * Created    : December 14, 2012
 * Modified   : May 30, 2017
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Field_Error extends MW_WP_Form_Abstract_Form_Field {

	/**
	 * Types of form type.
	 * input|select|button|input_button|error|other
	 * @var string
	 */
	public $type = 'error';

	/**
	 * Set shortcode_name and display_name
	 * Overwrite required for each child class
	 *
	 * @return array(shortcode_name, display_name)
	 */
	protected function set_names() {
		return array(
			'shortcode_name' => 'mwform_error',
			'display_name'   => __( 'Error Message', 'mw-wp-form' ),
		);
	}

	/**
	 * Set default attributes
	 *
	 * @return array defaults
	 */
	protected function set_defaults() {
		return array(
			'keys' => '',
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
		$keys = explode( ',', $this->atts['keys'] );
		$_ret = '';
		foreach ( $keys as $key ) {
			$_ret .= $this->get_error( trim( $key ) );
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
			<strong><?php esc_html_e( 'name of the element which wants to display error', 'mw-wp-form' ); ?></strong>
			<?php $keys = "\n" . $this->get_value_for_generator( 'keys', $options ); ?>
			<textarea name="keys"><?php echo esc_attr( $keys ); ?></textarea>
			<span class="mwf_note">
				<?php esc_html_e( 'Input one line about one item.', 'mw-wp-form' ); ?>
			</span>
		</p>
		<?php
	}
}
