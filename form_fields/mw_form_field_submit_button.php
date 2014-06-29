<?php
/**
 * Name: MW Form Field Submit
 * URI: http://2inc.org
 * Description: サブミットボタンを出力。
 * Description: 確認ボタンと送信ボタンを自動出力。
 * Version: 1.4.0
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : December 14, 2012
 * Modified: April 5, 2014
 * License: GPL2
 *
 * Copyright 2014 Takashi Kitajima (email : inc@2inc.org)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
class mw_form_field_submit_button extends mw_form_field {

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
	 * @return	Array	defaults
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
	 * @return	String	HTML
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
	 * @return	String	HTML
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
