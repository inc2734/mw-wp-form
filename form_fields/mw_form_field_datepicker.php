<?php
/**
 * Name: MW Form Field Datepicker
 * Description: datepickerを出力。
 * Version: 1.4.2
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : December 14, 2012
 * Modified: September 3, 2014
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_Form_Field_Datepicker extends MW_Form_Field {

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
			'shortcode_name' => 'mwform_datepicker',
			'display_name' => __( 'Datepicker', MWF_Config::DOMAIN ),
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
			'size'       => 30,
			'js'         => '',
			'value'      => '',
			'show_error' => 'true',
		);
	}

	/**
	 * inputPage
	 * 入力ページでのフォーム項目を返す
	 * @return string HTML
	 */
	protected function inputPage() {
		global $wp_scripts;
		$ui = $wp_scripts->query( 'jquery-ui-core' );
		wp_enqueue_style( 'jquery.ui', '//ajax.googleapis.com/ajax/libs/jqueryui/' . $ui->ver . '/themes/smoothness/jquery-ui.min.css', array(), $ui->ver );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		// jsの指定がないときはデフォルトで年付き変更機能追加
		if ( empty( $this->atts['js'] ) ) {
			$this->atts['js'] = 'showMonthAfterYear: true, changeYear: true, changeMonth: true';
		}
		// 日本語の場合は日本語表記に変更
		if ( get_locale() == 'ja' ) {
			if ( !empty( $this->atts['js'] ) )
				$this->atts['js'] = $this->atts['js'] . ',';
			$this->atts['js'] .= '
				yearSuffix: "年",
				dateFormat: "yy-mm-dd",
				dayNames: ["日曜日","月曜日","火曜日","水曜日","木曜日","金曜日","土曜日"],
				dayNamesMin: ["日","月","火","水","木","金","土"],
				dayNamesShort: ["日曜","月曜","火曜","水曜","木曜","金曜","土曜"],
				monthNames: ["1月","2月","3月","4月","5月","6月","7月","8月","9月","10月","11月","12月"],
				monthNamesShort: ["1月","2月","3月","4月","5月","6月","7月","8月","9月","10月","11月","12月"]
			';
		}
		$_ret  = '';
		$_ret .= $this->Form->datepicker( $this->atts['name'], array(
			'id'    => $this->atts['id'],
			'size'  => $this->atts['size'],
			'js'    => $this->atts['js'],
			'value' => $this->atts['value'],
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
			<strong>JavaScript(<?php esc_html_e( 'option', MWF_Config::DOMAIN ); ?>)</strong>
			<input type="text" name="js" />
		</p>
		<p>
			<strong><?php esc_html_e( 'Default value', MWF_Config::DOMAIN ); ?>(<?php esc_html_e( 'option', MWF_Config::DOMAIN ); ?>)</strong>
			<input type="text" name="value" />
		</p>
		<p>
			<strong><?php esc_html_e( 'Dsiplay error', MWF_Config::DOMAIN ); ?></strong>
			<input type="checkbox" name="show_error" value="false" /> <?php esc_html_e( 'Don\'t display error.', MWF_Config::DOMAIN ); ?>
		</p>
		<?php
	}
}
