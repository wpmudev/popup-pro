<?php

/**
 * Test the popup rules.
 */
class Popup_Test_Rules extends WP_UnitTestCase {

	protected $rules = array(
		array(
			'file' => 'class-popup-rule-advurl.php',     'class' => 'IncPopupRule_AdvUrl',     'available' => true,
		),
		array(
			'file' => 'class-popup-rule-browser.php',    'class' => 'IncPopupRule_Browser',    'available' => true,
		),
		array(
			'file' => 'class-popup-rule-category.php',   'class' => 'IncPopupRule_Category',   'available' => true,
		),
		array(
			'file' => 'class-popup-rule-events.php',     'class' => 'IncPopupRule_Events',     'available' => true,
		),
		array(
			'file' => 'class-popup-rule-geo.php',        'class' => 'IncPopupRule_Geo',        'available' => true,
		),
		array(
			'file' => 'class-popup-rule-membership.php', 'class' => 'IncPopupRule_Membership', 'available' => true,
		),
		array(
			'file' => 'class-popup-rule-popup.php',      'class' => 'IncPopupRule_Popup',      'available' => true,
		),
		array(
			'file' => 'class-popup-rule-posttype.php',   'class' => 'IncPopupRule_Posttype',   'available' => true,
		),
		array(
			'file' => 'class-popup-rule-prosite.php',    'class' => 'IncPopupRule_Prosite',    'available' => true,
		),
		array(
			'file' => 'class-popup-rule-referrer.php',   'class' => 'IncPopupRule_Referrer',   'available' => true,
		),
		array(
			'file' => 'class-popup-rule-role.php',       'class' => 'IncPopupRule_UserRole',   'available' => true,
		),
		array(
			'file' => 'class-popup-rule-url.php',        'class' => 'IncPopupRule_Url',        'available' => true,
		),
		array(
			'file' => 'class-popup-rule-user.php',       'class' => 'IncPopupRule_User',       'available' => true,
		),
		array(
			'file' => 'class-popup-rule-width.php',      'class' => 'IncPopupRule_Width',      'available' => true,
		),
		array(
			'file' => 'class-popup-rule-xprofile.php',   'class' => 'IncPopupRule_XProfile',   'available' => true,
		),
	);

	/**
	 * Check if all rules are available
	 */
	function test_available_rules() {
		// Check if the Rule-Collection exists
		$this->assertTrue( class_exists( 'IncPopupRules' ), 'Missing class: IncPopupRules' );

		// Load all rules.
		foreach ( $this->rules as $info ) {
			$path = PO_INC_DIR . 'rules/' . $info['file'];
			$this->assertTrue( file_exists( $path ), 'Rule not found: ' . $path );

			include_once $path;
			if ( $info['available'] ) {
				$this->assertTrue( class_exists( $info['class'] ), 'Class not found: ' . $info['class'] );
			} else {
				$this->assertFalse( class_exists( $info['class'] ), 'Class should not exist: ' . $info['class'] );
			}
		}

		// Check if all rules are loaded.
		foreach ( $this->rules as $info ) {

			if ( $info['available'] ) {
				$this->assertArrayHasKey(
					$info['class'],
					IncPopupRules::$classes,
					'Rule not registered: ' . $info['class']
				);
			} else {
				$this->assertArrayNotHasKey(
					$info['class'],
					IncPopupRules::$classes,
					'Rule should not be registered: ' . $info['class']
				);
			}
		}
	}

	/**
	 * Test the function of the simple URL rule
	 *
	 * @depends test_available_rules
	 */
	function test_url_rule() {
		$rule = IncPopupRules::$classes['IncPopupRule_Url'];

		$this->assertTrue( is_callable( array( $rule, 'current_url' ) ), 'Missing or private: $rule->current_url()' );
		$this->assertTrue( is_callable( array( $rule, 'check_url' ) ), 'Missing or private: $rule->check_url()' );

		$_REQUEST['thefrom'] = 'http://www.test.site/';
		$this->assertEquals( 'http://www.test.site/', $rule->current_url(), 'Invalid URL' );

		$_REQUEST['thefrom'] = 'https://www.test.site/';
		$this->assertEquals( 'https://www.test.site/', $rule->current_url(), 'Invalid URL' );

		$_REQUEST['thefrom'] = 'http://www.test.site/page-a';
		$this->assertEquals( 'http://www.test.site/page-a', $rule->current_url(), 'Invalid URL' );

		$_REQUEST['thefrom'] = 'http://www.test.site/page-a?page=2';
		$this->assertEquals( 'http://www.test.site/page-a?page=2', $rule->current_url(), 'Invalid URL' );

		$_REQUEST['thefrom'] = 'http://www.test.site/page-a?page=2#comments';
		$this->assertEquals( 'http://www.test.site/page-a?page=2', $rule->current_url(), 'Invalid URL' );

		unset( $_REQUEST['thefrom'] );

		// Default PHPUnit Request URL:
		$this->assertEquals( 'http://example.org', $rule->current_url(), 'Invalid URL' );

		// -- Test the "check_url" function, which is the core of this condition!

		// 4 URL Lists where a+b are identical and c+d are identical.
		$url_list_1 = array(
			'a' => array( 'http://example.org' ),
			'b' => array( 'http://example.org/' ),
			'c' => array( 'example.org' ),
			'd' => array( 'example.org/' ),
		);

		foreach ( $url_list_1 as $key => $list ) {
			$this->assertTrue( $rule->check_url( 'http://example.org', $list ), 'Rule-1' . $key . '-1' );
			$this->assertTrue( $rule->check_url( 'http://example.org/', $list ), 'Rule-1' . $key . '-2' );
			$this->assertFalse( $rule->check_url( 'example.org', $list ), 'Rule-1' . $key . '-3' );
			$this->assertFalse( $rule->check_url( 'example.org/', $list ), 'Rule-1' . $key . '-4' );

			if ( $key == 'a' or $key == 'b' ) {
				$this->assertFalse( $rule->check_url( 'https://example.org', $list ), 'Rule-1' . $key . '-5' );
			} else {
				$this->assertTrue( $rule->check_url( 'https://example.org', $list ), 'Rule-1' . $key . '-5' );
			}
		}

		// 1 URL List with different rules.
		$url_list_2 = array(
			'http://example.org/page-a',
			'http://example.org/page-a?page=2',
			'http://example.org/page-a?page=3#comments',
			'example.org/page-b',
		);

		$this->assertFalse( $rule->check_url( 'http://example.org', $url_list_2 ), 'Rule-2-1' );
		$this->assertFalse( $rule->check_url( 'http://example.org/', $url_list_2 ), 'Rule-2-2' );
		$this->assertFalse( $rule->check_url( 'example.org', $url_list_2 ), 'Rule-2-3' );
		$this->assertFalse( $rule->check_url( 'example.org/', $url_list_2 ), 'Rule-2-4' );
		$this->assertFalse( $rule->check_url( 'https://example.org', $url_list_2 ), 'Rule-2-5' );
		$this->assertTrue( $rule->check_url( 'http://example.org/page-a', $url_list_2 ), 'Rule-2-6' );
		$this->assertTrue( $rule->check_url( 'http://example.org/page-a/', $url_list_2 ), 'Rule-2-7' );
		$this->assertTrue( $rule->check_url( 'http://example.org/page-a?page=2', $url_list_2 ), 'Rule-2-8' );
		$this->assertTrue( $rule->check_url( 'http://example.org/page-a?page=3', $url_list_2 ), 'Rule-2-9' );
		$this->assertFalse( $rule->check_url( 'https://example.org/page-a?page=3', $url_list_2 ), 'Rule-2-10' ); // https!
		$this->assertTrue( $rule->check_url( 'http://example.org/page-a?page=3/', $url_list_2 ), 'Rule-2-11' );
		$this->assertTrue( $rule->check_url( 'http://example.org/page-a?page=3#comments', $url_list_2 ), 'Rule-2-12' );
		$this->assertTrue( $rule->check_url( 'http://example.org/page-a?page=3#not-defined', $url_list_2 ), 'Rule-2-13' );
		$this->assertTrue( $rule->check_url( 'http://example.org/page-a?page=2#new-tag', $url_list_2 ), 'Rule-2-14' );
		$this->assertTrue( $rule->check_url( 'http://example.org/page-b', $url_list_2 ), 'Rule-2-15' );
		$this->assertTrue( $rule->check_url( 'https://example.org/page-b', $url_list_2 ), 'Rule-2-16' );
		$this->assertTrue( $rule->check_url( 'http://example.org/page-b/', $url_list_2 ), 'Rule-2-17' );
		$this->assertTrue( $rule->check_url( 'https://example.org/page-b/', $url_list_2 ), 'Rule-2-18' );
	}


	/**
	 * Test the function of the simple URL rule
	 *
	 * @depends test_available_rules
	 */
	function test_url_referrer() {
		$rule = IncPopupRules::$classes['IncPopupRule_Referrer'];

		$this->assertEquals( '', $rule->get_referrer(), 'Referrer should be empty' );

		$_REQUEST['thereferrer'] = 'http://www.google.com/?some-param=1234';
		$this->assertEquals( 'http://www.google.com/?some-param=1234', $rule->get_referrer(), 'Referrer expected' );

		// -- Test the "test_referrer()" function

		// This rule will always return false:
		$ref_list_1 = array( '' );
		$this->assertFalse( $rule->test_referrer( $ref_list_1 ), 'Rule-1-1' );

		// Empty referrer will always return false:
		unset( $_REQUEST['thereferrer'] );
		$this->assertFalse( $rule->test_referrer( $ref_list_1 ), 'Rule-1-2' );

		$ref_list_2 = array( '.google.', '?moogle', 'http://example.org/' );

		$_REQUEST['thereferrer'] = 'http://www.google.com/?some-param=1234';
		$this->assertTrue( $rule->test_referrer( $ref_list_2 ), 'Rule-2-1' );
		$_REQUEST['thereferrer'] = 'http://www.shoogle.com/?some-param=1234';
		$this->assertFalse( $rule->test_referrer( $ref_list_2 ), 'Rule-2-2' );
		$_REQUEST['thereferrer'] = 'http://www.shoogle.com/?moogle=1234';
		$this->assertTrue( $rule->test_referrer( $ref_list_2 ), 'Rule-2-3' );
		$_REQUEST['thereferrer'] = 'http://example.org/?some-param=1234';
		$this->assertTrue( $rule->test_referrer( $ref_list_2 ), 'Rule-2-4' );
		$_REQUEST['thereferrer'] = 'http://example.org/';
		$this->assertTrue( $rule->test_referrer( $ref_list_2 ), 'Rule-2-5' );
		$_REQUEST['thereferrer'] = 'http://example.org';
		$this->assertFalse( $rule->test_referrer( $ref_list_2 ), 'Rule-2-6' );
		unset( $_REQUEST['thereferrer'] );
		$this->assertFalse( $rule->test_referrer( $ref_list_2 ), 'Rule-2-7' );

		// This stupid rule will match all referrers...
		$ref_list_3 = array( '//' );

		$_REQUEST['thereferrer'] = 'http://premium.wpmudev.org/';
		$this->assertTrue( $rule->test_referrer( $ref_list_3 ), 'Rule-3-1' );
		unset( $_REQUEST['thereferrer'] );
		$this->assertFalse( $rule->test_referrer( $ref_list_3 ), 'Rule-3-2' );

		// -- Test the "not internal" condition

		$home_url = get_option( 'home' );
		$internal = preg_replace( '#^https?://#', '', $home_url );

		$_REQUEST['thereferrer'] = $home_url;
		$this->assertTrue( $rule->test_referrer( $internal ), 'Rule-4-1' );
		unset( $_REQUEST['thereferrer'] );
		$this->assertFalse( $rule->test_referrer( $internal ), 'Rule-4-2' );
		$_REQUEST['thereferrer'] = 'http://www.wordpress.org';
		$this->assertFalse( $rule->test_referrer( $internal ), 'Rule-4-3' );
	}
}