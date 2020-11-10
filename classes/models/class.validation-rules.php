<?php
/**
 * @package mw-wp-form
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * MW_WP_Form_Validation_Rules
 */
class MW_WP_Form_Validation_Rules {

	/**
	 * @var array Array of MW_WP_Form_Validation_Rules.
	 */
	protected static $Instances;

	/**
	 * @var string
	 */
	protected $form_key;

	/**
	 * Array of validation rules. Definition is necessary to fix the order.
	 *
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

	/**
	 * Constructor.
	 *
	 * @param string $form_key Form key.
	 */
	private function __construct( $form_key ) {
		$this->form_key = $form_key;

		foreach ( glob( plugin_dir_path( __FILE__ ) . '../validation-rules/*.php' ) as $filename ) {
			$class_name = self::_get_class_name_from_validation_rule_filename( $filename );
			if ( ! class_exists( $class_name ) ) {
				continue;
			}

			new $class_name( MW_WP_Form_Data::connect( $this->form_key ) );
		}
	}

	/**
	 * Instantiation.
	 *
	 * @param string $form_key Form key.
	 * @return MW_WP_Form_Validation_Rules
	 */
	public static function instantiation( $form_key ) {
		if ( isset( self::$Instances[ $form_key ] ) ) {
			return self::$Instances[ $form_key ];
		}

		self::$Instances[ $form_key ] = new self( $form_key );
		return self::$Instances[ $form_key ];
	}

	/**
	 * Instantiation of validation rules. Set in the array through the hook.
	 *
	 * @return array
	 */
	public function get_validation_rules() {
		self::$validation_rules = apply_filters(
			'mwform_validation_rules',
			self::$validation_rules,
			null // backward compatibility
		);

		foreach ( self::$validation_rules as $validation_rule => $validation_rule_object ) {
			if ( is_a( $validation_rule_object, 'MW_WP_Form_Abstract_Validation_Rule' ) ) {
				// For backward compatibility (< 4.0.0)
				if ( method_exists( $validation_rule_object, 'set_Data' ) && ! $validation_rule_object->is_set_Data() ) {
					$validation_rule_object->set_Data( MW_WP_Form_Data::connect( $this->form_key ) );
				}
			} else {
				unset( self::$validation_rules[ $validation_rule ] );
			}
		}

		return self::$validation_rules;
	}

	/**
	 * Return class name from filename of validation rule.
	 *
	 * @param string $filename File name.
	 * @return string
	 */
	protected static function _get_class_name_from_validation_rule_filename( $filename ) {
		$class_name = preg_replace( '/^class\./', '', basename( $filename, '.php' ) );
		$class_name = str_replace( '-', '_', $class_name );
		$class_name = 'MW_WP_Form_Validation_Rule_' . $class_name;
		return $class_name;
	}
}
