<?php
/**
 * Name: MW Form Field Text
 * Description: テキストフィールドを出力。
 * Version: 1.4.4
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : December 14, 2012
 * Modified: September 3, 2014
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_Form_Field_Text extends MW_Form_Field {

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
			'shortcode_name' => 'mwform_text',
			'display_name' => __( 'Text', MWF_Config::DOMAIN ),
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
			'size'        => 60,
			'maxlength'   => 255,
			'value'       => '',
			'placeholder' => '',
			'show_error'  => 'true',
			'conv_half_alphanumeric' => 'false',
		);
	}

	/**
	 * inputPage
	 * 入力ページでのフォーム項目を返す
	 * @return string html
	 */
	protected function inputPage() {
		$conv_half_alphanumeric = false;
		if ( $this->atts['conv_half_alphanumeric'] === 'true' ) {
			$conv_half_alphanumeric = true;
		}
		$_ret = $this->Form->text( $this->atts['name'], array(
			'id'          => $this->atts['id'],
			'size'        => $this->atts['size'],
			'maxlength'   => $this->atts['maxlength'],
			'value'       => $this->atts['value'],
			'placeholder' => $this->atts['placeholder'],
			'conv-half-alphanumeric' => $conv_half_alphanumeric,
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
		$_ret  = esc_html( $value );
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
			<strong>size(<?php esc_html_e( 'option', MWF_Config::DOMAIN ); ?>)</strong>
			<input type="text" name="size" />
		</p>
		<p>
			<strong>maxlength(<?php esc_html_e( 'option', MWF_Config::DOMAIN ); ?>)</strong>
			<input type="text" name="maxlength" />
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
		<p>
			<strong><?php esc_html_e( 'Convert half alphanumeric', MWF_Config::DOMAIN ); ?></strong>
			<input type="checkbox" name="conv_half_alphanumeric" value="true" /> <?php esc_html_e( 'Convert.', MWF_Config::DOMAIN ); ?>
		</p>
		<?php
	}
}
