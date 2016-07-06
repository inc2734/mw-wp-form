<?php
/**
 * Name       : MW WP Form Validation Rule FileSize
 * Description: ファイルサイズが指定したサイズ以内
 * Version    : 1.1.2
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : July 21, 2014
 * Modified   : July 6, 2016
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Validation_Rule_FileSize extends MW_WP_Form_Abstract_Validation_Rule {

	/**
	 * バリデーションルール名を指定
	 * @var string
	 */
	protected $name = 'filesize';

	/**
	 * バリデーションチェック
	 *
	 * @param string $key name属性
	 * @param array $option
	 * @return string エラーメッセージ
	 */
	public function rule( $key, array $options = array() ) {
		$data = $this->Data->get_post_value_by_key( MWF_Config::UPLOAD_FILES );
		if ( !is_null( $data ) && is_array( $data ) && array_key_exists( $key, $data ) ) {
			$file = $data[$key];
			if ( !empty( $file['size'] ) ) {
				$defaults = array(
					'bytes'   => '0',
					'message' => __( 'This file size is too big.', 'mw-wp-form' )
				);
				$options = array_merge( $defaults, $options );
				if ( !( preg_match( '/^[\d]+$/', $options['bytes'] ) && $options['bytes'] >= $file['size'] ) ) {
					return $options['message'];
				}
			} elseif ( !empty( $file['error'] ) && $file['error'] == 1 ) {
				return __( 'Failed to upload the file.', 'mw-wp-form' );
			}
		}
	}

	/**
	 * 設定パネルに追加
	 *
	 * @param numeric $key バリデーションルールセットの識別番号
	 * @param array $value バリデーションルールセットの内容
	 */
	public function admin( $key, $value ) {
		$bytes = '';
		if ( is_array( $value[$this->getName()] ) && isset( $value[$this->getName()]['bytes'] ) ) {
			$bytes = $value[$this->getName()]['bytes'];
		}
		?>
		<table>
			<tr>
				<td><?php esc_html_e( 'Permitted file size', 'mw-wp-form' ); ?></td>
				<td><input type="text" value="<?php echo esc_attr( $bytes ); ?>" name="<?php echo MWF_Config::NAME; ?>[validation][<?php echo $key; ?>][<?php echo esc_attr( $this->getName() ); ?>][bytes]" /> <span class="mwf_note"><?php esc_html_e( 'bytes', 'mw-wp-form' ); ?></span></td>
			</tr>
		</table>
		<?php
	}
}
