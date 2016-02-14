<?php
/**
 * Name       : MW WP Form Contact Data
 * Version    : 1.0.0
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : January 1, 2015
 * Modified   :
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Contact_Data {

	/**
	 * save_post
	 *
	 * @param int $post_id
	 */
	public function save_post( $post_id ) {
		$contact_data_post_types = MW_WP_Form_Contact_Data_setting::get_posts();
		if ( !isset( $_POST['post_type'] ) )
			return $post_id;
		if ( !in_array( $_POST['post_type'], $contact_data_post_types ) )
			return $post_id;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_id;
		if ( !wp_verify_nonce( $_POST[MWF_Config::NAME . '_nonce'], MWF_Config::NAME ) )
			return $post_id;
		if ( !current_user_can( MWF_Config::CAPABILITY ) )
			return $post_id;

		$Contact_Data_Setting = new MW_WP_Form_Contact_Data_setting( $post_id );
		$permit_keys = $Contact_Data_Setting->get_permit_keys();
		$data = array();
		foreach ( $permit_keys as $key ) {
			if ( isset( $_POST[MWF_Config::CONTACT_DATA_NAME][$key] ) ) {
				$value = $_POST[MWF_Config::CONTACT_DATA_NAME][$key];
				if ( $key === 'response_status' ) {
					if ( !array_key_exists( $value, $Contact_Data_Setting->get_response_statuses() ) ) {
						continue;
					}
				}
				$data[$key] = $value;
			}
		}
		$Contact_Data_Setting->sets( $data );
		$Contact_Data_Setting->save();
	}
}
