<?php
/**
 * @package mw-wp-form
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * MW_WP_Form_Validation_Rule_FileSize
 */
class MW_WP_Form_Validation_Rule_FileSize extends MW_WP_Form_Abstract_Validation_Rule {

	/**
	 * Validation rule name.
	 *
	 * @var string
	 */
	protected $name = 'filesize';

	/**
	 * Validation process.
	 *
	 * @param string $name    Validation name.
	 * @param array  $options Validation options.
	 * @return string
	 */
	public function rule( $name, array $options = array() ) {
		$data = $this->Data->get_post_value_by_key( MWF_Config::UPLOAD_FILES );

		if ( ! is_null( $data ) ) {
			if ( is_array( $data ) && array_key_exists( $name, $data ) ) {
				$file = $data[ $name ];
				if ( ! empty( $file['size'] ) ) {
					return $this->_filesize_validate( $file['size'], $options );
				} elseif ( ! empty( $file['error'] ) && 1 === $file['error'] ) {
					return __( 'Failed to upload the file.', 'mw-wp-form' );
				}
			}
		} else {
			$upload_file_keys = $this->Data->get_post_value_by_key( MWF_Config::UPLOAD_FILE_KEYS );
			$filepath         = MWF_Functions::fileurl_to_path( $this->Data->get( $name ) );
			if (
				is_array( $upload_file_keys )
				&& in_array( $name, $upload_file_keys, true )
				&& file_exists( $filepath )
			) {
				$error_message = $this->_filesize_validate( filesize( $filepath ), $options );
				if ( $error_message ) {
					// バリデーションは送信ボタン押下時に発火するため
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
	 * Validates the filesize.
	 *
	 * @param int   $byte    Filesize.
	 * @param array $options Validate options.
	 * @return string
	 */
	protected function _filesize_validate( $byte, $options ) {
		$defaults = array(
			'bytes'   => '0',
			'message' => __( 'This file size is too big.', 'mw-wp-form' ),
		);
		$options  = array_merge( $defaults, $options );
		if ( ! ( preg_match( '/^[\d]+$/', $options['bytes'] ) && $options['bytes'] >= $byte ) ) {
			return $options['message'];
		}
	}

	/**
	 * Add setting field to validation rule setting panel.
	 *
	 * @param numeric $key ID of validation rule.
	 * @param array   $value Content of validation rule.
	 * @return void
	 */
	public function admin( $key, $value ) {
		$bytes = '';
		if ( is_array( $value[ $this->getName() ] ) && isset( $value[ $this->getName() ]['bytes'] ) ) {
			$bytes = $value[ $this->getName() ]['bytes'];
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
