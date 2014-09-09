<?php
/**
 * Name: MW Form Field Confirm Button
 * Description: 確認ボタンを出力。
 * Version: 1.4.3
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : December 14, 2012
 * Modified: September 5, 2014
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_Form_Field_Confirm_Button extends MW_Form_Field {

	/**
	 * string $type フォームタグの種類
	 * input, select, button, other
	 */
	public $type = 'button';

	/**
	 * set_names
	 * shortcode_name、display_nameを定義。各子クラスで上書きする。
	 * @return array shortcode_name, display_name
	 */
	protected function set_names() {
		return array(
			'shortcode_name' => 'mwform_confirmButton',
			'display_name' => __( 'Confirm Button', MWF_Config::DOMAIN ),
		);
	}

	/**
	 * setDefaults
	 * $this->defaultsを設定し返す
	 * @return array defaults
	 */
	protected function setDefaults() {
		return array(
			'value' => __( 'Confirm', MWF_Config::DOMAIN ),
		);
	}

	/**
	 * inputPage
	 * 入力ページでのフォーム項目を返す
	 * @return string HTML
	 */
	protected function inputPage() {
		return $this->Form->submit( $this->Form->getConfirmButtonName(), $this->atts['value'] );
	}

	/**
	 * confirmPage
	 * 確認ページでのフォーム項目を返す
	 * @return string HTML
	 */
	protected function confirmPage() {
	}

	/**
	 * add_mwform_tag_generator
	 * フォームタグジェネレーター
	 */
	public function mwform_tag_generator_dialog() {
		?>
		<p>
			<strong><?php esc_html_e( 'String on the button', MWF_Config::DOMAIN ); ?>(<?php esc_html_e( 'option', MWF_Config::DOMAIN ); ?>)</strong>
			<input type="text" name="value" />
		</p>
		<?php
	}
}
