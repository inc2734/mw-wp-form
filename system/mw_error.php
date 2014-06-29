<?php
/**
 * Name: MW Error
 * URI: http://2inc.org
 * Description: エラークラス
 * Version: 1.0.1
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : July 17, 2012
 * Modified: December 14, 2013
 * License: GPL2
 *
 * Copyright 2013 Takashi Kitajima (email : inc@2inc.org)
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
class MW_Error {

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