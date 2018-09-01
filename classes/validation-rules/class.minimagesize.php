<?php
/**
 * Name       : MW WP Form Validation Rule MinImageSize
 * Version    : 2.0.0
 * Author     : Takashi Kitajima
 * Author URI : https://2inc.org
 * Created    : April 4, 2016
 * Modified   : May 30, 2017
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Validation_Rule_MinImageSize extends MW_WP_Form_Abstract_Validation_Rule {

	/**
	 * Validation rule name
	 * @var string
	 */
	protected $name = 'minfilesize';

	/**
	 * Validation process
	 *
	 * @param string $name
	 * @param array $option
	 * @return string Error message
	 */
	public function rule( $name, array $options = array() ) {
		$value = $this->Data->get( $name );

		if ( ! $value ) {
			return;
		}

		if ( ! MWF_Functions::is_numeric( $options['width'] ) || ! MWF_Functions::is_numeric( $options['width'] ) ) {
			return;
		}

		$upload_file_keys = $this->Data->get_post_value_by_key( MWF_Config::UPLOAD_FILE_KEYS );
		$upload_files     = $this->Data->get_post_value_by_key( MWF_Config::UPLOAD_FILES );
		$is_error = false;

		if ( ! is_array( $upload_file_keys ) ) {
			$upload_file_keys = array();
		}

		if ( ! is_array( $upload_files ) ) {
			$upload_files = array();
		}

		// Check after upload
		if ( ! in_array( $name, $upload_file_keys ) && array_key_exists( $name, $upload_files ) ) {
			$file_path = $upload_files[ $name ]['tmp_name'];
		}
		// Check if uploaded
		else {
			$file_path = MWF_Functions::fileurl_to_path( $value );
		}

		if ( file_exists( $file_path ) && exif_imagetype( $file_path ) ) {
			$imagesize = getimagesize( $file_path );
		} else {
			if ( ! in_array( $name, $upload_file_keys ) ) {
				$is_error = true;
			}
		}

		$defaults = array(
			'width'   => 1,
			'height'  => 1,
			'message' => __( 'This image size is too small.', 'mw-wp-form' )
		);
		$options = array_merge( $defaults, $options );
		if ( $is_error || $imagesize[0] < $options['width'] || $imagesize[1] < $options['height'] ) {
			return $options['message'];
		}
	}

	/**
	 * Add setting field to validation rule setting panel
	 *
	 * @param numeric $key ID of validation rule
	 * @param array $value Content of validation rule
	 * @return void
	 */
	public function admin( $key, $value ) {
		$width  = '';
		$height = '';
		if ( is_array( $value[ $this->getName() ] ) ) {
			if ( isset( $value[ $this->getName() ]['width'] ) ) {
				$width = $value[ $this->getName() ]['width'];
			}
			if ( isset( $value[ $this->getName() ]['height'] ) ) {
				$height = $value[ $this->getName() ]['height'];
			}
		}
		?>
		<table>
			<tr>
				<td><?php esc_html_e( 'Minimum image size', 'mw-wp-form' ); ?></td>
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
