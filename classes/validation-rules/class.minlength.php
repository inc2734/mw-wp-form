<?php
/**
 * Name       : MW WP Form Validation Rule MinLength
 * Version    : 2.0.0
 * Author     : Takashi Kitajima
 * Author URI : https://2inc.org
 * Created    : July 21, 2014
 * Modified   : May 30, 2017
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Validation_Rule_MinLength extends MW_WP_Form_Abstract_Validation_Rule {

	/**
	 * Validation rule name
	 * @var string
	 */
	protected $name = 'minlength';

	/**
	 * Validation process
	 *
	 * @param string $name
	 * @param array $option
	 * @return string Error message
	 */
	public function rule( $name, array $options = array() ) {
		$value = $this->Data->get( $name );
		$value = MWF_Functions::convert_eol( $value );

		if ( MWF_Functions::is_empty( $value ) ) {
			return;
		}

		$defaults = array(
			'min' => 0,
			'message' => __( 'The number of characters is a few.', 'mw-wp-form' )
		);
		$options = array_merge( $defaults, $options );
		$length = mb_strlen( $value, get_bloginfo( 'charset' ) );
		if ( MWF_Functions::is_numeric( $options['min'] ) && $options['min'] > $length ) {
			return $options['message'];
		}
	}

	/**
	 * Add setting field to validation rule setting panel
	 *
	 * @param numeric $key ID of validation rule
	 * @param array $value Content of validation rule
	 * @return void
	 */
	public function admin( $key, $value ) {
		$min = '';
		if ( is_array( $value[ $this->getName() ] ) && isset( $value[ $this->getName() ]['min'] ) ) {
			$min = $value[ $this->getName() ]['min'];
		}
		?>
		<table>
			<tr>
				<td><?php esc_html_e( 'The number of the minimum characters', 'mw-wp-form' ); ?></td>
				<td><input type="text" value="<?php echo esc_attr( $min ); ?>" size="3" name="<?php echo MWF_Config::NAME; ?>[validation][<?php echo $key; ?>][<?php echo esc_attr( $this->getName() ); ?>][min]" /></td>
			</tr>
		</table>
		<?php
	}
}
