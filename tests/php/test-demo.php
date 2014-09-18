<?php

/**
 * An example test case.
 */
class MyPlugin_Test_Example extends WP_UnitTestCase {

	// Example Test: Check if we are using a certain PHP Version
	function test_php_version() {
		$actual = phpversion();
		$expected = '5.4.24';
		$this->assertEquals( $expected, $actual, 'Wrong PHP version!' );
	}
}