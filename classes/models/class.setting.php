<?php
/**
 * Name       : MW WP Form Setting
 * Version    : 1.0.0
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : December 31, 2014
 * Modified   :
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Setting {

	/**
	 * $post_id
	 * フォームの Post ID
	 * @var int
	 */
	protected $post_id;

	/**
	 * $mail_subject
	 * 自動返信メールの題名
	 * @var string
	 */
	protected $mail_subject = '';

	/**
	 * $mail_from
	 * 自動返信メールの送信元
	 * @var string
	 */
	protected $mail_from = '';

	/**
	 * $mail_sender
	 * 自動返信メールの送信者
	 * @var string
	 */
	protected $mail_sender = '';

	/**
	 * $mail_content
	 * 自動返信メールの本文
	 * @var string
	 */
	protected $mail_content = '';

	/**
	 * $automatic_reply_email
	 * 自動返信メールの送信先を格納したフォームフィールドのname属性
	 * @var string
	 */
	protected $automatic_reply_email = '';

	/**
	 * $mail_to
	 * 管理者メールの送信先
	 * @var string
	 */
	protected $mail_to = '';

	/**
	 * $mail_cc
	 * 管理者メールのCC
	 * @var string
	 */
	protected $mail_cc = '';

	/**
	 * $mail_bcc
	 * 管理者メールのBCC
	 * @var string
	 */
	protected $mail_bcc = '';

	/**
	 * $admin_mail_subject
	 * 管理者メールの題名
	 * @var string
	 */
	protected $admin_mail_subject = '';

	/**
	 * $admin_mail_from
	 * 管理者メールの送信元
	 * @var string
	 */
	protected $admin_mail_from = '';

	/**
	 * $admin_mail_sender
	 * 管理者メールの送信者
	 * @var string
	 */
	protected $admin_mail_sender = '';

	/**
	 * $admin_mail_content
	 * 管理者メールの本文
	 * @var string
	 */
	protected $admin_mail_content = '';

	/**
	 * $querystring
	 * URL引数を有効にするかどうか
	 * @var false|1
	 */
	protected $querystring = false;

	/**
	 * $usedb
	 * 問い合わせデータを保存するかどうか
	 * @var false|1
	 */
	protected $usedb = false;

	/**
	 * $akismet_author
	 * akismet送信者の対象とするフォームフィールドのname属性
	 * @var string
	 */
	protected $akismet_author = '';

	/**
	 * $akismet_author_email
	 * akismetメールアドレスの対象とするフォームフィールドのname属性
	 * @var string
	 */
	protected $akismet_author_email = '';

	/**
	 * $akismet_author_url
	 * akismet url の対象とするフォームフィールドのname属性
	 * @var string
	 */
	protected $akismet_author_url = '';

	/**
	 * $complete_message
	 * 完了画面メッセージ
	 * @var string
	 */
	protected $complete_message = '';

	/**
	 * $input_url
	 * 入力画面URL
	 * @var string
	 */
	protected $input_url = '';

	/**
	 * $confirmation_url
	 * 確認画面URL
	 * @var string
	 */
	protected $confirmation_url = '';

	/**
	 * $complete_url
	 * 完了画面URL
	 * @var string
	 */
	protected $complete_url = '';

	/**
	 * $validation_error_url
	 * バリデーションエラー画面URL
	 * @var string
	 */
	protected $validation_error_url = '';

	/**
	 * $validation
	 * フォームに設定されたバリデーションルールの配列
	 * @var array
	 */
	protected $validation = array();

	/**
	 * $style
	 * フォームに設定されたスタイル
	 * @var string
	 */
	protected $style = '';

	/**
	 * $scroll
	 * 入力画面以外を表示したときにフォームの位置までスクロールするかどうか
	 * @var false|1
	 */
	protected $scroll = false;

	/**
	 * __construct
	 * @param int $post_id
	 */
	public function __construct( $post_id ) {
		if ( get_post_type( $post_id ) === MWF_Config::NAME ) {
			$this->post_id = $post_id;
			$values = get_post_meta( $post_id, MWF_Config::NAME, true );
			if ( is_array( $values ) ) {
				$this->sets( $values );
			}
		}
	}

	/**
	 * get
	 * 属性の取得
	 * @param string $key
	 * @return mixed|null
	 */
	public function get( $key ) {
		if ( isset( $this->$key ) ) {
			return $this->$key;
		}
	}

	/**
	 * set
	 * 属性をセット
	 * @param string $key
	 * @param mixed $value
	 */
	public function set( $key, $value ) {
		if ( isset( $this->$key ) ) {
			$this->$key = $value;
		}
	}

	/**
	 * sets
	 * 属性をセット
	 * @param array $values
	 */
	public function sets( array $values ) {
		foreach ( $values as $key => $value ) {
			$this->set( $key, $value );
		}
	}

	/**
	 * save
	 * 保持しているデータでアップデート
	 */
	public function save() {
		$values = get_object_vars( $this );
		$new_values = array();
		foreach ( $values as $key => $value ) {
			if ( $key === 'post_id' ) {
				continue;
			}
			$new_values[$key] = $value;
		}
		update_post_meta( $this->post_id, MWF_Config::NAME, $new_values );
	}

	/**
	 * get_posts
	 * @return array フォーム（WP_Post）の配列
	 */
	public function get_posts() {
		$forms = get_posts( array(
			'post_type'      => MWF_Config::NAME,
			'posts_per_page' => -1,
		) );
		if ( !is_array( $forms ) ) {
			return array();
		}
		return $forms;
	}

	/**
	 * get_tracking_number
	 * @return int $tracking_number
	 */
	public function get_tracking_number() {
		$tracking_number = get_post_meta( $this->post_id, MWF_Config::TRACKINGNUMBER, true );
		if ( empty( $tracking_number ) ) {
			$tracking_number = 1;
		}
		return intval( $tracking_number );
	}

	/**
	 * update_tracking_number
	 */
	public function update_tracking_number() {
		$tracking_number     = $this->get_tracking_number();
		$new_tracking_number = $tracking_number + 1;
		update_post_meta( $this->post_id, MWF_Config::TRACKINGNUMBER, $new_tracking_number );
	}
}
