<?php
class MW_WP_Form_Form_Test extends WP_UnitTestCase {

	/**
	 * @var MW_WP_Form_Form
	 */
	protected $Form;

	/**
	 * setUp
	 */
	public function setUp() {
		$this->Form = new MW_WP_Form_Form();
	}

	/**
	 * @group separator
	 */
	public function test_separator() {
		$this->assertEquals(
			'<input type="hidden" name="separator[separator]" value="-" />',
			$this->Form->separator( 'separator', '-' )
		);
	}

	/**
	 * @group separator
	 */
	public function test_separator_separatorがemptyなら出力しない() {
		$this->assertNull( $this->Form->separator( 'separator', '' ) );
	}

	/**
	 * @group children
	 */
	public function test_children() {
		$children = array( 'a' => 'aaa', 'b' => 'bbb' );
		$this->assertEquals(
			'<input type="hidden" name="__children[test][]" value="' . esc_attr( json_encode( $children ) ) . '" />',
			$this->Form->children( 'test', $children )
		);
	}

	/**
	 * @group start
	 */
	public function test_start() {
		$this->assertEquals(
			'<form method="post" action="" enctype="' . esc_attr( 'multipart/form-data' ) . '">',
			$this->Form->start()
		);
	}

	/**
	 * @group end
	 */
	public function test_end() {
		$this->assertEquals(
			'</form>',
			$this->Form->end()
		);
	}

	/**
	 * @group end
	 */
	public function test_end_mwform_form_end_htmlフック使用() {
		add_filter( 'mwform_form_end_html', function( $html ) {
			return $html . 'test';
		} );
		$this->assertEquals(
			'test</form>',
			$this->Form->end()
		);
	}

	/**
	 * @group text
	 */
	public function test_text() {
		$this->assertEquals(
			'<input type="text" name="text" size="60" maxlength="255" value="" />',
			$this->Form->text( 'text' )
		);
	}

	/**
	 * @group text
	 */
	public function test_text_id() {
		$this->assertEquals(
			'<input type="text" name="text" id="text" size="60" maxlength="255" value="" />',
			$this->Form->text( 'text', array( 'id' => 'text' ) )
		);
	}

	/**
	 * @group text
	 */
	public function test_text_size() {
		$this->assertEquals(
			'<input type="text" name="text" size="" maxlength="255" value="" />',
			$this->Form->text( 'text', array( 'size' => '' ) )
		);
	}

	/**
	 * @group text
	 */
	public function test_text_maxlength() {
		$this->assertEquals(
			'<input type="text" name="text" size="60" maxlength="" value="" />',
			$this->Form->text( 'text', array( 'maxlength' => '' ) )
		);
	}

	/**
	 * @group text
	 */
	public function test_text_value() {
		$this->assertEquals(
			'<input type="text" name="text" size="60" maxlength="255" value="text" />',
			$this->Form->text( 'text', array( 'value' => 'text' ) )
		);
	}

	/**
	 * @group text
	 */
	public function test_text_data_conv_half_alphanumeric() {
		$this->assertEquals(
			'<input type="text" name="text" size="60" maxlength="255" value="" data-conv-half-alphanumeric="true" />',
			$this->Form->text( 'text', array( 'conv-half-alphanumeric' => 'true' ) )
		);
	}

	/**
	 * @group text
	 */
	public function test_text_placeholder() {
		$this->assertEquals(
			'<input type="text" name="text" size="60" maxlength="255" value="" placeholder="text" />',
			$this->Form->text( 'text', array( 'placeholder' => 'text' ) )
		);
	}

	/**
	 * @group hidden
	 */
	public function test_hidden() {
		$this->assertEquals(
			'<input type="hidden" name="hidden" value="" />',
			$this->Form->hidden( 'hidden', '' )
		);
	}

	/**
	 * @group hidden
	 */
	public function test_hidden_value() {
		$this->assertEquals(
			'<input type="hidden" name="hidden" value="value" />',
			$this->Form->hidden( 'hidden', 'value' )
		);
	}

	/**
	 * @group password
	 */
	public function test_password() {
		$this->assertEquals(
			'<input type="password" name="password" size="60" maxlength="255" value="" />',
			$this->Form->password( 'password' )
		);
	}

	/**
	 * @group password
	 */
	public function test_password_id() {
		$this->assertEquals(
			'<input type="password" name="password" id="id" size="60" maxlength="255" value="" />',
			$this->Form->password( 'password', array( 'id' => 'id' ) )
		);
	}

	/**
	 * @group password
	 */
	public function test_password_size() {
		$this->assertEquals(
			'<input type="password" name="password" size="" maxlength="255" value="" />',
			$this->Form->password( 'password', array( 'size' => '' ) )
		);
	}

	/**
	 * @group password
	 */
	public function test_password_maxlength() {
		$this->assertEquals(
			'<input type="password" name="password" size="60" maxlength="" value="" />',
			$this->Form->password( 'password', array( 'maxlength' => '' ) )
		);
	}

	/**
	 * @group password
	 */
	public function test_password_value() {
		$this->assertEquals(
			'<input type="password" name="password" size="60" maxlength="255" value="value" />',
			$this->Form->password( 'password', array( 'value' => 'value' ) )
		);
	}

	/**
	 * @group password
	 */
	public function test_password_placeholder() {
		$this->assertEquals(
			'<input type="password" name="password" size="60" maxlength="255" value="" placeholder="placeholder" />',
			$this->Form->password( 'password', array( 'placeholder' => 'placeholder' ) )
		);
	}

	/**
	 * @group zip
	 */
	public function test_zip() {
		$this->assertEquals(
			'<span class="mwform-zip-field">〒<input type="text" name="zip[data][0]" size="4" maxlength="3" value="" /> - <input type="text" name="zip[data][1]" size="5" maxlength="4" value="" /><input type="hidden" name="zip[separator]" value="-" /></span>',
			$this->Form->zip( 'zip' )
		);
	}

	/**
	 * @group zip
	 */
	public function test_zip_conv_half_alphanumeric() {
		$this->assertEquals(
			'<span class="mwform-zip-field">〒<input type="text" name="zip[data][0]" size="4" maxlength="3" value="" data-conv-half-alphanumeric="true" /> - <input type="text" name="zip[data][1]" size="5" maxlength="4" value="" data-conv-half-alphanumeric="true" /><input type="hidden" name="zip[separator]" value="-" /></span>',
			$this->Form->zip( 'zip', array( 'conv-half-alphanumeric' => 'true' ) )
		);
	}

	/**
	 * @group zip
	 */
	public function test_zip_value() {
		$this->assertEquals(
			'<span class="mwform-zip-field">〒<input type="text" name="zip[data][0]" size="4" maxlength="3" value="value" /> - <input type="text" name="zip[data][1]" size="5" maxlength="4" value="" /><input type="hidden" name="zip[separator]" value="-" /></span>',
			$this->Form->zip( 'zip', array( 'value' => 'value' ) )
		);
	}

	/**
	 * @group tel
	 */
	public function test_tel() {
		$this->assertEquals(
			'<span class="mwform-tel-field"><input type="text" name="tel[data][0]" size="6" maxlength="5" value="" /> - <input type="text" name="tel[data][1]" size="5" maxlength="4" value="" /> - <input type="text" name="tel[data][2]" size="5" maxlength="4" value="" /><input type="hidden" name="tel[separator]" value="-" /></span>',
			$this->Form->tel( 'tel' )
		);
	}

	/**
	 * @group tel
	 */
	public function test_tel_conv_half_alphanumeric() {
		$this->assertEquals(
			'<span class="mwform-tel-field"><input type="text" name="tel[data][0]" size="6" maxlength="5" value="" data-conv-half-alphanumeric="true" /> - <input type="text" name="tel[data][1]" size="5" maxlength="4" value="" data-conv-half-alphanumeric="true" /> - <input type="text" name="tel[data][2]" size="5" maxlength="4" value="" data-conv-half-alphanumeric="true" /><input type="hidden" name="tel[separator]" value="-" /></span>',
			$this->Form->tel( 'tel', array( 'conv-half-alphanumeric' => 'true' ) )
		);
	}

	/**
	 * @group tel
	 */
	public function test_tel_value() {
		$this->assertEquals(
			'<span class="mwform-tel-field"><input type="text" name="tel[data][0]" size="6" maxlength="5" value="value" /> - <input type="text" name="tel[data][1]" size="5" maxlength="4" value="" /> - <input type="text" name="tel[data][2]" size="5" maxlength="4" value="" /><input type="hidden" name="tel[separator]" value="-" /></span>',
			$this->Form->tel( 'tel', array( 'value' => 'value' ) )
		);
	}

	/**
	 * @group textarea
	 */
	public function test_textarea() {
		$this->assertEquals(
			'<textarea name="textarea" cols="50" rows="5"></textarea>',
			$this->Form->textarea( 'textarea' )
		);
	}

	/**
	 * @group textarea
	 */
	public function test_textarea_id() {
		$this->assertEquals(
			'<textarea name="textarea" id="id" cols="50" rows="5"></textarea>',
			$this->Form->textarea( 'textarea', array( 'id' => 'id' ) )
		);
	}

	/**
	 * @group textarea
	 */
	public function test_textarea_cols() {
		$this->assertEquals(
			'<textarea name="textarea" cols="" rows="5"></textarea>',
			$this->Form->textarea( 'textarea', array( 'cols' => '' ) )
		);
	}

	/**
	 * @group textarea
	 */
	public function test_textarea_rows() {
		$this->assertEquals(
			'<textarea name="textarea" cols="50" rows=""></textarea>',
			$this->Form->textarea( 'textarea', array( 'rows' => '' ) )
		);
	}

	/**
	 * @group textarea
	 */
	public function test_textarea_value() {
		$this->assertEquals(
			'<textarea name="textarea" cols="50" rows="5">value</textarea>',
			$this->Form->textarea( 'textarea', array( 'value' => 'value' ) )
		);
	}

	/**
	 * @group textarea
	 */
	public function test_textarea_placeholder() {
		$this->assertEquals(
			'<textarea name="textarea" cols="50" rows="5" placeholder="placeholder"></textarea>',
			$this->Form->textarea( 'textarea', array( 'placeholder' => 'placeholder' ) )
		);
	}

	/**
	 * @group select
	 */
	public function test_select() {
		$this->assertEquals(
			'<select name="select"></select>',
			$this->Form->select( 'select' )
		);
	}

	/**
	 * @group select
	 */
	public function test_select_children() {
		$this->assertEquals(
			'<select name="select"><option value="a" >a</option><option value="b" >b</option><option value="c" >c</option></select>',
			$this->Form->select( 'select', array( 'a' => 'a', 'b' => 'b', 'c' => 'c' ) )
		);
	}

	/**
	 * @group select
	 */
	public function test_select_id() {
		$this->assertEquals(
			'<select name="select" id="id"><option value="a" >a</option><option value="b" >b</option><option value="c" >c</option></select>',
			$this->Form->select( 'select', array( 'a' => 'a', 'b' => 'b', 'c' => 'c' ), array( 'id' => 'id' ) )
		);
	}

	/**
	 * @group select
	 */
	public function test_select_valueが一致する() {
		$this->assertEquals(
			'<select name="select"><option value="a"  selected=\'selected\'>a</option><option value="b" >b</option><option value="c" >c</option></select>',
			$this->Form->select( 'select', array( 'a' => 'a', 'b' => 'b', 'c' => 'c' ), array( 'value' => 'a' ) )
		);
	}

	/**
	 * @group select
	 */
	public function test_select_valueが一致しない() {
		$this->assertEquals(
			'<select name="select"><option value="a" >a</option><option value="b" >b</option><option value="c" >c</option></select>',
			$this->Form->select( 'select', array( 'a' => 'a', 'b' => 'b', 'c' => 'c' ), array( 'value' => 'value' ) )
		);
	}

	/**
	 * @group radio
	 */
	public function test_radio() {
		$this->assertEquals(
			'',
			$this->Form->radio( 'radio' )
		);
	}

	/**
	 * @group radio
	 */
	public function test_radio_children() {
		$this->assertEquals(
			'<span class=""><label><input type="radio" name="radio" value="a"  />a</label></span><span class=""><label><input type="radio" name="radio" value="b"  />b</label></span><span class=""><label><input type="radio" name="radio" value="c"  />c</label></span>',
			$this->Form->radio( 'radio', array( 'a' => 'a', 'b' => 'b', 'c' => 'c' ) )
		);
	}

	/**
	 * @group radio
	 */
	public function test_radio_id() {
		$this->assertEquals(
			'<span class=""><label for="id-1"><input type="radio" name="radio" value="a"  id="id-1" />a</label></span><span class=""><label for="id-2"><input type="radio" name="radio" value="b"  id="id-2" />b</label></span><span class=""><label for="id-3"><input type="radio" name="radio" value="c"  id="id-3" />c</label></span>',
			$this->Form->radio( 'radio', array( 'a' => 'a', 'b' => 'b', 'c' => 'c' ), array( 'id' => 'id' ) )
		);
	}

	/**
	 * @group radio
	 */
	public function test_radio_valueが一致する() {
		$this->assertEquals(
			'<span class=""><label><input type="radio" name="radio" value="a" checked=\'checked\'  />a</label></span><span class=""><label><input type="radio" name="radio" value="b"  />b</label></span><span class=""><label><input type="radio" name="radio" value="c"  />c</label></span>',
			$this->Form->radio( 'radio', array( 'a' => 'a', 'b' => 'b', 'c' => 'c' ), array( 'value' => 'a' ) )
		);
	}

	/**
	 * @group radio
	 */
	public function test_radio_valueが一致しない() {
		$this->assertEquals(
			'<span class=""><label><input type="radio" name="radio" value="a"  />a</label></span><span class=""><label><input type="radio" name="radio" value="b"  />b</label></span><span class=""><label><input type="radio" name="radio" value="c"  />c</label></span>',
			$this->Form->radio( 'radio', array( 'a' => 'a', 'b' => 'b', 'c' => 'c' ), array( 'value' => 'value' ) )
		);
	}

	/**
	 * @group radio
	 */
	public function test_radio_vertically() {
		$this->assertEquals(
			'<span class="vertical-item"><label><input type="radio" name="radio" value="a"  />a</label></span><span class="vertical-item"><label><input type="radio" name="radio" value="b"  />b</label></span><span class="vertical-item"><label><input type="radio" name="radio" value="c"  />c</label></span>',
			$this->Form->radio( 'radio', array( 'a' => 'a', 'b' => 'b', 'c' => 'c' ), array( 'vertically' => 'true' ) )
		);
	}

	/**
	 * @group checkbox
	 */
	public function test_checkbox() {
		$this->assertEquals(
			'<input type="hidden" name="checkbox[separator]" value="," />',
			$this->Form->checkbox( 'checkbox' )
		);
	}

	/**
	 * @group checkbox
	 */
	public function test_checkbox_children() {
		$this->assertEquals(
			'<span class=""><label><input type="checkbox" name="checkbox[data][]" value="a"  />a</label></span><span class=""><label><input type="checkbox" name="checkbox[data][]" value="b"  />b</label></span><span class=""><label><input type="checkbox" name="checkbox[data][]" value="c"  />c</label></span><input type="hidden" name="checkbox[separator]" value="," />',
			$this->Form->checkbox( 'checkbox', array( 'a' => 'a', 'b' => 'b', 'c' => 'c' ) )
		);
	}

	/**
	 * @group checkbox
	 */
	public function test_checkbox_separator() {
		$this->assertEquals(
			'<span class=""><label><input type="checkbox" name="checkbox[data][]" value="a"  checked=\'checked\' />a</label></span><span class=""><label><input type="checkbox" name="checkbox[data][]" value="b"  checked=\'checked\' />b</label></span><span class=""><label><input type="checkbox" name="checkbox[data][]" value="c"  />c</label></span><input type="hidden" name="checkbox[separator]" value="、" />',
			$this->Form->checkbox( 'checkbox', array( 'a' => 'a', 'b' => 'b', 'c' => 'c' ), array( 'value' => 'a、b' ), '、' )
		);
	}

	/**
	 * @group checkbox
	 */
	public function test_checkbox_id() {
		$this->assertEquals(
			'<span class=""><label for="id-1"><input type="checkbox" name="checkbox[data][]" value="a" id="id-1"  />a</label></span><span class=""><label for="id-2"><input type="checkbox" name="checkbox[data][]" value="b" id="id-2"  />b</label></span><span class=""><label for="id-3"><input type="checkbox" name="checkbox[data][]" value="c" id="id-3"  />c</label></span><input type="hidden" name="checkbox[separator]" value="," />',
			$this->Form->checkbox( 'checkbox', array( 'a' => 'a', 'b' => 'b', 'c' => 'c' ), array( 'id' => 'id' ) )
		);
	}

	/**
	 * @group checkbox
	 */
	public function test_checkbox_valueが一致する_文字列() {
		$this->assertEquals(
			'<span class=""><label><input type="checkbox" name="checkbox[data][]" value="a"  checked=\'checked\' />a</label></span><span class=""><label><input type="checkbox" name="checkbox[data][]" value="b"  checked=\'checked\' />b</label></span><span class=""><label><input type="checkbox" name="checkbox[data][]" value="c"  />c</label></span><input type="hidden" name="checkbox[separator]" value="," />',
			$this->Form->checkbox( 'checkbox', array( 'a' => 'a', 'b' => 'b', 'c' => 'c' ), array( 'value' => 'a,b' ) )
		);
	}

	/**
	 * @group checkbox
	 */
	public function test_checkbox_valueが一致する_配列() {
		$this->assertEquals(
			'<span class=""><label><input type="checkbox" name="checkbox[data][]" value="a"  checked=\'checked\' />a</label></span><span class=""><label><input type="checkbox" name="checkbox[data][]" value="b"  checked=\'checked\' />b</label></span><span class=""><label><input type="checkbox" name="checkbox[data][]" value="c"  />c</label></span><input type="hidden" name="checkbox[separator]" value="," />',
			$this->Form->checkbox( 'checkbox',
				array( 'a' => 'a', 'b' => 'b', 'c' => 'c' ),
				array( 'value' => array( 'a' => 'a', 'b' => 'b' )
			) )
		);
	}

	/**
	 * @group checkbox
	 */
	public function test_checkbox_valueが一致しない() {
		$this->assertEquals(
			'<span class=""><label><input type="checkbox" name="checkbox[data][]" value="a"  />a</label></span><span class=""><label><input type="checkbox" name="checkbox[data][]" value="b"  />b</label></span><span class=""><label><input type="checkbox" name="checkbox[data][]" value="c"  />c</label></span><input type="hidden" name="checkbox[separator]" value="," />',
			$this->Form->checkbox( 'checkbox', array( 'a' => 'a', 'b' => 'b', 'c' => 'c' ), array( 'value' => 'value' ) )
		);
	}

	/**
	 * @group checkbox
	 */
	public function test_checkbox_vertically() {
		$this->assertEquals(
			'<span class="vertical-item"><label><input type="checkbox" name="checkbox[data][]" value="a"  />a</label></span><span class="vertical-item"><label><input type="checkbox" name="checkbox[data][]" value="b"  />b</label></span><span class="vertical-item"><label><input type="checkbox" name="checkbox[data][]" value="c"  />c</label></span><input type="hidden" name="checkbox[separator]" value="," />',
			$this->Form->checkbox( 'checkbox', array( 'a' => 'a', 'b' => 'b', 'c' => 'c' ), array( 'vertically' => 'true' ) )
		);
	}

	/**
	 * @group submit
	 */
	public function test_submit() {
		$this->assertEquals(
			'<input type="submit" name="submit" value="" />',
			$this->Form->submit( 'submit', '' )
		);
	}

	/**
	 * @group submit
	 */
	public function test_submit_value() {
		$this->assertEquals(
			'<input type="submit" name="submit" value="value" />',
			$this->Form->submit( 'submit', 'value' )
		);
	}

	/**
	 * @group button
	 */
	public function test_button() {
		$this->assertEquals(
			'<input type="button" name="button" value="" />',
			$this->Form->button( 'button', '' )
		);
	}

	/**
	 * @group button
	 */
	public function test_button_value() {
		$this->assertEquals(
			'<input type="button" name="button" value="value" />',
			$this->Form->button( 'button', 'value' )
		);
	}

	/**
	 * @group datepicker
	 */
	public function test_datepicker() {
		$this->assertEquals(
			'<input type="text" name="datepicker" size="30" value="" /><script type="text/javascript">jQuery( function( $ ) { $("input[name=\'datepicker\']").datepicker( {  } ); } );</script>',
			$this->Form->datepicker( 'datepicker' )
		);
	}

	/**
	 * @group datepicker
	 */
	public function test_datepicker_id() {
		$this->assertEquals(
			'<input type="text" name="datepicker" id="id" size="30" value="" /><script type="text/javascript">jQuery( function( $ ) { $("input[name=\'datepicker\']").datepicker( {  } ); } );</script>',
			$this->Form->datepicker( 'datepicker', array( 'id' => 'id' ) )
		);
	}

	/**
	 * @group datepicker
	 */
	public function test_datepicker_size() {
		$this->assertEquals(
			'<input type="text" name="datepicker" size="" value="" /><script type="text/javascript">jQuery( function( $ ) { $("input[name=\'datepicker\']").datepicker( {  } ); } );</script>',
			$this->Form->datepicker( 'datepicker', array( 'size' => '' ) )
		);
	}

	/**
	 * @group datepicker
	 */
	public function test_datepicker_js() {
		$this->assertEquals(
			'<input type="text" name="datepicker" size="30" value="" /><script type="text/javascript">jQuery( function( $ ) { $("input[name=\'datepicker\']").datepicker( { showMonthAfterYear: true } ); } );</script>',
			$this->Form->datepicker( 'datepicker', array( 'js' => 'showMonthAfterYear: true' ) )
		);
	}

	/**
	 * @group datepicker
	 */
	public function test_datepicker_value() {
		$this->assertEquals(
			'<input type="text" name="datepicker" size="30" value="value" /><script type="text/javascript">jQuery( function( $ ) { $("input[name=\'datepicker\']").datepicker( {  } ); } );</script>',
			$this->Form->datepicker( 'datepicker', array( 'value' => 'value' ) )
		);
	}

	/**
	 * @group file
	 */
	public function test_file() {
		$this->assertEquals(
			'<input type="file" name="file" /><span data-mwform-file-delete="file" class="mwform-file-delete">&times;</span>',
			$this->Form->file( 'file' )
		);
	}

	/**
	 * @group file
	 */
	public function test_file_id() {
		$this->assertEquals(
			'<input type="file" name="file" /><span data-mwform-file-delete="file" class="mwform-file-delete">&times;</span>',
			$this->Form->file( 'file', array( 'id' => 'id' ) )
		);
	}

	/**
	 * @group generate_attributes
	 */
	public function test_generate_attributes_空のときはNull() {
		$this->assertNull( $this->Form->generate_attributes( array() ) );
	}

	/**
	 * @group generate_attributes
	 */
	public function test_generate_attributes_全てNullのときはNull() {
		$attributes = array(
			'conv-half-alphanumeric' => null,
			'size' => null,
		);
		$this->assertNull( $this->Form->generate_attributes( $attributes ) );
	}

	/**
	 * @group generate_attributes
	 */
	public function test_generate_attributes_conv_half_alphanumericのときはキーを変換() {
		$attributes = array(
			'conv-half-alphanumeric' => 'true',
			'size' => '60',
		);
		$this->assertEquals(
			' data-conv-half-alphanumeric="true" size="60"',
			$this->Form->generate_attributes( $attributes )
		);
	}
}
