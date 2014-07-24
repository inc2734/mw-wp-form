<?php
/**
 * Name: MWF_Config
 * Description: 設定ファイル
 * Version: 1.2.1
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : May 29, 2013
 * Modified: July 24, 2014
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MWF_Config {

	/**
	 * NAME
	 * プラグイン識別子
	 */
	const NAME = 'mw-wp-form';

	/**
	 * DOMAIN
	 * ネームスペース
	 */
	const DOMAIN = 'mw-wp-form';

	/**
	 * DBDATA
	 * DBに保存する問い合わせデータの post_type名 の接頭辞
	 */
	const DBDATA = 'mwf_';

	/**
	 * UPLOAD_FILE_KEYS
	 * アップロードファイルを示す name属性 を保存する配列、メタデータの名前
	 */
	const UPLOAD_FILE_KEYS = 'mwf_upload_files';

	/**
	 * UPLOAD_FILES
	 * $_FILES
	 */
	const UPLOAD_FILES = 'mwf_files';

	/**
	 * AKISMET
	 * akismetのエラーを格納するValidationのキー
	 */
	const AKISMET = 'mwf_akismet';

	/**
	 * CAPABILITY
	 * 権限
	 */
	const CAPABILITY = 'edit_pages';
}
