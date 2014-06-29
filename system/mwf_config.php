<?php
/**
 * Name: MWF_Config
 * URI: http://2inc.org
 * Description: 設定ファイル
 * Version: 1.2.0
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : May 29, 2013
 * Modified: April, 2, 2014
 * License: GPL2
 *
 * Copyright 2014 Takashi Kitajima (email : inc@2inc.org)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
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
