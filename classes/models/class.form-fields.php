<?php
class MW_WP_Form_Form_Fields {

	/**
	 * @var MW_WP_Form_Form_Fields
	 */
	protected static $Instance;

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

	public static function instantiation() {
		if ( isset( self::$Instance ) ) {
			return self::$Instance;
		}

		self::$Instance = new self();
		return self::$Instance;
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
	 * フォーム項目クラスのファイル名からクラス名を取得
	 *
	 * @param string $filename ファイル名
	 * @return string クラス名
	 */
	protected static function _get_class_name_from_form_field_filename( $filename ) {
		$class_name = preg_replace( '/^class\./', '', basename( $filename, '.php' ) );
		$class_name = str_replace( '-', '_', $class_name );
		$class_name = 'MW_WP_Form_Field_' . $class_name;
		return $class_name;
	}
}
