<?php
/**
 * @package mw-wp-form
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * MW_WP_Form_Validation_Rule_FileType
 */
class MW_WP_Form_Validation_Rule_FileType extends MW_WP_Form_Abstract_Validation_Rule {

	/**
	 * Validation rule name.
	 *
	 * @var string
	 */
	protected $name = 'filetype';

	/**
	 * Validation process.
	 *
	 * @param string $name    Validation name.
	 * @param array  $options Validation options.
	 * @return string
	 */
	public function rule( $name, array $options = array() ) {
		$value = $this->Data->get( $name );

		if ( MWF_Functions::is_empty( $value ) ) {
			return;
		}

		$defaults = array(
			'types'   => '',
			'message' => __( 'This file is invalid.', 'mw-wp-form' ),
		);
		$options  = array_merge( $defaults, $options );
		$_types   = explode( ',', $options['types'] );
		$types    = array();
		foreach ( $_types as $type ) {
			$types[] = preg_quote( trim( $type ), '/' );
		}
		$types   = implode( '|', MWF_Functions::array_clean( $types ) );
		$pattern = '/\.(' . $types . ')$/i';
		if ( ! preg_match( $pattern, $value ) ) {
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
		$types = '';
		if ( is_array( $value[ $this->getName() ] ) && isset( $value[ $this->getName() ]['types'] ) ) {
			$types = $value[ $this->getName() ]['types'];
		}
		?>
		<table>
			<tr>
				<td><?php esc_html_e( 'Permitted Extension', 'mw-wp-form' ); ?></td>
				<td><input type="text" value="<?php echo esc_attr( $types ); ?>" name="<?php echo MWF_Config::NAME; ?>[validation][<?php echo $key; ?>][<?php echo esc_attr( $this->getName() ); ?>][types]" /> <span class="mwf_note"><?php esc_html_e( 'Example:jpg or jpg,txt,…', 'mw-wp-form' ); ?></span></td>
			</tr>
		</table>
		<?php
	}
}
