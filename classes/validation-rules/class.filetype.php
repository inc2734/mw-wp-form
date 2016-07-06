<?php
/**
 * Name       : MW WP Form Validation Rule FileType
 * Description: ファイル名が指定した拡張子を含む。types は , 区切り
 * Version    : 1.1.1
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : July 21, 2014
 * Modified   : April 1, 2015
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Validation_Rule_FileType extends MW_WP_Form_Abstract_Validation_Rule {

	/**
	 * バリデーションルール名を指定
	 * @var string
	 */
	protected $name = 'filetype';

	/**
	 * バリデーションチェック
	 *
	 * @param string $key name属性
	 * @param array $option
	 * @return string エラーメッセージ
	 */
	public function rule( $key, array $options = array() ) {
		$value = $this->Data->get( $key );
		if ( !MWF_Functions::is_empty( $value ) ) {
			$defaults = array(
				'types'   => '',
				'message' => __( 'This file is invalid.', 'mw-wp-form' )
			);
			$options = array_merge( $defaults, $options );
			$_types = explode( ',', $options['types'] );
			foreach ( $_types as $type ) {
				$types[] = preg_quote( trim( $type ), '/' );
			}
			$types = implode( '|', MWF_Functions::array_clean( $types ) );
			$pattern = '/\.(' . $types . ')$/i';
			if ( !preg_match( $pattern, $value ) ) {
				return $options['message'];
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
		$types = '';
		if ( is_array( $value[$this->getName()] ) && isset( $value[$this->getName()]['types'] ) ) {
			$types = $value[$this->getName()]['types'];
		}
		?>
		<table>
			<tr>
				<td><?php esc_html_e( 'Permitted Extension', 'mw-wp-form' ); ?></td>
				<td><input type="text" value="<?php echo esc_attr( $types ); ?>" name="<?php echo MWF_Config::NAME; ?>[validation][<?php echo $key; ?>][<?php echo esc_attr( $this->getName() ); ?>][types]" /> <span class="mwf_note"><?php esc_html_e( 'Example:jpg or jpg,txt,…', 'mw-wp-form' ); ?></span></td>
			</tr>
		</table>
		<?php
	}
}
