<?php
/**
 * Name       : MW WP Form Field Checkbox
 * Description: チェックボックスを出力
 * Version    : 1.5.2
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : December 14, 2012
 * Modified   : January 21, 2015
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Field_Checkbox extends MW_WP_Form_Abstract_Form_Field {

	/**
	 * $type
	 * フォームタグの種類 input|select|button|error|other
	 * @var string
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
			'display_name'   => __( 'Checkbox', MWF_Config::DOMAIN ),
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
			'id'         => '',
			'children'   => '',
			'value'      => '',
			'vertically' => '',
			'show_error' => 'true',
			'separator'  => ', ',
		);
	}

	/**
	 * input_page
	 * 入力ページでのフォーム項目を返す
	 * @return string HTML
	 */
	protected function input_page() {
		$children = $this->get_children( $this->atts['children'] );
		$value    = $this->get_children( $this->atts['value'] );
		$separator = ( $this->atts['separator'] ) ? $this->atts['separator'] : $this->defaults['separator'];
		$_ret = $this->Form->checkbox( $this->atts['name'], $children, array(
			'id'         => $this->atts['id'],
			'value'      => $value,
			'vertically' => $this->atts['vertically'],
		), $separator );
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
		$children     = $this->get_children( $this->atts['children'] );
		$value        = $this->Form->get_checked_value( $this->atts['name'], $children );
		$posted_value = $this->Form->get_separated_raw_value( $this->atts['name'], $children );
		$_ret         = esc_html( $value );
		$_ret        .= $this->Form->hidden( $this->atts['name'] . '[data]', $posted_value );
		$_ret        .= $this->Form->separator( $this->atts['name'] );
		return $_ret;
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
			<strong><?php esc_html_e( 'Choices', MWF_Config::DOMAIN ); ?><span class="mwf_require">*</span></strong>
			<?php $children = "\n" . $this->get_value_for_generator( 'children', $options ); ?>
			<textarea name="children"><?php echo esc_attr( $children ); ?></textarea>
			<span class="mwf_note">
				<?php esc_html_e( 'Input one line about one item.', MWF_Config::DOMAIN ); ?><br />
				<?php esc_html_e( 'Example: value1&crarr;value2 or key1:value1&crarr;key2:value2', MWF_Config::DOMAIN ); ?>
			</span>
		</p>
		<p>
			<strong><?php esc_html_e( 'Default value', MWF_Config::DOMAIN ); ?></strong>
			<?php $value = $this->get_value_for_generator( 'value', $options ); ?>
			<input type="text" name="value" value="<?php echo esc_attr( $value ); ?>" />
		</p>
		<p>
			<strong><?php esc_html_e( 'Separator string', MWF_Config::DOMAIN ); ?></strong>
			<?php $separator = $this->get_value_for_generator( 'separator', $options ); ?>
			<input type="text" name="separator" size="10" value="<?php echo esc_attr( $separator ); ?>" />
		</p>
		<p>
			<strong><?php esc_html_e( 'Display method', MWF_Config::DOMAIN ); ?></strong>
			<?php $vertically = $this->get_value_for_generator( 'vertically', $options ); ?>
			<label><input type="checkbox" name="vertically" value="true" <?php checked( 'true', $vertically ); ?> /> <?php esc_html_e( 'Arranged vertically.', MWF_Config::DOMAIN ); ?></label>
		</p>
		<p>
			<strong><?php esc_html_e( 'Dsiplay error', MWF_Config::DOMAIN ); ?></strong>
			<?php $show_error = $this->get_value_for_generator( 'show_error', $options ); ?>
			<label><input type="checkbox" name="show_error" value="false" <?php checked( 'false', $show_error ); ?> /> <?php esc_html_e( 'Don\'t display error.', MWF_Config::DOMAIN ); ?></label>
		</p>
		<?php
	}
}
