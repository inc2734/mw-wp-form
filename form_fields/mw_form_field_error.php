<?php
/**
 * Name: MW Form Field Error
 * Description: エラーを出力。
 * Version: 1.4.1
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : December 14, 2012
 * Modified: July 24, 2014
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_Form_Field_Error extends MW_Form_Field {

	/**
	 * set_names
	 * shortcode_name、display_nameを定義。各子クラスで上書きする。
	 * @return array shortcode_name, display_name
	 */
	protected function set_names() {
		return array(
			'shortcode_name' => 'mwform_error',
			'display_name' => __( 'Error Message', MWF_Config::DOMAIN ),
		);
	}

	/**
	 * setDefaults
	 * $this->defaultsを設定し返す
	 * @return array defaults
	 */
	protected function setDefaults() {
		return array(
			'keys' => '',
		);
	}

	/**
	 * inputPage
	 * 入力ページでのフォーム項目を返す
	 * @return string HTML
	 */
	protected function inputPage() {
		$keys = explode( ',', $this->atts['keys'] );
		$_ret = '';
		foreach ( $keys as $key ) {
			$_ret .= $this->getError( trim( $key ) );
		}
		return $_ret;
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
			<strong><?php esc_html_e( 'name of the element which wants to display error', MWF_Config::DOMAIN ); ?></strong>
			<textarea name="keys"></textarea>
			<span class="mwf_note">
				<?php esc_html_e( 'Input one line about one item.', MWF_Config::DOMAIN ); ?>
			</span>
		</p>
		<?php
	}
}
