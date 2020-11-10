<?php
/**
 * @package mw-wp-form
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * MWF_Config
 */
class MWF_Config {

	/**
	 * Plugin ID.
	 *
	 * @var string
	 */
	const NAME = 'mw-wp-form';

	/**
	 * Text Domain.
	 *
	 * @var string
	 */
	const DOMAIN = 'mw-wp-form';

	/**
	 * Prefix of post type of saved inquiry data.
	 *
	 * @var string
	 */
	const DBDATA = 'mwf_';

	/**
	 * The name of field that array of uploaded file names.
	 *
	 * @var string
	 */
	const UPLOAD_FILE_KEYS = 'mwf_upload_files';

	/**
	 * The name of field that array of custom mail tag names.
	 *
	 * @var string
	 */
	const CUSTOM_MAIL_TAG_KEYS = 'mwf_custom_mail_tags';

	/**
	 * $_FILES.
	 *
	 * @var string
	 */
	const UPLOAD_FILES = 'mwf_files';

	/**
	 * Field name of Akismet.
	 *
	 * @var string
	 */
	const AKISMET = 'mwf_akismet';

	/**
	 * Capability.
	 *
	 * @var string
	 */
	const CAPABILITY = 'edit_pages';

	/**
	 * Name of tracking number.
	 *
	 * @var string
	 */
	const TRACKINGNUMBER = 'tracking_number';

	/**
	 * Field name of confirm button.
	 *
	 * @var string
	 */
	const CONFIRM_BUTTON = 'submitConfirm';

	/**
	 * Field name of back button.
	 *
	 * @var string
	 */
	const BACK_BUTTON = 'submitBack';

	/**
	 * Name of meta data of saved inquiry data.
	 *
	 * @var string
	 */
	const CONTACT_DATA_NAME = '_mw-wp-form_data';

	/**
	 * Name of meta data of saved inquiry data.
	 *
	 * @var string
	 */
	const INQUIRY_DATA_NAME = self::CONTACT_DATA_NAME;

	/**
	 * Name of sending error data.
	 *
	 * @var string
	 */
	const SEND_ERROR = 'mw-wp-form-send-error';

	/**
	 * Nonce field name.
	 *
	 * @var string
	 */
	const TOKEN_NAME = 'mw_wp_form_token';
}
