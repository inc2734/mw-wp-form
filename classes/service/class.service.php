<?php
class MW_WP_Form_Service {

	/**
	 * form_fields
	 * フォームフィールドの配列
	 */
	protected $form_fields = array();

	/**
	 * $validation_rules
	 * バリデーションルールの配列
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
		'zip'           => '',
		'tel'           => '',
		'mail'          => '',
		'date'          => '',
		'url'           => '',
		'eq'            => '',
		'between'       => '',
		'minlength'     => '',
		'filetype'      => '',
		'filesize'      => '',
	);

	/**
	 * instantiate_form_fields
	 * フォームフィールドのインスタンス化。配列にはフックを通して格納する。
	 */
	public function instantiate_form_fields() {
		$plugin_dir_path = plugin_dir_path( __FILE__ );
		foreach ( glob( $plugin_dir_path . '../form-fields/*.php' ) as $form_field ) {
			include_once $form_field;
			$className = basename( $form_field, '.php' );
			if ( class_exists( $className ) ) {
				new $className();
			}
		}
		$this->form_fields = apply_filters( 'mwform_form_fields', $this->form_fields );
	}

	/**
	 * get_validation_rule_objects
	 * 各バリデーションルールクラスを読み込み返す
	 * @param string $key フォーム識別子
	 * @return $validation_rules バリデーションルールオブジェクトの配列
	 */
	protected function get_validation_rule_objects( $key ) {
		$validation_rules = array();
		$plugin_dir_path = plugin_dir_path( __FILE__ );
		foreach ( glob( $plugin_dir_path . '../validation-rules/*.php' ) as $validation_rule ) {
			include_once $validation_rule;
			$className = basename( $validation_rule, '.php' );
			if ( class_exists( $className ) ) {
				$instance = new $className( $key );
				$validation_rules[$instance->getName()] = $instance;
			}
		}
		return $validation_rules;
	}

	/**
	 * set_validation_rules
	 * @param string $key フォーム識別子
	 */
	public function set_validation_rules( $key = '' ) {
		$validation_rules = $this->get_validation_rule_objects( $key );
		$validation_rules = array_merge(
			$this->validation_rules,
			$validation_rules
		);
		$this->validation_rules = apply_filters(
			'mwform_validation_rules',
			$validation_rules,
			$key
		);
	}
}