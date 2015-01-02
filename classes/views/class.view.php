<?php
/**
 * Name       : MW WP Form View
 * Version    : 1.0.0
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : January 2, 2015
 * Modified   : 
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_View {
	
	/** 
	 * $variables
	 * @var array
	 */
	protected $variables = array();
	
	/** 
	 * set
	 * @param string $key
	 * @param mixed $value
	 */
	public function set( $key, $value ) {
		$this->variables[$key] = $value;
	}
	
	/** 
	 * get
	 * @param string $key
	 * @return mixed
	 */
	public function get( $key ) {
		if ( isset( $this->variables[$key] ) ) {
			return $this->variables[$key];
		}
	}
}