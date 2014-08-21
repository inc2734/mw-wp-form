<?php
/**
 * Name: MW Validation Rule MinLength
 * Description: 値の文字数が範囲内
 * Version: 1.0.2
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : July 21, 2014
 * Modified: August 18, 2014
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_Validation_Rule_MinLength extends MW_Validation_Rule {

	/**
	 * バリデーションルール名を指定
	 */
	protected $name = 'minlength';

	/**
	 * rule
	 * @param string $key name属性
	 * @param array $option
	 * @return string エラーメッセージ
	 */
	public function rule( $key, array $options = array() ) {
		$value = $this->Data->get( $key );
		if ( !is_null( $value ) && !MWF_Functions::is_empty( $value ) ) {
			$defaults = array(
				'min' => 0,
				'message' => __( 'The number of characters is a few.', MWF_Config::DOMAIN )
			);
			$options = array_merge( $defaults, $options );
			$length = mb_strlen( $value, get_bloginfo( 'charset' ) );
			if ( MWF_Functions::is_numeric( $options['min'] ) && $options['min'] > $length ) {
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
		$min = '';
		if ( is_array( $value[$this->getName()] ) && isset( $value[$this->getName()]['min'] ) ) {
			$min = $value[$this->getName()]['min'];
		}
		?>
		<table>
			<tr>
				<td><?php esc_html_e( 'The number of the minimum characters', MWF_Config::DOMAIN ); ?></td>
				<td><input type="text" value="<?php echo esc_attr( $min ); ?>" size="3" name="<?php echo MWF_Config::NAME; ?>[validation][<?php echo $key; ?>][<?php echo esc_attr( $this->getName() ); ?>][min]" /></td>
			</tr>
		</table>
		<?php
	}
}