=== MW WP Form ===
Contributors: inc2734, ryu263
Donate link: http://www.amazon.co.jp/registry/wishlist/39ANKRNSTNW40
Tags: plugin, form, confirm, preview, shortcode, mail, chart, graph
Requires at least: 3.7
Tested up to: 4.0
Stable tag: 1.9.4
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

MW WP Form can create mail form with a confirmation screen using shortcode.

== Description ==

MW WP Form can create mail form with a confirmation screen using shortcode.

* Form created using shortcodes
* Using confirmation page is possible.
* The page changes by the same URL or individual URL are possible.
* Many validation rules
* Saving contact data is possible.
* Displaying Chart using saved contact data is possible.

MW WP Form はショートコードを使って確認画面付きのメールフォームを作成することができるプラグインです。

* ショートコードを使用したフォーム生成
* 確認画面が表示可能
* 同一URL・個別URLでの画面変遷が可能
* 豊富なバリデーションルール
* 問い合わせデータを保存可能
* 保存した問い合わせデータをグラフ可能

= Official =

http://plugins.2inc.org/mw-wp-form/

= The following third-party resources =

Google Charts 
Source: https://developers.google.com/chart/

= Contributors =

* [Takashi Kitajima](http://2inc.org) ( [inc2734](http://profiles.wordpress.org/inc2734) )
* [Ryujiro Yamamoto](http://webcre-archive.com) ( [ryu263](http://profiles.wordpress.org/ryu263) )

== Installation ==

1. Upload `MW WP Form` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. You can create a form by settings page.

== Frequently Asked Questions ==

Do you have questions or issues with MW WP Form? Use these support channels appropriately.

1. [Official](http://plugins.2inc.org/mw-wp-form/)
1. [Support Forum](http://wordpress.org/support/plugin/mw-wp-form)

== Screenshots ==

1. Form creation page.
2. Form item create box. You can easily insert the form.
3. Supports saving inquiry data to database.
4. List page of inquiry data that has been saved.
5. Supports chart display of saved inquiry data.

== Changelog ==

= 1.9.4 =
* Bug fix : Fix HTML structures.
* Bug fix : Fix bug that does not display image of image field in SSL.
* Bug fix : Fix bug that does not display link of file field in SSL.

= 1.9.3 =
* Bug fix : Fix bug that would be escaped in double.
* Bug fix : Fix uninstall process.

= 1.9.2 =
* Bug fix : Fix comment in mwform_confirmButton dialogbox.
* Bug fix : Fix bug in stacking order of the dialog in WordPress 4.0.

= 1.9.1 =
* Changed : Easy to see change the form tag insertion selectbox.
* Deleted : Delete size attribute in file or image field.
* Bug fix : Fix bug that there are cases where the page chache does not disabled on Nginx.

= 1.9.0 =
* Added   : Add chart function.
* Added   : Add separator attribute in checkbox tag generator.
* Bug fix : Fix bug that can not change display option in saving contact data list page.
* Bug fix : Fix bug that display wrong number of inquiries.

= 1.8.4 =
* Bug fix : Fix bug that can not be set maxlength in mwform_text shortcode.

= 1.8.3 =
* Bug fix : Fix bug that file is not uploaded when validation is not set in the file field.

= 1.8.2 =
* Bug fix : Fix bug that PHP error is displayed in file type validation.
* Bug fix : Fix bug that PHP error is displayed in admin page.
* Changed : Change to upload file even if there is a validation error in other fields.
* Changed : Change to display by scrolling when width of list of stored data is wide.

= 1.8.1 =
* Bug fix : Fix PHP error under PHP 5.3.0
* Bug fix : Fix bug that are not validated of noEmpty in zip and tel field.
* Bug fix : Fix bug that error is displayed if the validation that was registered is disabled.

= 1.8.0 =
* Added   : Add mwform_validation_rules filter hook.
* Added   : Add API to get the data for mail.
* Added   : Add setting of response status in saved contact data.
* Added   : Add response status and memo in csv.
* Added   : Add returning link from detail of database saving data to list page.
* Changed : Form field is empty if seted null in value of shortcode.
* Changed : WordPress 3.7 higher is required.

= 1.7.2 =
* Bug fix : XSS vulnerability fix.

= 1.7.1 =
* Bug fix : Fixed a bug that MW WP Form's shortcodes doesn't parse in the nested shortcode.

= 1.7.0 =
* Added   : X-Accel-Expires param in header.
* Added   : Add CC setting in admin mail setting.
* Added   : Add BCC setting in admin mail setting.
* Added   : X-Accel-Expires param in header.
* Changed : Data store has been changed to Transient API from PHP SESSION.
* Changed : Nonce check system has been changed to WordPress nonce check system from original.
* Changed : Accept space in katakana validation.
* Changed : Accept space in hiragana validation.
* Changed : The way of isplaying attached files has been changed to URL from ID in csv file.
* Changed : Require WordPress Version 3.5
* Bug fix : Fixed a bug that sender not set at email when {sender} and {e-mail} in mail settings were conversion blank.

= 1.6.1 =
* Bug fix : Support Akismet 3.0.0
* Changed : Support psd, ai, eps file upload.

= 1.6.0 =
* Changed : Changed Redirection flow.
* Changed : If querystring setting is set and doesn't get post from querystring, return empty.
* Changed : When a URL doesn't begin http or https in URL settings, home_url() is complemented.
* Added   : {xxx} ( e.g. {post_title} ) get post property from the now post if querystring setting does't set.
* Added   : Repeat submitting came to be blocked.
* Bug fix : Fixed translation mistake in admin page.
* Bug fix : Fixed selected file reset button bug on file field.
* Bug fix : Fixed bug that not to work rightly when setting noempty validation in image or file field.

= 1.5.6 =
* Bug fix : Fix selected file reset button bug in Firefox.
* Changed : Change file field's default size attribute.

= 1.5.5 =
* Added   : Selected file reset button has been added.

= 1.5.4 =
* Bug fix : Fix spelling mistake in admin page.
* Added   : Convert full-pitch character to half character in text shortcode.

= 1.5.3 =
* Bug fix : Fixed a bug where <br> is added in textarea.

= 1.5.2 =
* Bug fix : Datapicker starting a new line.

= 1.5.1 =
* Bug fix : Fix wpautop bug.

= 1.5.0 =
* Deleted   : Delete qtags.
* Bug fix   : Fix inquiery data are not saved when admin mail content is empty.
* Bug fix   : Fix bug that doesn't start a new line.
* Added     : Add filter_hook mwform_admin_mail_raw_mw-wp-form-xxx.
* Added     : Add filter_hook mwform_auto_mail_raw_mw-wp-form-xxx.
* Deprecated: Deprecated mw_form_field::set_qtags()

= 1.4.1 =
* Changed : Change flow to read saving inquiry data.
* Bug fix : Fix tel validation.

= 1.4.0 =
* Added   : Add form tag generator.

= 1.3.3 =
* Buf fix : Fix param $rule in mwform_error_message_mw-wp-form-xxx filter hook.
* Buf fix : Fix param $rule in mwform_error_message_html filter hook.

= 1.3.2 =
* Buf fix : Fix session has already started.

= 1.3.1 =
* Added   : Support attribute id in text, textarea, radio, checkbox, select, datepicker, file, image, password shortcode.
* Added   : Support attribute placeholder in password shortcode.
* Changed : Change admin page sentence.

= 1.3.0 =
* Added   : 自動返信メール設定、管理者宛メール設定で本文の以外の項目にも{キー}を使用可能に

= 1.2.7 =
* Added   : Support docx, xlsx, pptx upload.
* Change  : Change main process hook from get_header to template_include.

= 1.2.6 =
* Added   : mwform_styles フィルターフック

= 1.2.5 =
* Added   : 管理者宛メールの複数人送信をサポート
* Added   : mwform_error_message_html フィルターフック
* Added   : mwform_error_message_wrapper フィルターフック
* Buf fix : DB保存データ一覧表示画面 Noticeエラー

= 1.2.4 =
* Bug fix : メールアドレスバリデーションのバグを修正

= 1.2.3 =
* Bug fix : ビジュアルエディタショートコードボタンがどの投稿タイプでもでてしまうバグを修正

= 1.2.2 =
* Added   : ビジュアルエディタにショートコード挿入ボタンを追加

= 1.2.1 =
* Bug fix : 管理者宛メール本文入力欄のサイズ
* Bug fix : WM_Form::zip, WM_Form::tel
* Bug fix : MW_Validation::fileType, MW_Validation::fileSize
* Bug fix : children が未指定でも mwform_choices フィルターフックの引数に空値が渡ってくるバグを修正
* Change  : jquery.ui.css のプロトコル指定、バージョンを変更
* Change  : データの持ち方を singleton に変更
* Added   : mwform_validation_xxx フィルターフックに引数を追加（$data）
* Added   : DB登録データ一覧で画像・ファイルカラムの項目は編集画面にリンク
* Added   : URLバリデーション

= 1.2.0 =
* Added   : 電話番号、郵便番号フィールドはデフォルトで全角 -> 半角置換
* Added   : mwform_error_message_識別子 フィルターフック追加
* Added   : ひらがな バリデーション項目を追加
* Added   : テンプレートでもショートコード [mwform_formkey] を実行可能に
* Added   : Support placeholder in input, textarea
* Changed : MW_Form::previewPage() -> MW_Form::confirmPage()
* Changed : [mwform_submitButton preview_value=""] -> [mwform_submitButton confirm_value=""]
* Changed : [mwform preview=""] -> [mwform confirm=""]
* Changed : [mwform_previewButton] -> [mwform_confirmButton]
* Changed : URL引数を有効にする の場合のみURL引数が利用されるように変更（URL設定で利用されているものは除く）
* Bug fix : 入力画面にpostしたときにhiddenフィールドの値がリセットされる（引き継がれない）バグを修正

= 1.1.5 =
* Bug fix : $MW_Mail->createBody()のバグ修正

= 1.1.4 =
* Changed : 設定を読み込むため際、無駄な do_shortcode() が行われないように修正
* Bug fix : チェックボックスの値が送信されないバグ修正

= 1.1.3 =
* Deprecated: div.mw_wp_form_previewは次回のバージョンアップで削除予定（div.mw_wp_form_confirmに置換）
* Deprecated: MW_Form::previewPage()は次回のバージョンアップで削除予定（MW_Form::confirmPage()に置換）
* Deprecated: [mwform_submitButton]の引数preview_valueは次回のバージョンアップで削除予定（confirm_valueに置換）
* Deprecated: [mwform]の引数previewは次回のバージョンアップで削除予定（confirmに置換）
* Deprecated: [mwform_previewButton]は次回のバージョンアップで削除予定（[mwform_confirmButton]に置換）
* Changed   : MW_Form::isPreview() -> MW_Form::isConfirm()
* Changed   : MW_Form::getPreviewButtonName() -> MW_Form::getConfirmButtonName()
* Added     : mwform_default_content フィルターフック
* Added     : mwform_default_postdata フィルターフック

= 1.1.2 =
* Cahged : セッションまわりの処理をリファクタリング

= 1.1.1 =
* Bug fix: ダウンロードしたCSVに全件表示されないバグを修正

= 1.1.0 =
* Added  : mwform_value_識別子 フィルターフック追加
* Added  : mwform_hidden の引数 echo を追加（ true or false ）
* Added  : カタカナ バリデーション項目を追加
* Cahged : 管理画面メニュー表示、設定保存の権限を変更（edit_pagesに統一）
* Bug fix: 複数のMIMEタイプをとりえる拡張子を持つファイルのアップロードに対応（avi、mp3、mpg）

= 1.0.4 =
* Bug fix: 画像以外の添付ファイルがカスタムフィールドに表示されないバグを修正
* Bug fix: 動画アップロード時にFatal Errorがでるバグを修正

= 1.0.3 =
* Added  : 管理画面に Donate link を追加

= 1.0.2 =
* Bug fix: シングルページのみ実行可能に変更（検索結果ページ等でリダイレクトしてしまうため）
* Bug fix: URL引数有効 + 同一URL時にリダイレクトループが発生してしまうバグを修正

= 1.0.1 =
* Bug fix: DBに保存しないときに添付ファイルが送られてこない

= 1.0.0 =
* Added  : Donate link を追加
* Added  : DB保存データにメモ欄追加
* Cahged : ファイルアップロード用のディレクトリにアップロードするように変更専用
* Cahged : 拡張子が偽造されたファイルの場合はアップロードしない（php5.3.0以上）
* Cahged : 表示ページのURLに引数が付いている場合でも管理画面で設定したURLにリダイレクトしてしまわないように変更
* Bug fix: 通常バリデーションは配列が来ることを想定していなかったため修正

= 0.9.11 =
* Bug fix: 添付ファイルが複数あり、かつDB保存の場合、管理画面で最後の画像しか表示されないバグを修正
* Cahged : どのフィールドが画像かを示すメタデータの保存形式を配列に変更
* Cahged : mw_form_field::inputPage、mw_form_field::previewPage の引数削除

= 0.9.10 =
* Bug fix: mwform_admin_mail_識別子、mwform_auto_mail_識別子フィルターフックの定義位置が逆だったのを修正
* Bug fix: 添付ファイルが添付されないバグを修正（From Ver0.9.4）
* Bug fix: Akismet Email、Akismet URL の設定が正しく行えなかったのを修正
* Cahged : フォーム送信時は $_POST を WP Query に含めない

= 0.9.9 =
* Added  : mwform_csv_button_識別子 フィルターフック
* Bug fix: name属性が未指定のとき、MW_Form::getZipValue, MW_Form::getCheckedValue でエラーがでるバグ修正

= 0.9.8 =
* Added  : 管理者用・自動返信用メール設定それぞれに 送信元メールアドレス・送信者名の設定を追加
* Added  : mwform_admin_mail_識別子 フィルターフック追加
* Added  : mwform_auto_mail_識別子 フィルターフック追加
* Deleted: mwform_admin_mail_from_識別子 フィルターフック
* Deleted: mwform_admin_mail_sender_識別子 フィルターフック
* Deleted: mwform_auto_mail_from_識別子 フィルターフック
* Deleted: mwform_auto_mail_sender_識別子 フィルターフック

= 0.9.7 =
* Bug fix: CSVダウンロードのバグ修正

= 0.9.6 =
* Bug fix: 電話番号のバリデーションチェックを修正
* Added  : CSVダウンロード機能追加
* Added  : mwform_admin_mail_from_識別子 フック追加
* Added  : mwform_admin_mail_sender_識別子 フック追加
* Added  : mwform_auto_mail_from_識別子 フック追加
* Added  : mwform_auto_mail_sender_識別子 フック追加

= 0.9.5 =
* Added  : バリデーションエラー時に遷移するURLを設定可能に
* Cahged : 送信メールの Return-Path に「管理者宛メール設定の送信先」が利用されるように変更
* Cahged : {投稿情報}、{ユーザー情報}の値がない場合は空値が返るように変更
* Cahged : 設定済みのバリデーションルールは閉じた状態で表示されるように変更
* Cahged : Mail::createBody の挙動を変更（送信された値がnullの場合はキーも値も出力しない）
* Bug fix: Mail::createBody で Checkbox が未チェックで送信されたときに Array と出力されてしまうバグを修正

= 0.9.4 =
* Bug fix: 管理画面での 確認ボタン の表記間違いを修正

= 0.9.3 =
* Added  : readme.txt にマニュアルのURLを追記
* Bug fix: 確認ボタン 挿入ボタンが表示されていなかったのを修正
* Bug fix: 末尾に / のつかない URL の場合に画面変遷が正しく行われないバグを修正

= 0.9.2 =
* Bug fix: ファイルの読み込みタイミング等を変更

= 0.9.1 =
* Bug fix: 画像・ファイルアップロードフィールドのクラス名が正しく設定されていないのを修正
* Bug fix: 画像・ファイルアップロードフィールドで未アップロード時でも確認画面に項目が表示されてしまうのを修正
* Cahged : 言語ファイルの読み込みタイミングを変更

= 0.9 =
* Added  : Akismet設定を追加

= 0.8.1 =
* Cahged : functions.php を用いたフォーム作成は非推奨・サポート、メンテナンス停止
* Added  : チェックボックスで区切り文字の設定機能を追加
           [mwform_checkbox name="checkbox" children="A,B,C" separator="、"]

= 0.8 =
* Added  : 画像アップロードフィールドを追加
* Added  : ファイルアップロードフィールドを追加
* Added  : ファイルタイプ バリデーション項目を追加
* Added  : ファイルサイズ バリデーション項目を追加
* Added  : 管理画面で不正な値は save しないように修正
* Added  : datepickerで年月をセレクトボックスで選択できる設定をデフォルトに
* Added  : アクションフック mwform_add_shortcode, mwform_add_qtags 追加
* Bug fix: バリデーション項目 文字数の範囲, 最小文字数 の挙動を修正
* Cahged : フォーム制作画面でビジュアルエディタを使えるように変更

= 0.7.1 =
* Added  : メール設定を 自動返信メール設定 と 管理者宛メール設定 に分割
* Note   : データベースには 管理者宛メール設定 のデータが保存される
* Note   : 管理者宛メール設定 が空の場合は 自動返信メール設定 が使用される

= 0.7 =
* Added  : 問い合わせデータをデータベースに保存する機能を追加
* Added  : アンインストール時にデータを削除するように修正
* Bug fix: 一覧画面で QTags の JSエラーがでていたのを修正

= 0.6.4 =
* Added  : 引数を有効にする meta_box を追加
* Bug fix: "Zip Code" が日本語化されていないバグを修正
* Bug fix: ページリダイレクトのURL判定を変更
* Bug fix: バリデーション mail に複数のメールアドレスを指定できないように変更

= 0.6.3 =
* Bug fix: 管理画面のURL設定で http から入れないとメールが二重送信されてしまうバグを修正
* Bug fix: フォーム識別子部分が Firefox でコピペできないバグを修正

= 0.6.2 =
* Bug fix: Infinite loop when WordPress not root installed.

= 0.6.1 =
* Added To E-mail adress settings.

= 0.6 =
* Added settings page.
* Deprecated: acton hook mwform_mail_{$key}. This hook is removed when next version up.
* Added filter hook mwform_mail_{$key}.
* Bug fix: Validations.

= 0.5.5 =
* Added tag to show login user meta.
{user_id}, {user_login}, {user_email}, {user_url}, {user_registered}, {display_name}

= 0.5 =
* Initial release.