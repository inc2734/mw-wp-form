<?php
/**
 * Name: MW Validation Rule Between
 * Description: 値の文字数が範囲内
 * Version: 1.0.0
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : July 21, 2014
 * Modified:
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_Validation_Rule_Between extends MW_Validation_Rule {

	/**
	 * バリデーションルール名を指定
	 */
	protected static $name = 'between';

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
				'max' => 0,
				'message' => __( 'The number of characters is invalid.', MWF_Config::DOMAIN )
			);
			$options = array_merge( $defaults, $options );
			$length = mb_strlen( $value, get_bloginfo( 'charset' ) );
			if ( MWF_Functions::is_numeric( $options['min'] ) ) {
				if ( MWF_Functions::is_numeric( $options['max'] ) ) {
					if ( !( $options['min'] <= $length && $length <= $options['max'] ) ) {
						return $options['message'];
					}
				} else {
					if ( $options['min'] > $length ) {
						return $options['message'];
					}
				}
			} elseif ( MWF_Functions::is_numeric( $options['max'] ) ) {
				if ( $options['max'] < $length ) {
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
	public static function admin( $key, $value ) {
		?>
		<table>
			<tr>
				<td><?php esc_html_e( 'The range of the number of characters', MWF_Config::DOMAIN ); ?></td>
				<td>
					<input type="text" value="<?php echo esc_attr( @$value[self::getName()]['min'] ); ?>" size="3" name="<?php echo MWF_Config::NAME; ?>[validation][<?php echo $key; ?>][<?php echo esc_attr( self::getName() ); ?>][min]" />
					〜
					<input type="text" value="<?php echo esc_attr( @$value[self::getName()]['max'] ); ?>" size="3" name="<?php echo MWF_Config::NAME; ?>[validation][<?php echo $key; ?>][<?php echo esc_attr( self::getName() ); ?>][max]" />
				</td>
			</tr>
		</table>
		<?php
	}
}