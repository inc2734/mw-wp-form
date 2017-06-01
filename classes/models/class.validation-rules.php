<?php
/**
 * Name       : MW WP Form Validation Rules
 * Version    : 1.0.0
 * Author     : Takashi Kitajima
 * Author URI : https://2inc.org
 * Created    : June 1, 2017
 * Modified   :
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Validation_Rules {

	/**
	 * @var MW_WP_Form_Validation_Rules
	 */
	protected static $Instance;

	/**
	 * @var string
	 */
	protected static $form_key;

	/**
	 * Array of validation rules. Definition is necessary to fix the order
	 * @var array
	 */
	protected static $validation_rules = array(
		'akismet_check' => '',
		'noempty'       => '',
		'required'      => '',
		'numeric'       => '',
		'alpha'         => '',
		'alphanumeric'  => '',
		'katakana'      => '',
		'hiragana'      => '',
		'kana'          => '',
		'zip'           => '',
		'tel'           => '',
		'mail'          => '',
		'date'          => '',
		'month'         => '',
		'url'           => '',
		'eq'            => '',
		'between'       => '',
		'minlength'     => '',
		'filetype'      => '',
		'filesize'      => '',
		'maxfilesize'   => '',
		'minfilesize'   => '',
	);

	private function __construct() {
		foreach ( glob( plugin_dir_path( __FILE__ ) . '../validation-rules/*.php' ) as $filename ) {
			$class_name = self::_get_class_name_from_validation_rule_filename( $filename );
			if ( ! class_exists( $class_name ) ) {
				continue;
			}

			new $class_name();
		}
	}

	public static function instantiation( $form_key ) {
		self::$form_key = $form_key;

		if ( isset( self::$Instance ) ) {
			return self::$Instance;
		}

		self::$Instance = new self();
		return self::$Instance;
	}

	/**
	 * Instantiation of validation rules. Set in the array through the hook.
	 *
	 * @return $validation_rules Array of MW_WP_Form_Abstract_Validation_Rule
	 */
	public function get_validation_rules() {
		self::$validation_rules = apply_filters(
			'mwform_validation_rules',
			self::$validation_rules,
			null // backward compatibility
		);

		foreach ( self::$validation_rules as $validation_rule => $validation_rule_object ) {
			if ( method_exists( $validation_rule_object, 'set_Data' ) ) {
				$validation_rule_object->set_Data( MW_WP_Form_Data::connect( self::$form_key ) );
			}
		}

		return self::$validation_rules;
	}

	/**
	 * Return class name from filename of validation rule
	 *
	 * @param string $filename
	 * @return string
	 */
	protected static function _get_class_name_from_validation_rule_filename( $filename ) {
		$class_name = preg_replace( '/^class\./', '', basename( $filename, '.php' ) );
		$class_name = str_replace( '-', '_', $class_name );
		$class_name = 'MW_WP_Form_Validation_Rule_' . $class_name;
		return $class_name;
	}
}
