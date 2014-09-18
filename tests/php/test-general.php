<?php

/**
 * Test general plugin aspects
 */
class Popup_Test_General extends WP_UnitTestCase {

	/**
	 * Check if all constants are defined.
	 */
	function test_constants() {
		$this->assertTrue( defined( 'PO_LANG' ), 'Const not defined: PO_LANG' );
		$this->assertTrue( defined( 'PO_VERSION' ), 'Const not defined: PO_VERSION' );
		$this->assertTrue( defined( 'PO_BUILD' ), 'Const not defined: PO_BUILD' );
		$this->assertTrue( defined( 'PO_LANG_DIR' ), 'Const not defined: PO_LANG_DIR' );
		$this->assertTrue( defined( 'PO_TPL_DIR' ), 'Const not defined: PO_TPL_DIR' );
		$this->assertTrue( defined( 'PO_INC_DIR' ), 'Const not defined: PO_INC_DIR' );
		$this->assertTrue( defined( 'PO_JS_DIR' ), 'Const not defined: PO_JS_DIR' );
		$this->assertTrue( defined( 'PO_CSS_DIR' ), 'Const not defined: PO_CSS_DIR' );
		$this->assertTrue( defined( 'PO_VIEWS_DIR' ), 'Const not defined: PO_VIEWS_DIR' );
		$this->assertTrue( defined( 'PO_TPL_URL' ), 'Const not defined: PO_TPL_URL' );
		$this->assertTrue( defined( 'PO_JS_URL' ), 'Const not defined: PO_JS_URL' );
		$this->assertTrue( defined( 'PO_CSS_URL' ), 'Const not defined: PO_CSS_URL' );
		$this->assertTrue( defined( 'PO_IMG_URL' ), 'Const not defined: PO_IMG_URL' );

		$this->assertEquals( PO_VERSION, 'pro', 'PO_VERSION has wrong value' );
	}

	/**
	 * See if the WDev() function is available.
	 *
	 * @depends test_constants
	 */
	function test_wpmulib() {
		$this->assertTrue( function_exists( 'WDev' ), 'Missing: WDev()' );
		$this->assertTrue( class_exists( 'TheLibWrap' ), 'Missing: TheLibWrap' );

		$this->assertNotEquals( TheLibWrap::$version, '0.0.0', 'Lib Version not set' );
		$this->assertNotNull( TheLibWrap::$object, 'Lib Object not set' );

		// Check for minimum required version.
		$this->assertTrue( version_compare( TheLibWrap::$version, '1.0.12', '>=' ), 'Lib Version too low' );
	}
}