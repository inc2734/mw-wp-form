<?php
/**
 * Name       : MW WP Form Field Zip
 * Description: 郵便番号フィールドを出力
 * Version    : 1.6.0
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : December 14, 2012
 * Modified   : November 14, 2015
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Field_Zip extends MW_WP_Form_Abstract_Form_Field {

	/**
	 * $type
	 * フォームタグの種類 input|select|button|error|other
	 * @var string
	 */
	public $type = 'input';

	/**
	 * set_names
	 * shortcode_name、display_nameを定義。各子クラスで上書きする。
	 * @return array shortcode_name, display_name
	 */
	protected function set_names() {
		return array(
			'shortcode_name' => 'mwform_zip',
			'display_name'   => __( 'Zip Code', 'mw-wp-form' ),
		);
	}

	/**
	 * set_defaults
	 * $this->defaultsを設定し返す
	 * @return array defaults
	 */
	protected function set_defaults() {
		return array(
			'name'       => '',
			'class'      => null,
			'value'      => '',
			'show_error' => 'true',
			'conv_half_alphanumeric' => 'true',
		);
	}

	/**
	 * input_page
	 * 入力ページでのフォーム項目を返す
	 * @return string HTML
	 */
	protected function input_page() {
		$value = $this->Data->get_raw( $this->atts['name'] );
		if ( is_array( $value ) && isset( $value['data'] ) ) {
			$value = $value['data'];
		}
		if ( is_null( $value ) ) {
			$value = $this->atts['value'];
		}
		$conv_half_alphanumeric = 'true';
		if ( $this->atts['conv_half_alphanumeric'] !== 'true' ) {
			$conv_half_alphanumeric = null;
		}
		$_ret = $this->Form->zip( $this->atts['name'], array(
			'class' => $this->atts['class'],
			'conv-half-alphanumeric' => $conv_half_alphanumeric,
			'value' => $value,
		) );
		if ( $this->atts['show_error'] !== 'false' ) {
			$_ret .= $this->get_error( $this->atts['name'] );
		}
		return $_ret;
	}

	/**
	 * confirm_page
	 * 確認ページでのフォーム項目を返す
	 * @return string HTML
	 */
	protected function confirm_page() {
		$value     = $this->Data->get( $this->atts['name'] );
		$separator = $this->Data->get_separator_value( $this->atts['name'] );
		$_ret  = esc_html( $value );
		$_ret .= $this->Form->hidden( $this->atts['name'] . '[data]', $value );
		$_ret .= $this->Form->separator( $this->atts['name'], $separator );
		return $_ret;
	}

	/**
	 * add_mwform_tag_generator
	 * フォームタグジェネレーター
	 */
	public function mwform_tag_generator_dialog( array $options = array() ) {
		?>
		<p>
			<strong>name</strong>
			<?php $name = $this->get_value_for_generator( 'name', $options ); ?>
			<input type="text" name="name" value="<?php echo esc_attr( $name ); ?>" /></td>
		</p>
		<p>
			<strong>class</strong>
			<?php $class = $this->get_value_for_generator( 'class', $options ); ?>
			<input type="text" name="class" value="<?php echo esc_attr( $class ); ?>" />
		</p>
		<p>
			<strong><?php esc_html_e( 'Dsiplay error', 'mw-wp-form' ); ?></strong>
			<?php $show_error = $this->get_value_for_generator( 'show_error', $options ); ?>
			<label><input type="checkbox" name="show_error" value="false" <?php checked( 'false', $show_error ); ?> /> <?php esc_html_e( 'Don\'t display error.', 'mw-wp-form' ); ?></label>
		</p>
		<p>
			<strong><?php esc_html_e( 'Convert half alphanumeric', 'mw-wp-form' ); ?></strong>
			<?php $conv_half_alphanumeric = $this->get_value_for_generator( 'conv_half_alphanumeric', $options ); ?>
			<label><input type="checkbox" name="conv_half_alphanumeric" value="false" <?php checked( 'false', $conv_half_alphanumeric ); ?> /> <?php esc_html_e( 'Don\'t Convert.', 'mw-wp-form' ); ?></label>
		</p>
		<?php
	}
}
