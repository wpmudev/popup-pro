<?php
/*
Addon Name: Test Head Footer
Plugin URI:  http://gist.github.com/378450
Description: Tests for the existence and functionality of wp_head and wp_footer in the active theme
Author:      Matt Martz
Author URI:  http://sivel.net/
Version:     1.0

	Copyright (c) 2010 Matt Martz (http://sivel.net/)
	Test Head Footer is released under the GNU General Public License (GPL)
	http://www.gnu.org/licenses/gpl-2.0.txt
*/

// Lets not do anything until init
add_action( 'init', 'test_head_footer_init' );
function test_head_footer_init() {
	// Hook in at admin_init to perform the check for wp_head and wp_footer
	add_action( 'admin_init', 'check_head_footer' );

	// If test-head query var exists hook into wp_head
	if ( isset( $_GET['test-head'] ) )
		add_action( 'wp_head', 'test_head', 99999 ); // Some obscene priority, make sure we run last

	// If test-footer query var exists hook into wp_footer
	if ( isset( $_GET['test-footer'] ) )
		add_action( 'wp_footer', 'test_footer', 99999 ); // Some obscene priority, make sure we run last
}

// Echo a string that we can search for later into the head of the document
// This should end up appearing directly before </head>
function test_head() {
	echo '<!--wp_head-->';
}

// Echo a string that we can search for later into the footer of the document
// This should end up appearing directly before </body>
function test_footer() {
	echo '<!--wp_footer-->';
}

// Check for the existence of the strings where wp_head and wp_footer should have been called from
function check_head_footer() {
	// Build the url to call, NOTE: uses home_url and thus requires WordPress 3.0
	$url = add_query_arg( array( 'test-head' => '', 'test-footer' => '' ), home_url() );
	// Perform the HTTP GET ignoring SSL errors
	$response = wp_remote_get( $url, array( 'sslverify' => false ) );
	// Grab the response code and make sure the request was sucessful
	$code = (int) wp_remote_retrieve_response_code( $response );
	if ( $code == 200 ) {
		global $head_footer_errors;
		$head_footer_errors = array();

		// Strip all tabs, line feeds, carriage returns and spaces
		$html = preg_replace( '/[\t\r\n\s]/', '', wp_remote_retrieve_body( $response ) );

		// Check to see if we found the existence of wp_head
		if ( ! strstr( $html, '<!--wp_head-->' ) )
			$head_footer_errors['nohead'] = 'Is missing the call to <?php wp_head(); ?> which should appear directly before </head>';
		// Check to see if we found the existence of wp_footer
		if ( ! strstr( $html, '<!--wp_footer-->' ) )
			$head_footer_errors['nofooter'] = 'Is missing the call to <?php wp_footer(); ?> which should appear directly before </body>';

		// Check to see if we found wp_head and if was located in the proper spot
		if ( ! strstr( $html, '<!--wp_head--></head>' ) && ! isset( $head_footer_errors['nohead'] ) )
			$head_footer_errors[] = 'Has the call to <?php wp_head(); ?> but it is not called directly before </head>';
		// Check to see if we found wp_footer and if was located in the proper spot
		if ( ! strstr( $html, '<!--wp_footer--></body>' ) && ! isset( $head_footer_errors['nofooter'] ) )
			$head_footer_errors[] = 'Has the call to <?php wp_footer(); ?> but it is not called directly before </body>';

		// If we found errors with the existence of wp_head or wp_footer hook into admin_notices to complain about it
		if ( ! empty( $head_footer_errors ) )
			add_action ( 'admin_notices', 'test_head_footer_notices' );
	}
}

// Output the notices
function test_head_footer_notices() {
	global $head_footer_errors;

	// If we made it here it is because there were errors, lets loop through and state them all
	echo '<div class="error"><p><strong>Your active theme:</strong></p><ul>';
	foreach ( $head_footer_errors as $error )
		echo '<li>' . esc_html( $error ) . '</li>';
	echo '</ul></div>';
}
?>