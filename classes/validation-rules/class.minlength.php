<?php
/**
 * @package mw-wp-form
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * MW_WP_Form_Validation_Rule_MinLength
 */
class MW_WP_Form_Validation_Rule_MinLength extends MW_WP_Form_Abstract_Validation_Rule {

	/**
	 * Validation rule name.
	 *
	 * @var string
	 */
	protected $name = 'minlength';

	/**
	 * Validation process.
	 *
	 * @param string $name    Validation name.
	 * @param array  $options Validation options.
	 * @return string
	 */
	public function rule( $name, array $options = array() ) {
		$value = $this->Data->get( $name );
		$value = MWF_Functions::convert_eol( $value );

		if ( MWF_Functions::is_empty( $value ) ) {
			return;
		}

		$defaults = array(
			'min'     => 0,
			'message' => __( 'The number of characters is a few.', 'mw-wp-form' ),
		);
		$options  = array_merge( $defaults, $options );
		$length   = mb_strlen( $value, get_bloginfo( 'charset' ) );
		if ( MWF_Functions::is_numeric( $options['min'] ) && $options['min'] > $length ) {
			return $options['message'];
		}
	}

	/**
	 * Add setting field to validation rule setting panel.
	 *
	 * @param numeric $key ID of validation rule.
	 * @param array   $value Content of validation rule.
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
