<?php
/**
 * Name       : MW WP Form Admin View
 * Version    : 1.0.1
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : January 2, 2015
 * Modified   : January 20, 2015
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Admin_View extends MW_WP_Form_View {
	
	/**
	 * complete_message
	 * 完了画面内容の入力画面を表示
	 */
	public function complete_message() {
		$complete_message = $this->get( 'complete_message' );
		wp_editor( $complete_message, MWF_Config::NAME . '_complete_message', array(
			'textarea_name' => MWF_Config::NAME . '[complete_message]',
			'textarea_rows' => 7,
		) );
	}

	/**
	 * url
	 * URL設定フォームを表示
	 */
	public function url() {
		$input_url            = $this->get( 'input_url' );
		$confirmation_url     = $this->get( 'confirmation_url' );
		$complete_url         = $this->get( 'complete_url' );
		$validation_error_url = $this->get( 'validation_error_url' );
		?>
		<input type="hidden" name="<?php echo esc_attr( MWF_Config::NAME ); ?>_nonce" value="<?php echo wp_create_nonce( MWF_Config::NAME ); ?>" />
		<table border="0" cellpadding="0" cellspacing="4">
			<tr>
				<th><?php esc_html_e( 'Input Page URL', MWF_Config::DOMAIN ); ?></th>
				<td>
					<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[input_url]" value="<?php echo esc_attr( $input_url ); ?>" />
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Confirmation Page URL', MWF_Config::DOMAIN ); ?></th>
				<td>
					<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[confirmation_url]" value="<?php echo esc_attr( $confirmation_url ); ?>" />
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Complete Page URL', MWF_Config::DOMAIN ); ?></th>
				<td>
					<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[complete_url]" value="<?php echo esc_attr( $complete_url ); ?>" />
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Validation Error Page URL', MWF_Config::DOMAIN ); ?></th>
				<td>
					<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[validation_error_url]" value="<?php echo esc_attr( $validation_error_url ); ?>" />
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
	 * validation_rule
	 * バリデーションルール設定フォームを表示
	 */
	public function validation_rule() {
		$validation = $this->get( 'validation' );
		$validation_rules = $this->get( 'validation_rules' );

		if ( !$validation ) {
			$validation = array();
		}
		$validation_keys = array(
			'target' => '',
		);
		foreach ( $validation_rules as $validation_rule => $instance ) {
			$validation_keys[$instance->getName()] = '';
		}
		// 空の隠れバリデーションフィールド（コピー元）を挿入
		array_unshift( $validation, $validation_keys );
		?>
		<b class="add-btn"><?php esc_html_e( 'Add Validation rule', MWF_Config::DOMAIN ); ?></b>
		<div class="repeatable-boxes">
			<?php foreach ( $validation as $key => $value ) : $value = array_merge( $validation_keys, $value ); ?>
			<div class="repeatable-box" <?php if ( $key === 0 ) : ?>style="display:none"<?php endif; ?>>
				<div class="sortable-icon-handle"></div>
				<div class="remove-btn"><b>×</b></div>
				<div class="open-btn"><span><?php echo esc_attr( $value['target'] ); ?></span><b>▼</b></div>
				<div class="repeatable-box-content">
					<?php esc_html_e( 'The key which applies validation', MWF_Config::DOMAIN ); ?>：<input type="text" class="targetKey" value="<?php echo esc_attr( $value['target'] ); ?>" name="<?php echo MWF_Config::NAME; ?>[validation][<?php echo esc_attr( $key ); ?>][target]" />
					<table border="0" cellpadding="0" cellspacing="0">
						<tr>
							<td colspan="2">
								<?php foreach ( $validation_rules as $validation_rule => $instance ) : ?>
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
	 * add_ons
	 * アドオン
	 */
	public function add_ons() {
		?>
		<p>
			<?php esc_html_e( 'You can use more easy and useful to the MW WP Form in add-on!', MWF_Config::DOMAIN ); ?><br />
			<a href="http://plugins.2inc.org/mw-wp-form/add-on/" target="_blank" class="button button-primary"><?php esc_html_e( 'View Add-ons', MWF_Config::DOMAIN ); ?></a>
		</p>
		<?php
	}

	/**
	 * form_key
	 * フォーム識別子のテキストフィールドを表示
	 */
	public function form_key() {
		$post_id = $this->get( 'post_id' );
		?>
		<p>
			<span id="formkey_field">[mwform_formkey key="<?php echo esc_html( $post_id ); ?>"]</span>
			<span class="mwf_note">
				<?php esc_html_e( 'Copy and Paste this shortcode.', MWF_Config::DOMAIN ); ?><br />
				<?php esc_html_e( 'The key to use with hook is ', MWF_Config::DOMAIN ); ?><?php echo MWF_Config::NAME; ?>-<?php echo esc_html( $post_id ); ?>
			</span>
		</p>
		<?php
	}

	/**
	 * mail_options
	 * 自動返信メール設定フォームを表示
	 */
	public function mail_options() {
		$mail_subject          = $this->get( 'mail_subject' );
		$mail_sender           = $this->get( 'mail_sender' );
		$mail_from             = $this->get( 'mail_from' );
		$mail_content          = $this->get( 'mail_content' );
		$automatic_reply_email = $this->get( 'automatic_reply_email' );
		?>
		<p>
			<?php esc_html_e( '{key} is converted form data.', MWF_Config::DOMAIN ); ?>
			<?php echo sprintf(
				esc_html__( 'It is automatically converted to Tracking number when you input {%s}.', MWF_Config::DOMAIN ),
				MWF_Config::TRACKINGNUMBER
			); ?>
		</p>
		<p>
			<b><?php esc_html_e( 'Subject', MWF_Config::DOMAIN ); ?></b><br />
			<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[mail_subject]" value="<?php echo esc_attr( $mail_subject ); ?>" />
		</p>
		<p>
			<b><?php esc_html_e( 'Sender', MWF_Config::DOMAIN ); ?></b><br />
			<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[mail_sender]" value="<?php echo esc_attr( $mail_sender ); ?>" /><br />
			<span class="mwf_note"><?php esc_html_e( 'If empty:', MWF_Config::DOMAIN ); ?> <?php bloginfo( 'name' ); ?></span>
		</p>
		<p>
			<b><?php esc_html_e( 'From ( E-mail address )', MWF_Config::DOMAIN ); ?></b><br />
			<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[mail_from]" value="<?php echo esc_attr( $mail_from ); ?>" /><br />
			<span class="mwf_note"><?php esc_html_e( 'If empty:', MWF_Config::DOMAIN ); ?> <?php bloginfo( 'admin_email' ); ?></span>
		</p>
		<p>
			<b><?php esc_html_e( 'Content', MWF_Config::DOMAIN ); ?></b><br />
			<textarea name="<?php echo esc_attr( MWF_Config::NAME ); ?>[mail_content]" cols="30" rows="10"><?php echo esc_attr( $mail_content ); ?></textarea>
		</p>
		<p>
			<b><?php esc_html_e( 'Automatic reply email', MWF_Config::DOMAIN ); ?></b><br />
			<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[automatic_reply_email]" value="<?php echo esc_attr( $automatic_reply_email ); ?>" /><br />
			<span class="mwf_note"><?php esc_html_e( 'Input the key to use as transmission to automatic reply email. {} is unnecessary.', MWF_Config::DOMAIN ); ?></span>
		</p>
		<?php
	}

	/**
	 * admin_mail_options
	 * 管理者宛メール設定フォームを表示
	 */
	public function admin_mail_options() {
		$mail_to            = $this->get( 'mail_to' );
		$mail_cc            = $this->get( 'mail_cc' );
		$mail_bcc           = $this->get( 'mail_bcc' );
		$admin_mail_subject = $this->get( 'admin_mail_subject' );
		$admin_mail_sender  = $this->get( 'admin_mail_sender' );
		$admin_mail_from    = $this->get( 'admin_mail_from' );
		$admin_mail_content = $this->get( 'admin_mail_content' );
		?>
		<p>
			<?php esc_html_e( '{key} is converted form data.', MWF_Config::DOMAIN ); ?>
			<?php echo sprintf(
				esc_html__( 'It is automatically converted to Tracking number when you input {%s}.', MWF_Config::DOMAIN ),
				MWF_Config::TRACKINGNUMBER
			); ?>
		</p>
		<p>
			<?php esc_html_e( 'If Admin Email Options is a blank, Automatic Replay Email Options is used as Admin Email Options.', MWF_Config::DOMAIN ); ?>
		</p>
		<p>
			<b><?php esc_html_e( 'To ( E-mail address )', MWF_Config::DOMAIN ); ?></b><br />
			<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[mail_to]" value="<?php echo esc_attr( $mail_to ); ?>" /><br />
			<span class="mwf_note"><?php esc_html_e( 'If empty:', MWF_Config::DOMAIN ); ?> <?php bloginfo( 'admin_email' ); ?></span>
		</p>
		<p>
			<b><?php esc_html_e( 'CC ( E-mail address )', MWF_Config::DOMAIN ); ?></b><br />
			<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[mail_cc]" value="<?php echo esc_attr( $mail_cc ); ?>" />
		</p>
		<p>
			<b><?php esc_html_e( 'BCC ( E-mail address )', MWF_Config::DOMAIN ); ?></b><br />
			<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[mail_bcc]" value="<?php echo esc_attr( $mail_bcc ); ?>" />
		</p>
		<p>
			<b><?php esc_html_e( 'Subject', MWF_Config::DOMAIN ); ?></b><br />
			<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[admin_mail_subject]" value="<?php echo esc_attr( $admin_mail_subject ); ?>" />
		</p>
		<p>
			<b><?php esc_html_e( 'Sender', MWF_Config::DOMAIN ); ?></b><br />
			<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[admin_mail_sender]" value="<?php echo esc_attr( $admin_mail_sender ); ?>" /><br />
			<span class="mwf_note"><?php esc_html_e( 'If empty:', MWF_Config::DOMAIN ); ?> <?php bloginfo( 'name' ); ?></span>
		</p>
		<p>
			<b><?php esc_html_e( 'From ( E-mail address )', MWF_Config::DOMAIN ); ?></b><br />
			<input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[admin_mail_from]" value="<?php echo esc_attr( $admin_mail_from ); ?>" /><br />
			<span class="mwf_note"><?php esc_html_e( 'If empty:', MWF_Config::DOMAIN ); ?> <?php bloginfo( 'admin_email' ); ?></span>
		</p>
		<p>
			<b><?php esc_html_e( 'Content', MWF_Config::DOMAIN ); ?></b><br />
			<textarea name="<?php echo esc_attr( MWF_Config::NAME ); ?>[admin_mail_content]" cols="30" rows="10"><?php echo esc_attr( $admin_mail_content ); ?></textarea>
		</p>
		<?php
	}

	/**
	 * settings
	 * $post を取得するための引数を有効にするフィールドを表示
	 */
	public function settings() {
		$querystring          = $this->get( 'querystring' );
		$usedb                = $this->get( 'usedb' );
		$scroll               = $this->get( 'scroll' );
		$akismet_author       = $this->get( 'akismet_author' );
		$akismet_author_email = $this->get( 'akismet_author_email' );
		$akismet_author_url   = $this->get( 'akismet_author_url' );
		?>
		<p>
			<label><input type="checkbox" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[querystring]" value="1" <?php checked( $querystring, 1 ); ?> /> <?php esc_html_e( 'Activate Query string of post', MWF_Config::DOMAIN ); ?></label><br />
			<span class="mwf_note"><?php esc_html_e( 'If this field is active, MW WP Form get query string. And get post data from query string "post_id". You can use $post\'s property in editor.', MWF_Config::DOMAIN ); ?><br />
			<?php esc_html_e( 'Example: {ID}, {post_title}, {post_meta} etc...', MWF_Config::DOMAIN ); ?></span>
		</p>
		<p>
			<label><input type="checkbox" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[usedb]" value="1" <?php checked( $usedb, 1 ); ?> /> <?php esc_html_e( 'Saving inquiry data in database', MWF_Config::DOMAIN ); ?></label>
		</p>
		<p>
			<label><input type="checkbox" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[scroll]" value="1" <?php checked( $scroll, 1 ); ?> /> <?php esc_html_e( 'Enable scrolling of screen transition.', MWF_Config::DOMAIN ); ?></label>
		</p>
		<table border="0" cellpadding="0" cellspacing="0" class="akismet">
			<tr>
				<th colspan="2"><?php esc_html_e( 'Akismet Setting', MWF_Config::DOMAIN ); ?></th>
			</tr>
			<tr>
				<td>author</td>
				<td><input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[akismet_author]" value="<?php echo esc_attr( $akismet_author ); ?>" /></td>
			</tr>
			<tr>
				<td>email</td>
				<td><input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[akismet_author_email]" value="<?php echo esc_attr( $akismet_author_email ); ?>" /></td>
			</tr>
			<tr>
				<td>url</td>
				<td><input type="text" name="<?php echo esc_attr( MWF_Config::NAME ); ?>[akismet_author_url]" value="<?php echo esc_attr( $akismet_author_url ); ?>" /></td>
			</tr>
		</table>
		<span class="mwf_note"><?php esc_html_e( 'Input the key to use Akismet.', MWF_Config::DOMAIN ); ?></span>
		<?php
	}

	/**
	 * style
	 */
	public function style() {
		$styles = $this->get( 'styles' );
		$style  = $this->get( 'style' );
		?>
		<p>
			<select name="<?php echo MWF_Config::NAME; ?>[style]">
				<option value=""><?php esc_html_e( 'Select Style', MWF_Config::DOMAIN ); ?></option>
				<?php foreach ( $styles as $style_key => $css ) : ?>
				<option value="<?php echo esc_attr( $style_key ); ?>" <?php selected( $style, $style_key ); ?>>
					<?php echo esc_html( $style_key ); ?>
				</option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php
	}

	/**
	 * donate_link
	 * @param array $views
	 * @return array
	 */
	public function donate_link( $views ) {
		$donation = array( 'donation' => '<div class="donation"><p>' . __( 'Your contribution is needed for making this plugin better.', MWF_Config::DOMAIN ) . ' <a href="http://www.amazon.co.jp/registry/wishlist/39ANKRNSTNW40" class="button">' . __( 'Donate', MWF_Config::DOMAIN ) . '</a></p></div>' );
		$views = array_merge( $donation, $views );
		return $views;
	}

	/**
	 * tag_generator
	 * タグジェネレータを出力
	 * @param string $editor_id
	 */
	public function tag_generator( $editor_id ) {
		$post_type = get_post_type();
		if ( $post_type !== MWF_Config::NAME ) {
			return;
		}
		if ( $editor_id !== 'content' ) {
			return;
		}
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
				<optgroup label="<?php echo esc_attr_e( 'Error fields', MWF_Config::DOMAIN ); ?>">
					<?php do_action( 'mwform_tag_generator_error_option' ); ?>
				</optgroup>
				<optgroup label="<?php echo esc_attr_e( 'Other fields', MWF_Config::DOMAIN ); ?>">
					<?php do_action( 'mwform_tag_generator_option' ); ?>
				</optgroup>
			</select>
			<span class="button"><?php esc_html_e( 'Add form tag', MWF_Config::DOMAIN ); ?></span>
		</div>
		<?php
	}

	/**
	 * quicktag
	 * HTMLエディタにクイックタグを追加
	 */
	public function quicktag() {
		if ( MWF_Config::NAME == get_post_type() ) : ?>
		<script type="text/javascript">
		if ( typeof( QTags ) !== 'undefined' ) {
			<?php do_action( 'mwform_add_qtags' ); ?>
		}
		</script>
		<?php
		endif;
	}
}