<?php
/**
 * Name       : MW WP Form Validation Rule Zip
 * Version    : 2.0.0
 * Author     : Takashi Kitajima
 * Author URI : https://2inc.org
 * Created    : July 21, 2014
 * Modified   : May 30, 2017
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Validation_Rule_Zip extends MW_WP_Form_Abstract_Validation_Rule {

	/**
	 * Validation rule name
	 * @var string
	 */
	protected $name = 'zip';

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

		$defaults = array(
			'message' => __( 'This is not the format of a zip code.', 'mw-wp-form' )
		);
		$options = array_merge( $defaults, $options );
		if ( preg_match( '/^\d{3}-\d{4}$/', $value )
			|| preg_match( '/^\d{7}$/', $value ) ) {

			return;
		}

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
		<label><input type="checkbox" <?php checked( $value[ $this->getName() ], 1 ); ?> name="<?php echo MWF_Config::NAME; ?>[validation][<?php echo $key; ?>][<?php echo esc_attr( $this->getName() ); ?>]" value="1" /><?php esc_html_e( 'Zip Code', 'mw-wp-form' ); ?></label>
		<?php
	}

	/**
	 * This validation rule is for Japanese environments only
	 *
	 * @param array $validation_rules array of MW_WP_Form_Abstract_Validation_Rule
	 * @return array $validation_rules
	 */
	public function _mwform_validation_rules( array $validation_rules ) {
		$validation_rules = parent::_mwform_validation_rules( $validation_rules );

		if ( 'ja' === get_locale() ) {
			return $validation_rules;
		}

		$validation_rules[ $this->getName() ] = '';
		return $validation_rules;
	}
}
