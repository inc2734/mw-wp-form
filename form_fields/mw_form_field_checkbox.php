<?php
/**
 * Name: MW Form Field Checkbox
 * Description: チェックボックスを出力。
 * Version: 1.4.3
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : December 14, 2012
 * Modified: September 3, 2014
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_Form_Field_Checkbox extends MW_Form_Field {

	/**
	 * string $type フォームタグの種類
	 * input, select, button, other
	 */
	public $type = 'select';

	/**
	 * set_names
	 * shortcode_name、display_nameを定義。各子クラスで上書きする。
	 * @return array shortcode_name, display_name
	 */
	protected function set_names() {
		return array(
			'shortcode_name' => 'mwform_checkbox',
			'display_name' => __( 'Checkbox', MWF_Config::DOMAIN ),
		);
	}

	/**
	 * setDefaults
	 * $this->defaultsを設定し返す
	 * @return array defaults
	 */
	protected function setDefaults() {
		return array(
			'name'       => '',
			'id'         => '',
			'children'   => '',
			'value'      => '',
			'show_error' => 'true',
			'separator'  => ',',
		);
	}

	/**
	 * inputPage
	 * 入力ページでのフォーム項目を返す
	 * @return string HTML
	 */
	protected function inputPage() {
		$children = $this->getChildren( $this->atts['children'] );
		$_ret = $this->Form->checkbox( $this->atts['name'], $children, array(
			'id'    => $this->atts['id'],
			'value' => $this->atts['value'],
		), $this->atts['separator'] );
		if ( $this->atts['show_error'] !== 'false' )
			$_ret .= $this->getError( $this->atts['name'] );
		return $_ret;
	}

	/**
	 * confirmPage
	 * 確認ページでのフォーム項目を返す
	 * @return string HTML
	 */
	protected function confirmPage() {
		$children = $this->getChildren( $this->atts['children'] );
		$value = $this->Form->getCheckedValue( $this->atts['name'], $children );
		$_ret  = esc_html( $value );
		$_ret .= $this->Form->hidden( $this->atts['name'] . '[data]', $value );
		$_ret .= $this->Form->separator( $this->atts['name'] );
		return $_ret;
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
			<strong>id(<?php esc_html_e( 'option', MWF_Config::DOMAIN ); ?>)</strong>
			<input type="text" name="id" />
		</p>
		<p>
			<strong><?php esc_html_e( 'Choices', MWF_Config::DOMAIN ); ?>(<?php esc_html_e( 'option', MWF_Config::DOMAIN ); ?>)</strong>
			<textarea name="children"></textarea>
			<span class="mwf_note">
				<?php esc_html_e( 'Input one line about one item.', MWF_Config::DOMAIN ); ?>
			</span>
		</p>
		<p>
			<strong><?php esc_html_e( 'Default value', MWF_Config::DOMAIN ); ?>(<?php esc_html_e( 'option', MWF_Config::DOMAIN ); ?>)</strong>
			<input type="text" name="value" />
		</p>
		<p>
			<strong><?php esc_html_e( 'Separator string', MWF_Config::DOMAIN ); ?></strong>
			<input type="text" name="separator" value="" size="10" />
		</p>
		<p>
			<strong><?php esc_html_e( 'Dsiplay error', MWF_Config::DOMAIN ); ?></strong>
			<input type="checkbox" name="show_error" value="false" /> <?php esc_html_e( 'Don\'t display error.', MWF_Config::DOMAIN ); ?>
		</p>
		<?php
	}
}
