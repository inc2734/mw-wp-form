<?php
/**
 * Name: MW WP Form Data
 * Description: mw_wp_form のデータ操作用
 * Version: 1.2.1
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : October 10, 2013
 * Modified: August 8, 2014
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Data {

	/**
	 * MW_WP_Form_Data オブジェクト
	 */
	private static $Instance;

	/**
	 * フォームから送信された内容を保存した配列
	 */
	private $data = array();

	/**
	 * MW_Sesion オブジェクト
	 */
	private $Session;

	/**
	 * __construct
	 * @param string $key データのキー
	 */
	private function __construct( $key ) {
		$this->Session = new MW_Session( $key );
		$this->data = $this->Session->getValues();
	}

	public static function getInstance( $key ) {
		if ( is_null( self::$Instance ) ) {
			self::$Instance = new self( $key );
		}
		return self::$Instance;
	}

	/**
	 * getValue
	 * データを取得
	 * @param string $key データのキー
	 * @return string データ
	 */
	public function getValue( $key ) {
		if ( isset( $this->data[$key] ) )
			return $this->data[$key];
	}

	/**
	 * getValues
	 * 全てのデータを取得
	 * @return array データ
	 */
	public function getValues() {
		if ( $this->data === null )
			return array();
		return $this->data;
	}

	/**
	 * setValue
	 * データを追加
	 * @param string $key データのキー
	 * @param string $value 値
	 */
	public function setValue( $key, $value ){
		$this->data[$key] = $value;
		$this->Session->setValue( $key, $value );
	}

	/**
	 * setValue
	 * 複数のデータを一括で追加
	 * @param array 値
	 */
	public function setValues( Array $array ) {
		foreach ( $array as $key => $value ) {
			$this->data[$key] = $value;
			$this->Session->setValue( $key, $value );
		}
	}

	/**
	 * clearValue
	 * データを消す
	 * @param string $key データのキー
	 */
	public function clearValue( $key ) {
		unset( $this->data[$key] );
		$this->Session->clearValue( $key );
	}

	/**
	 * clearValues
	 * データを消す
	 * @param string $key データのキー
	 */
	public function clearValues() {
		$this->data = array();
		$this->Session->clearValues();
	}

	/**
	 * pushValue
	 * 指定した $key をキーと配列にデータを追加
	 * @param string $key データのキー
	 * @param string $value 値
	 */
	public function pushValue( $key, $value ) {
		$this->data[$key][] = $value;
		$this->Session->pushValue( $key, $value );
	}

	/**
	 * get
	 * 整形済み（メール送信可能な）データを取得
	 * @param string $key データのキー
	 * @return string データ
	 */
	public function get( $key ) {
		if ( isset( $this->data[$key] ) ) {
			if ( is_array( $this->data[$key] ) ) {
				if ( !array_key_exists( 'data', $this->data[$key] ) )
					return;
				if ( is_array( $this->data[$key]['data'] ) ) {
					return $this->getSeparatedValue( $key );
				} else {
					return $this->data[$key]['data'];
				}
			} else {
				return $this->data[$key];
			}
		}
	}

	/**
	 * getSeparatorValue
	 * 送られてきたseparatorを返す
	 * @param string $key name属性
	 * @return string
	 */
	public function getSeparatorValue( $key ) {
		$value = $this->getValue( $key );
		if ( is_array( $value ) && isset( $value['separator'] ) ) {
			return $value['separator'];
		}
	}

	/**
	 * getSeparatedValue
	 * 配列データを整形して返す ( 郵便番号等用 )
	 * @param string $key name属性
	 * @param array $children 選択肢
	 * @return string データ
	 */
	public function getSeparatedValue( $key, array $children = array() ) {
		$separator = $this->getSeparatorValue( $key );
		$value = $this->getValue( $key );
		if ( is_array( $value ) && isset( $value['data'] ) && is_array( $value['data'] ) && !empty( $separator ) ) {
			if ( $children ) {
				$rightData = array();
				foreach ( $value['data'] as $child ) {
					if ( isset( $children[$child] ) && !in_array( $children[$child], $rightData ) ) {
						$rightData[] = $children[$child];
					}
				}
				return implode( $separator, $rightData );
			} else {
				// すべて空のからのときはimplodeしないように（---がいってしまうため）
				foreach ( $value['data'] as $child ) {
					if ( $child !== '' && $child !== null ) {
						return implode( $separator, $value['data'] );
					}
				}
				return '';
			}
		}
	}
}
