<?php
/**
 * Name       : MW WP Form Setting
 * Version    : 1.2.0
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : December 31, 2014
 * Modified   : August 22, 2016
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Setting {

	/**
	 * フォームの Post ID
	 * @var int
	 */
	protected $post_id;

	/**
	 * URL引数を有効にするかどうか
	 * @var false|1
	 */
	protected $querystring = false;

	/**
	 * 問い合わせデータを保存するかどうか
	 * @var false|1
	 */
	protected $usedb = false;

	/**
	 * 自動返信メールの題名
	 * @var string
	 */
	protected $mail_subject = '';

	/**
	 * 自動返信メールの送信元
	 * @var string
	 */
	protected $mail_from = '';

	/**
	 * 自動返信メールの送信者
	 * @var string
	 */
	protected $mail_sender = '';

	/**
	 * 自動返信メールの本文
	 * @var string
	 */
	protected $mail_content = '';

	/**
	 * 自動返信メールの送信先を格納したフォームフィールドのname属性
	 * @var string
	 */
	protected $automatic_reply_email = '';

	/**
	 * 管理者メールの送信先
	 * @var string
	 */
	protected $mail_to = '';

	/**
	 * 管理者メールのCC
	 * @var string
	 */
	protected $mail_cc = '';

	/**
	 * 管理者メールのBCC
	 * @var string
	 */
	protected $mail_bcc = '';

	/**
	 * 管理者メールの題名
	 * @var string
	 */
	protected $admin_mail_subject = '';

	/**
	 * Return-Path
	 * @var string
	 */
	protected $mail_return_path = '';

	/**
	 * 管理者メールの送信元
	 * @var string
	 */
	protected $admin_mail_from = '';

	/**
	 * 管理者メールの送信者
	 * @var string
	 */
	protected $admin_mail_sender = '';

	/**
	 * 管理者メールの本文
	 * @var string
	 */
	protected $admin_mail_content = '';

	/**
	 * akismet送信者の対象とするフォームフィールドのname属性
	 * @var string
	 */
	protected $akismet_author = '';

	/**
	 * akismetメールアドレスの対象とするフォームフィールドのname属性
	 * @var string
	 */
	protected $akismet_author_email = '';

	/**
	 * akismet url の対象とするフォームフィールドのname属性
	 * @var string
	 */
	protected $akismet_author_url = '';

	/**
	 * 完了画面メッセージ
	 * @var string
	 */
	protected $complete_message = '';

	/**
	 * 入力画面URL
	 * @var string
	 */
	protected $input_url = '';

	/**
	 * 確認画面URL
	 * @var string
	 */
	protected $confirmation_url = '';

	/**
	 * 完了画面URL
	 * @var string
	 */
	protected $complete_url = '';

	/**
	 * バリデーションエラー画面URL
	 * @var string
	 */
	protected $validation_error_url = '';

	/**
	 * フォームに設定されたバリデーションルールの配列
	 * @var array
	 */
	protected $validation = array();

	/**
	 * フォームに設定されたスタイル
	 * @var string
	 */
	protected $style = '';

	/**
	 * 入力画面以外を表示したときにフォームの位置までスクロールするかどうか
	 * @var false|1
	 */
	protected $scroll = false;

	/**
	 * __construct
	 *
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
	 * 属性の取得
	 *
	 * @param string $key
	 * @return mixed|null
	 */
	public function get( $key ) {
		if ( isset( $this->$key ) ) {
			return $this->$key;
		}
	}

	/**
	 * 属性をセット
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function set( $key, $value ) {
		if ( isset( $this->$key ) ) {
			$this->$key = $value;
		}
	}

	/**
	 * 属性をセット
	 *
	 * @param array $values
	 */
	public function sets( array $values ) {
		foreach ( $values as $key => $value ) {
			$this->set( $key, $value );
		}
	}

	/**
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
	 * 全ての設定（投稿）を取得
	 *
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
	 * 問い合わせ番号を取得
	 *
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
	 * 問い合わせ番号を更新
	 *
	 * @param null|int $count 指定があればそれに更新
	 */
	public function update_tracking_number( $count = null ) {
		$new_tracking_number = null;
		if ( is_null( $count ) ) {
			$tracking_number     = $this->get_tracking_number();
			$new_tracking_number = $tracking_number + 1;
		} elseif ( MWF_Functions::is_numeric( $count ) ) {
			$new_tracking_number = $count;
		}
		if ( !is_null( $new_tracking_number ) ) {
			update_post_meta( $this->post_id, MWF_Config::TRACKINGNUMBER, $new_tracking_number );
		}
	}
}
