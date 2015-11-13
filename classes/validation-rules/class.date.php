<?php
/**
 * Name       : MW WP Form Validation Rule Date
 * Description: 日付が正しいかどうか
 * Version    : 1.1.2
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : July 21, 2014
 * Modified   : October 16, 2015
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Validation_Rule_Date extends MW_WP_Form_Abstract_Validation_Rule {

	/**
	 * バリデーションルール名を指定
	 * @var string
	 */
	protected $name = 'date';

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
				'message' => __( 'This is not the format of a date.', 'mw-wp-form' )
			);
			$options = array_merge( $defaults, $options );
			$timestamp = strtotime( $value );
			if ( !$timestamp ) {
				$timestamp = $this->convert_jpdate_to_timestamp( $value );
			}
			if ( !$timestamp ) {
				return $options['message'];
			}
			$year  = date( 'Y', $timestamp );
			$month = date( 'm', $timestamp );
			$day   = date( 'd', $timestamp );
			$checkdate = checkdate( $month, $day, $year );
			if ( !$timestamp || !$checkdate || preg_match( '/^[a-zA-Z]$/', $value ) || preg_match( '/^\s+$/', $value ) ) {
				return $options['message'];
			}
		}
	}
	
	/**
	 * 日本語表記の日付をタイムスタンプに変換する
	 *
	 * @param string $jpdate yyyy年mm月dd日
	 * @return string|false
	 */
	public function convert_jpdate_to_timestamp( $jpdate ) {
		if ( preg_match( '/^(\d+)年(\d{1,2})月(\d{1,2})日$/', $jpdate, $reg ) ) {
			$date = sprintf( '%d-%d-%d', $reg[1], $reg[2], $reg[3] );
			return strtotime( $date );
		}
		return false;
	}

	/**
	 * 設定パネルに追加
	 *
	 * @param numeric $key バリデーションルールセットの識別番号
	 * @param array $value バリデーションルールセットの内容
	 */
	public function admin( $key, $value ) {
		?>
		<label><input type="checkbox" <?php checked( $value[$this->getName()], 1 ); ?> name="<?php echo MWF_Config::NAME; ?>[validation][<?php echo $key; ?>][<?php echo esc_attr( $this->getName() ); ?>]" value="1" /><?php esc_html_e( 'Date', 'mw-wp-form' ); ?></label>
		<?php
	}
}
