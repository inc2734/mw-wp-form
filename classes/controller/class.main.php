<?php
/**
 * Name: MW WP Form Main Controller
 * Version: 1.0.0
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : December 23, 2014
 * Modified:
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Main_Controller {

	/**
	 * __controller
	 */
	public function __construct() {
		$plugin_dir_path = plugin_dir_path( __FILE__ );
		include_once( $plugin_dir_path . '../service/class.main.php' );
	}

	/**
	 * initialize
	 */
	public function initialize() {
		$this->Main_Service = new MW_WP_Form_Main_Service();

		// フォームフィールドの読み込み、インスタンス化
		$this->Main_Service->instantiate_form_fields();

		// バリデーションルールの読み込み、インスタンス化
		$this->Main_Service->set_validation_rules( $this->Main_Service->get_key() );
		
		add_filter( 'nocache_headers' , array( $this->Main_Service, 'nocache_headers' ) , 1 );
		add_action( 'parse_request'   , array( $this, 'remove_query_vars_from_post' ) );
		add_filter( 'template_include', array( $this, 'template_include' ), 10000 );

		// スクロール用スクリプトのロード
		if ( $this->Main_Service->get_option( 'scroll' ) ) {
			$post_condition = $this->Main_Service->get_post_condition();
			if ( $post_condition === 'confirm' || $post_condition === 'complete' || !$this->Main_Service->is_valid() ) {
				add_action( 'wp_enqueue_scripts', array( $this, 'scroll_script' ) );
			}
		}

	}

	/**
	 * remove_query_vars_from_post
	 * WordPressへのリクエストに含まれている、$_POSTの値を削除
	 */
	public function remove_query_vars_from_post( $wp_query ) {
		if ( isset( $_POST['token'], $_SERVER['REQUEST_METHOD'] ) && 
			 strtolower( $_SERVER['REQUEST_METHOD'] ) === 'post' ) {
			$query_vars = $this->Main_Service->remove_query_vars_from_post( $wp_query->query_vars, $_POST );
			$wp_query->query_vars = $query_vars;
		}
	}

	/**
	 * template_include
	 * 表示画面でのプラグインの処理等
	 * @param string $template
	 * @return string $template
	 */
	public function template_include( $template ) {
		global $post;

		$this->Main_Service->initialize( $post, $template );

		// フォームが定義されていない場合は終了
		if ( !$this->Main_Service->is_initialized() ) {
			return $template;
		}

		nocache_headers();

		// セッション初期化
		// $_FILESがあるときは$this->dataに統合
		$this->Main_Service->init_request_data( $_POST, $_FILES );

		// フォームオブジェクト生成
		$Form = new MW_Form( $this->Main_Service->get_key() );
		$this->Main_Service->set_form_object( $Form );

		// フォームオブジェクト生成
		$Validation = new MW_Validation( $this->Main_Service->get_key() );
		$this->Main_Service->set_validation_object( $Validation );

		// バリデーション実行（Validation->dataに値がないと$Errorは返さない（true））
		$this->Main_Service->validate();

		// ファイル操作オブジェクト生成
		$File = new MW_WP_Form_File();
		$this->Main_Service->set_file_object( $File );

		// 画面を表示
		$this->display();

		// 画面表示用のショートコードを登録
		$this->Main_Service->add_shortocde_that_display_content();

		add_action( 'wp_footer'         , array( $this->Main_Service, 'clear_data' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );

		return $template;
	}

	/**
	 * display
	 * 適切にリダイレクトして画面を表示
	 */
	protected function display() {
		$input            = $this->Main_Service->get_input();
		$confirm          = $this->Main_Service->get_confirm();
		$complete         = $this->Main_Service->get_complete();
		$validation_error = $this->Main_Service->get_validation_error();
		$REQUEST_URI      = $this->Main_Service->get_request_uri();
		$post_condition   = $this->Main_Service->get_post_condition();

		// 入力画面（戻る）のとき
		if ( $post_condition === 'back' ) {
			$this->redirect( $input );
		}
		// 確認画面のとき
		elseif ( $post_condition === 'confirm' ) {
			$this->Main_Service->fileUpload();
			if ( $this->Main_Service->is_valid() ) {
				$this->Main_Service->set_view_flg( 'confirm' );
				$this->redirect( $confirm );
			} else {
				if ( !$this->Main_Service->get_validation_error() ) {
					$this->redirect( $validation_error );
				} else {
					$this->redirect( $input );
				}
			}
		}
		// 完了画面のとき
		elseif ( $post_condition === 'complete' ) {
			$this->Main_Service->fileUpload();
			if ( $this->Main_Service->is_valid() ) {
				$this->Main_Service->set_view_flg( 'complete' );
				if ( $this->Main_Service->get_token() ) {
					$this->Main_Service->send();
					$this->Main_Service->clear_token();

					// 手動フォーム対応
					if ( !$this->Main_Service->is_generated_by_formkey() &&
						 $REQUEST_URI !== $complete && $input !== $complete ) {
						$this->Main_Service->clear_data();
					}
				}
				$this->redirect( $complete );
			} else {
				if ( !empty( $validation_error ) ) {
					$this->redirect( $validation_error );
				} else {
					$this->redirect( $input );
				}
			}
		}
		// 完了 or 確認画面 or エラーURLが設定済みで
		// 完了 or 確認画面 or エラーに直接アクセスした場合、
		// 入力画面に戻れれば戻る。戻れない場合はトップに戻す
		else {
			$check_urls = array(
				$confirm,
				$complete,
			);
			$back_url = ( $input ) ? $input : home_url();
			foreach ( $check_urls as $check_url ) {
				if ( $REQUEST_URI === $check_url ) {
					$this->Main_Service->clear_data();
					$this->redirect( $back_url );
				}
			}
			$this->redirect( $input );

			if ( $this->Main_Service->is_valid() && $REQUEST_URI == $validation_error ) {
				$this->Main_Service->clear_data();
				$this->redirect( $back_url );
			}
		}
	}

	/**
	 * redirect
	 * 現在のURLと引数で渡されたリダイレクトURLが同じであればリダイレクトしない
	 * @param string リダイレクトURL
	 */
	private function redirect( $url ) {
		$redirect = ( empty( $url ) ) ? $this->Main_Service->get_request_uri() : $url;
		$REQUEST_URI = $this->Main_Service->get_request_uri();
		if ( !empty( $_POST ) || $redirect != $REQUEST_URI ) {
			$redirect = wp_sanitize_redirect( $redirect );
			$redirect = wp_validate_redirect( $redirect, home_url() );
			wp_redirect( $redirect );
			exit();
		}
	}

	/**
	 * wp_enqueue_scripts
	 */
	public function wp_enqueue_scripts() {
		$url = plugin_dir_url( __FILE__ );
		wp_enqueue_style( MWF_Config::NAME, $url . './css/style.css' );

		$style  = $this->Main_Service->get_option( 'style' );
		$styles = apply_filters( 'mwform_styles', array() );
		if ( is_array( $styles ) && isset( $styles[$style] ) ) {
			$css = $styles[$style];
			wp_enqueue_style( MWF_Config::NAME . '_style', $css );
		}
		wp_enqueue_script( MWF_Config::NAME, $url . './js/form.js', array( 'jquery' ), false, true );
	}

	/**
	 * scroll_script
	 */
	public function scroll_script() {
		$url = plugin_dir_url( __FILE__ );
		wp_register_script(
			MWF_Config::NAME . '-scroll',
			$url . 'js/scroll.js',
			array( 'jquery' ),
			false,
			true
		);
		wp_localize_script( MWF_Config::NAME . '-scroll', 'mwform_scroll', array(
			'offset' => apply_filters( 'mwform_scroll_offset_' . $this->Main_Service->get_key(), 0 ),
		) );
		wp_enqueue_script( MWF_Config::NAME . '-scroll' );
	}
}
