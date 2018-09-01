<?php
/**
 * Name       : MWF Config
 * Version    : 2.0.0
 * Author     : Takashi Kitajima
 * Author URI : https://2inc.org
 * Created    : May 29, 2013
 * Modified   : May 30, 2017
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MWF_Config {

	/**
	 * Plugin ID
	 */
	const NAME = 'mw-wp-form';

	/**
	 * Text Domain
	 */
	const DOMAIN = 'mw-wp-form';

	/**
	 * Prefix of post type of saved inquiry data
	 */
	const DBDATA = 'mwf_';

	/**
	 * The name of field that array of uploaded file names
	 */
	const UPLOAD_FILE_KEYS = 'mwf_upload_files';

	/**
	 * The name of field that array of custom mail tag names
	 */
	const CUSTOM_MAIL_TAG_KEYS = 'mwf_custom_mail_tags';

	/**
	 * $_FILES
	 */
	const UPLOAD_FILES = 'mwf_files';

	/**
	 * Field name of Akismet
	 */
	const AKISMET = 'mwf_akismet';

	/**
	 * Capability
	 */
	const CAPABILITY = 'edit_pages';

	/**
	 * Name of tracking number
	 */
	const TRACKINGNUMBER = 'tracking_number';

	/**
	 * Field name of confirm button
	 */
	const CONFIRM_BUTTON = 'submitConfirm';

	/**
	 * Field name of back button
	 */
	const BACK_BUTTON = 'submitBack';

	/**
	 * Name of meta data of saved inquiry data
	 */
	const CONTACT_DATA_NAME = '_mw-wp-form_data';
	const INQUIRY_DATA_NAME = self::CONTACT_DATA_NAME;

	/**
	 * Name of sending error data
	 */
	const SEND_ERROR = 'mw-wp-form-send-error';

	/**
	 * Nonce field name
	 */
	const TOKEN_NAME = 'mw_wp_form_token';
}
