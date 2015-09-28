<?php

/**
 * Test general plugin aspects
 */
class Popup_Test_General extends WP_UnitTestCase {

	/**
	 * Runs before the first test
	 * @beforeClass
	 */
	static function setup_once() {
		WP_UnitTestCase::setUpBeforeClass();
		require_once 'shared-setup.php';
	}

	/**
	 * Runs before the each test
	 * @before
	 */
	function setup() {
		parent::setUp();
		TData::reset();
	}

	/**
	 * Check if all constants are defined.
	 */
	function test_constants() {
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
	 * See if the lib3() function is available.
	 *
	 * @depends test_constants
	 */
	function test_wpmulib() {
		$this->assertTrue( function_exists( 'lib2' ), 'Missing: lib3()' );
		$this->assertTrue( class_exists( 'TheLib2Wrap' ), 'Missing: TheLib2Wrap' );

		$this->assertNotEquals( TheLib2Wrap::$version, '0.0.0', 'Lib Version not set' );
		$this->assertNotNull( TheLib2Wrap::$object, 'Lib Object not set' );

		// Check for minimum required version.
		$this->assertTrue( version_compare( TheLib2Wrap::$version, '2.0.0', '>=' ), 'Lib Version too low' );
	}
}