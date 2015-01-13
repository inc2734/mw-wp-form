<?php
/**
 * Name       : MW WP Form Field Confirm Button
 * Description: 確認ボタンを出力
 * Version    : 1.5.0
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : December 14, 2012
 * Modified   : January 2, 2015
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Field_Confirm_Button extends MW_WP_Form_Abstract_Form_Field {

	/**
	 * $type
	 * フォームタグの種類 input|select|button|error|other
	 * @var string
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
			'display_name'   => __( 'Confirm Button', MWF_Config::DOMAIN ),
		);
	}

	/**
	 * set_defaults
	 * $this->defaultsを設定し返す
	 * @return array defaults
	 */
	protected function set_defaults() {
		return array(
			'value' => __( 'Confirm', MWF_Config::DOMAIN ),
		);
	}

	/**
	 * input_page
	 * 入力ページでのフォーム項目を返す
	 * @return string HTML
	 */
	protected function input_page() {
		return $this->Form->submit( MWF_Config::CONFIRM_BUTTON, $this->atts['value'] );
	}

	/**
	 * confirm_page
	 * 確認ページでのフォーム項目を返す
	 * @return string HTML
	 */
	protected function confirm_page() {
	}

	/**
	 * add_mwform_tag_generator
	 * フォームタグジェネレーター
	 */
	public function mwform_tag_generator_dialog( array $options = array() ) {
		?>
		<p>
			<strong><?php esc_html_e( 'String on the button', MWF_Config::DOMAIN ); ?></strong>
			<?php $name = $this->get_value_for_generator( 'name', $options ); ?>
			<input type="text" name="value" value="<?php echo esc_attr( $name ); ?>" />
		</p>
		<?php
	}
}
