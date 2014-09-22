<?php
/**
 * Plugin Name: MW WP Form
 * Plugin URI: http://plugins.2inc.org/mw-wp-form/
 * Description: MW WP Form can create mail form with a confirmation screen.
 * Version: 1.9.4
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : September 25, 2012
 * Modified: September 22, 2014
 * Text Domain: mw-wp-form
 * Domain Path: /languages/
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
include_once( plugin_dir_path( __FILE__ ) . 'system/mwf_functions.php' );
include_once( plugin_dir_path( __FILE__ ) . 'system/mwf_config.php' );
$mw_wp_form = new mw_wp_form();
class mw_wp_form {

	protected $key;
	protected $input;
	protected $confirm;
	protected $complete;
	protected $validation_error;
	protected $Data;
	protected $Form;
	protected $Validation;
	protected $Error;
	protected $File;
	protected $viewFlg = 'input';
	protected $MW_WP_Form_Admin_Page;
	protected $options_by_formkey;
	protected $insert_id;
	private $validation_rules = array(
		'akismet_check' => '',
		'noempty' => '',
		'required' => '',
		'numeric' => '',
		'alpha' => '',
		'alphanumeric' => '',
		'katakana' => '',
		'hiragana' => '',
		'zip' => '',
		'tel' => '',
		'mail' => '',
		'date' => '',
		'url' => '',
		'eq' => '',
		'between' => '',
		'minlength' => '',
		'filetype' => '',
		'filesize' => '',
	);
	private $defaults = array(
		'mail_subject' => '',
		'mail_from' => '',
		'mail_sender' => '',
		'mail_content' => '',
		'automatic_reply_email' => '',
		'mail_to' => '',
		'mail_cc' => '',
		'mail_bcc' => '',
		'admin_mail_subject' => '',
		'admin_mail_from' => '',
		'admin_mail_sender' => '',
		'admin_mail_content' => '',
		'querystring' => null,
		'usedb' => null,
		'akismet_author' => '',
		'akismet_author_email' => '',
		'akismet_author_url' => '',
		'complete_message' => '',
		'input_url' => '',
		'confirmation_url' => '',
		'complete_url' => '',
		'validation_error_url' => '',
		'validation' => array(),
		'style' => '',
	);

	/**
	 * __construct
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'load_init_files' ), 9 );
		add_action( 'plugins_loaded', array( $this, 'init' ), 11 );
		// 有効化した時の処理
		register_activation_hook( __FILE__, array( __CLASS__, 'activation' ) );
		// アンインストールした時の処理
		register_uninstall_hook( __FILE__, array( __CLASS__, 'uninstall' ) );
	}

	/**
	 * load_init_files
	 * init に必要なファイルをロード
	 */
	public function load_init_files() {
		include_once( plugin_dir_path( __FILE__ ) . 'system/mw_wp_form_admin_page.php' );
		include_once( plugin_dir_path( __FILE__ ) . 'system/mw_wp_form_contact_data_page.php' );
		include_once( plugin_dir_path( __FILE__ ) . 'system/mw_session.php' );
		include_once( plugin_dir_path( __FILE__ ) . 'system/mw_wp_form_data.php' );
		include_once( plugin_dir_path( __FILE__ ) . 'system/mw_validation_rule.php' );
		include_once( plugin_dir_path( __FILE__ ) . 'system/mw_form_field.php' );
		include_once( plugin_dir_path( __FILE__ ) . 'system/mw_wp_form_chart_page.php' );
	}

	/**
	 * init
	 * ファイルの読み込み等
	 */
	public function init() {
		load_plugin_textdomain( MWF_Config::DOMAIN, false, basename( dirname( __FILE__ ) ) . '/languages' );

		// 管理画面の実行
		$this->MW_WP_Form_Admin_Page = new MW_WP_Form_Admin_Page();
		$MW_WP_Form_Contact_Data_Page = new MW_WP_Form_Contact_Data_Page();
		$MW_WP_Form_Chart_Page = new MW_WP_Form_Chart_Page();
		add_action( 'init', array( $this, 'register_post_type' ) );

		// フォームフィールドの読み込み、インスタンス化
		foreach ( glob( plugin_dir_path( __FILE__ ) . 'form_fields/*.php' ) as $form_field ) {
			include_once $form_field;
			$className = basename( $form_field, '.php' );
			if ( class_exists( $className ) ) {
				new $className();
			}
		}

		// バリデーションルールの読み込み、インスタンス化
		$validation_rules = $this->validation_rules;
		foreach ( glob( plugin_dir_path( __FILE__ ) . 'validation_rules/*.php' ) as $validation_rule ) {
			include_once $validation_rule;
			$className = basename( $validation_rule, '.php' );
			if ( class_exists( $className ) ) {
				$instance = new $className( $this->key );
				$validation_rules[$instance->getName()] = $instance;
			}
		}
		$validation_rules = apply_filters( 'mwform_validation_rules', $validation_rules, $this->key );
		foreach ( $validation_rules as $validation_name => $instance ) {
			if ( is_callable( array( $instance, 'admin' ) ) ) {
				$this->MW_WP_Form_Admin_Page->add_validation_rule( $instance->getName(), $instance );
			}
		}
		$this->validation_rules = $validation_rules;

		if ( is_admin() ) return;

		include_once( plugin_dir_path( __FILE__ ) . 'system/mw_akismet.php' );
		include_once( plugin_dir_path( __FILE__ ) . 'system/mw_error.php' );
		include_once( plugin_dir_path( __FILE__ ) . 'system/mw_form.php' );
		include_once( plugin_dir_path( __FILE__ ) . 'system/mw_mail.php' );
		include_once( plugin_dir_path( __FILE__ ) . 'system/mw_validation.php' );
		include_once( plugin_dir_path( __FILE__ ) . 'system/mw_wp_form_file.php' );
		add_filter( 'nocache_headers' , array( $this, 'nocache_headers' ) , 1 );
		add_filter( 'template_include', array( $this, 'main' ), 10000 );
		add_action( 'parse_request', array( $this, 'remove_query_vars_from_post' ) );
	}

	/**
	 * nocache_headers
	 * Nginx Cache Controller用
	 * @param array $headers
	 * @return array $headers
	 */
	public function nocache_headers( $headers ) {
		$headers['X-Accel-Expires'] = 0;
		return $headers;
	}

	/**
	 * remove_query_vars_from_post
	 * WordPressへのリクエストに含まれている、$_POSTの値を削除
	 */
	public function remove_query_vars_from_post( $query ) {
		if ( strtolower( $_SERVER['REQUEST_METHOD'] ) === 'post' && isset( $_POST['token'] ) ) {
			foreach ( $_POST as $key => $value ) {
				if ( $key == 'token' )
					continue;
				if ( isset( $query->query_vars[$key] ) && $query->query_vars[$key] === $value && !empty( $value ) ) {
					$query->query_vars[$key] = '';
				}
			}
		}
	}

	/**
	 * activation
	 * 有効化した時の処理
	 */
	public static function activation() {
	}

	/**
	 * uninstall
	 * アンインストールした時の処理
	 */
	public static function uninstall() {
		$forms = get_posts( array(
			'post_type' => MWF_Config::NAME,
			'posts_per_page' => -1,
		) );

		$data_post_ids = array();
		foreach ( $forms as $form ) {
			$data_post_ids[] = $form->ID;
			wp_delete_post( $form->ID, true );
		}

		foreach ( $data_post_ids as $data_post_id ) {
			delete_option( MWF_Config::NAME . '-chart-' . $data_post_id );
			$data_posts = get_posts( array(
				'post_type' => MWF_Config::DBDATA . $data_post_id,
				'posts_per_page' => -1,
			) );
			if ( empty( $data_posts ) ) continue;
			foreach ( $data_posts as $data_post ) {
				wp_delete_post( $data_post->ID, true );
			}
		}

		include_once( plugin_dir_path( __FILE__ ) . 'system/mw_wp_form_file.php' );
		$File = new MW_WP_Form_File();
		$File->removeTempDir();

		delete_option( MWF_Config::NAME );
	}

	/**
	 * register_post_type
	 * 管理画面（カスタム投稿タイプ）の設定
	 */
	public function register_post_type() {
		$this->MW_WP_Form_Admin_Page->register_post_type();
	}

	/**
	 * original_style
	 * CSS適用
	 */
	public function original_style() {
		$url = plugin_dir_url( __FILE__ );
		wp_register_style( MWF_Config::NAME, $url . 'css/style.css' );
		wp_enqueue_style( MWF_Config::NAME );

		$style = $this->options_by_formkey['style'];
		$styles = apply_filters( 'mwform_styles', array() );
		if ( is_array( $styles ) && isset( $styles[$style] ) ) {
			$css = $styles[$style];
			wp_register_style( MWF_Config::NAME . '_style', $css );
			wp_enqueue_style( MWF_Config::NAME . '_style' );
		}
	}

	/**
	 * original_script
	 * JS適用
	 */
	public function original_script() {
		$url = plugin_dir_url( __FILE__ );
		wp_register_script( MWF_Config::NAME, $url . 'js/form.js', array( 'jquery' ), false, true );
		wp_enqueue_script( MWF_Config::NAME );
	}

	/**
	 * get_shortcode
	 * MW WP Form のショートコードが含まれていればそのショートコードを返す
	 * @param string $content
	 * @return string $_shortcode
	 */
	private function get_shortcode( $content ) {
		preg_match_all( '/' . get_shortcode_regex() . '/s', $content, $matches, PREG_SET_ORDER );
		if ( $matches ) {
			foreach ( $matches as $shortcode ) {
				if ( in_array( $shortcode[2], array( 'mwform', 'mwform_formkey' ) ) ) {
					return $shortcode;
				} else {
					$_shortcode = $this->get_shortcode( $shortcode[5] );
					if ( $_shortcode ) {
						return $_shortcode;
					}
				}
			}
		}
	}

	/**
	 * main
	 * 表示画面でのプラグインの処理等。
	 * @param string $template
	 * @return string $template
	 */
	public function main( $template ) {
		global $post;

		// URL設定を取得
		add_shortcode( 'mwform', array( $this, '_meta_mwform' ) );
		// formkeyでのフォーム生成の場合はそれをもとに設定を取得
		add_shortcode( 'mwform_formkey', array( $this, '_meta_mwform_formkey' ) );

		if ( is_singular() && !empty( $post->ID ) ) {
			$shortcode = $this->get_shortcode( $post->post_content );
		}
		if ( empty( $shortcode ) && !( defined( 'MWFORM_NOT_USE_TEMPLATE' ) && MWFORM_NOT_USE_TEMPLATE === true ) ) {
			$template_data = @file_get_contents( $template );
			$shortcode = $this->get_shortcode( $template_data );
		}
		if ( is_array( $shortcode ) && !empty( $shortcode[0] ) ) {
			do_shortcode( $shortcode[0] );
		}
		remove_shortcode( 'mwform' );
		remove_shortcode( 'mwform_formkey' );

		// フォームが定義されていない場合は終了
		if ( is_null( $this->key ) ||
			 is_null( $this->input ) ||
			 is_null( $this->confirm ) ||
			 is_null( $this->complete ) ||
			 is_null( $this->validation_error ) )
			return $template;

		nocache_headers();

		// セッション初期化
		$this->Data = MW_WP_Form_Data::getInstance( $this->key );
		// $_POSTがあるときは$_POST
		if ( !empty( $_POST ) ) {
			$this->Data->setValues( stripslashes_deep( $_POST ) );
		}

		// $_FILESがあるときは$this->dataに統合
		$files = array();
		foreach ( $_FILES as $key => $file ) {
			if ( !isset( $_POST[$key] ) || !empty( $file['name'] ) ) {
				if ( $file['error'] == UPLOAD_ERR_OK && is_uploaded_file( $file['tmp_name'] ) ) {
					$this->Data->setValue( $key, $file['name'] );
				} else {
					$this->Data->setValue( $key, '' );
				}
				if ( !empty( $file['name'] ) ) {
					$files[$key] = $file;
				}
			}
		}
		// この条件判定がないと fileSize チェックが正しく動作しない
		if ( $files ) {
			$this->Data->setValue( MWF_Config::UPLOAD_FILES, $files );
		}

		// フォームオブジェクト生成
		$this->Form = new MW_Form( $this->key );

		// バリデーションオブジェクト生成
		$this->Validation = new MW_Validation( $this->key );
		foreach ( $this->validation_rules as $validation_name => $instance ) {
			if ( is_callable( array( $instance, 'rule' ) ) ) {
				$this->Validation->add_validation_rule( $instance->getName(), $instance );
			}
		}
		// バリデーション実行（Validation->dataに値がないと$Errorは返さない（true））
		$this->apply_filters_mwform_validation();

		// ファイル操作オブジェクト生成
		$this->File = new MW_WP_Form_File();

		// 入力画面（戻る）のとき
		if ( $this->Form->isBack() ) {
			$this->redirect( $this->input );
		}
		// 確認画面のとき
		elseif ( $this->Form->isConfirm() ) {
			$this->fileUpload();
			if ( $this->Validation->check() ) {
				$this->viewFlg = 'confirm';
				$this->redirect( $this->confirm );
			} else {
				if ( !empty( $this->validation_error ) ) {
					$this->redirect( $this->validation_error );
				} else {
					$this->redirect( $this->input );
				}
			}
		}
		// 完了画面のとき
		elseif ( $this->Form->isComplete() ) {
			$this->fileUpload();
			if ( $this->Validation->check() ) {
				$this->viewFlg = 'complete';

				if ( $this->Data->getValue( $this->Form->getTokenName() ) ) {
					$this->apply_filters_mwform_mail();
					$this->Data->clearValue( $this->Form->getTokenName() );

					// 手動フォーム対応
					$REQUEST_URI = $this->parse_url( $this->get_request_uri() );
					$input = $this->parse_url( $this->input );
					$complete = $this->parse_url( $this->complete );
					if ( !$this->options_by_formkey && $REQUEST_URI !== $complete && $input !== $complete ) {
						$this->Data->clearValues();
					}
				}

				$this->redirect( $this->complete );
			} else {
				if ( !empty( $this->validation_error ) ) {
					$this->redirect( $this->validation_error );
				} else {
					$this->redirect( $this->input );
				}
			}
		} else {
			// 完了 or 確認画面 or エラーURLが設定済みで
			// 完了 or 確認画面 or エラーに直接アクセスした場合、
			// 入力画面に戻れれば戻る。戻れない場合はトップに戻す
			$REQUEST_URI = $this->parse_url( $this->get_request_uri() );
			$check_urls = array(
				$this->confirm,
				$this->complete,
			);
			$back_url = ( $this->input ) ? $this->input : home_url();
			foreach ( $check_urls as $check_url ) {
				if ( $REQUEST_URI === $check_url ) {
					$this->Data->clearValues();
					$this->redirect( $back_url );
				}
			}
			$this->redirect( $this->input );

			if ( $this->Validation->check() && $REQUEST_URI == $this->validation_error ) {
				$this->Data->clearValues();
				$this->redirect( $back_url );
			}
		}
		add_shortcode( 'mwform_formkey', array( $this, '_mwform_formkey' ) );
		add_shortcode( 'mwform', array( $this, '_mwform' ) );
		add_shortcode( 'mwform_complete_message', array( $this, '_mwform_complete_message' ) );
		add_action( 'wp_footer', array( $this->Data, 'clearValues' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'original_style' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'original_script' ) );
		return $template;
	}

	/**
	 * _meta_mwform
	 * [mwform〜]を解析し、プロパティを設定
	 * @param array $atts
	 */
	public function _meta_mwform( $atts ) {
		$atts = shortcode_atts( array(
			'input' => '',
			'confirm' => '',
			'complete' => '',
			'validation_error' => '',
			'key' => 'mwform'
		), $atts );
		$this->key = $atts['key'];
		$this->input = $this->parse_url( $atts['input'] );
		if ( $atts['confirm'] ) {
			$this->confirm = $this->parse_url( $atts['confirm'] );
		} else {
			$this->confirm = $this->parse_url( $atts['confirm'] );
		}
		$this->complete = $this->parse_url( $atts['complete'] );
		$this->validation_error = $this->parse_url( $atts['validation_error'] );
	}

	/**
	 * _meta_mwform_formkey
	 * formkeyをもとにフォームの設定を取得
	 */
	public function _meta_mwform_formkey( $atts ) {
		global $post;
		$atts = shortcode_atts( array(
			'key' => ''
		), $atts );
		$post = get_post( $atts['key'] );
		if ( !empty( $post ) ) {
			setup_postdata( $post );
			if ( get_post_type() === MWF_Config::NAME ) {
				$this->options_by_formkey = array_merge(
					$this->defaults,
					( array )get_post_meta( $post->ID, MWF_Config::NAME, true )
				);
				$this->options_by_formkey['post_id'] = $post->ID;
				$this->key = MWF_Config::NAME . '-' . $atts['key'];
				$this->input = $this->parse_url( $this->options_by_formkey['input_url'] );
				$this->confirm = $this->parse_url( $this->options_by_formkey['confirmation_url'] );
				$this->complete = $this->parse_url( $this->options_by_formkey['complete_url'] );
				$this->validation_error = $this->parse_url( $this->options_by_formkey['validation_error_url'] );
			}
		}
		wp_reset_postdata();
	}

	/**
	 * apply_filters_mwform_validation
	 * バリデーション用フィルタ。フィルタの実行結果としてValidationオブジェクトが返ってこなければエラー
	 * 各バリデーションメソッドの詳細は /system/mw_validation.php を参照
	 */
	protected function apply_filters_mwform_validation() {
		$filterName = 'mwform_validation_' . $this->key;

		if ( $this->options_by_formkey ) {
			foreach ( $this->options_by_formkey['validation'] as $validation ) {
				foreach ( $validation as $key => $value ) {
					if ( $key == 'target' ) continue;
					if ( is_array( $value ) ) {
						$this->Validation->setRule( $validation['target'], $key, $value );
					} else {
						$this->Validation->setRule( $validation['target'], $key );
					}
				}
			}
		}

		$Akismet = new MW_Akismet();
		$akismet_check = $Akismet->check(
			$this->options_by_formkey['akismet_author'],
			$this->options_by_formkey['akismet_author_email'],
			$this->options_by_formkey['akismet_author_url'],
			$this->Data
		);
		if ( $akismet_check ) {
			$this->Validation->setRule( MWF_Config::AKISMET, 'akismet_check' );
		}

		$this->Validation = apply_filters( $filterName, $this->Validation, $this->Data->getValues() );
		if ( !is_a( $this->Validation, 'MW_Validation' ) ) {
			exit( esc_html__( 'Validation Object is not a MW Validation Class.', MWF_Config::DOMAIN ) );
		}
	}

	/**
	 * apply_filters_mwform_mail
	 * メール送信フィルター
	 */
	protected function apply_filters_mwform_mail() {
		$Mail = new MW_Mail();
		$Mail_raw = clone $Mail;

		if ( $this->options_by_formkey ) {
			$Mail_raw = $this->set_admin_mail_raw_params( $Mail_raw );

			// 添付ファイルのデータをためた配列を作成
			$attachments = array();
			// $Mail->attachments を設定（メールにファイルを添付）
			$upload_file_keys = $this->Data->getValue( MWF_Config::UPLOAD_FILE_KEYS );
			if ( $upload_file_keys !== null && is_array( $upload_file_keys ) ) {
				$wp_upload_dir = wp_upload_dir();
				foreach ( $upload_file_keys as $key ) {
					$upload_file_url = $this->Data->getValue( $key );
					if ( !$upload_file_url )
						continue;
					$filepath = MWF_Functions::fileurl_to_path( $upload_file_url );
					if ( file_exists( $filepath ) ) {
						$filepath = $this->File->moveTempFileToUploadDir( $filepath );
						$new_upload_file_url = MWF_Functions::filepath_to_url( $filepath );
						$attachments[$key] = $filepath;
						$this->Data->setValue( $key, $new_upload_file_url );
					}
				}
				$Mail_raw->attachments = $attachments;
			}

			$filter_name = 'mwform_admin_mail_raw_' . $this->key;
			$Mail_raw = apply_filters( $filter_name, $Mail_raw, $this->Data->getValues() );
			if ( !is_a( $Mail_raw, 'MW_Mail' ) )
				return;

			$Mail = $this->parse_mail_object( $Mail_raw );
			$Mail = $this->set_admin_mail_reaquire_params( $Mail );
		}

		$filter_name = 'mwform_mail_' . $this->key;
		$Mail = apply_filters( $filter_name, $Mail, $this->Data->getValues() );

		if ( $this->options_by_formkey && is_a( $Mail, 'MW_Mail' ) && is_a( $Mail_raw, 'MW_Mail' ) ) {

			// メール送信前にファイルのリネームをしないと、tempファイル名をメールで送信してしまう。
			if ( !empty( $this->options_by_formkey['usedb'] ) ) {
				// save_mail_body で登録されないように
				foreach ( $attachments as $key => $filepath ) {
					$this->Data->clearValue( $key );
				}

				// $this->insert_id を設定 ( save_mail_body で 使用 )
				$this->insert_id = wp_insert_post( array(
					'post_title' => $Mail->subject,
					'post_status' => 'publish',
					'post_type' => MWF_Config::DBDATA . $this->options_by_formkey['post_id'],
				) );
				// 保存
				$this->save_mail_body( $Mail_raw->body );

				// 添付ファイルをメディアに保存
				if ( !empty( $this->insert_id ) ) {
					$this->File->saveAttachmentsInMedia(
						$this->insert_id,
						$attachments,
						$this->options_by_formkey['post_id']
					);
				}
			}

			$filter_name = 'mwform_admin_mail_' . $this->key;
			$Mail = apply_filters( $filter_name, $Mail, $this->Data->getValues() );
			if ( !is_a( $Mail, 'MW_Mail' ) )
				return;
			$Mail->send();

			// DB非保存時は管理者メール送信後、ファイルを削除
			if ( empty( $this->options_by_formkey['usedb'] ) ) {
				foreach ( $attachments as $filepath ) {
					if ( file_exists( $filepath ) )
						unlink( $filepath );
				}
			}

			if ( isset( $this->options_by_formkey['automatic_reply_email'] ) ) {
				$automatic_reply_email = $this->Data->getValue( $this->options_by_formkey['automatic_reply_email'] );
				if ( $automatic_reply_email && !$this->validation_rules['mail']->rule( $automatic_reply_email ) ) {
					$Mail_raw = $this->set_reply_mail_raw_params( $Mail_raw );

					// 自動返信メールからは添付ファイルを削除
					$Mail_raw->attachments = array();

					$filter_name = 'mwform_auto_mail_raw_' . $this->key;
					$Mail_raw = apply_filters( $filter_name, $Mail_raw, $this->Data->getValues() );
					if ( !is_a( $Mail_raw, 'MW_Mail' ) )
						return;

					$Mail = $this->parse_mail_object( $Mail_raw );
					$Mail = $this->set_reply_mail_reaquire_params( $Mail );

					$filter_name = 'mwform_auto_mail_' . $this->key;
					$Mail = apply_filters( $filter_name, $Mail, $this->Data->getValues() );
					if ( !is_a( $Mail, 'MW_Mail' ) )
						return;
					$Mail->send();
				}
			}
		}
	}

	/**
	 * parse_mail_object
	 * @param MW_Mail $obj
	 * @return MW_Mail $parsed_obj
	 */
	private function parse_mail_object( MW_Mail $obj ) {
		$parsed_obj = clone $obj;
		$parsed_obj_vars = get_object_vars( $parsed_obj );
		foreach ( $parsed_obj_vars as $key => $value ) {
			if ( is_array( $value ) || $key == 'to' || $key == 'cc' || $key == 'bcc' )
				continue;
			$value = $this->parse_mail_content( $value );
			$parsed_obj->$key = $value;
		}
		return $parsed_obj;
	}

	/**
	 * parse_mail_content
	 * メール本文用に {name属性} を置換
	 * @param string $value
	 * @return string
	 */
	public function parse_mail_content( $value ) {
		return preg_replace_callback(
			'/{(.+?)}/',
			array( $this, '_parse_mail_content' ),
			$value
		);
	}
	public function _parse_mail_content( $matches ) {
		return $this->parse_mail_body( $matches, false );
	}

	/**
	 * save_mail_body
	 * DB保存用に {name属性} を置換、保存
	 */
	public function save_mail_body( $value ) {
		return preg_replace_callback(
			'/{(.+?)}/',
			array( $this, '_save_mail_body' ),
			$value
		);
	}
	public function _save_mail_body( $matches ) {
		return $this->parse_mail_body( $matches, true );
	}

	/**
	 * parse_mail_body
	 * $this->create_mail_body(), $this->save_mail_body の本体
	 * 第2引数でDB保存するか判定
	 */
	protected function parse_mail_body( $matches, $doUpdate = false ) {
		$value = $this->Data->get( $matches[1] );
		if ( $value !== null && $doUpdate ) {
			update_post_meta( $this->insert_id, $matches[1], $value );
		}
		return $value;
	}

	/**
	 * redirect
	 * 現在のURLと引数で渡されたリダイレクトURLが同じであればリダイレクトしない
	 * @param string リダイレクトURL
	 */
	private function redirect( $url ) {
		$redirect = ( empty( $url ) ) ? $this->get_request_uri() : $url;
		$redirect = $this->parse_url( $redirect );
		$REQUEST_URI = $this->parse_url( $this->get_request_uri() );
		if ( !empty( $_POST ) || $redirect != $REQUEST_URI ) {
			$redirect = wp_sanitize_redirect( $redirect );
			$redirect = wp_validate_redirect( $redirect, home_url() );
			wp_redirect( $redirect );
			exit();
		}
	}

	/**
	 * get_request_uri
	 * $_SERVER['REQUEST_URI'] を http:// からはじまるURLに変換する
	 * @return string URL
	 */
	protected function get_request_uri() {
		$_REQUEST_URI = $_SERVER['REQUEST_URI'];
		if ( !preg_match( '/^https?:\/\//', $_REQUEST_URI ) ) {
			$REQUEST_URI = home_url() . $_REQUEST_URI;
			$parse_url = parse_url( home_url() );
			// サブディレクトリ型の場合
			if ( !empty( $parse_url['path'] ) ) {
				$pettern = preg_quote( $parse_url['path'], '/' );
				if ( preg_match( '/^' . $pettern . '/', $_REQUEST_URI ) ) {
					$REQUEST_URI = preg_replace( '/' . $pettern . '$/', $_REQUEST_URI, home_url() );
				}
			}
		} else {
			$REQUEST_URI = $_REQUEST_URI;
		}
		return $REQUEST_URI;
	}

	/**
	 * parse_url
	 * http:// からはじまるURLに変換する
	 * @param string URL
	 * @return string URL
	 */
	protected function parse_url( $url ) {
		if ( empty( $url ) )
			return '';

		$query_string = array();
		preg_match( '/\?(.*)$/', $url, $reg );
		if ( !empty( $reg[1] ) ) {
			$url = str_replace( '?', '', $url );
			$url = str_replace( $reg[1], '', $url );
			parse_str( $reg[1], $query_string );
		}
		if ( !preg_match( '/^https?:\/\//', $url ) ) {
			$home_url = home_url();
			$url = $home_url . $url;
		}
		$url = preg_replace( '/([^:])\/+/', '$1/', $url );

		// URL設定でURL引数が使用されている場合はそれを使う。
		// 「URL引数を有効にする」が有効の場合は $_GET を利用する（重複するURL引数はURL設定のものが優先される ※post_id除く）
		if ( !empty( $this->options_by_formkey['querystring'] ) ) {
			$query_string = array_merge( $_GET, $query_string );
			if ( isset( $_GET['post_id'] ) && MWF_Functions::is_numeric( $_GET['post_id'] ) ) {
				$query_string['post_id'] = $_GET['post_id'];
			}
		}

		if ( !empty( $query_string ) )
			$url = $url . '?' . http_build_query( $query_string, null, '&' );
		return $url;
	}

	/**
	 * _mwform_formkey
	 * 管理画面で作成したフォームを出力（実際の出力は _mwform ）
	 * @example
	 * 		[mwform_formkey key="post_id"]
	 */
	public function _mwform_formkey( $atts ) {
		global $post;
		$atts = shortcode_atts( array(
			'key' => ''
		), $atts );
		$post = get_post( $atts['key'] );
		setup_postdata( $post );

		// 入力画面・確認画面
		if ( $this->viewFlg == 'input' || $this->viewFlg == 'confirm' ) {
			$content = get_the_content();
			if ( has_filter( 'the_content', 'wpautop' ) ) {
				$content = wpautop( $content );
			}
			$_ret ='[mwform]' . $content . '[/mwform]';
		}
		// 完了画面
		elseif( $this->viewFlg == 'complete' ) {
			$content = $this->options_by_formkey['complete_message'];
			if ( has_filter( 'the_content', 'wpautop' ) ) {
				$content = wpautop( $content );
			}
			$_ret = '[mwform_complete_message]' . $content . '[/mwform_complete_message]';
		}
		wp_reset_postdata();
		$_ret = do_shortcode( $_ret );
		return $_ret;
	}

	/**
	 * _mwform
	 * フォームを出力
	 */
	public function _mwform( $atts, $content = '' ) {
		if ( $this->viewFlg == 'input' || $this->viewFlg == 'confirm' ) {
			$this->Error = $this->Validation->Error();
			do_action( 'mwform_add_shortcode', $this->Form, $this->viewFlg, $this->Error, $this->key );

			// ユーザー情報取得
			$content = $this->replace_user_property( $content );

			// 投稿情報取得
			if ( isset( $this->options_by_formkey['querystring'] ) )
				$querystring = $this->options_by_formkey['querystring'];
			if ( !empty( $querystring ) ) {
				$content = preg_replace_callback( '/{(.+?)}/', array( $this, 'get_post_property_from_querystring' ), $content );
			} else {
				$content = preg_replace_callback( '/{(.+?)}/', array( $this, 'get_post_property_from_this' ), $content );
			}

			$upload_file_keys = $this->Form->getValue( MWF_Config::UPLOAD_FILE_KEYS );
			$upload_file_hidden = '';
			if ( is_array( $upload_file_keys ) ) {
				foreach ( $upload_file_keys as $value ) {
					$upload_file_hidden .= $this->Form->hidden( MWF_Config::UPLOAD_FILE_KEYS . '[]', $value );
				}
			}
			$_preview_class = ( $this->viewFlg === 'confirm' ) ? ' mw_wp_form_preview' : '';
			return
				'<div id="mw_wp_form_' . $this->key . '" class="mw_wp_form mw_wp_form_' . $this->viewFlg . $_preview_class . '">' .
				$this->Form->start() .
				do_shortcode( $content ) .
				$upload_file_hidden .
				$this->Form->end() .
				'<!-- end .mw_wp_form --></div>';
		}
	}

	/**
	 * replace_user_property
	 * ユーザーがログイン中の場合、{ユーザー情報のプロパティ}を置換する。
	 * @param string フォーム内容
	 * @return string フォーム内容
	 */
	protected function replace_user_property( $content ) {
		$user = wp_get_current_user();
		$search = array(
			'{user_id}',
			'{user_login}',
			'{user_email}',
			'{user_url}',
			'{user_registered}',
			'{display_name}',
		);
		if ( !empty( $user ) ) {
			$content = str_replace( $search, array(
				$user->get( 'ID' ),
				$user->get( 'user_login' ),
				$user->get( 'user_email' ),
				$user->get( 'user_url' ),
				$user->get( 'user_registered' ),
				$user->get( 'display_name' ),
			), $content );
		} else {
			$content = str_replace( $search, '', $content );
		}
		return $content;
	}

	/**
	 * get_post_property_from_querystring
	 * 引数 post_id が有効の場合、投稿情報を取得するために preg_replace_callback から呼び出される。
	 * @param array $matches
	 * @return string
	 */
	public function get_post_property_from_querystring( $matches ) {
		if ( isset( $this->options_by_formkey['querystring'] ) )
			$querystring = $this->options_by_formkey['querystring'];
		if ( !empty( $querystring ) && isset( $_GET['post_id'] ) && MWF_Functions::is_numeric( $_GET['post_id'] ) ) {
			$_post = get_post( $_GET['post_id'] );
			if ( empty( $_post->ID ) )
				return;
			if ( isset( $_post->$matches[1] ) ) {
				return $_post->$matches[1];
			} else {
				// post_meta の処理
				$pm = get_post_meta( $_post->ID, $matches[1], true );
				if ( !empty( $pm ) )
					return $pm;
			}
		}
		return;
	}

	/**
	 * get_post_property_from_this
	 * 引数 post_id が無効の場合、投稿情報を取得するために preg_replace_callback から呼び出される。
	 * @param array $matches
	 * @return string
	 */
	public function get_post_property_from_this( $matches ) {
		global $post;
		if ( !is_singular() )
			return;
		$post_id = get_the_ID();
		if ( isset( $post->ID ) && MWF_Functions::is_numeric( $post->ID ) ) {
			if ( isset( $post->$matches[1] ) ) {
				return $post->$matches[1];
			} else {
				// post_meta の処理
				$pm = get_post_meta( $post->ID, $matches[1], true );
				if ( !empty( $pm ) )
					return $pm;
			}
		}
		return;
	}

	/**
	 * _mwform_complete_message
	 * 完了後のメッセージ。
	 */
	public function _mwform_complete_message( $atts, $content = '' ) {
		if ( $this->viewFlg == 'complete' ) {
			return $content;
		}
	}

	/**
	 * fileupload
	 * ファイルアップロード処理。実際のアップロード状況に合わせてフォームデータも再生成する。
	 */
	protected function fileupload() {
		$uploadedFiles = array();
		$files = $this->Data->getValue( MWF_Config::UPLOAD_FILES );
		if ( !is_array( $files ) ) {
			$files = array();
		}
		foreach ( $files as $key => $file ) {
			if ( $this->Validation->singleCheck( $key ) ) {
				$uploadedFile = $this->File->singleFileupload( $key );
				if ( $uploadedFile ) {
					$uploadedFiles[$key] = $uploadedFile;
				}
			}
		}

		// 時間切れなどで削除されたファイルのキーを削除
		$upload_file_keys = $this->Data->getValue( MWF_Config::UPLOAD_FILE_KEYS );
		if ( !$upload_file_keys )
			$upload_file_keys = array();

		$wp_upload_dir = wp_upload_dir();
		foreach ( $upload_file_keys as $upload_file_key ) {
			$upload_file_url = $this->Data->getValue( $upload_file_key );
			if ( $upload_file_url ) {
				$filepath = MWF_Functions::fileurl_to_path( $upload_file_url );
				if ( !file_exists( $filepath ) ) {
					unset( $upload_file_keys[$upload_file_key] );
				}
			}
		}
		$this->Data->setValue( MWF_Config::UPLOAD_FILE_KEYS, $upload_file_keys );

		// アップロードに成功したファイルをフォームデータに格納
		foreach ( $uploadedFiles as $key => $uploadfile ) {
			$this->Data->setValue( $key, $uploadfile );
			if ( !in_array( $key, $upload_file_keys ) ) {
				$this->Data->pushValue( MWF_Config::UPLOAD_FILE_KEYS, $key );
			}
		}
	}

	/**
	 * set_admin_mail_reaquire_params
	 * 管理者メールに必須の項目を設定
	 * @param MW_Mail $Mail
	 * @return MW_Mail $Mail
	 */
	private function set_admin_mail_reaquire_params( MW_Mail $Mail ) {
		$admin_mail_to = get_bloginfo( 'admin_email' );
		$admin_mail_from = get_bloginfo( 'admin_email' );
		$admin_mail_sender = get_bloginfo( 'name' );

		if ( !$Mail->to ) {
			$Mail->to = $admin_mail_to;
		}
		if ( !$Mail->from ) {
			$Mail->from = $admin_mail_from;;
		}
		if ( !$Mail->sender ) {
			$Mail->sender = $admin_mail_sender;;
		}
		return $Mail;
	}

	/**
	 * set_reply_mail_reaquire_params
	 * 自動返信メールに必須の項目を設定
	 * @param MW_Mail $Mail
	 * @return MW_Mail $Mail
	 */
	private function set_reply_mail_reaquire_params( MW_Mail $Mail ) {
		$reply_mail_from = get_bloginfo( 'admin_email' );
		$reply_mail_sender = get_bloginfo( 'name' );

		if ( !$Mail->from ) {
			$Mail->from = $reply_mail_from;;
		}
		if ( !$Mail->sender ) {
			$Mail->sender = $reply_mail_sender;;
		}
		return $Mail;
	}

	/**
	 * set_admin_mail_raw_params
	 * 管理者メールに項目を設定
	 * @param MW_Mail $Mail
	 * @return MW_Mail $Mail
	 */
	private function set_admin_mail_raw_params( MW_Mail $Mail ) {
		if ( $this->options_by_formkey ) {
			// タイトルを指定
			$admin_mail_subject = $this->options_by_formkey['mail_subject'];
			if ( !empty( $this->options_by_formkey['admin_mail_subject'] ) )
				$admin_mail_subject = $this->options_by_formkey['admin_mail_subject'];
			$Mail->subject = $admin_mail_subject;

			// 本文を指定
			$admin_mail_content = $this->options_by_formkey['mail_content'];
			if ( !empty( $this->options_by_formkey['admin_mail_content'] ) )
				$admin_mail_content = $this->options_by_formkey['admin_mail_content'];
			$Mail->body = $admin_mail_content;

			// 送信先を指定
			$admin_mail_to = get_bloginfo( 'admin_email' );
			if ( !empty( $this->options_by_formkey['mail_to'] ) )
				$admin_mail_to = $this->options_by_formkey['mail_to'];
			$Mail->to = $admin_mail_to;

			// CCを指定
			$admin_mail_cc = $this->defaults['mail_cc'];
			if ( !empty( $this->options_by_formkey['mail_cc'] ) )
				$admin_mail_cc = $this->options_by_formkey['mail_cc'];
			$Mail->cc = $admin_mail_cc;

			// BCCを指定
			$admin_mail_bcc = $this->defaults['mail_bcc'];
			if ( !empty( $this->options_by_formkey['mail_bcc'] ) )
				$admin_mail_bcc = $this->options_by_formkey['mail_bcc'];
			$Mail->bcc = $admin_mail_bcc;

			// 送信元を指定
			$admin_mail_from = get_bloginfo( 'admin_email' );
			if ( !empty( $this->options_by_formkey['admin_mail_from'] ) )
				$admin_mail_from = $this->options_by_formkey['admin_mail_from'];
			$Mail->from = $admin_mail_from;

			// 送信者を指定
			$admin_mail_sender = get_bloginfo( 'name' );
			if ( !empty( $this->options_by_formkey['admin_mail_sender'] ) )
				$admin_mail_sender = $this->options_by_formkey['admin_mail_sender'];
			$Mail->sender = $admin_mail_sender;
		}
		return $Mail;
	}

	/**
	 * set_reply_mail_raw_params
	 * 自動返信メールに項目を設定
	 * @param MW_Mail $Mail
	 * @return MW_Mail $Mail
	 */
	private function set_reply_mail_raw_params( MW_Mail $Mail ) {
		$Mail->to = '';
		$Mail->cc = '';
		$Mail->bcc = '';
		if ( $this->options_by_formkey ) {
			$automatic_reply_email = $this->Data->getValue( $this->options_by_formkey['automatic_reply_email'] );
			if ( $automatic_reply_email && !$this->validation_rules['mail']->rule( $automatic_reply_email ) ) {
				// 送信先を指定
				$Mail->to = $automatic_reply_email;

				// 送信元を指定
				$reply_mail_from = get_bloginfo( 'admin_email' );
				if ( !empty( $this->options_by_formkey['mail_from'] ) )
					$reply_mail_from = $this->options_by_formkey['mail_from'];
				$Mail->from = $reply_mail_from;

				// 送信者を指定
				$reply_mail_sender = get_bloginfo( 'name' );
				if ( !empty( $this->options_by_formkey['mail_sender'] ) )
					$reply_mail_sender = $this->options_by_formkey['mail_sender'];
				$Mail->sender = $reply_mail_sender;

				// タイトルを指定
				$reply_mail_subject = $this->options_by_formkey['mail_subject'];
				$Mail->subject = $reply_mail_subject;

				// 本文を指定
				$reply_mail_content = $this->options_by_formkey['mail_content'];
				$Mail->body = $reply_mail_content;
			}
		}
		return $Mail;
	}
}
