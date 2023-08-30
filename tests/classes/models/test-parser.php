<?php
class MW_WP_Form_Parser_Test extends WP_UnitTestCase {

	protected function _create_form() {
		return $this->factory->post->create(
			array(
				'post_type' => MWF_Config::NAME,
			)
		);
	}

	public function tear_down() {
		parent::tear_down();
		_delete_all_data();
	}

	/**
	 * @test
	 * @group replace_for_mail_destination
	 */
	public function replace_for_mail_destination() {
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Setting  = new MW_WP_Form_Setting( $form_id );
		$Data     = MW_WP_Form_Data::connect( $form_key );
		$Parser   = new MW_WP_Form_Parser( $Setting );

		$Data->set( 'name-1', 'value-1' );
		$content = 'abcde {name-1} fghijk {name-2} lmnopq';
		$this->assertEquals( 'abcde  fghijk  lmnopq', $Parser->replace_for_mail_destination( $content ) );

		add_filter( 'mwform_custom_mail_tag', function( $value, $name, $saved_mail_id ) {
			if ( 'name-1' === $name ) {
				return 'custom-value-1';
			}
			return $value;
		}, 10, 3 );
		$this->assertEquals( 'abcde custom-value-1 fghijk  lmnopq', $Parser->replace_for_mail_destination( $content ) );
	}

	/**
	 * @test
	 * @group replace_for_mail_content
	 */
	public function replace_for_mail_content() {
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Setting  = new MW_WP_Form_Setting( $form_id );
		$Data     = MW_WP_Form_Data::connect( $form_key );
		$Parser   = new MW_WP_Form_Parser( $Setting );

		$Data->set( 'name-1', 'value-1' );
		$content = 'abcde {name-1} fghijk {name-2} lmnopq';
		$this->assertEquals( 'abcde value-1 fghijk  lmnopq', $Parser->replace_for_mail_content( $content ) );

		add_filter( 'mwform_custom_mail_tag', function( $value, $name, $saved_mail_id ) {
			if ( 'name-1' === $name ) {
				return 'custom-value-1';
			}
		}, 10, 3 );
		$this->assertEquals( 'abcde custom-value-1 fghijk  lmnopq', $Parser->replace_for_mail_content( $content ) );
	}

	/**
	 * @test
	 * @group replace_for_page
	 */
	public function replace_for_page() {
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Setting  = new MW_WP_Form_Setting( $form_id );
		$Data     = MW_WP_Form_Data::connect( $form_key );
		$Parser   = new MW_WP_Form_Parser( $Setting );

		$user      = wp_get_current_user();
		$post_id   = $this->factory->post->create( array( 'post_title' => 'title', 'post_type' => 'post' ) );
		$author_id = $this->factory->user->create( array( 'display_name' => 'user' ) );
		$this->go_to( get_permalink( $post_id ) );
		$Data->set( 'display_name', 'dummy' );
		$this->go_to( get_permalink($post_id) );

		$content = 'abcde {display_name} fghijk {name-1} lmnopq';
		$this->assertEquals(
			'abcde ' . get_the_author_meta( 'display_name', $user ) . ' fghijk  lmnopq',
			$Parser->replace_for_page( $content )
		);

		$content = 'abcde {post_title} fghijk {name-1} lmnopq';
		$this->assertEquals(
			'abcde title fghijk  lmnopq',
			$Parser->replace_for_page( $content )
		);

		$this->go_to( home_url( '?post_id=' . $post_id ) );
		$this->assertEquals(
			'abcde  fghijk  lmnopq',
			$Parser->replace_for_page( $content )
		);

		$Setting->set( 'querystring', 1 );
		$this->go_to( home_url( '?post_id=' . $post_id ) );
		$this->assertEquals(
			'abcde title fghijk  lmnopq',
			$Parser->replace_for_page( $content )
		);
	}

	/**
	 * @test
	 * @group search
	 */
	public function search() {
		$content = 'abcde {name-1} fghijk {name-2} lmnopq';
		$matches = MW_WP_Form_Parser::search( $content );
		$this->assertEquals( '{name-1}', $matches[0][0] );
		$this->assertEquals( '{name-2}', $matches[0][1] );
	}

	/**
	 * @test
	 * @group parse
	 */
	public function parse() {
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Setting  = new MW_WP_Form_Setting( $form_id );
		$Data     = MW_WP_Form_Data::connect( $form_key );
		$Parser   = new MW_WP_Form_Parser( $Setting );

		// Pattern: Tracking number
		$value = $Parser->parse( MWF_Config::TRACKINGNUMBER );
		$this->assertEquals( 1, $value );

		// Pattern: custom mail tag
		add_filter( 'mwform_custom_mail_tag', function( $value, $name, $saved_mail_id ) {
			if ( 'name-1' === $name ) {
				return 'custom-value-1';
			}
			return $value;
		}, 10, 3 );
		$value = $Parser->parse( 'name-1' );
		$this->assertEquals( 'custom-value-1', $value );

		// Pattern: default
		$Data->set( 'name-1', 'value-1' );
		$Data->set( 'name-2', 'value-2' );
		$value = $Parser->parse( 'name-1' );
		$this->assertEquals( 'custom-value-1', $value );
		$value = $Parser->parse( 'name-2' );
		$this->assertEquals( 'value-2', $value );
	}
}
