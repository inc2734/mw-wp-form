<?php
/**
 * @todo こんな大袈裟なクラスは多分いらない。MW WP Form の初期化時に全部 new してしまって、
 * 使うとき、もしくは __construct 時に jp のフィルタリングするのが良さそう
 */
class MW_WP_Form_Validation_Rules {

	/**
	 * バリデーションルールの配列。順番を固定するために定義が必要
	 * @var array
	 */
	protected $validation_rules = array(
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

	/**
	 * 日本語の時のみ使用できるバリデーションルール
	 * @var array
	 */
	protected $validation_rules_only_jp = array(
		'MW_WP_Form_Validation_Rule_Zip',
		'MW_WP_Form_Validation_Rule_Tel',
	);

	public function __construct() {
		$plugin_dir_path = plugin_dir_path( __FILE__ ) . '../../';

		foreach ( $this->validation_rules_only_jp as $key => $value ) {
			$this->validation_rules_only_jp[ $key ] = strtolower( $value );
		}

		foreach ( glob( $plugin_dir_path . './classes/validation-rules/*.php' ) as $filename ) {
			$class_name = $this->_get_class_name_from_validation_rule_filename( $filename );
			if ( ! class_exists( $class_name ) ) {
				continue;
			}

			if ( 'ja' !== get_locale()  && in_array( strtolower( $class_name ), $this->validation_rules_only_jp ) ) {
				continue;
			}

			new $class_name();
		}

		$this->validation_rules = apply_filters(
			'mwform_validation_rules',
			$this->validation_rules,
			null // 後方互換性のために残してるだけ
		);
	}

	/**
	 * バリデーションルールのインスタンス化。配列にはフックを通して格納する。
	 *
	 * @param string $key フォーム識別子
	 * @return $validation_rules バリデーションルールオブジェクトの配列
	 */
	public function get_validation_rules() {
		return $this->validation_rules;
	}

	/**
	 * バリデーションルールクラスのファイル名からクラス名を取得
	 *
	 * @param string $filename ファイル名
	 * @return string クラス名
	 */
	protected function _get_class_name_from_validation_rule_filename( $filename ) {
		$class_name = preg_replace( '/^class\./', '', basename( $filename, '.php' ) );
		$class_name = str_replace( '-', '_', $class_name );
		$class_name = 'MW_WP_Form_Validation_Rule_' . $class_name;
		return $class_name;
	}
}
