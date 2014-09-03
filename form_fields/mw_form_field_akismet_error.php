<?php
/**
 * Name: MW Form Field Akismet Error
 * Description: Akismetのエラーを出力。
 * Version: 1.3.3
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : June 21, 2013
 * Modified: September 3, 2014
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_Form_Field_Akismet_Error extends MW_Form_Field {

	/**
	 * set_names
	 * shortcode_name、display_nameを定義。各子クラスで上書きする。
	 * @return array shortcode_name, display_name
	 */
	protected function set_names() {
		return array(
			'shortcode_name' => 'mwform_akismet_error',
			'display_name' => __( 'Akismet Error', MWF_Config::DOMAIN ),
		);
	}

	/**
	 * setDefaults
	 * $this->defaultsを設定し返す
	 * @return array defaults
	 */
	protected function setDefaults() {
		return array();
	}

	/**
	 * inputPage
	 * 入力ページでのフォーム項目を返す
	 * @return string HTML
	 */
	protected function inputPage() {
		$error = $this->getError( MWF_Config::AKISMET );
		if ( $error ) {
			return sprintf(
				'<span class="akismet_error">%s</span>',
				$this->getError( MWF_Config::AKISMET )
			);
		}
	}

	/**
	 * confirmPage
	 * 確認ページでのフォーム項目を返す
	 * @return string HTML
	 */
	protected function confirmPage() {
	}
}
