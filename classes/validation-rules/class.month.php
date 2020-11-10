<?php
/**
 * @package mw-wp-form
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * MW_WP_Form_Validation_Rule_Month
 */
class MW_WP_Form_Validation_Rule_Month extends MW_WP_Form_Abstract_Validation_Rule {

	/**
	 * Validation rule name.
	 *
	 * @var string
	 */
	protected $name = 'month';

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

		$defaults  = array(
			'message' => __( 'This is not the format of a date (Year/Month).', 'mw-wp-form' ),
		);
		$options   = array_merge( $defaults, $options );
		$timestamp = strtotime( $value );
		if ( ! $timestamp ) {
			$timestamp = $this->convert_jpdate_to_timestamp( $value );
		}
		if ( ! $timestamp ) {
			return $options['message'];
		}

		$year      = date_i18n( 'Y', $timestamp );
		$month     = date_i18n( 'm', $timestamp );
		$checkdate = checkdate( $month, 1, $year );

		if ( ! $timestamp || ! $checkdate || preg_match( '/^[a-zA-Z]$/', $value ) || preg_match( '/^\s+$/', $value ) ) {
			return $options['message'];
		}
	}

	/**
	 * Convert Japanese notation date to time stamp.
	 *
	 * @param string $jpdate yyyy年mm月.
	 * @return string|false
	 */
	public function convert_jpdate_to_timestamp( $jpdate ) {
		if ( preg_match( '/^(\d+)年(\d{1,2})月$/', $jpdate, $reg ) ) {
			$date = sprintf( '%d-%d', $reg[1], $reg[2] );
			return strtotime( $date );
		}
		return false;
	}

	/**
	 * Add setting field to validation rule setting panel.
	 *
	 * @param numeric $key ID of validation rule.
	 * @param array   $value Content of validation rule.
	 * @return void
	 */
	public function admin( $key, $value ) {
		?>
		<label><input type="checkbox" <?php checked( $value[ $this->getName() ], 1 ); ?> name="<?php echo MWF_Config::NAME; ?>[validation][<?php echo $key; ?>][<?php echo esc_attr( $this->getName() ); ?>]" value="1" /><?php esc_html_e( 'Date(Year/Month)', 'mw-wp-form' ); ?></label>
		<?php
	}
}
