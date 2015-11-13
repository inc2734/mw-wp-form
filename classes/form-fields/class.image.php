<?php
/**
 * Name       : MW WP Form Field Image
 * Description: 画像アップロードフィールドを出力
 * Version    : 1.6.0
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : May 17, 2013
 * Modified   : November 14, 2015
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Field_Image extends MW_WP_Form_Abstract_Form_Field {

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
			'shortcode_name' => 'mwform_image',
			'display_name'   => __( 'Image', 'mw-wp-form' ),
		);
	}

	/**
	 * set_defaults
	 * $this->defaultsを設定し返す
	 * @return array defaults
	 */
	protected function set_defaults() {
		return array(
			'name'  => '',
			'id'    => null,
			'class' => null,
			'show_error' => 'true',
		);
	}

	/**
	 * input_page
	 * 入力ページでのフォーム項目を返す
	 * @return string HTML
	 */
	protected function input_page() {
		$_ret = $this->Form->file( $this->atts['name'], array(
			'id'    => $this->atts['id'],
			'class' => $this->atts['class'],
		) );
		$value = $this->Data->get_raw( $this->atts['name'] );

		$upload_file_keys = $this->Data->get_post_value_by_key( MWF_Config::UPLOAD_FILE_KEYS );
		if ( !empty( $value ) && is_array( $upload_file_keys ) && in_array( $this->atts['name'], $upload_file_keys ) ) {
			$filepath = MWF_Functions::fileurl_to_path( $value );
			if ( file_exists( $filepath ) ) {
				$_ret .= sprintf(
					'<div class="%s_image">
						<img src="%s" alt="" />
						%s
					</div>',
					esc_attr( MWF_Config::NAME ),
					esc_attr( $value ),
					$this->Form->hidden( $this->atts['name'], $value )
				);
			}
		}
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
		if ( $value ) {
			$filepath = MWF_Functions::fileurl_to_path( $value );
			if ( file_exists( $filepath ) ) {
				return sprintf(
					'<div class="%s_image">
						<img src="%s" alt="" />
						%s
					</div>',
					esc_attr( MWF_Config::NAME ),
					esc_attr( $value ),
					$this->Form->hidden( $this->atts['name'], $value )
				);
			}
		}
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
			<strong><?php esc_html_e( 'Dsiplay error', 'mw-wp-form' ); ?></strong>
			<?php $show_error = $this->get_value_for_generator( 'show_error', $options ); ?>
			<label><input type="checkbox" name="show_error" value="false" <?php checked( 'false', $show_error ); ?> /> <?php esc_html_e( 'Don\'t display error.', 'mw-wp-form' ); ?></label>
		</p>
		<?php
	}
}
