<?php
/**
 * @package mw-wp-form
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * MW_WP_Form_Redirected
 */
class MW_WP_Form_Redirected {

	/**
	 * @var string
	 */
	protected $form_key;

	/**
	 * @var MW_WP_Form_Setting
	 */
	protected $Setting;

	/**
	 * @var string input|confirm|complete
	 */
	protected $view_flg = 'input';

	/**
	 * @var string
	 */
	protected $url;

	/**
	 * Constructor.
	 *
	 * @param string             $form_key Form key.
	 * @param MW_WP_Form_Setting $setting  MW_WP_Form_Setting object.
	 * @param boolean            $is_valid Return true when valid.
	 * @param string             $post_condition back|confirm|complete.
	 */
	public function __construct( $form_key, $setting, $is_valid, $post_condition ) {
		$this->form_key = $form_key;
		$this->Setting  = $setting;

		$input    = $this->_parse_url( $this->Setting->get( 'input_url' ) );
		$confirm  = $this->_parse_url( $this->Setting->get( 'confirmation_url' ) );
		$complete = $this->_parse_url( $this->Setting->get( 'complete_url' ) );
		$error    = $this->_parse_url( $this->Setting->get( 'validation_error_url' ) );

		if ( 'back' === $post_condition ) {
			if ( $is_valid ) {
				$this->url = $input;
			} else {
				if ( $error ) {
					$this->url = $error;
				} else {
					$this->url = $input;
				}
			}
			return;
		}

		if ( 'confirm' === $post_condition ) {
			if ( $is_valid ) {
				$this->view_flg = 'confirm';
				$this->url      = $confirm;
			} else {
				if ( $error ) {
					$this->url = $error;
				} else {
					$this->url = $input;
				}
			}
			return;
		}

		if ( 'complete' === $post_condition ) {
			if ( $is_valid ) {
				$this->view_flg = 'complete';
				$this->url      = $complete;
			} else {
				if ( $error ) {
					$this->url = $error;
				} else {
					$this->url = $input;
				}
			}
			return;
		}

		$this->url = ( $input ) ? $input : $this->get_request_uri();
	}

	/**
	 * Return URL of redirect destination
	 *
	 * @return string
	 */
	public function get_url() {
		$Data = MW_WP_Form_Data::connect( $this->form_key );
		return apply_filters( 'mwform_redirect_url_' . $this->form_key, $this->url, $Data );
	}

	/**
	 * Returns a flg indicating the screen to be displayed
	 *
	 * @return string $this->view_flg
	 */
	public function get_view_flg() {
		return $this->view_flg;
	}

	/**
	 * Return $_SERVER['REQUEST_URI'] that converted https?://...
	 *
	 * @return string
	 */
	public function get_request_uri() {
		$request_uri = $_SERVER['REQUEST_URI'];

		if ( ! $request_uri ) {
			return;
		}

		if ( preg_match( '/^https?:\/\//', $request_uri ) ) {
			return $request_uri;
		}

		$parse_url = parse_url( home_url() );

		// For WP installed in subdirectory
		if ( ! empty( $parse_url['path'] ) ) {
			$pettern = preg_quote( $parse_url['path'], '/' );
			return preg_replace( '/' . $pettern . '$/', $request_uri, home_url() );
		}

		return trailingslashit( home_url() ) . ltrim( $request_uri, '/' );
		return $request_uri;
	}

	/**
	 * Convert URL to https?://...
	 *
	 * @param string $url URL.
	 * @return string
	 */
	protected function _parse_url( $url ) {
		if ( empty( $url ) ) {
			return '';
		}

		$query_string = array();
		preg_match( '/\?(.*)$/', $url, $reg );
		if ( ! empty( $reg[1] ) ) {
			$url = str_replace( '?' . $reg[1], '', $url );
			parse_str( $reg[1], $query_string );
		}
		if ( ! preg_match( '/^https?:\/\//', $url ) ) {
			$home_url = home_url();
			$url      = $home_url . $url;
		}
		$url = preg_replace( '/([^:])\/+/', '$1/', $url );

		// URL設定でURL引数が使用されている場合はそれを使う。
		// 「URL引数を有効にする」が有効の場合は $_GET を利用する（重複するURL引数はURL設定のものが優先される ※post_id除く）
		if ( $this->Setting->get( 'querystring' ) ) {
			$query_string = array_merge( $_GET, $query_string );
			if ( isset( $_GET['post_id'] ) && MWF_Functions::is_numeric( $_GET['post_id'] ) ) {
				$query_string['post_id'] = $_GET['post_id'];
			}
		}

		if ( ! empty( $query_string ) ) {
			return $url . '?' . http_build_query( $query_string, null, '&', PHP_QUERY_RFC3986 );
		}

		return $url;
	}

	/**
	 * Redirect.
	 * Don't redirect if the current URL and the redirect URL are the same.
	 */
	public function redirect() {
		$redirect = $this->_get_real_redirect_url();
		if ( ! $redirect ) {
			return;
		}

		do_action( 'mwform_before_redirect_' . $this->form_key );

		wp_safe_redirect( $redirect, 302 );
		exit();
	}

	/**
	 * Redirect using JavaScript.
	 * Don't redirect if the current URL and the redirect URL are the same.
	 */
	public function redirect_js() {
		$redirect = $this->_get_real_redirect_url();
		if ( ! $redirect ) {
			return;
		}

		do_action( 'mwform_before_redirect_' . $this->form_key );
		?>
		<script type="text/javascript">
		window.location = "<?php echo esc_js( $redirect ); ?>";
		</script>
		<?php
	}

	/**
	 * Return redirect url that sanitize and validate.
	 *
	 * @return string
	 */
	protected function _get_real_redirect_url() {
		$redirect    = ( $this->get_url() ) ? $this->get_url() : $this->get_request_uri();
		$request_uri = $this->get_request_uri();

		if ( empty( $_POST ) && $redirect === $request_uri ) {
			return;
		}

		$redirect = wp_sanitize_redirect( $redirect );
		$redirect = wp_validate_redirect( $redirect, home_url() );

		return $redirect;
	}
}
