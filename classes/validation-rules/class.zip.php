<?php
/**
 * Name       : MW WP Form Validation Rule Zip
 * Description: 値が郵便番号
 * Version    : 1.1.1
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : July 21, 2014
 * Modified   : January 17, 2015
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Validation_Rule_Zip extends MW_WP_Form_Abstract_Validation_Rule {

	/**
	 * $name
	 * バリデーションルール名を指定
	 * @var string
	 */
	protected $name = 'zip';

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
				'message' => __( 'This is not the format of a zip code.', MWF_Config::DOMAIN )
			);
			$options = array_merge( $defaults, $options );
			if ( !preg_match( '/^\d{3}-\d{4}$/', $value ) ) {
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
		<label><input type="checkbox" <?php checked( $value[$this->getName()], 1 ); ?> name="<?php echo MWF_Config::NAME; ?>[validation][<?php echo $key; ?>][<?php echo esc_attr( $this->getName() ); ?>]" value="1" /><?php esc_html_e( 'Zip Code', MWF_Config::DOMAIN ); ?></label>
		<?php
	}
}