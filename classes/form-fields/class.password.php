<?php
/**
 * Name       : MW WP Form Password
 * Description: パスワードフィールドを出力
 * Version    : 1.6.0
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : December 14, 2012
 * Modified   : November 14, 2015
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Field_Password extends MW_WP_Form_Abstract_Form_Field {

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
			'shortcode_name' => 'mwform_password',
			'display_name'   => __( 'Password', 'mw-wp-form' ),
		);
	}

	/**
	 * set_defaults
	 * $this->defaultsを設定し返す
	 * @return array defaults
	 */
	protected function set_defaults() {
		return array(
			'name'        => '',
			'id'          => null,
			'class'       => null,
			'size'        => 60,
			'maxlength'   => null,
			'value'       => '',
			'placeholder' => null,
			'show_error'  => 'true',
		);
	}

	/**
	 * input_page
	 * 入力ページでのフォーム項目を返す
	 * @return string HTML
	 */
	protected function input_page() {
		$value = $this->Data->get_raw( $this->atts['name'] );
		if ( is_null( $value ) ) {
			$value = $this->atts['value'];
		}

		$_ret = $this->Form->password( $this->atts['name'], array(
			'id'          => $this->atts['id'],
			'class'       => $this->atts['class'],
			'size'        => $this->atts['size'],
			'maxlength'   => $this->atts['maxlength'],
			'value'       => $value,
			'placeholder' => $this->atts['placeholder'],
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
		$value = $this->Data->get_raw( $this->atts['name'] );
		return '*****' . $this->Form->hidden( $this->atts['name'], $value );
	}

	/**
	 * add_mwform_tag_generator
	 * フォームタグジェネレーター
	 */
	public function mwform_tag_generator_dialog( array $options = array() ) {
		?>
		<p>
			<strong>name<span class="mwf_require">*</span></strong>
			<?php $name = $this->get_value_for_generator( 'name', $options ); ?>
			<input type="text" name="name" value="<?php echo esc_attr( $name ); ?>" />
		</p>
		<p>
			<strong>id</strong>
			<?php $id = $this->get_value_for_generator( 'id', $options ); ?>
			<input type="text" name="id" value="<?php echo esc_attr( $id ); ?>" />
		</p>
		<p>
			<strong>class</strong>
			<?php $class = $this->get_value_for_generator( 'class', $options ); ?>
			<input type="text" name="class" value="<?php echo esc_attr( $class ); ?>" />
		</p>
		<p>
			<strong>size</strong>
			<?php $size = $this->get_value_for_generator( 'size', $options ); ?>
			<input type="text" name="size" value="<?php echo esc_attr( $size ); ?>" />
		</p>
		<p>
			<strong>maxlength</strong>
			<?php $maxlength = $this->get_value_for_generator( 'maxlength', $options ); ?>
			<input type="text" name="maxlength" value="<?php echo esc_attr( $maxlength ); ?>" />
		</p>
		<p>
			<strong><?php esc_html_e( 'Default value', 'mw-wp-form' ); ?></strong>
			<?php $value = $this->get_value_for_generator( 'value', $options ); ?>
			<input type="text" name="value" value="<?php echo esc_attr( $value ); ?>" />
		</p>
		<p>
			<strong>placeholder</strong>
			<?php $placeholder = $this->get_value_for_generator( 'placeholder', $options ); ?>
			<input type="text" name="placeholder" value="<?php echo esc_attr( $placeholder ); ?>" />
		</p>
		<p>
			<strong><?php esc_html_e( 'Dsiplay error', 'mw-wp-form' ); ?></strong>
			<?php $show_error = $this->get_value_for_generator( 'show_error', $options ); ?>
			<label><input type="checkbox" name="show_error" value="false" <?php checked( 'false', $show_error ); ?> /> <?php esc_html_e( 'Don\'t display error.', 'mw-wp-form' ); ?></label>
		</p>
		<?php
	}
}
