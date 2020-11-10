<?php
/**
 * @package mw-wp-form
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * MW_WP_Form_Validation_Rule_Eq
 */
class MW_WP_Form_Validation_Rule_Eq extends MW_WP_Form_Abstract_Validation_Rule {

	/**
	 * Validation rule name.
	 *
	 * @var string
	 */
	protected $name = 'eq';

	/**
	 * Validation process.
	 *
	 * @param string $name    Validation name.
	 * @param array  $options Validation options.
	 * @return string
	 */
	public function rule( $name, array $options = array() ) {
		$value = $this->Data->get( $name );

		if ( is_null( $value ) ) {
			return;
		}

		$defaults     = array(
			'target'  => null,
			'message' => __( 'This is not in agreement.', 'mw-wp-form' ),
		);
		$options      = array_merge( $defaults, $options );
		$target_value = $this->Data->get( $options['target'] );

		if ( (string) $value !== (string) $target_value ) {
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
		$target = '';
		if ( is_array( $value[ $this->getName() ] ) && isset( $value[ $this->getName() ]['target'] ) ) {
			$target = $value[ $this->getName() ]['target'];
		}
		?>
		<table>
			<tr>
				<td><?php esc_html_e( 'The key at same value', 'mw-wp-form' ); ?></td>
				<td><input type="text" value="<?php echo esc_attr( $target ); ?>" name="<?php echo MWF_Config::NAME; ?>[validation][<?php echo $key; ?>][<?php echo esc_attr( $this->getName() ); ?>][target]" /></td>
			</tr>
		</table>
		<?php
	}
}
