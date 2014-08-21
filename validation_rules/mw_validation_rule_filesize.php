<?php
/**
 * Name: MW Validation Rule FileType
 * Description: ファイル名が指定した拡張子を含む。types は , 区切り
 * Version: 1.0.2
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : July 21, 2014
 * Modified: August 18, 2014
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_Validation_Rule_FileSize extends MW_Validation_Rule {

	/**
	 * バリデーションルール名を指定
	 */
	protected $name = 'filesize';

	/**
	 * rule
	 * @param string $key name属性
	 * @param array $option
	 * @return string エラーメッセージ
	 */
	public function rule( $key, array $options = array() ) {
		$data = $this->Data->getValue( MWF_Config::UPLOAD_FILES );
		if ( !is_null( $data ) && is_array( $data ) && array_key_exists( $key, $data ) ) {
			$file = $data[$key];
			if ( !empty( $file['size'] ) ) {
				$defaults = array(
					'bytes' => '0',
					'message' => __( 'This file size is too big.', MWF_Config::DOMAIN )
				);
				$options = array_merge( $defaults, $options );
				if ( !( preg_match( '/^[\d]+$/', $options['bytes'] ) && $options['bytes'] > $file['size'] ) ) {
					return $options['message'];
				}
			}
		}
	}

	/**
	 * admin
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
				<td><?php esc_html_e( 'Permitted file size', MWF_Config::DOMAIN ); ?></td>
				<td><input type="text" value="<?php echo esc_attr( $bytes ); ?>" name="<?php echo MWF_Config::NAME; ?>[validation][<?php echo $key; ?>][<?php echo esc_attr( $this->getName() ); ?>][bytes]" /> <span class="mwf_note"><?php esc_html_e( 'bytes', MWF_Config::DOMAIN ); ?></span></td>
			</tr>
		</table>
		<?php
	}
}