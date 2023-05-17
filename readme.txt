=== MW WP Form ===
Contributors: inc2734, ryu263, tomothumb, nanniku, mt8.biz, NExt-Season, kuck1u, mypacecreator, mh35, grace-create, musus, wildworks, likr, yudai524, noldorinfo
Donate link: https://www.amazon.co.jp/registry/wishlist/39ANKRNSTNW40
Tags: plugin, form, confirm, preview, shortcode, mail, chart, graph, html, contact form, form creation, form creator, form manager, form builder, custom form
Requires at least: 4.0
Tested up to: 6.2
Stable tag: 4.4.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

MW WP Form is shortcode base contact form plugin. This plugin have many features. For example you can use many validation rules, inquiry data saving, and chart aggregation using saved inquiry data.

== Description ==

MW WP Form can create mail form with a confirmation screen using shortcode.

* Form created using shortcodes
* Using confirmation page is possible.
* The page changes by the same URL or individual URL are possible.
* Many validation rules
* Saving inquiry data is possible.
* Displaying Chart using saved inquiry data is possible.

= Official =

https://plugins.2inc.org/mw-wp-form/

= GitHub =

https://github.com/inc2734/mw-wp-form

= The following third-party resources =

Google Charts
Source: https://developers.google.com/chart/

= Contributors =

* [Takashi Kitajima](https://2inc.org) ( [inc2734](https://profiles.wordpress.org/inc2734) )
* [Ryujiro Yamamoto](https://webcre-archive.com) ( [ryu263](https://profiles.wordpress.org/ryu263) )
* [Tsujimoto Tomoyuki](http://kee-non.com) ( [tomothumb](https://profiles.wordpress.org/tomothumb) )
* [Naoyuki Ohata] ( [nanniku](https://profiles.wordpress.org/nanniku) )
* [Kazuto Takeshita](https://mt8.biz/) ( [moto hachi](https://profiles.wordpress.org/mt8biz/) )
* [Atsushi Ando](https://www.next-season.net/) ( [NExt-Season](https://profiles.wordpress.org/next-season/) )
* [Kazuki Tomiyasu](https://visualive.jp/) ( [KUCKLU](https://profiles.wordpress.org/kuck1u/) )
* [Kei Nomura](https://mypacecreator.net/) ( [mypacecreator](https://profiles.wordpress.org/mypacecreator/) )
* [mh35](https://profiles.wordpress.org/mh35)
* [Takashi Nojima](https://github.com/nojimage)
* [herikutu](https://github.com/herikutu)
* [tsucharoku](https://github.com/tsucharoku)
* [Tetsuaki Hamano](https://github.com/t-hamano) ( [t-hamano](https://profiles.wordpress.org/wildworks/) )
* [Susumu Seino](https://github.com/musus) ( [Susumu Seino](https://profiles.wordpress.org/musus/) )
* [Yosuke Onoue](https://github.com/likr) ( [likr](https://profiles.wordpress.org/likr/) )
* [Yudai Konishi](https://github.com/yudai524) ( [Yudai Konishi](https://profiles.wordpress.org/yudai524/) )
* [takekoshi](https://github.com/noldorinfo) ( [takekoshi](https://profiles.wordpress.org/noldorinfo/) )

== Installation ==

1. Upload `MW WP Form` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. You can create a form by settings page.

== Frequently Asked Questions ==

Do you have questions or issues with MW WP Form? Use these support channels appropriately.

1. [Official](https://plugins.2inc.org/mw-wp-form/)
1. [Support Forum](https://wordpress.org/support/plugin/mw-wp-form)

== Screenshots ==

1. Form creation page.
2. Form item create box. You can easily insert the form.
3. Supports saving inquiry data to database.
4. List page of inquiry data that has been saved.
5. Supports chart display of saved inquiry data.

== Changelog ==

= 4.4.5 =
* Fixed a bug in WordPress 6.2 that the file name of a file sent from file fields or image fields and attached to it becomes "name".

= 4.4.4 =
* Fixed a bug that prevented some file and image fields from being uploaded.

= 4.4.3 =
* Fixed a directory traversal vulnerability. We strongly encourage you to update to it immediately.

= 4.4.2 =
* Fix infinite redirections.

= 4.4.1 =
* Fixed a bug that caused forms to be cached when Batcache was enabled.
* Fix infinite redirections with asterisks.
* Add audio/x-wav etc. as mimetype for wav.

= 4.4.0 =
* Add filter hook `mwform_form_start_attr_action`.
* Add maxlength attribute to textarea field arguments.

= 4.3.2 =
* Fix fatal error on saved contact data page.

= 4.3.1 =
* Some fix for `mwform_settings_extend_fields`.
* Some fix for `mwform_settings_extend_fields_mw-wp-form-xxx`.

= 4.3.0 =
* Add filter hook `mwform_template_render`.
* Add filter hook `mwform_template_render_mw-wp-form-xxx`.
* Add filter hook `mwform_settings_extend_fields`.
* Add filter hook `mwform_settings_extend_fields_mw-wp-form-xxx`.
* Fix monthpicker bug.
* Add wp-env.

= 4.2.0 =
* Fixed a bug that memo is not saved.
* The select element can be used in tag generator dialog box.
* You can set original tag group in tag generator.
* Add filter hook mwform_tag_generator_labels.

= 4.1.2 =
* Fixed a bug that tracking number was displayed +1 on the complete screen.

= 4.1.1 =
* Add admin_email_to column to CSV.

= 4.1.0 =
* Add filter hook mwform_csv_columns-mwf_xxx
* Fixed the bug that "MW WP Form dosen't support" mwform_after_exec_shortcode "already." is output to the error log even though it is not hooked.
* Changed to save destination admin email address in inquiry data.
* Fixed a bug that notice occurs when changing the response status of inquiry data.

= 4.0.6 =
* Fixed a bug that line feed was not applied to the form that using the block editor.

= 4.0.5 =
* Add filter hook mwform_send_nocache_header.
* Fix MWF_Functions::_return_deprecated_message() error.
* Fix bug that mwform_value_mw-wp-form-xxx filter not applyed to radio and checkbox.
* Fix bug that nocache_header not applyed.
* Add the echo attribute to mwform_custom_mail_tag.

= 4.0.4 =
* PHP 5.3, 5.2.4 support

= 4.0.3 =
* Fix saved contact data list bug.
* Fix nocache headers bug.

= 4.0.2 =
* Fixed a bug caused by erroneous use of set_error_handler()

= 4.0.1 =
* Fix bug that form layout broken when Gutenberg installed.

= 4.0.0 =
* Refactoring
* Update redirect process.
* Changed that admin and reply Mail settings are required.
* Changed to be able to set the form besides $post and main template.
* Each input fields can overwrite from themes.
* Deprecated action hook `mwform_exec_shortcode`. Please use this instead `mwform_start_main_process`
* The hook to `mwform_validation_rules` is no longer needed to add your own validation rule.
* Deprecated `MW_WP_Form_Contact_Data_Setting::get_posts()`. Please use this instead `MW_WP_Form_Contact_Data_Setting::get_form_post_types()`
* Added method MW_WP_Form_Data::get_saved_mail_id();
* Added method MW_WP_Form_Data::set_saved_mail_id();
* Deprecated `MW_WP_Form_Data::getInstance()`. Please use this instead `MW_WP_Form_Data::connect()`
* Deprecated `MW_WP_Form_Form::remove_linefeed_space()`. Please use this instead `MW_WP_Form_Form::remove_newline_space()`
* Deprecated `MW_WP_Form_Validation::check()`. Please use this instead `MW_WP_Form_Validation::is_valid()`
* Deprecated `MW_WP_Form_Validation::single_check()`. Please use this instead `MW_WP_Form_Validation::is_valid_field()`

= 3.2.3 =
* Added   : Added filter hook mwform_response_statuses_mwf_xxx

= 3.2.2 =
* Bugfix  : Support validation check of custom mail tag fields.

= 3.2.1 =
* Bugfix  : Fixed a bug that displayed send error page when admin mail address is `false`.

= 3.2.0 =
* Added   : Added process of mail sending error. When failed mail sending, displayed mail sending error page.
* Added   : Added filter hook mwform_is_mail_sended
* Added   : Added filter hook mwform_send_error_content_raw_mw-wp-form-xxx
* Added   : Added filter hook mwform_send_error_content_mw-wp-form-xxx

= 3.1.0 =
* Added   : Added the month picker field.

= 3.0.1 =
* Bugfix  : Fixed a bug of action hook mwform_contact_data_save-mwf_xxx

= 3.0.0 =
* Added   : Added method MWF_Functions::get_form_id_from_form_key( $form_key );
* Added   : Added action hook mwform_after_exec_shortcode
* Added   : Added action hook mwform_before_load_content_mw-wp-form-xxx
* Added   : Added action hook mwform_after_load_content_mw-wp-form-xxx
* Added   : Added action hook mwform_before_redirect_mw-wp-form-xxx
* Added   : Added filter hook mwform_redirect_url_mw-wp-form-xxx
* Added   : Added filter hook mwform_complete_content_raw_mw-wp-form-xxx
* Added   : Added action hook mwform_settings_meta_box
* Added   : Added action hook mwform_settings_save_mw-wp-form-xxx
* Added   : Added action hook mwform_contact_data_save-mwf_xxx

= 2.14.2 =
* Bugfix  : Fixed type on japanese.

= 2.14.1 =
* Bugfix  : Fixed a bug that automatic linefeed affects markup of radio and checkbox.

= 2.14.0 =
* Changed : Update debug log format.
* Changed : Update checkbox and radio field markup.

= 2.13.1 =
* Bugfix  : Fix undefined constant error FILEINFO_MIME_TYPE

= 2.13.0 =
* Added   : Added the button elements.
* Added   : Added filter hook mwform_custom_mail_tag
* Added   : Added Method MW_WP_Form_Data::get_view_flg()

= 2.12.0 =
* Added   : Added filter hook mwform_complete_content_mw-wp-form-xxx
* Changed : Changed to pass MW_WP_Form_Data object as the 2nd argument of mwform_post_content_mw-wp-form-xxx hook.

= 2.11.0 =
* Bugfix  : Fixed a bug that to remove the uploaded file when filesize validation error.
* Bugfix  : Fixed a bug that removing temp files only when uploading is success.
* Bugfix  : Fixed a bug that xss vulnerability exists in the echo option is enabled in the hidden field.

= 2.10.0 =
* Added   : Added filter hook mwform_translate_datepicker_mw-wp-form-xxx

= 2.9.0 =
* Added   : Added the Return-Path setting.

= 2.8.3 =
* Added   : Added the upload failure of the error message in filesize validation.

= 2.8.2 =
* Bugfix  : Fixed a json parser bug.

= 2.8.1 =
* Added   : Added MinImageSize validation.
* Added   : Added MaxImageSize validation.
* Bugfix  : Fixed a set_upload_file_keys bug and rename to regenerate_upload_file_keys.

= 2.8.0 =
* Added   : Support slug attribute. e.g. [mwform_formkey slug="form_slug"]
* Added   : Added filter hook mwform_inquiry_data_columns-mwf_xxx
* Added   : Added filter hook mwform_upload_file_keys_mw-wp-form-xxx
* Added   : Added args of filter hook mwform_upload_dir_mw-wp-form-xxx and mwform_upload_filename_mw-wp-form-xxx
* Added   : Added the Custom Mail Tag field. This field display value of mwform_custom_mail_tag_mw-wp-form-xxx.
* Bugfix  : docx, xlsx, pptx upload bug fixed.
* Bugfix  : Fixed a bug that the extension isn't added when using filter hook mwform_upload_filename_mw-wp-form-xxx.
* Changed : Sending the file url when saving in database and input {file key} and {image key} in the mail.
* Changed : Check of the js attribute of datepicker is now strictly. Property MUST be enclosed in double quotes.
* Changed : Changed the form token name.

= 2.7.0 =
* Added   : Added Method MW_WP_Form_Mail_Parser::get_saved_mail_id()
* Added   : Added Method MW_WP_Form_Mail::get_saved_mail_id()
* Added   : Added filter hook mwform_upload_dir_mw-wp-form-xxx
* Added   : Added filter hook mwform_upload_filename_mw-wp-form-xxx
* Added   : Added filter hook mwform_no_save_keys_mw-wp-form-xxx
* Changed : Changed to save the default values of contact data meta data at the time of email saved.
* Bugfix  : Fixed a bug that e-mail is not sent when the "from" is in violation of the RFC.

= 2.6.4 =
* Added   : Add filter hook mwform_content_wpautop_mw-wp-form-xxx
* Added   : Add argument at mwform_after_send_mw-wp-form-xxx
* Added   : Add method MW_WP_Form_Data::get_form_key()

= 2.6.3 =
* Bugfix  : Fixed a eq validation bug.

= 2.6.2 =
* Bugfix  : Fixed a bug that class attribute can't set at radio.
* Bugfix  : Fixed a bug that id and class attribute can't set at file.

= 2.6.1 =
* Changed : Removed for the Generator code.
* Bugfix  : Fixed a bug that mwform_default_settings doesn't fired.

= 2.6.0 =
* Bugfix  : JavaScript bug fix on validation.
* Changed : Multilingual support. Changed domain.
* Changed : Changed radio and checkbox style.
* Added   : Added the class attribute setting.

= 2.5.3 =
* Added   : Japanese zip code validation allows the format of the form 0000000.
* Bugfix  : Fixed a date validation bug.

= 2.5.2 =
* Added   : Add new validation rule japanese kana.
* Added   : Add action hook mwform_before_send_admin_mail_mw-wp-form-xxx.
* Added   : Add action hook mwform_before_send_reply_mail_mw-wp-form-xxx.
* Added   : Add action hook mwform_after_send_mw-wp-form-xxx.
* Added   : Add action hook mwform_enqueue_scripts_mw-wp-form-xxx.

= 2.5.1 =
* Update readme.txt

= 2.5.0 =
* Added   : Add html5 email field.
* Added   : Add html5 url field.
* Added   : Add html5 range field.
* Added   : Add html5 number field.
* Added   : Support attribute placeholder in datepicker.
* Changed : maxlength default value is null.
* Bugfix  : Fixed a bug that is CC and BCC have been overlapping sent when To is multiple.

= 2.4.12 =
* Bugfix  : filter hook 'mwform_value_mwf_xxx' does not work when to use radio or checkboxes.
* Changed : Changed checkbox default separator ', ' to ','.

= 2.4.11 =
* Bugfix  : Fixed a bug that attachment file does not displayed in contact data list page.
* Changed : Trim email address on inputs.

= 2.4.10 =
* Bugfix  : Fixed a bug that does not scroll when you return to the input screen.

= 2.4.9 =
* Bugfix  : Fixes a bug that the value of last checkbox is only posted when multiple same name checkboxes created and those post_raw is true.

= 2.4.8 =
* Changed : Change the value to save even null when you save the contact data.
* Changed : Default value of radio and checkbox is null.

= 2.4.7 =
* Bugfix  : Fixed a bug the custom_mail_tag from To, CC and BCC.

= 2.4.6 =
* Changed : The custom_mail_tag filter hook applied to To, CC and BCC.
* Changed : Full size image is displayed when you click the thumbnail of the attached image in saving contact data list.

= 2.4.5 =
* Refactoring MW_WP_Form_Data Class.
* Added   : Add MW_WP_Form_Mail_Parse class.
* Added   : Add Tracking Number setting field.
* Added   : Sender and From are written to the debug log.
* Bugfix  : Fixed a bug that sometimes icon is not displayed in contact data list.

= 2.4.4 =
* Bugfix  : Fixed a conv_half_alphanumeric attribute bug.

= 2.4.3 =
* Changed : Changed visibility of MW_WP_Form_Validation::set_rule() protected to public.

= 2.4.2 =
* Bugfix  : Fixed a hidden field bug.

= 2.4.1 =
* Bugfix  : Fixed a mwform_csv_encoding-mwf_xxx bug.

= 2.4.0 =
* Refactoring
* Added     : Add filter hook mwform_csv_encoding-mwf_xxx.
* Deleted   : Delete some Deprecated hooks, methods.
* Bugfix    : Fixed a csv bug.
* Deprecated: MW_WP_Form_Form::get_raw()
* Deprecated: MW_WP_Form_Form::get_raw_in_children()
* Deprecated: MW_WP_Form_Form::get_zip_value()
* Deprecated: MW_WP_Form_Form::get_tel_value()
* Deprecated: MW_WP_Form_Form::get_checked_value()
* Deprecated: MW_WP_Form_Form::get_radio_value()
* Deprecated: MW_WP_Form_Form::get_selected_value()
* Deprecated: MW_WP_Form_Form::get_separated_raw_value()
* Deprecated: MW_WP_Form_Form::get_separator_value()

= 2.3.5 =
* Bugfix  : Fixed a post_raw bug at select and radio.

= 2.3.4 =
* Bugfix  : Fixed a bug that checkboxes are checked when children are added by hook.

= 2.3.3 =
* Bugfix  : Fixed a bug that tracking number does not count up.

= 2.3.2 =
* Bugfix  : Fixed a bug that form does not display when is surrounded by enclosed type shortcode.
* Changed : Update tests.

= 2.3.1 =
* Bugfix  : Fixed a post_raw option bug.

= 2.3.0 =
* Refactoring mail sending part.
* Added   : Add "Send value by e-mail" option for checkbox, select, radio.
* Added   : Add filter hook mwform_custom_mail_tag_mw-wp-form-xxx.
* Added   : Add filter hook mwform_contact_data_post_type.
* Added   : Add filter hook mwform_get_inquiry_data_args-mwf_xxx
* Added   : Add debug mode. no send mail and logging when set "define( 'MWFORM_DEBUG', true );".
* Added   : Add filter hook mwform_log_directory.
* Changed : Refactoring admin pages.
* Changed : Changed generating method of file name that uploaded.
* Bugfix  : Fixed ai, psd, eps file upload bug.
* Bugfix  : Fix typo.

= 2.2.7 =
* Changed : Changed to be able to use ":" as value at checkbox, select, radio.

= 2.2.6 =
* Added   : Adding MW_WP_Form_Data object to augment of mail related hooks.
* Added   : Adding MW_WP_Form_Data object to augment of mwform_validation hook.
* Bug fix : Fixed a bug that post_title is not parsed when contact data saving in database.
* Bug fix : Fixed a bug that post value is sent and saved when children attribute separate at post value and display value.

= 2.2.5 =
* Bug fix : Fixed a checkbox bug.

= 2.2.4 =
* Added   : Added sortable handle in validation settings and chart settings.
* Bug fix : Fixed a children attribute bug.
* Bug fix : Fixed a bug that also "contact data" menu is displayed when there is no form that has been set contact data saving.

= 2.2.3 =
* Bug fix : Fixed a bug that exec shortcode are not converted when use template.

= 2.2.2 =
* Bug fix : Fixed a tel validation bug.
* Bug fix : Fixed a zip validation bug.
* Bug fix : Fixed a in validation bug.

= 2.2.1 =
* Bug fix : Fixed a bug that remove_query_vars_from_post() is not executed.
* Bug fix : Fixed a akismet feature bug.
* Bug fix : Fixed a uninstall bug.

= 2.2.0 =
* Refactoring
* Bug fix : Fixed a mwform_tracking_number_title filter hook bug.
* Bug fix : Fixed a bug that can not set the more than 2 default values of the checkbox.
* Bug fix : Fixed a bug that double quotation of shortcodes are escaped in manual form.
* Changed : Class name changed MW_Form_Field to MW_WP_Form_Abstract_Form_Field.
* Changed : Class name changed MW_Validation_Rule to MW_WP_Form_Abstract_Validation_Rule.
* Changed : Zip or Tel validation can use Japanese only.
* Changed : Zip or Tel field can use Japanese only.
* Added   : You can set a different value in the key and display value for children of checkbox, select, radio. For xample, [mwform_checkbox name="hoge" children="key1:value1,key2:value2"]

= 2.1.4 =
* Bug fix : Fixed a bug that data lost when there are multiple same name radio buttons and checkboxes.

= 2.1.3 =
* Bug fix : Fixed a bug that number of newline characters are different by the environment.
* Changed : Modified to ignore uppercase letters of lowercase letters at the file type validation.

= 2.1.2 =
* Added   : Added form key in list of MW WP Form page.
* Bug fix : Fixed a bug that "add form tag button" is displayed in complete message area.

= 2.1.1 =
* Added   : Added CSV Download option.
* Added   : Added an option to vertically display in Radio and Checkbox.
* Changed : Optimization of the number display processing of saving contact data.
* Changed : Change separator of checkbox in confirm page.

= 2.1.0 =
* Added   : Add filter_hook mwform_post_content_raw_mw-wp-form-xxx.
* Added   : Add filter_hook mwform_post_content_mw-wp-form-xxx.
* Added   : Add filter_hook mwform_form_fields.
* Added   : Add "Error" form type.
* Changed : MW_Form_Field::mwform_tag_generator_dialog() method needs $options argument.
* Changed : Zip code field and Tel field are wrapped by span tag.
* Bug fix : Empty separator is changed to not allow in checkbox field.
* Bug fix : Fix add form tag button css bug.

= 2.0.0 =
* Added   : Add scrolling of screen transition setting.
* Added   : Add filter_hook mwform_scroll_offset_mw-wp-form-xxx.
* Added   : Support auto tracking number.
* Added   : Add filter_hook mwform_tracking_number_title_mw-wp-form-xxx.

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
