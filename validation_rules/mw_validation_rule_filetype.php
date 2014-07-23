<?php
/**
 * Name: MW Validation Rule FileType
 * URI: http://2inc.org
 * Description: ファイル名が指定した拡張子を含む。types は , 区切り
 * Version: 1.0.0
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : July 21, 2014
 * Modified:
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_Validation_Rule_FileType extends mw_validation_rule {

	/**
	 * バリデーションルール名を指定
	 */
	protected $name = 'filetype';

	/**
	 * rule
	 * @param mw_wp_form_data $Data
	 * @param string $key name属性
	 * @param array $option
	 * @return string エラーメッセージ
	 */
	public function rule( mw_wp_form_data $Data, $key, $options = array() ) {
		$value = $Data->get( $key );
		if ( !is_null( $value ) && !$this->isEmpty( $value ) ) {
			$defaults = array(
				'types' => '',
				'message' => __( 'This file is invalid.', MWF_Config::DOMAIN )
			);
			$options = array_merge( $defaults, $options );
			$_types = explode( ',', $options['types'] );
			foreach ( $_types as $type ) {
				$types[] = preg_quote( trim( $type ) );
			}
			$types = implode( '|', $this->array_clean( $types ) );
			$pattern = '/\.(' . $types . ')$/';
			if ( !preg_match( $pattern, $value ) ) {
				return $options['message'];
			}
		}
	}

	/**
	 * admin
	 * @param numeric $key バリデーションルールセットの識別番号
	 * @param array $value バリデーションルールセットの内容
	 */
	public function admin( $key, $value ) {
		?>
		<table>
			<tr>
				<td><?php esc_html_e( 'Permitted Extension', MWF_Config::DOMAIN ); ?></td>
				<td><input type="text" value="<?php echo esc_attr( @$value[$this->name]['types'] ); ?>" name="<?php echo MWF_Config::NAME; ?>[validation][<?php echo $key; ?>][<?php echo esc_attr( $this->name ); ?>][types]" /> <span class="mwf_note"><?php esc_html_e( 'Example:jpg or jpg,txt,…', MWF_Config::DOMAIN ); ?></span></td>
			</tr>
		</table>
		<?php
	}
}