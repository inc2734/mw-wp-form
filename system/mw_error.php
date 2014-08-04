<?php
/**
 * Name: MW Error
 * Description: エラークラス
 * Version: 1.0.2
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : July 17, 2012
 * Modified: July 24, 2014
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_Error {

	/**
	 * エラー格納用の配列 array( 'key' => 'rule' => 'message' )
	 */
	private $errors = array();

	/**
	 * setError
	 * エラーメッセージをセット
	 * @param string $key name属性
	 * @param string $rule
	 * @param string $message
	 */
	public function setError( $key, $rule, $message ) {
		if ( !is_string( $message ) ) exit( 'The Validate error message must be string!');
		$this->errors[$key][$rule] = $message;
	}

	/**
	 * getError
	 * エラーメッセージを返す
	 * @param string $key name属性
	 * @return array
	 */
	public function getError( $key ) {
		if ( isset( $this->errors[$key] ) ) {
			return $this->errors[$key];
		}
		return array();
	}

	/**
	 * getErrors
	 * 全てのエラーメッセージを返す
	 * @return array
	 */
	public function getErrors() {
		return $this->errors;
	}
}