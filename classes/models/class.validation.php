<?php
/**
 * Name       : MW WP Form Validation
 * Version    : 2.0.0
 * Author     : Takashi Kitajima
 * Author URI : https://2inc.org
 * Created    : July 20, 2012
 * Modified   : June 1, 2017
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Validation {

	/**
	 * @var string
	 */
	protected $form_key;

	/**
	 * @var MW_WP_Form_Data
	 */
	protected $Data;

	/**
	 * @var MW_WP_Form_Setting
	 */
	protected $Setting;

	/**
	 * Array of name of validated and array of validation rules for it pairs
	 * @var array
	 */
	protected $validate = array();

	/**
	 * @param string $form_key
	 */
	public function __construct( $form_key ) {
		$this->form_key = $form_key;
		$this->Data     = MW_WP_Form_Data::connect( $form_key );
		$form_id        = MWF_Functions::get_form_id_from_form_key( $form_key );
		$this->Setting  = new MW_WP_Form_Setting( $form_id );

		$this->_set_rules();
	}

	/**
	 * Set validation rules of this form
	 *
	 * @return void
	 */
	protected function _set_rules() {
		$validations = $this->Setting->get( 'validation' );
		if ( $validations ) {
			foreach ( $validations as $validation ) {
				foreach ( $validation as $rule => $options ) {
					if ( 'target' === $rule ) {
						continue;
					}
					if ( ! is_array( $options ) ) {
						$options = array();
					}
					$this->set_rule( $validation['target'], $rule, $options );
				}
			}
		}

		$Akismet = new MW_WP_Form_Akismet();
		$akismet_check = $Akismet->is_valid(
			$this->Setting->get( 'akismet_author' ),
			$this->Setting->get( 'akismet_author_email' ),
			$this->Setting->get( 'akismet_author_url' ),
			$this->Data
		);
		if ( $akismet_check ) {
			$this->set_rule( MWF_Config::AKISMET, 'akismet_check' );
		}

		$Validation = apply_filters(
			'mwform_validation_' . $this->form_key,
			$this,
			$this->Data->gets(),
			clone $this->Data
		);
	}

	/**
	 * Set validation rule of the form field.
	 *
	 * @param string $key
	 * @param string $rule
	 * @param array $options
	 * @return MW_WP_Form_Validation
	 */
	public function set_rule( $key, $rule, array $options = array() ) {
		$rules = array(
			'rule'    => strtolower( $rule ),
			'options' => $options
		);
		$this->validate[ $key ][] = $rules;
		return $this;
	}

	/**
	 * for unit tests
	 *
	 * @return bool
	 */
	public function is_valid_validation_settings() {
		if ( ! is_array( $this->validate ) ) {
			return false;
		}

		foreach ( $this->validate as $validate ) {
			if ( ! is_array( $validate ) ) {
				return false;
			}

			foreach ( $validate as $key => $validation_rule ) {
				if ( ! is_array( $validation_rule ) ) {
					return false;
				}

				if ( ! isset( $validation_rule['rule'] ) ) {
					return false;
				}

				if ( ! isset( $validation_rule['options'] ) ) {
					return false;
				}

				if ( ! is_array( $validation_rule['options'] ) ) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Validation check form fields
	 *
	 * @return bool Return true when nothing errors
	 */
	public function is_valid() {
		foreach ( $this->validate as $key => $rules ) {
			$this->_is_valid( $key, $rules );
		}

		return (bool) ! $this->Data->get_validation_errors();
	}

	public function check() {
		MWF_Functions::deprecated_message(
			'MW_WP_Form_Validation::check()',
			'MW_WP_Form_Validation::is_valid()'
		);

		return $this->is_valid();
	}

	/**
	 * Validation check the one form field
	 *
	 * @param string $key
	 * @return bool Return true when nothing errors
	 */
	public function is_valid_field( $key ) {
		if ( isset( $this->validate[ $key ] ) ) {
			$this->_is_valid( $key, $this->validate[ $key ] );
		}

		return (bool) ! $this->Data->get_validation_error( $key );
	}

	public function single_check( $key ) {
		MWF_Functions::deprecated_message(
			'MW_WP_Form_Validation::single_check()',
			'MW_WP_Form_Validation::is_valid_field()'
		);

		return $this->is_valid_field();
	}

	/**
	 * Set varidation errors into MW_WP_Form_Data
	 *
	 * @param string $key
	 * @param array $rules
	 */
	protected function _is_valid( $key, array $rules ) {
		$Validation_Rules = MW_WP_Form_Validation_Rules::instantiation( $this->form_key );
		$validation_rules = $Validation_Rules->get_validation_rules();

		foreach ( $rules as $rule_set ) {
			if ( ! isset( $rule_set['rule'] ) ) {
				continue;
			}

			$options = array();
			if ( isset( $rule_set['options'] ) ) {
				$options = $rule_set['options'];
			}

			$rule = $rule_set['rule'];
			if ( ! isset( $validation_rules[ $rule ] ) ) {
				continue;
			}

			$validation_rule = $validation_rules[ $rule ];
			if ( ! is_callable( array( $validation_rule, 'rule' ) ) ) {
				continue;
			}

			$message = $validation_rule->rule( $key, $options );
			if ( empty( $message ) ) {
				continue;
			}

			$this->Data->set_validation_error( $key, $rule, $message );
		}
	}
}
