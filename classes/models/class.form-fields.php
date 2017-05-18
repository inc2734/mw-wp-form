<?php
/**
 * @todo こんな大袈裟なクラスは多分いらない。MW WP Form の初期化時に全部 new してしまって、
 * 使うとき、もしくは __construct 時に jp のフィルタリングするのが良さそう
 */
class MW_WP_Form_Form_Fields {

	/**
	 * フォームフィールドの配列
	 * @var array
	 */
	protected $form_fields = array();

	/**
	 * 日本語の時のみ使用できるフォーム項目
	 * @var array
	 */
	protected $form_fields_only_jp = array(
		'MW_WP_Form_Field_Zip',
		'MW_WP_Form_Field_Tel',
	);

	public function __construct() {
		$plugin_dir_path = plugin_dir_path( __FILE__ ) . '../../';

		foreach ( $this->form_fields_only_jp as $key => $value ) {
			$this->form_fields_only_jp[$key] = strtolower( $value );
		}

		foreach ( glob( $plugin_dir_path . './classes/form-fields/*.php' ) as $filename ) {
			$class_name = $this->_get_class_name_from_form_field_filename( $filename );
			if ( ! class_exists( $class_name ) ) {
				continue;
			}

			if ( 'ja' !== get_locale() && in_array( strtolower( $class_name ), $this->form_fields_only_jp ) ) {
				continue;
			}

			new $class_name();
		}

		$this->form_fields = apply_filters( 'mwform_form_fields', $this->form_fields );
	}

	/**
	 * Return all form fields
	 *
	 * @return array
	 */
	public function get_form_fields() {
		return $form_fields;
	}

	/**
	 * フォーム項目クラスのファイル名からクラス名を取得
	 *
	 * @param string $filename ファイル名
	 * @return string クラス名
	 */
	protected function _get_class_name_from_form_field_filename( $filename ) {
		$class_name = preg_replace( '/^class\./', '', basename( $filename, '.php' ) );
		$class_name = str_replace( '-', '_', $class_name );
		$class_name = 'MW_WP_Form_Field_' . $class_name;
		return $class_name;
	}
}
