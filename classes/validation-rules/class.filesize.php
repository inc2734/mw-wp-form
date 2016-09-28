<?php
/**
 * Name       : MW WP Form Validation Rule FileSize
 * Description: ファイルサイズが指定したサイズ以内
 * Version    : 2.0.0
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : July 21, 2014
 * Modified   : September 28, 2016
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Validation_Rule_FileSize extends MW_WP_Form_Abstract_Validation_Rule {

	/**
	 * バリデーションルール名を指定
	 * @var string
	 */
	protected $name = 'filesize';

	/**
	 * バリデーションチェック
	 *
	 * @param string $key name属性
	 * @param array $options
	 * @return string エラーメッセージ
	 */
	public function rule( $key, array $options = array() ) {
		$data = $this->Data->get_post_value_by_key( MWF_Config::UPLOAD_FILES );

		if ( ! is_null( $data )  ) {

			if ( is_array( $data ) && array_key_exists( $key, $data ) ) {
				$file = $data[$key];
				if ( ! empty( $file['size'] ) ) {
					return $this->filesize_validate( $file['size'], $options );
				} elseif ( ! empty( $file['error'] ) && $file['error'] == 1 ) {
					return __( 'Failed to upload the file.', 'mw-wp-form' );
				}
			}

		} else {

			$upload_file_keys = $this->Data->get_post_value_by_key( MWF_Config::UPLOAD_FILE_KEYS );
			$filepath = MWF_Functions::fileurl_to_path( $this->Data->get( $key ) );
			if ( is_array( $upload_file_keys ) && in_array( $key, $upload_file_keys ) && file_exists( $filepath ) ) {
				$error_message = $this->filesize_validate( filesize( $filepath ), $options );
				if ( $error_message ) {
					// バリデーションは送信ボタン押下時、ページ遷移の後画面表示時にも発火するため
					// 普通に削除すると画面表示時のチェックが発火せずエラーメッセージが表示されない
					// そのため、非 POST 時（= リダイレクト = 画面表示時）にのみ削除する
					if ( empty( $_POST ) ) {
						unlink( $filepath );
					}
					return $error_message;
				}
			}

		}
	}

	/**
	 * Validates the filesize
	 *
	 * @param int $byte filesize
	 * @param array $options
	 * @return string Error message
	 */
	protected function filesize_validate( $byte, $options ) {
		$defaults = array(
			'bytes'   => '0',
			'message' => __( 'This file size is too big.', 'mw-wp-form' )
		);
		$options = array_merge( $defaults, $options );
		if ( ! ( preg_match( '/^[\d]+$/', $options['bytes'] ) && $options['bytes'] >= $byte ) ) {
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
		$bytes = '';
		if ( is_array( $value[$this->getName()] ) && isset( $value[$this->getName()]['bytes'] ) ) {
			$bytes = $value[$this->getName()]['bytes'];
		}
		?>
		<table>
			<tr>
				<td><?php esc_html_e( 'Permitted file size', 'mw-wp-form' ); ?></td>
				<td><input type="text" value="<?php echo esc_attr( $bytes ); ?>" name="<?php echo MWF_Config::NAME; ?>[validation][<?php echo $key; ?>][<?php echo esc_attr( $this->getName() ); ?>][bytes]" /> <span class="mwf_note"><?php esc_html_e( 'bytes', 'mw-wp-form' ); ?></span></td>
			</tr>
		</table>
		<?php
	}
}
