<?php
/**
 * Name       : MW WP Form Field Range
 * Description: range フィールドを出力
 * Version    : 1.1.0
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : July 21, 2015
 * Modified   : November 14, 2015
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Field_Range extends MW_WP_Form_Abstract_Form_Field {

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
			'shortcode_name' => 'mwform_range',
			'display_name'   => __( 'Range', 'mw-wp-form' ),
		);
	}

	/**
	 * set_defaults
	 * $this->defaultsを設定し返す
	 * @return array
	 */
	protected function set_defaults() {
		return array(
			'name'       => '',
			'id'         => null,
			'class'      => null,
			'value'      => '',
			'min'        => 0,
			'max'        => 100,
			'step'       => 1,
			'show_error' => 'true',
		);
	}

	/**
	 * input_page
	 * 入力ページでのフォーム項目を返す
	 * @return string html
	 */
	protected function input_page() {
		$value = $this->Data->get_raw( $this->atts['name'] );
		if ( is_null( $value ) ) {
			$value = $this->atts['value'];
		}

		$_ret = $this->Form->range( $this->atts['name'], array(
			'id'    => $this->atts['id'],
			'class' => $this->atts['class'],
			'value' => $value,
			'min'   => $this->atts['min'],
			'max'   => $this->atts['max'],
			'step'  => $this->atts['step'],
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
		$_ret  = esc_html( $value );
		$_ret .= $this->Form->hidden( $this->atts['name'], $value );
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
			<strong>class</strong>
			<?php $class = $this->get_value_for_generator( 'class', $options ); ?>
			<input type="text" name="class" value="<?php echo esc_attr( $class ); ?>" />
		</p>
		<p>
			<strong>min</strong>
			<?php $min = $this->get_value_for_generator( 'min', $options ); ?>
			<input type="text" name="min" value="<?php echo esc_attr( $min ); ?>" />
		</p>
		<p>
			<strong>max</strong>
			<?php $max = $this->get_value_for_generator( 'max', $options ); ?>
			<input type="text" name="max" value="<?php echo esc_attr( $max ); ?>" />
		</p>
		<p>
			<strong>step</strong>
			<?php $step = $this->get_value_for_generator( 'step', $options ); ?>
			<input type="text" name="step" value="<?php echo esc_attr( $step ); ?>" />
		</p>
		<p>
			<strong><?php esc_html_e( 'Dsiplay error', 'mw-wp-form' ); ?></strong>
			<?php $show_error = $this->get_value_for_generator( 'show_error', $options ); ?>
			<label><input type="checkbox" name="show_error" value="false" <?php checked( 'false', $show_error ); ?> /> <?php esc_html_e( 'Don\'t display error.', 'mw-wp-form' ); ?></label>
		</p>
		<?php
	}
}
