<?php
/**
 * Name       : MW WP Form Abstract Form Field
 * Description: フォームフィールドの抽象クラス
 * Version    : 1.7.6
 * Author     : Takashi Kitajima
 * Author URI : https://2inc.org
 * Created    : December 14, 2012
 * Modified   : December 26, 2016
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
abstract class MW_WP_Form_Abstract_Form_Field {

	/**
	 * $shortcode_name
	 * @var string
	 */
	protected $shortcode_name;

	/**
	 * $display_name
	 * @var string
	 */
	protected $display_name;

	/**
	 * $Form
	 * @var MW_WP_Form_Form
	 */
	protected $Form;

	/**
	 * @var MW_WP_Form_Data
	 */
	protected $Data;

	/**
	 * $defaults
	 * 属性値等初期値
	 * @var array
	 */
	protected $defaults = array();

	/**
	 * $atts
	 * 属性値
	 * @var array
	 */
	protected $atts = array();

	/**
	 * $form_key
	 * フォーム識別子
	 * @var string
	 */
	protected $form_key;

	/**
	 * $type
	 * フォームタグの種類 input|select|button|input_button|error|other
	 * @var string
	 */
	protected $type = 'other';

	/**
	 * $qtags
	 * qtagsの引数
	 * @var array
	 */
	protected $qtags = array(
		'id'      => '',
		'display' => '',
		'arg1'    => '',
		'arg2'    => '',
	);

	/**
	 * 囲み型ショートコードのコンテンツ
	 * @var string
	 */
	protected $element_content = null;

	public function __construct() {
		$this->_set_names();
		$this->_set_defaults();
		add_filter( 'mwform_form_fields'  , array( $this, '_mwform_form_fields' ) );
	}

	/**
	 * Add form field shortcodes
	 *
	 * @param MW_WP_Form_Form $Form
	 * @param string $form_key
	 * @param string $view_flg
	 */
	public function initialize( MW_WP_Form_Form $Form, $form_key, $view_flg ) {
		if ( empty( $this->shortcode_name ) ) {
			return;
		}

		$this->Form     = $Form;
		$this->form_key = $form_key;
		$this->Data     = MW_WP_Form_Data::connect( $form_key );

		switch( $view_flg ) {
			case 'input' :
				add_shortcode( $this->shortcode_name, array( $this, '_input_page' ) );
				break;
			case 'confirm' :
				add_shortcode( $this->shortcode_name, array( $this, '_confirm_page' ) );
				break;
			case 'complete' :
				break;
			default :
				exit( '$view_flg is not right value. $view_flg is ' . $view_flg . ' now.' );
		}

		return true;
	}

	/**
	 * shortcode_name、display_nameを定義。各子クラスで上書きする。
	 *
	 * @return array shortcode_name, display_name
	 */
	abstract protected function set_names();
	private function _set_names() {
		$args = $this->set_names();

		if ( empty( $args['shortcode_name'] ) || empty( $args['display_name'] ) ) {
			exit( get_class() . '::set_names() returns not right values. Returned values is ' . serialize( $args ) . ' now.' );
		}

		$this->shortcode_name = $args['shortcode_name'];
		$this->display_name   = $args['display_name'];
	}

	/**
	 * $this->defaultsを設定し返す
	 *
	 * @return array defaults
	 */
	abstract protected function set_defaults();
	private function _set_defaults() {
		$this->defaults = $this->set_defaults();
	}

	/**
	 * @param  string $key name属性
	 * @return string エラーHTML
	 */
	protected function get_error( $key ) {
		if ( ! is_array( $this->Data->get_validation_error( $key ) ) ) {
			return;
		}

		$error_html = '';
		$start_tag  = '<span class="error">';
		$end_tag    = '</span>';
		foreach ( $this->Data->get_validation_error( $key ) as $rule => $error ) {
			$rule = strtolower( $rule );
			$error = apply_filters(
				'mwform_error_message_' . $this->form_key,
				$error,
				$key,
				$rule
			);

			$error_html .= apply_filters( 'mwform_error_message_html',
				$start_tag . esc_html( $error ) . $end_tag,
				$error,
				$start_tag,
				$end_tag,
				$this->form_key,
				$key,
				$rule
			);
		}

		if ( $error_html ) {
			return apply_filters( 'mwform_error_message_wrapper', $error_html, $this->form_key );
		}
	}

	/**
	 * 入力ページでのフォーム項目を返す
	 *
	 * @param array $atts
	 * @param string $element_content
	 * @return string HTML
	 */
	abstract protected function input_page();
	public function _input_page( $atts, $element_content = null ) {
		$this->element_content = $element_content;
		if ( array_key_exists( 'value', $this->defaults ) && isset( $atts['name'] ) && ! isset( $atts['value'] ) ) {
			$atts['value'] = apply_filters(
				'mwform_value_' . $this->form_key,
				$this->defaults['value'],
				$atts['name']
			);
		}
		$this->atts = shortcode_atts( $this->defaults, $atts );
		return $this->input_page();
	}

	/**
	 * 確認ページでのフォーム項目を返す
	 *
	 * @param array $atts
	 * @param string $element_content
	 * @return string HTML
	 */
	abstract protected function confirm_page();
	public function _confirm_page( $atts, $element_content = null ) {
		$this->element_content = $element_content;
		$this->atts = shortcode_atts( $this->defaults, $atts );
		return $this->confirm_page();
	}

	/**
	 * 選択肢の配列を返す（:が含まれている場合は分割して前をキーに、後ろを表示名にする）
	 *
	 * @param string $_children
	 * @return array $children
	 */
	public function get_children( $_children ) {
		$children = array();
		if ( ! empty( $_children ) && ! is_array( $_children ) ) {
			$_children = explode( ',', $_children );
		}

		if ( is_array( $_children ) ) {
			foreach ( $_children as $child ) {
				$temp_replacement = '@-[_-_]-@';
				if ( preg_match( '/(^:[^:])|([^:]:[^:])/', $child ) ) {
					$child = str_replace( '::', $temp_replacement, $child );
					$child = explode( ':', $child, 2 );
				} else {
					$child = str_replace( '::', $temp_replacement, $child );
					$child = array( $child );
				}
				foreach ( $child as $child_key => $child_value ) {
					$child[ $child_key ] = str_replace( $temp_replacement, ':', $child_value );
				}
				if ( count( $child ) === 1 ) {
					$children[ $child[0] ] = $child[0] ;
				} else {
					$children[ $child[0] ] = $child[1];
				}
			}
		}

		if ( $this->form_key ) {
			$children = apply_filters( 'mwform_choices_' . $this->form_key, $children, $this->atts );
		}

		return $children;
	}

	/**
	 * フォームタグジェネレータのタグ選択肢とダイアログを設定
	 */
	public function add_tag_generator() {
		add_action( 'mwform_tag_generator_dialog', array( $this, '_mwform_tag_generator_dialog' ) );
		if ( 'other' !== $this->type ) {
			$tag = 'mwform_tag_generator_' . $this->type . '_option';
		} else {
			$tag = 'mwform_tag_generator_option';
		}
		add_action( $tag, array( $this, '_mwform_tag_generator_option' ) );
	}

	/**
	 * タグジェネレータのダイアログ枠を出力
	 */
	public function _mwform_tag_generator_dialog() {
		?>
		<div id="dialog-<?php echo esc_attr( $this->shortcode_name ); ?>" class="mwform-dialog" title="<?php echo esc_attr( $this->shortcode_name ); ?>">
			<div class="form">
				<?php $this->mwform_tag_generator_dialog( $this->defaults ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * タグジェネレータのダイアログを出力。各フォーム項目クラスでオーバーライド
	 */
	public function mwform_tag_generator_dialog( array $options = array() ) {}

	/**
	 * フォームタグ挿入ボタンのセレクトボックスに選択項目を追加
	 */
	public function _mwform_tag_generator_option() {
		$display_name = $this->qtags['display'];
		if ( $this->display_name ) {
			$display_name = $this->display_name;
		}
		?>
		<option value="<?php echo esc_attr( $this->shortcode_name ); ?>"><?php echo esc_html( $display_name ); ?></option>
		<?php
	}

	/**
	 * @param array $form_fields MW_WP_Form_Abstract_Form_Field を継承したオブジェクトの一覧
	 * @return array $form_fields
	 */
	public function _mwform_form_fields( array $form_fields ) {
		return array_merge( $form_fields, array( $this->shortcode_name => $this ) );
	}

	/**
	 * @return string 表示名
	 */
	public function get_display_name() {
		return $this->display_name;
	}

	/**
	 * @return string ショートコード名
	 */
	public function get_shortcode_name() {
		return $this->shortcode_name;
	}

	/**
	 * get_value_for_generator
	 */
	public function get_value_for_generator( $key, $options ) {
		$attributes = array_keys( $this->defaults );
		$attributes = array_flip( $attributes );

		if ( ! isset( $attributes[ $key ] ) ) {
			return;
		}

		if ( isset( $options[ $key ] ) ) {
			return $options[ $key ];
		} else {
			return '';
		}
	}
}
