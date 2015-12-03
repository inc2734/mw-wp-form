<?php
/**
 * Name       : MW WP Form Validation Rule Eq
 * Description: 値が一致している
 * Version    : 1.1.2
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : July 21, 2014
 * Modified   : December 3, 2015
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Validation_Rule_Eq extends MW_WP_Form_Abstract_Validation_Rule {

	/**
	 * バリデーションルール名を指定
	 * @var string
	 */
	protected $name = 'eq';

	/**
	 * バリデーションチェック
	 *
	 * @param string $key name属性
	 * @param array $option
	 * @return string エラーメッセージ
	 */
	public function rule( $key, array $options = array() ) {
		$value = $this->Data->get( $key );
		if ( !is_null( $value ) ) {
			$defaults = array(
				'target'  => null,
				'message' => __( 'This is not in agreement.', 'mw-wp-form' )
			);
			$options = array_merge( $defaults, $options );
			$target_value = $this->Data->get( $options['target'] );
			if ( ( string ) $value !== ( string ) $target_value ) {
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
		$target = '';
		if ( is_array( $value[$this->getName()] ) && isset( $value[$this->getName()]['target'] ) ) {
			$target = $value[$this->getName()]['target'];
		}
		?>
		<table>
			<tr>
				<td><?php esc_html_e( 'The key at same value', 'mw-wp-form' ); ?></td>
				<td><input type="text" value="<?php echo esc_attr( $target ); ?>" name="<?php echo MWF_Config::NAME; ?>[validation][<?php echo $key; ?>][<?php echo esc_attr( $this->getName() ); ?>][target]" /></td>
			</tr>
		</table>
		<?php
	}
}
