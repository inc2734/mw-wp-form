<?php
/**
 * Name: MW Form Field Submit
 * Description: 確認ボタンと送信ボタンを自動出力。
 * Version: 1.4.2
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : December 14, 2012
 * Modified: September 3, 2014
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_Form_Field_Submit_Button extends MW_Form_Field {

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
			'shortcode_name' => 'mwform_submitButton',
			'display_name' => __( 'Confirm &amp; Submit', MWF_Config::DOMAIN ),
		);
	}

	/**
	 * setDefaults
	 * $this->defaultsを設定し返す
	 * @return array defaults
	 */
	protected function setDefaults() {
		return array(
			'name' => '',
			'confirm_value' => __( 'Confirm', MWF_Config::DOMAIN ),
			'submit_value'  => __( 'Send', MWF_Config::DOMAIN ),
		);
	}

	/**
	 * inputPage
	 * 入力ページでのフォーム項目を返す
	 * @return string HTML
	 */
	protected function inputPage() {
		if ( !empty( $this->atts['confirm_value'] ) ) {
			return $this->Form->submit( $this->Form->getConfirmButtonName(), $this->atts['confirm_value'] );
		}
		return $this->Form->submit( $this->atts['name'], $this->atts['submit_value'] );
	}

	/**
	 * confirmPage
	 * 確認ページでのフォーム項目を返す
	 * @return string HTML
	 */
	protected function confirmPage() {
		return $this->Form->submit( $this->atts['name'], $this->atts['submit_value'] );
	}

	/**
	 * add_mwform_tag_generator
	 * フォームタグジェネレーター
	 */
	public function mwform_tag_generator_dialog() {
		?>
		<p>
			<strong>name</strong>
			<input type="text" name="name" />
		</p>
		<p>
			<strong><?php esc_html_e( 'String on the confirm button', MWF_Config::DOMAIN ); ?></strong>
			<input type="text" name="confirm_value" />
		</p>
		<p>
			<strong><?php esc_html_e( 'String on the submit button', MWF_Config::DOMAIN ); ?></strong>
			<input type="text" name="submit_value" />
		</p>
		<?php
	}
}
