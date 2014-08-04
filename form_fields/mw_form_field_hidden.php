<?php
/**
 * Name: MW Form Field Hidden
 * Description: hiddenフィールドを出力。
 * Version: 1.5.1
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : December 14, 2012
 * Modified: July 24, 2014
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_Form_Field_Hidden extends MW_Form_Field {

	/**
	 * set_names
	 * shortcode_name、display_nameを定義。各子クラスで上書きする。
	 * @return array shortcode_name, display_name
	 */
	protected function set_names() {
		return array(
			'shortcode_name' => 'mwform_hidden',
			'display_name' => __( 'Hidden', MWF_Config::DOMAIN ),
		);
	}

	/**
	 * setDefaults
	 * $this->defaultsを設定し返す
	 * @return array defaults
	 */
	protected function setDefaults() {
		return array(
			'name'  => '',
			'value' => '',
			'echo'  => 'false',
		);
	}

	/**
	 * inputPage
	 * 入力ページでのフォーム項目を返す
	 * @return string HTML
	 */
	protected function inputPage() {
		$echo_value = '';
		if ( $this->atts['echo'] === 'true' ) {
			$echo_value = $this->atts['value'];
		}
		return esc_html( $echo_value ) . $this->Form->hidden( $this->atts['name'], $this->atts['value'] );
	}

	/**
	 * confirmPage
	 * 確認ページでのフォーム項目を返す
	 * @return string HTML
	 */
	protected function confirmPage() {
		$value = $this->Form->getValue( $this->atts['name'] );
		$echo_value = '';
		if ( $this->atts['echo'] === 'true' ) {
			$echo_value = $value;
		}
		return $echo_value . $this->Form->hidden( $this->atts['name'], $value );
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
			<strong><?php esc_html_e( 'Default value', MWF_Config::DOMAIN ); ?>(<?php esc_html_e( 'option', MWF_Config::DOMAIN ); ?>)</strong>
			<input type="text" name="value" />
		</p>
		<p>
			<strong><?php esc_html_e( 'Display', MWF_Config::DOMAIN ); ?></strong>
			<input type="checkbox" name="echo" value="true" /> <?php esc_html_e( 'Display hidden value.', MWF_Config::DOMAIN ); ?>
		</p>
		<?php
	}
}
