<?php
/**
 * Name: MW Validation
 * URI: http://2inc.org
 * Description: バリデーションクラス
 * Version: 1.6.5
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : July 20, 2012
 * Modified: June 13, 2014
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
class MW_Validation {

	protected $Data;
	protected $Error;
	public $validate = array();
	private $ENCODE = 'utf-8';

	/**
	 * __construct
	 * @param string $key 識別子
	 */
	public function __construct( $key ) {
		$this->Data = MW_WP_Form_Data::getInstance( $key );
		$this->Error = new MW_Error();
	}

	/**
	 * getValue
	 * フォームの送信値を返す（配列の場合は $data['data'] を、違う場合は $data）。
	 * @param string $key name属性
	 * @return mixed
	 */
	private function getValue( $key ) {
		$data = $this->Data->getValue( $key );
		if ( !isset( $data ) )
			return;
		if ( is_array( $data ) ) {
			if ( array_key_exists( 'data', $data ) ) {
				if ( is_array( $data['data'] ) ) {
					return $this->array_clean( $data['data'] );
				} else {
					return $data['data'];
				}
			}
		} else {
			return $data;
		}
	}

	/**
	 * required
	 * 値が存在する
	 * @param string $key name属性
	 * @param array $option
	 * @return string エラーメッセージ
	 */
	public function required( $key, $options = array() ) {
		$value = $this->getValue( $key );
		// 値が存在するとき、もしくは存在しないけど他のデータもない（=フォーム送信自体されていない）ときはエラーではない
		if ( !is_null( $value ) || is_null( $value ) && !$this->Data->getValues() ) {
			return;
		}
		$defaults = array(
			'message' => __( 'This is required.', MWF_Config::DOMAIN )
		);
		$options = array_merge( $defaults, $options );
		return $options['message'];
	}

	/**
	 * noEmpty
	 * 値が空ではない（0は許可）
	 * @param string $key name属性
	 * @param array $options
	 * @return string エラーメッセージ
	 */
	public function noEmpty( $key, $options = array() ) {
		$value = $this->getValue( $key );
		if ( !is_null( $value ) && $this->isEmpty( $value ) ) {
			$defaults = array(
				'message' => __( 'Please enter.', MWF_Config::DOMAIN )
			);
			$options = array_merge( $defaults, $options );
			return $options['message'];
		}
	}

	/**
	 * noFalse
	 * 値が空ではない（0も不可）
	 * @param string $key name属性
	 * @param array $options
	 * @return string エラーメッセージ
	 */
	public function noFalse( $key, $options = array() ) {
		$value = $this->getValue( $key );
		if ( !is_null( $value ) && empty( $value ) ) {
			$defaults = array(
				'message' => __( 'Please enter.', MWF_Config::DOMAIN )
			);
			$options = array_merge( $defaults, $options );
			return $options['message'];
		}
	}

	/**
	 * alpha
	 * 値がアルファベット
	 * @param string $key name属性
	 * @param array $options
	 * @return string エラーメッセージ
	 */
	public function alpha( $key, $options = array() ) {
		$value = $this->getValue( $key );
		if ( !is_null( $value ) && !$this->isEmpty( $value ) ) {
			if ( is_array( $value ) ) {
				$value = implode( $this->getSeparatorValue( $key ), $value );
			}
			if ( !preg_match( '/^[A-Za-z]+$/', $value ) ) {
				$defaults = array(
					'message' => __( 'Please enter with a half-width alphabetic character.', MWF_Config::DOMAIN )
				);
				$options = array_merge( $defaults, $options );
				return $options['message'];
			}
		}
	}

	/**
	 * numeric
	 * 値が数値
	 * @param string $key name属性
	 * @param array $options
	 * @return string エラーメッセージ
	 */
	public function numeric( $key, $options = array() ) {
		$value = $this->getValue( $key );
		if ( !is_null( $value ) && !$this->isEmpty( $value ) ) {
			if ( is_array( $value ) ) {
				$value = implode( $this->getSeparatorValue( $key ), $value );
			}
			if ( !preg_match( '/^[0-9]+$/', $value ) ) {
				$defaults = array(
					'message' => __( 'Please enter with a half-width number.', MWF_Config::DOMAIN )
				);
				$options = array_merge( $defaults, $options );
				return $options['message'];
			}
		}
	}

	/**
	 * alphaNumeric
	 * 値が英数値
	 * @param string $key name属性
	 * @param array $options
	 * @return string エラーメッセージ
	 */
	public function alphaNumeric( $key, $options = array() ) {
		$value = $this->getValue( $key );
		if ( !is_null( $value ) && !$this->isEmpty( $value ) ) {
			if ( is_array( $value ) ) {
				$value = implode( $this->getSeparatorValue( $key ), $value );
			}
			if ( !preg_match( '/^[0-9A-Za-z]+$/', $value ) ) {
				$defaults = array(
					'message' => __( 'Please enter with a half-width alphanumeric character.', MWF_Config::DOMAIN )
				);
				$options = array_merge( $defaults, $options );
				return $options['message'];
			}
		}
	}

	/**
	 * katakana
	 * 値がカタカナ
	 * @param string $key name属性
	 * @param array $options
	 * @return string エラーメッセージ
	 */
	public function katakana( $key, $options = array() ) {
		$value = $this->getValue( $key );
		if ( !is_null( $value ) && !$this->isEmpty( $value ) ) {
			if ( is_array( $value ) ) {
				$value = implode( $this->getSeparatorValue( $key ), $value );
			}
			if ( !preg_match( '/^[ァ-ヾ 　]*?[ァ-ヾ]+?[ァ-ヾ 　]*?$/u', $value ) ) {
				$defaults = array(
					'message' => __( 'Please enter with a Japanese Katakana.', MWF_Config::DOMAIN )
				);
				$options = array_merge( $defaults, $options );
				return $options['message'];
			}
		}
	}

	/**
	 * hiragana
	 * 値がひらがな
	 * @param string $key name属性
	 * @param array $options
	 * @return string エラーメッセージ
	 */
	public function hiragana( $key, $options = array() ) {
		$value = $this->getValue( $key );
		if ( !is_null( $value ) && !$this->isEmpty( $value ) ) {
			if ( is_array( $value ) ) {
				$value = implode( $this->getSeparatorValue( $key ), $value );
			}
			if ( !preg_match( '/^[ぁ-ゞ 　]*?[ぁ-ゞ]+?[ぁ-ゞ 　]*?$/u', $value ) ) {
				$defaults = array(
					'message' => __( 'Please enter with a Japanese Hiragana.', MWF_Config::DOMAIN )
				);
				$options = array_merge( $defaults, $options );
				return $options['message'];
			}
		}
	}

	/**
	 * zip
	 * 値が郵便番号
	 * @param string $key name属性
	 * @param array $options
	 * @return string エラーメッセージ
	 */
	public function zip( $key, $options = array() ) {
		$value = $this->getValue( $key );
		if ( !empty( $value ) ) {
			$defaults = array(
				'message' => __( 'This is not the format of a zip code.', MWF_Config::DOMAIN )
			);
			$options = array_merge( $defaults, $options );
			if ( is_array( $value ) ) {
				$value = implode( $this->getSeparatorValue( $key ), $value );
			}
			if ( !preg_match( '/^\d{3}-\d{4}$/', $value ) ) {
				return $options['message'];
			}
		}
	}

	/**
	 * tel
	 * 値が電話番号
	 * @param string $key name属性
	 * @param array $options
	 * @return string エラーメッセージ
	 */
	public function tel( $key, $options = array() ) {
		$value = $this->getValue( $key );
		if ( !empty( $value ) ) {
			$defaults = array(
				'message' => __( 'This is not the format of a tel number.', MWF_Config::DOMAIN )
			);
			$options = array_merge( $defaults, $options );
			if ( is_array( $value ) ) {
				$value = implode( $this->getSeparatorValue( $key ), $value );
			}
			if ( ! (
				preg_match( '/^\d{2}-\d{4}-\d{4}$/', $value ) ||
				preg_match( '/^\d{3}-\d{3,4}-\d{4}$/', $value ) ||
				preg_match( '/^\d{4}-\d{2}-\d{4}$/', $value ) ||
				preg_match( '/^\d{4}-\d{3}-\d{3}$/', $value ) ||
				preg_match( '/^\d{5}-\d{1}-\d{4}$/', $value )
			) ) {
				return $options['message'];
			}
		}
	}

	/**
	 * mail
	 * 値がメールアドレス
	 * @param string $key name属性
	 * @param array $options
	 * @return string エラーメッセージ
	 */
	public function mail( $key, $options = array() ) {
		$value = $this->getValue( $key );
		if ( !is_null( $value ) && !$this->isEmpty( $value ) ) {
			if ( is_array( $value ) ) {
				$value = implode( $this->getSeparatorValue( $key ), $value );
			}
			if ( !preg_match( '/^[^@]+@([^@^\.]+\.)+[^@^\.]+$/', $value ) ) {
				$defaults = array(
					'message' => __( 'This is not the format of a mail address.', MWF_Config::DOMAIN )
				);
				$options = array_merge( $defaults, $options );
				return $options['message'];
			}
		}
	}

	/**
	 * url
	 * 値がURL
	 * @param string $key name属性
	 * @param array $options
	 * @return string エラーメッセージ
	 */
	public function url( $key, $options = array() ) {
		$value = $this->getValue( $key );
		if ( !is_null( $value ) && !$this->isEmpty( $value ) ) {
			if ( is_array( $value ) ) {
				$value = implode( $this->getSeparatorValue( $key ), $value );
			}
			if ( !preg_match( '/^https{0,1}:\/\/[^\/].?/', $value ) ) {
				$defaults = array(
					'message' => __( 'This is not the format of a url.', MWF_Config::DOMAIN )
				);
				$options = array_merge( $defaults, $options );
				return $options['message'];
			}
		}
	}

	/**
	 * eq
	 * 値が一致している
	 * @param string $name name属性
	 * @param array $options
	 * @return string エラーメッセージ
	 */
	public function eq( $key, $options = array() ) {
		$value = $this->getValue( $key );
		if ( !is_null( $value ) ) {
			$defaults = array(
				'target' => null,
				'message' => __( 'This is not in agreement.', MWF_Config::DOMAIN )
			);
			$options = array_merge( $defaults, $options );
			$target_value = $this->getValue( $options['target'] );
			if ( $value !== $target_value ) {
				return $options['message'];
			}
		}
	}

	/**
	 * between
	 * 値の文字数が範囲内
	 * @param string $name name属性
	 * @param array $options
	 * @return string エラーメッセージ
	 */
	public function between( $key, $options = array() ) {
		$value = $this->getValue( $key );
		if ( !is_null( $value ) && !$this->isEmpty( $value ) ) {
			if ( is_array( $value ) ) {
				$value = implode( $this->getSeparatorValue( $key ), $value );
			}
			$defaults = array(
				'min' => 0,
				'max' => 0,
				'message' => __( 'The number of characters is invalid.', MWF_Config::DOMAIN )
			);
			$options = array_merge( $defaults, $options );
			$length = mb_strlen( $value, $this->ENCODE );
			if ( MWF_Functions::is_numeric( $options['min'] ) ) {
				if ( MWF_Functions::is_numeric( $options['max'] ) ) {
					if ( !( $options['min'] <= $length && $length <= $options['max'] ) ) {
						return $options['message'];
					}
				} else {
					if ( $options['min'] > $length ) {
						return $options['message'];
					}
				}
			} elseif ( MWF_Functions::is_numeric( $options['max'] ) ) {
				if ( $options['max'] < $length ) {
					return $options['message'];
				}
			}
		}
	}

	/**
	 * minLength
	 * 値の文字数が範囲内
	 * @param string $name name属性
	 * @param array $options
	 * @return string エラーメッセージ
	 */
	public function minLength( $key, $options = array() ) {
		$value = $this->getValue( $key );
		if ( !is_null( $value ) && !$this->isEmpty( $value ) ) {
			if ( is_array( $value ) ) {
				$value = implode( $this->getSeparatorValue( $key ), $value );
			}
			$defaults = array(
				'min' => 0,
				'message' => __( 'The number of characters is a few.', MWF_Config::DOMAIN )
			);
			$options = array_merge( $defaults, $options );
			$length = mb_strlen( $value, $this->ENCODE );
			if ( MWF_Functions::is_numeric( $options['min'] ) && $options['min'] > $length ) {
				return $options['message'];
			}
		}
	}

	/**
	 * in
	 * 値が、配列で指定された中に含まれている
	 * @param string $name name属性
	 * @param array $options
	 * @return string エラーメッセージ
	 */
	public function in( $key, $options = array() ) {
		$value = $this->getValue( $key );
		if ( !is_null( $value ) && !$this->isEmpty( $value ) ) {
			if ( is_array( $value ) ) {
				$value = implode( $this->getSeparatorValue( $key ), $value );
			}
			$defaults = array(
				'options' => array(),
				'message' => __( 'This value is invalid.', MWF_Config::DOMAIN )
			);
			$options = array_merge( $defaults, $options );
			if ( !( is_array( $options['options'] ) && in_array( $value, $options['options'] ) ) ) {
				return $options['message'];
			}
		}
	}

	/**
	 * date
	 * 日付が正しいかどうか
	 * @param string $name name属性
	 * @param array $options
	 * @return string エラーメッセージ
	 */
	public function date( $key, $options = array() ) {
		$value = $this->getValue( $key );
		if ( !is_null( $value ) && !$this->isEmpty( $value ) ) {
			if ( is_array( $value ) ) {
				$value = implode( $this->getSeparatorValue( $key ), $value );
			}
			$defaults = array(
				'message' => __( 'This is not the format of a date.', MWF_Config::DOMAIN )
			);
			$options = array_merge( $defaults, $options );
			$timestamp = strtotime( $value );
			$year = date( 'Y', $timestamp );
			$month = date( 'm', $timestamp );
			$day = date( 'd', $timestamp );
			$checkdate = checkdate( $month, $day, $year );
			if ( !$timestamp || !$checkdate || preg_match( '/^[a-zA-Z]$/', $value ) || preg_match( '/^\s+$/', $value ) ) {
				return $options['message'];
			}
		}
	}

	/**
	 * fileType
	 * ファイル名が指定した拡張子を含む。types は , 区切り
	 * @param string $key name属性値
	 * @param array $options
	 * @return string エラーメッセージ
	 */
	public function fileType( $key, $options = array() ) {
		$value = $this->getValue( $key );
		if ( !is_null( $value ) && !$this->isEmpty( $value ) ) {
			if ( is_array( $value ) ) {
				$value = implode( $this->getSeparatorValue( $key ), $value );
			}
			$defaults = array(
				'types' => '',
				'message' => __( 'This file is invalid.', MWF_Config::DOMAIN )
			);
			$options = array_merge( $defaults, $options );
			$_types = explode( ',', $options['types'] );
			foreach ( $_types as $type ) {
				$types[] = preg_quote( trim( $type ) );
			}
			$types = implode( '|', $this->array_clean( $types ) );
			$pattern = '/\.(' . $types . ')$/';
			if ( !preg_match( $pattern, $value ) ) {
				return $options['message'];
			}
		}
	}

	/**
	 * fileSize
	 * ファイルが指定したサイズより小さい
	 * @param string $key name属性値
	 * @param array $options
	 * @return string エラーメッセージ
	 */
	public function fileSize( $key, $options = array() ) {
		$data = $this->Data->getValue( MWF_Config::UPLOAD_FILES );
		if ( !is_null( $data ) && is_array( $data ) && array_key_exists( $key, $data ) ) {
			$file = $data[$key];
			if ( !empty( $file['size'] ) ) {
				$defaults = array(
					'bytes' => '0',
					'message' => __( 'This file size is too big.', MWF_Config::DOMAIN )
				);
				$options = array_merge( $defaults, $options );
				if ( !( preg_match( '/^[\d]+$/', $options['bytes'] ) && $options['bytes'] > $file['size'] ) ) {
					return $options['message'];
				}
			}
		}
	}

	/**
	 * akismet_check
	 * Akismetのエラー。常にtrue。
	 * @param	String	キー
	 *			Array	( 'message' => )
	 * @return	String	エラーメッセージ
	 */
	public function akismet_check( $key, $options = array() ) {
		$defaults = array(
			'message' => __( 'The contents which you input were judged with spam.', MWF_Config::DOMAIN )
		);
		$options = array_merge( $defaults, $options );
		return $options['message'];
	}

	/**
	 * Error
	 * エラーオブジェクトを返す
	 * @return	Error	エラーオブジェクト
	 */
	public function Error() {
		return $this->Error;
	}

	/**
	 * isValid
	 * バリデートが通っているかチェック
	 * @return	Boolean
	 */
	protected function isValid() {
		$errors = $this->Error->getErrors();
		if ( empty( $errors ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * setRule
	 * バリデートが通っているかチェック
	 * @param	String	キー
	 * 			String	バリデーションルール名
	 * 			Array	オプション
	 * @return	Boolean
	 */
	public function setRule( $key, $rule, Array $options = array() ) {
		$rules = array(
			'rule' => $rule,
			'options' => $options
		);
		$this->validate[$key][] = $rules;
		return $this;
	}

	/**
	 * check
	 * validate実行
	 * @return	Boolean
	 */
	public function check() {
		foreach ( $this->validate as $key => $rules ) {
			foreach ( $rules as $ruleSet ) {
				if ( isset( $ruleSet['rule'] ) ) {
					$rule = $ruleSet['rule'];
					$options = array();
					if ( isset( $ruleSet['options'] ) ) {
						$options = $ruleSet['options'];
					}
					if ( method_exists( $this, $rule ) ) {
						$message = $this->$rule( $key, $options );
						if ( !empty( $message ) ) {
							$this->Error->setError( $key, $rule, $message );
						}
					}
				}
			}
		}
		return $this->isValid();
	}

	/**
	 * array_clean
	 * 配列内の値の重複を消す
	 * @param	Array
	 * @return	Array
	 */
	protected function array_clean( $array ) {
		return array_merge( array_diff( $array, array( '' ) ) );
	}

	/**
	 * isEmpty
	 * 値が空（0は許可）
	 * @param	Mixed
	 * @return	Boolean
	 */
	protected function isEmpty( $value ) {
		if ( $value === array() || $value === '' || $value === null ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * getSeparatorValue
	 * 送られてきたseparatorを返す
	 * @param string $key name属性
	 * @return string セパレータ
	 */
	public function getSeparatorValue( $key ) {
		$data = $this->Data->getValue( $key );
		if ( isset( $data ) && is_array( $data ) && array_key_exists( 'separator', $data ) ) {
			return $data['separator'];
		}
	}
}
?>