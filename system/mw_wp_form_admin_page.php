<?php
/**
 * Name: MW WP Form Admin Page
 * Description: 管理画面クラス
 * Version: 1.11.1
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : February 21, 2013
 * Modified: September 3, 2014
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Admin_Page {

	/**
	 * 登録済みのフォームスタイルの一覧
	 */
	private $styles = array();

	/**
	 * フォームの設定データ
	 */
	private $postdata;

	/**
	 * バリデーションルールの一覧
	 */
	private $validation_rules = array();

	/**
	 * __construct
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_style' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'admin_head', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_post' ) );
		add_action( 'admin_print_footer_scripts', array( $this, 'add_quicktag' ) );
		add_action( 'current_screen', array( $this, 'current_screen' ) );
		add_filter( 'default_content', array( $this, 'default_content' ) );
		add_action( 'edit_form_after_title', array( $this, 'add_tag_generator' ) );
	}

	/**
	 * current_screen
	 * 寄付リンクを表示
	 * @param WP_Screen $screen
	 */
	public function current_screen( $screen ) {
		if ( $screen->id === 'edit-' . MWF_Config::NAME )
			add_filter( 'views_' . $screen->id, array( $this, 'display_donate_link' ) );
	}
	public function display_donate_link( $views ) {
		$donation = array( 'donation' => '<div class="donation"><p>' . __( 'Your contribution is needed for making this plugin better.', MWF_Config::DOMAIN ) . ' <a href="http://www.amazon.co.jp/registry/wishlist/39ANKRNSTNW40" class="button">' . __( 'Donate', MWF_Config::DOMAIN ) . '</a></p></div>' );
		$views = array_merge( $donation, $views );
		return $views;
	}

	/**
	 * get_post_data
	 * フォームの設定データを返す
	 * @param string $key 設定データのキー
	 * @return mixed 設定データ
	 */
	protected function get_post_data( $key ) {
		global $post;
		if ( isset( $this->postdata[$key] ) ) {
			return $this->postdata[$key];
		} else {
			$date = $post->post_date;
			$modified = $post->post_modified;
			if ( $date === $modified ){
				return apply_filters( 'mwform_default_postdata', '', $key );
			}
		}
	}

	/**
	 * default_content
	 * 本文の初期値を設定
	 * @param string $content
	 * @return string
	 */
	public function default_content( $content ) {
		global $typenow;
		if ( $typenow === MWF_Config::NAME ) {
			return apply_filters( 'mwform_default_content', '' );
		}
	}

	/**
	 * register_post_type
	 */
	public function register_post_type() {
		register_post_type( MWF_Config::NAME, array(
			'label' => 'MW WP Form',
			'labels' => array(
				'name' => 'MW WP Form',
				'singular_name' => 'MW WP Form',
				'add_new_item' => __( 'Add New Form', MWF_Config::DOMAIN ),
				'edit_item' => __( 'Edit Form', MWF_Config::DOMAIN ),
				'new_item' => __( 'New Form', MWF_Config::DOMAIN ),
				'view_item' => __( 'View Form', MWF_Config::DOMAIN ),
				'search_items' => __( 'Search Forms', MWF_Config::DOMAIN ),
				'not_found' => __( 'No Forms found', MWF_Config::DOMAIN ),
				'not_found_in_trash' => __( 'No Forms found in Trash', MWF_Config::DOMAIN ),
			),
			'capability_type' => 'page',
			'public'  => false,
			'show_ui' => true,
		) );
	}

	/**
	 * add_meta_box
	 */
	public function add_meta_box() {
		$post_type = get_post_type();
		if ( MWF_Config::NAME == $post_type ) {
			global $post;
			// 設定データ取得
			$this->postdata = get_post_meta( $post->ID, MWF_Config::NAME, true );
			// 完了画面内容
			add_meta_box(
				MWF_Config::NAME . '_complete_message_metabox',
				__( 'Complete Message', MWF_Config::DOMAIN ),
				array( $this, 'add_complete_message' ),
				MWF_Config::NAME, 'normal'
			);
			// 入力画面URL
			add_meta_box(
				MWF_Config::NAME . '_url',
				__( 'URL Options', MWF_Config::DOMAIN ),
				array( $this, 'add_url' ),
				MWF_Config::NAME, 'normal'
			);
			// バリデーション
			add_meta_box(
				MWF_Config::NAME . '_validation',
				__( 'Validation Rule', MWF_Config::DOMAIN ),
				array( $this, 'display_validation_rule' ),
				MWF_Config::NAME, 'normal'
			);
			// フォーム識別子
			add_meta_box(
				MWF_Config::NAME . '_formkey',
				__( 'Form Key', MWF_Config::DOMAIN ),
				array( $this, 'display_form_key' ),
				MWF_Config::NAME, 'side'
			);
			// 自動返信メール設定
			add_meta_box(
				MWF_Config::NAME . '_mail',
				__( 'Automatic Reply Email Options', MWF_Config::DOMAIN ),
				array( $this, 'add_mail_options' ),
				MWF_Config::NAME, 'side'
			);
			// 管理者メール設定
			add_meta_box(
				MWF_Config::NAME . '_admin_mail',
				__( 'Admin Email Options', MWF_Config::DOMAIN ),
				array( $this, 'add_admin_mail_options' ),
				MWF_Config::NAME, 'side'
			);
			// 設定
			add_meta_box(
				MWF_Config::NAME . '_settings',
				__( 'settings', MWF_Config::DOMAIN ),
				array( $this, 'settings' ),
				MWF_Config::NAME, 'side'
			);
			// CSS
			$this->styles = apply_filters( 'mwform_styles', $this->styles );
			if ( $this->styles ) {
				add_meta_box(
					MWF_Config::NAME . '_styles',
					__( 'Style setting', MWF_Config::DOMAIN ),
					array( $this, 'add_style_option' ),
					MWF_Config::NAME, 'side'
				);
			}
		}
	}

	/**
	 * add_quicktag
	 * HTMLエディタにクイックタグを追加
	 */
	public function add_quicktag() {
		if ( MWF_Config::NAME == get_post_type() ) : ?>
		<script type="text/javascript">
		if ( typeof( QTags ) !== 'undefined' ) {
			<?php do_action( 'mwform_add_qtags' ); ?>
		}
		</script>
		<?php
		endif;
	}

	/**
	 * admin_style
	 * CSS適用
	 */
	public function admin_style() {
		$post_type = get_post_type();
		if ( isset( $_GET['post_type'] ) && MWF_Config::NAME === $_GET['post_type'] || MWF_Config::NAME == $post_type ) {
			$url = plugin_dir_url( __FILE__ );
			wp_register_style( MWF_Config::NAME . '-admin', $url . '../css/admin.css' );
			wp_enqueue_style( MWF_Config::NAME . '-admin' );
		}
	}

	/**
	 * admin_scripts
	 * JavaScript適用
	 */
	public function admin_scripts() {
		if ( MWF_Config::NAME == get_post_type() ) {
			$url = plugin_dir_url( __FILE__ );
			wp_register_script( MWF_Config::NAME . '-repeatable', $url . '../js/mw-wp-form-repeatable.js' );
			wp_enqueue_script( MWF_Config::NAME . '-repeatable' );
			wp_register_script( MWF_Config::NAME . '-admin', $url . '../js/admin.js' );
			wp_enqueue_script( MWF_Config::NAME . '-admin' );
			wp_enqueue_script( 'jquery-ui-dialog' );

			global $wp_scripts;
			$ui = $wp_scripts->query( 'jquery-ui-core' );
			wp_enqueue_style( 'jquery.ui', '//ajax.googleapis.com/ajax/libs/jqueryui/' . $ui->ver . '/themes/smoothness/jquery-ui.min.css', array(), $ui->ver );
		}
	}

	/**
	 * save_post
	 * @param int $post_ID
	 */
	public function save_post( $post_ID ) {
		if ( !( isset( $_POST['post_type'] ) && $_POST['post_type'] === MWF_Config::NAME ) )
			return $post_ID;
		if ( !isset( $_POST[MWF_Config::NAME . '_nonce'] ) )
			return $post_ID;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_ID;
		if ( !wp_verify_nonce( $_POST[MWF_Config::NAME . '_nonce'], MWF_Config::NAME ) )
			return $post_ID;
		if ( !current_user_can( MWF_Config::CAPABILITY ) )
			return $post_ID;

		$data = $_POST[MWF_Config::NAME];
		if ( !empty( $data['validation'] ) && is_array( $data['validation'] ) ) {
			$validation = array();
			foreach ( $data['validation'] as $_validation ) {
				if ( empty( $_validation['target'] ) ) continue;
				foreach ( $_validation as $key => $value ) {
					// between min, max
					if ( $key == 'between' ) {
						if ( !MWF_Functions::is_numeric( $value['min'] ) ) {
							unset( $_validation[$key]['min'] );
						}
						if ( !MWF_Functions::is_numeric( $value['max'] ) ) {
							unset( $_validation[$key]['max'] );
						}
					}
					// minlength min
					elseif ( $key == 'minlength' && !MWF_Functions::is_numeric( $value['min'] ) ) {
						unset( $_validation[$key] );
					}
					// fileType types
					elseif ( $key == 'fileType' && isset( $value['types'] ) && !preg_match( '/^[0-9A-Za-z,]+$/', $value['types'] ) ) {
						unset( $_validation[$key] );
					}
					// fileSize bytes
					elseif ( $key == 'fileSize' && !MWF_Functions::is_numeric( $value['bytes'] ) ) {
						unset( $_validation[$key] );
					}

					// 要素が空のときは削除
					// 単一項目のとき
					if ( empty( $value ) ) {
						unset( $_validation[$key] );
					}
					// 配列のとき
					elseif ( is_array( $value ) && !array_diff( $value, array( '' ) ) ) {
						unset( $_validation[$key] );
					}
				}
				$validation[] = $_validation;
			}
			$data['validation'] = $validation;
		}
		//$old_data = get_post_meta( $post_ID, MWF_Config::NAME, true );
		update_post_meta( $post_ID, MWF_Config::NAME, $data, $this->postdata );
	}

	/**
	 * display_form_key
	 * formkeyのテキストフィールドを表示
	 */
	public function display_form_key() {
		global $post;
		?>
		<p>
			<span id="formkey_field">[mwform_formkey key="<?php the_ID(); ?>"]</span>
			<span class="mwf_note">
				<?php esc_html_e( 'Copy and Paste this shortcode.', MWF_Config::DOMAIN ); ?><br />
				<?php esc_html_e( 'The key to use with hook is ', MWF_Config::DOMAIN ); ?><?php echo MWF_Config::NAME; ?>-<?php echo $post->ID; ?>
			</span>
		</p>
		<?php
	}

	/**
	 * settings
	 * $post を取得するための引数を有効にするフィールドを表示
	 */
	public function settings() {
		global $post;
		?>
		<p>
			<label><input type="checkbox" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[querystring]" value="1" <?php checked( $this->get_post_data( 'querystring' ), 1 ); ?> /> <?php esc_html_e( 'Activate Query string of post', MWF_Config::DOMAIN ); ?></label><br />
			<span class="mwf_note"><?php esc_html_e( 'If this field is active, MW WP Form get query string. And get post data from query string "post_id". You can use $post\'s property in editor.', MWF_Config::DOMAIN ); ?><br />
			<?php esc_html_e( 'Example: {ID}, {post_title}, {post_meta} etc...', MWF_Config::DOMAIN ); ?></span>
		</p>
		<p>
			<label><input type="checkbox" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[usedb]" value="1" <?php checked( $this->get_post_data( 'usedb' ), 1 ); ?> /> <?php esc_html_e( 'Saving inquiry data in database', MWF_Config::DOMAIN ); ?></label>
		</p>
		<table border="0" cellpadding="0" cellspacing="0" class="akismet">
			<tr>
				<th colspan="2"><?php esc_html_e( 'Akismet Setting', MWF_Config::DOMAIN ); ?></th>
			</tr>
			<tr>
				<td>author</td>
				<td><input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[akismet_author]" value="<?php echo esc_attr( $this->get_post_data( 'akismet_author' ) ); ?>" /></td>
			</tr>
			<tr>
				<td>email</td>
				<td><input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[akismet_author_email]" value="<?php echo esc_attr( $this->get_post_data( 'akismet_author_email' ) ); ?>" /></td>
			</tr>
			<tr>
				<td>url</td>
				<td><input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[akismet_author_url]" value="<?php echo esc_attr( $this->get_post_data( 'akismet_author_url' ) ); ?>" /></td>
			</tr>
		</table>
		<span class="mwf_note"><?php esc_html_e( 'Input the key to use Akismet.', MWF_Config::DOMAIN ); ?></span>
		<?php
	}

	/**
	 * add_complete_message
	 * 完了画面内容の入力画面を表示
	 */
	public function add_complete_message() {
		global $post;
		$content = $this->get_post_data( 'complete_message' );
		wp_editor( $content, MWF_Config::NAME . '_complete_message', array(
			'textarea_name' => MWF_Config::NAME . '[complete_message]',
			'textarea_rows' => 7,
		) );
	}

	/**
	 * add_mail_options
	 * 自動返信メール設定フォームを表示
	 */
	public function add_mail_options() {
		global $post;
		?>
		<p>
			<?php esc_html_e( '{key} is converted form data.', MWF_Config::DOMAIN ); ?>
		</p>
		<p>
			<b><?php esc_html_e( 'Subject', MWF_Config::DOMAIN ); ?></b><br />
			<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[mail_subject]" value="<?php echo esc_attr( $this->get_post_data( 'mail_subject' ) ); ?>" />
		</p>
		<p>
			<b><?php esc_html_e( 'Sender', MWF_Config::DOMAIN ); ?></b><br />
			<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[mail_sender]" value="<?php echo esc_attr( $this->get_post_data( 'mail_sender' ) ); ?>" /><br />
			<span class="mwf_note"><?php esc_html_e( 'If empty:', MWF_Config::DOMAIN ); ?> <?php bloginfo( 'name' ); ?></span>
		</p>
		<p>
			<b><?php esc_html_e( 'From ( E-mail address )', MWF_Config::DOMAIN ); ?></b><br />
			<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[mail_from]" value="<?php echo esc_attr( $this->get_post_data( 'mail_from' ) ); ?>" /><br />
			<span class="mwf_note"><?php esc_html_e( 'If empty:', MWF_Config::DOMAIN ); ?> <?php bloginfo( 'admin_email' ); ?></span>
		</p>
		<p>
			<b><?php esc_html_e( 'Content', MWF_Config::DOMAIN ); ?></b><br />
			<textarea name="<?php echo esc_attr( MWF_Config::NAME ); ?>[mail_content]" cols="30" rows="10"><?php echo esc_attr( $this->get_post_data( 'mail_content' ) ); ?></textarea>
		</p>
		<p>
			<b><?php esc_html_e( 'Automatic reply email', MWF_Config::DOMAIN ); ?></b><br />
			<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[automatic_reply_email]" value="<?php echo esc_attr( $this->get_post_data( 'automatic_reply_email') ); ?>" /><br />
			<span class="mwf_note"><?php esc_html_e( 'Input the key to use as transmission to automatic reply email. {} is unnecessary.', MWF_Config::DOMAIN ); ?></span>
		</p>
		<?php
	}

	/**
	 * add_admin_mail_options
	 * 管理者宛メール設定フォームを表示
	 */
	public function add_admin_mail_options() {
		global $post;
		?>
		<p>
			<?php esc_html_e( '{key} is converted form data.', MWF_Config::DOMAIN ); ?>
		</p>
		<p>
			<?php esc_html_e( 'If Admin Email Options is a blank, Automatic Replay Email Options is used as Admin Email Options.', MWF_Config::DOMAIN ); ?>
		</p>
		<p>
			<b><?php esc_html_e( 'To ( E-mail address )', MWF_Config::DOMAIN ); ?></b><br />
			<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[mail_to]" value="<?php echo esc_attr( $this->get_post_data( 'mail_to' ) ); ?>" /><br />
			<span class="mwf_note"><?php esc_html_e( 'If empty:', MWF_Config::DOMAIN ); ?> <?php bloginfo( 'admin_email' ); ?></span>
		</p>
		<p>
			<b><?php esc_html_e( 'CC ( E-mail address )', MWF_Config::DOMAIN ); ?></b><br />
			<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[mail_cc]" value="<?php echo esc_attr( $this->get_post_data( 'mail_cc' ) ); ?>" />
		</p>
		<p>
			<b><?php esc_html_e( 'BCC ( E-mail address )', MWF_Config::DOMAIN ); ?></b><br />
			<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[mail_bcc]" value="<?php echo esc_attr( $this->get_post_data( 'mail_bcc' ) ); ?>" />
		</p>
		<p>
			<b><?php esc_html_e( 'Subject', MWF_Config::DOMAIN ); ?></b><br />
			<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[admin_mail_subject]" value="<?php echo esc_attr( $this->get_post_data( 'admin_mail_subject' ) ); ?>" />
		</p>
		<p>
			<b><?php esc_html_e( 'Sender', MWF_Config::DOMAIN ); ?></b><br />
			<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[admin_mail_sender]" value="<?php echo esc_attr( $this->get_post_data( 'admin_mail_sender' ) ); ?>" /><br />
			<span class="mwf_note"><?php esc_html_e( 'If empty:', MWF_Config::DOMAIN ); ?> <?php bloginfo( 'name' ); ?></span>
		</p>
		<p>
			<b><?php esc_html_e( 'From ( E-mail address )', MWF_Config::DOMAIN ); ?></b><br />
			<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[admin_mail_from]" value="<?php echo esc_attr( $this->get_post_data( 'admin_mail_from' ) ); ?>" /><br />
			<span class="mwf_note"><?php esc_html_e( 'If empty:', MWF_Config::DOMAIN ); ?> <?php bloginfo( 'admin_email' ); ?></span>
		</p>
		<p>
			<b><?php esc_html_e( 'Content', MWF_Config::DOMAIN ); ?></b><br />
			<textarea name="<?php echo esc_attr( MWF_Config::NAME ); ?>[admin_mail_content]" cols="30" rows="10"><?php echo esc_attr( $this->get_post_data( 'admin_mail_content' ) ); ?></textarea>
		</p>
		<?php
	}

	/**
	 * add_url
	 * URL設定フォームを表示
	 */
	public function add_url() {
		global $post;
		?>
		<input type="hidden" name="<?php echo esc_attr( MWF_Config::NAME ); ?>_nonce" value="<?php echo wp_create_nonce( MWF_Config::NAME ); ?>" />
		<table border="0" cellpadding="0" cellspacing="4">
			<tr>
				<th><?php esc_html_e( 'Input Page URL', MWF_Config::DOMAIN ); ?></th>
				<td>
					<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[input_url]" value="<?php echo esc_attr( $this->get_post_data( 'input_url' ) ); ?>" />
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Confirmation Page URL', MWF_Config::DOMAIN ); ?></th>
				<td>
					<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[confirmation_url]" value="<?php echo esc_attr( $this->get_post_data( 'confirmation_url' ) ); ?>" />
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Complete Page URL', MWF_Config::DOMAIN ); ?></th>
				<td>
					<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[complete_url]" value="<?php echo esc_attr( $this->get_post_data( 'complete_url' ) ); ?>" />
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Validation Error Page URL', MWF_Config::DOMAIN ); ?></th>
				<td>
					<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[validation_error_url]" value="<?php echo esc_attr( $this->get_post_data( 'validation_error_url' ) ); ?>" />
				</td>
			</tr>
		</table>
		<p class="description">
			<?php esc_html_e( 'This urls are the redirection urls at the time of button press. When URL setting is empty, The page redirect on the same page.', MWF_Config::DOMAIN ); ?><br />
			<?php printf( esc_html__( 'When a URL doesn\'t begin http or https, %s is complemented.', MWF_Config::DOMAIN ), '<b>' . home_url() . '</b>' ); ?>
		</p>
		<?php
	}

	/**
	 * add_validation_rule
	 * 各バリデーションルールクラスのインスタンスをセット
	 * @param string $rule_name
	 * @param MW_Validation_Rule $instance
	 */
	public function add_validation_rule( $rule_name, $instance ) {
		$this->validation_rules[$rule_name] = $instance;
	}

	/**
	 * display_validation_rule
	 * バリデーションルール設定フォームを表示
	 */
	public function display_validation_rule() {
		global $post;
		if ( ! $postdata = $this->get_post_data( 'validation' ) )
			$postdata = array();
		$validation_keys = array(
			'target' => '',
		);
		foreach ( $this->validation_rules as $validation_rule => $instance ) {
			$validation_keys[$instance->getName()] = '';
		}

		// 空の隠れバリデーションフィールド（コピー元）を挿入
		array_unshift( $postdata, $validation_keys );
		?>
		<b class="add-btn"><?php esc_html_e( 'Add Validation rule', MWF_Config::DOMAIN ); ?></b>
		<div class="repeatable-boxes">
			<?php foreach ( $postdata as $key => $value ) : $value = array_merge( $validation_keys, $value ); ?>
			<div class="repeatable-box"<?php if ( $key === 0 ) : ?> style="display:none"<?php endif; ?>>
				<div class="remove-btn"><b>×</b></div>
				<div class="open-btn"><span><?php echo esc_attr( $value['target'] ); ?></span><b>▼</b></div>
				<div class="repeatable-box-content">
					<?php esc_html_e( 'The key which applies validation', MWF_Config::DOMAIN ); ?>：<input type="text" class="targetKey" value="<?php echo esc_attr( $value['target'] ); ?>" name="<?php echo MWF_Config::NAME; ?>[validation][<?php echo $key; ?>][target]" />
					<table border="0" cellpadding="0" cellspacing="0">
						<tr>
							<td colspan="2">
								<?php foreach ( $this->validation_rules as $validation_rule => $instance ) : ?>
									<?php $instance->admin( $key, $value ); ?>
								<?php endforeach; ?>
							</td>
						</tr>
					</table>
				<!-- end .repeatable-box-content --></div>
			<!-- end .repeatable-box --></div>
			<?php endforeach; ?>
		<!-- end .repeatable-boxes --></div>
		<?php
	}

	/**
	 * add_style_option
	 */
	public function add_style_option() {
		?>
		<p>
			<select name="<?php echo MWF_Config::NAME; ?>[style]">
				<option value=""><?php esc_html_e( 'Select Style', MWF_Config::DOMAIN ); ?></option>
				<?php foreach ( $this->styles as $style => $css ) : ?>
				<option value="<?php echo esc_attr( $style ); ?>" <?php selected( $this->get_post_data( 'style' ), $style ); ?>>
					<?php echo esc_html( $style ); ?>
				</option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php
	}

	/**
	 * disable_visual_editor
	 * ビジュアルエディタを無効に
	 * @return bool;
	 */
	public function disable_visual_editor() {
		if ( MWF_Config::NAME == get_post_type() ) {
			return false;
		}
		return true;
	}

	/**
	 * add_tag_generator
	 * タグジェネレータを出力
	 */
	public function add_tag_generator() {
		$post_type = get_post_type();
		if ( $post_type !== MWF_Config::NAME )
			return;
		do_action( 'mwform_tag_generator_dialog' );
		?>
		<div class="add-mwform-btn">
			<select>
				<option value=""><?php echo esc_html_e( 'Select this.', MWF_Config::DOMAIN ); ?></option>
				<optgroup label="<?php echo esc_attr_e( 'Input fields', MWF_Config::DOMAIN ); ?>">
					<?php do_action( 'mwform_tag_generator_input_option' ); ?>
				</optgroup>
				<optgroup label="<?php echo esc_attr_e( 'Select fields', MWF_Config::DOMAIN ); ?>">
					<?php do_action( 'mwform_tag_generator_select_option' ); ?>
				</optgroup>
				<optgroup label="<?php echo esc_attr_e( 'Button fields', MWF_Config::DOMAIN ); ?>">
					<?php do_action( 'mwform_tag_generator_button_option' ); ?>
				</optgroup>
				<optgroup label="<?php echo esc_attr_e( 'Other fields', MWF_Config::DOMAIN ); ?>">
					<?php do_action( 'mwform_tag_generator_option' ); ?>
				</optgroup>
			</select>
			<span class="button"><?php esc_html_e( 'Add form tag', MWF_Config::DOMAIN ); ?></span>
		</div>
		<?php
	}
}
