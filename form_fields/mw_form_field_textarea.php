<?php
/**
 * Name: MW Form Field TextArea
 * Description: テキストエリアを出力。
 * Version: 1.4.3
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : December 14, 2012
 * Modified: September 3, 2014
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_Form_Field_Textarea extends MW_Form_Field {

	/**
	 * string $type フォームタグの種類
	 * input, select, button, other
	 */
	public $type = 'input';

	/**
	 * set_names
	 * shortcode_name、display_nameを定義。各子クラスで上書きする。
	 * @return array shortcode_name, display_name
	 */
	protected function set_names() {
		return array(
			'shortcode_name' => 'mwform_textarea',
			'display_name' => __( 'Textarea', MWF_Config::DOMAIN ),
		);
	}

	/**
	 * setDefaults
	 * $this->defaultsを設定し返す
	 * @return array
	 */
	protected function setDefaults() {
		return array(
			'name'        => '',
			'id'          => '',
			'cols'        => 50,
			'rows'        => 5,
			'value'       => '',
			'placeholder' => '',
			'show_error'  => 'true',
		);
	}

	/**
	 * inputPage
	 * 入力ページでのフォーム項目を返す
	 * @return string html
	 */
	protected function inputPage() {
		$_ret = $this->Form->textarea( $this->atts['name'], array(
			'id' => $this->atts['id'],
			'cols' => $this->atts['cols'],
			'rows' => $this->atts['rows'],
			'value' => $this->atts['value'],
			'placeholder' => $this->atts['placeholder'],
		) );
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
		$value = $this->Form->getValue( $this->atts['name'] );
		$_ret  = nl2br( esc_html( $value ) );
		$_ret .= $this->Form->hidden( $this->atts['name'], $value );
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
			<strong>cols(<?php esc_html_e( 'option', MWF_Config::DOMAIN ); ?>)</strong>
			<input type="text" name="cols" />
		</p>
		<p>
			<strong>rows(<?php esc_html_e( 'option', MWF_Config::DOMAIN ); ?>)</strong>
			<input type="text" name="rows" />
		</p>
		<p>
			<strong><?php esc_html_e( 'Default value', MWF_Config::DOMAIN ); ?>(<?php esc_html_e( 'option', MWF_Config::DOMAIN ); ?>)</strong>
			<input type="text" name="value" />
		</p>
		<p>
			<strong>placeholder(<?php esc_html_e( 'option', MWF_Config::DOMAIN ); ?>)</strong>
			<input type="text" name="placeholder" />
		</p>
		<p>
			<strong><?php esc_html_e( 'Dsiplay error', MWF_Config::DOMAIN ); ?></strong>
			<input type="checkbox" name="show_error" value="false" /> <?php esc_html_e( 'Don\'t display error.', MWF_Config::DOMAIN ); ?>
		</p>
		<?php
	}
}
