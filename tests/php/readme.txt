Introduction to Unit Testing with WordPress:
http://codesymphony.co/writing-wordpress-plugin-unit-tests/

Preparation
===========

Install PHPUnit (http://phpunit.de/manual/current/en/installation.html)

Get latest version of the wordpress-dev trunk.
It goes in the same directory where this wordpress installation is located.
The dev trunk must be called "wordpress-develop".

	Example
	-------

	If this is the current WordPress installation:
	~/dev/wp-config.php
	~/dev/wp-content/plugins/hello/@tests/readme.txt

	Then put the wordpress-develop installation here:
	~/wordpress-develop/trunk


	Get the develop trunk
	---------------------

	$ mkdir ~/wordpress-develop
	$ cd ~/wordpress-develop
	$ svn co http://develop.svn.wordpress.org/trunk/
	$ cd trunk
	$ svn up


Run the tests
=============

Run via `grunt test` from the plugin root directory.