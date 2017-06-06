<?php
/**
 * Name       : MW WP Form Form Fields
 * Version    : 1.0.0
 * Author     : Takashi Kitajima
 * Author URI : https://2inc.org
 * Created    : June 1, 2017
 * Modified   :
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Form_Fields {

	/**
	 * @var array Array of MW_WP_Form_Form_Fields
	 */
	protected static $Instances;

	/**
	 * @var array
	 */
	protected static $form_fields = array();

	private function __construct() {
		foreach ( glob( plugin_dir_path( __FILE__ ) . '../form-fields/*.php' ) as $filename ) {
			$class_name = self::_get_class_name_from_form_field_filename( $filename );
			if ( ! class_exists( $class_name ) ) {
				continue;
			}

			new $class_name();
		}

		self::$form_fields = apply_filters( 'mwform_form_fields', self::$form_fields );
	}

	public static function instantiation( $form_key ) {
		if ( isset( self::$Instances[ $form_key ] ) ) {
			return self::$Instances[ $form_key ];
		}

		self::$Instances[ $form_key ] = new self();
		return self::$Instances[ $form_key ];
	}

	/**
	 * Return all form fields
	 *
	 * @return array
	 */
	public function get_form_fields() {
		return self::$form_fields;
	}

	/**
	 * Return class name from filename of input form field
	 *
	 * @param string $filename
	 * @return string
	 */
	protected static function _get_class_name_from_form_field_filename( $filename ) {
		$class_name = preg_replace( '/^class\./', '', basename( $filename, '.php' ) );
		$class_name = str_replace( '-', '_', $class_name );
		$class_name = 'MW_WP_Form_Field_' . $class_name;
		return $class_name;
	}
}
