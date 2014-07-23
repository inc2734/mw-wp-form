<?php
/**
 * Name: MW Validation Rule Date
 * URI: http://2inc.org
 * Description: 日付が正しいかどうか
 * Version: 1.0.0
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : July 21, 2014
 * Modified:
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_Validation_Rule_Date extends mw_validation_rule {

	/**
	 * バリデーションルール名を指定
	 */
	protected $name = 'date';

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
				'message' => __( 'This is not the format of a date.', MWF_Config::DOMAIN )
			);
			$options = array_merge( $defaults, $options );
			$timestamp = strtotime( $value );
			$year = date( 'Y', $timestamp );
			$month = date( 'm', $timestamp );
			$day = date( 'd', $timestamp );
			$checkdate = checkdate( $month, $day, $year );
			if ( !$timestamp || !$checkdate || preg_match( '/^[a-zA-Z]$/', $value ) || preg_match( '/^\s+$/', $value ) ) {
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
		<label><input type="checkbox" <?php checked( $value[$this->name], 1 ); ?> name="<?php echo MWF_Config::NAME; ?>[validation][<?php echo $key; ?>][<?php echo esc_attr( $this->name ); ?>]" value="1" /><?php esc_html_e( 'Date', MWF_Config::DOMAIN ); ?></label>
		<?php
	}
}