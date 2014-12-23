<?php
class MW_WP_Form_Admin_Service extends MW_WP_Form_Service {

	/**
	 * $Admim
	 * 管理画面を管理するためのオブジェクト
	 */
	protected $Admim;

	/**
	 * set_admin_page
	 * @param MW_WP_Form_Admin_Page $Admin
	 */
	public function set_admin_page( MW_WP_Form_Admin_Page $Admin ) {
		$this->Admin = $Admin;
		$this->Admin->initialize();
	}

	/**
	 * set_validation_rules_in_admin_page
	 */
	public function set_validation_rules_in_admin_page() {
		$validation_rules = $this->validation_rules;
		foreach ( $validation_rules as $validation_name => $instance ) {
			if ( is_callable( array( $instance, 'admin' ) ) ) {
				$this->Admin->add_validation_rule( $instance->getName(), $instance );
			}
		}
	}
}