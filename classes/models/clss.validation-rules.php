<?php
class MW_WP_Form_Validation_Rules {

	/**
	 * @var MW_WP_Form_Validation_Rules
	 */
	protected static $Instance;

	/**
	 * バリデーションルールの配列。順番を固定するために定義が必要
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
	);

	private function __construct() {
		foreach ( glob( plugin_dir_path( __FILE__ ) . '../validation-rules/*.php' ) as $filename ) {
			$class_name = self::_get_class_name_from_validation_rule_filename( $filename );
			if ( ! class_exists( $class_name ) ) {
				continue;
			}

			new $class_name();
		}

		self::$validation_rules = apply_filters(
			'mwform_validation_rules',
			self::$validation_rules,
			null // backward compatibility
		);
	}

	public static function instantiation() {
		if ( isset( self::$Instance ) ) {
			return self::$Instance;
		}

		self::$Instance = new self();
		return self::$Instance;
	}

	/**
	 * バリデーションルールのインスタンス化。配列にはフックを通して格納する。
	 *
	 * @param string $key フォーム識別子
	 * @return $validation_rules バリデーションルールオブジェクトの配列
	 */
	public function get_validation_rules() {
		return self::$validation_rules;
	}

	/**
	 * バリデーションルールクラスのファイル名からクラス名を取得
	 *
	 * @param string $filename ファイル名
	 * @return string クラス名
	 */
	protected static function _get_class_name_from_validation_rule_filename( $filename ) {
		$class_name = preg_replace( '/^class\./', '', basename( $filename, '.php' ) );
		$class_name = str_replace( '-', '_', $class_name );
		$class_name = 'MW_WP_Form_Validation_Rule_' . $class_name;
		return $class_name;
	}
}
