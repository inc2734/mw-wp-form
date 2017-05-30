<?php
/**
 * Name       : MW WP Form Validation Rule Kana
 * Version    : 2.0.0
 * Author     : Key Nomura, Takashi Kitajima
 * Author URI : http://mypacecreator.net/
 * Created    : September 1, 2015
 * Modified   : May 30, 2017
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Validation_Rule_Kana extends MW_WP_Form_Abstract_Validation_Rule {

	/**
	 * Validation rule name
	 * @var string
	 */
	protected $name = 'kana';

	/**
	 * Validation process
	 *
	 * @param string $name
	 * @param array $option
	 * @return string Error message
	 */
	public function rule( $name, array $options = array() ) {
		$value = $this->Data->get( $name );

		if ( MWF_Functions::is_empty( $value ) ) {
			return;
		}

		if ( preg_match( '/^[ぁ-ゞァ-ヾ 　]*?[ぁ-ゞァ-ヾ]+?[ぁ-ゞァ-ヾ 　]*?$/u', $value ) ) {
			return;
		}

		$defaults = array(
			'message' => __( 'Please enter with a Japanese Hiragana or Katakana.', 'mw-wp-form' )
		);
		$options = array_merge( $defaults, $options );
		return $options['message'];
	}

	/**
	 * Add setting field to validation rule setting panel
	 *
	 * @param numeric $key ID of validation rule
	 * @param array $value Content of validation rule
	 * @return void
	 */
	public function admin( $key, $value ) {
		?>
		<label><input type="checkbox" <?php checked( $value[ $this->getName() ], 1 ); ?> name="<?php echo MWF_Config::NAME; ?>[validation][<?php echo $key; ?>][<?php echo esc_attr( $this->getName() ); ?>]" value="1" /><?php esc_html_e( 'Japanese Hiragana or Katakana', 'mw-wp-form' ); ?></label>
		<?php
	}
}
