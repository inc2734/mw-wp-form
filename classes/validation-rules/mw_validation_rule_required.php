<?php
/**
 * Name: MW Validation Rule Required
 * Description: 値が存在する
 * Version: 1.0.1
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : July 21, 2014
 * Modified: August 8, 2014
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_Validation_Rule_Required extends MW_Validation_Rule {

	/**
	 * バリデーションルール名を指定
	 */
	protected $name = 'required';

	/**
	 * rule
	 * @param string $key name属性
	 * @param array $option
	 * @return string エラーメッセージ
	 */
	public function rule( $key, array $options = array() ) {
		$value = $this->Data->get( $key );
		// 値が存在するとき、もしくは存在しないけど他のデータもない（=フォーム送信自体されていない）ときはエラーではない
		if ( !is_null( $value ) || is_null( $value ) && !$this->Data->getValues() ) {
			return;
		}
		$defaults = array(
			'message' => __( 'This is required.', MWF_Config::DOMAIN )
		);
		$options = array_merge( $defaults, $options );
		return $options['message'];
	}

	/**
	 * admin
	 * @param numeric $key バリデーションルールセットの識別番号
	 * @param array $value バリデーションルールセットの内容
	 */
	public function admin( $key, $value ) {
		?>
		<label><input type="checkbox" <?php checked( $value[$this->getName()], 1 ); ?> name="<?php echo MWF_Config::NAME; ?>[validation][<?php echo $key; ?>][<?php echo esc_attr( $this->getName() ); ?>]" value="1" /><?php esc_html_e( 'No empty( with checkbox )', MWF_Config::DOMAIN ); ?></label>
		<?php
	}
}