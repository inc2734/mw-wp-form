<?php
/**
 * Name       : MW WP Form Validation Rule MaxImageSize
 * Description: 画像サイズが指定したサイズ以内
 * Version    : 1.0.0
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : April 4, 2016
 * Modified   :
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Validation_Rule_MaxImageSize extends MW_WP_Form_Abstract_Validation_Rule {

	/**
	 * バリデーションルール名を指定
	 * @var string
	 */
	protected $name = 'maxfilesize';

	/**
	 * バリデーションチェック
	 *
	 * @param string $key name属性
	 * @param array $option
	 * @return string エラーメッセージ
	 */
	public function rule( $key, array $options = array() ) {
		$value = $this->Data->get( $key );
		if ( !$value ) {
			return;
		}

		if ( !MWF_Functions::is_numeric( $options['width'] ) || !MWF_Functions::is_numeric( $options['width'] ) ) {
			return;
		}

		/**
		 * 送信ボタンを押して次のページが表示されるまでの間、
		 *   1.そのページに post されてチェック
		 *   2.リダイレクト先でチェック
		 * の2度チェックされる。画像サイズのチェックは画像が存在しないとできないが、1.でエラーだった場合
		 * ファイルがアップロードされないので 2.でスルーされ画面表示時にはエラーも出ない。
		 */
		$upload_file_keys = $this->Data->get_post_value_by_key( MWF_Config::UPLOAD_FILE_KEYS );
		$upload_files     = $this->Data->get_post_value_by_key( MWF_Config::UPLOAD_FILES );
		$is_error = false;

		if ( !is_array( $upload_file_keys ) ) {
			$upload_file_keys = array();
		}

		if ( !is_array( $upload_files ) ) {
			$upload_files = array();
		}

		// アップロード直後のチェック
		if ( !in_array( $key, $upload_file_keys ) && array_key_exists( $key, $upload_files ) ) {
			$file_path = $upload_files[$key]['tmp_name'];
		}
		// アップロード済みの場合のチェック
		else {
			$file_path = MWF_Functions::fileurl_to_path( $value );
		}

		if ( file_exists( $file_path ) && exif_imagetype( $file_path ) ) {
			$imagesize = getimagesize( $file_path );
		} else {
			if ( !in_array( $key, $upload_file_keys ) ) {
				$is_error = true;
			}
		}

		$defaults = array(
			'width'   => 1,
			'height'  => 1,
			'message' => __( 'This image size is too big.', 'mw-wp-form' )
		);
		$options = array_merge( $defaults, $options );
		if ( $is_error || $imagesize[0] > $options['width'] || $imagesize[1] > $options['height'] ) {
			return $options['message'];
		}
	}

	/**
	 * 設定パネルに追加
	 *
	 * @param numeric $key バリデーションルールセットの識別番号
	 * @param array $value バリデーションルールセットの内容
	 */
	public function admin( $key, $value ) {
		$width  = '';
		$height = '';
		if ( is_array( $value[$this->getName()] ) ) {
			if ( isset( $value[$this->getName()]['width'] ) ) {
				$width = $value[$this->getName()]['width'];
			}
			if ( isset( $value[$this->getName()]['height'] ) ) {
				$height = $value[$this->getName()]['height'];
			}
		}
		?>
		<table>
			<tr>
				<td><?php esc_html_e( 'Maximum image size', 'mw-wp-form' ); ?></td>
				<td>
					<input type="text" value="<?php echo esc_attr( $width ); ?>" size="4" name="<?php echo MWF_Config::NAME; ?>[validation][<?php echo $key; ?>][<?php echo esc_attr( $this->getName() ); ?>][width]" />
					&times;
					<input type="text" value="<?php echo esc_attr( $height ); ?>" size="4" name="<?php echo MWF_Config::NAME; ?>[validation][<?php echo $key; ?>][<?php echo esc_attr( $this->getName() ); ?>][height]" />
				</td>
			</tr>
		</table>
		<?php
	}
}
