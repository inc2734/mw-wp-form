<?php
/**
 * Name       : MW WP Form Validation Rule Tel
 * Description: 値が電話番号
 * Version    : 1.1.2
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : July 21, 2014
 * Modified   : April 1, 2015
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Validation_Rule_Tel extends MW_WP_Form_Abstract_Validation_Rule {

	/**
	 * バリデーションルール名を指定
	 * @var string
	 */
	protected $name = 'tel';

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
				'message' => __( 'This is not the format of a tel number.', 'mw-wp-form' )
			);
			$options = array_merge( $defaults, $options );
			if ( ! (
				preg_match( '/^\d{2}-\d{4}-\d{4}$/', $value ) ||
				preg_match( '/^\d{3}-\d{3,4}-\d{4}$/', $value ) ||
				preg_match( '/^\d{4}-\d{2}-\d{4}$/', $value ) ||
				preg_match( '/^\d{4}-\d{3}-\d{3}$/', $value ) ||
				preg_match( '/^\d{5}-\d{1}-\d{4}$/', $value ) ||
				preg_match( '/^\d{10,11}$/', $value )
			) ) {
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
		?>
		<label><input type="checkbox" <?php checked( $value[$this->getName()], 1 ); ?> name="<?php echo MWF_Config::NAME; ?>[validation][<?php echo $key; ?>][<?php echo esc_attr( $this->getName() ); ?>]" value="1" /><?php esc_html_e( 'Tel', 'mw-wp-form' ); ?></label>
		<?php
	}
}