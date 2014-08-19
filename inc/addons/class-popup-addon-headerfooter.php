<?php
/*
Addon Name: Test Head Footer
Plugin URI:  http://gist.github.com/378450
Description: Tests for the existence and functionality of wp_head and wp_footer in the active theme.
Author:      Matt Martz
Author URI:  http://sivel.net/
Type:        Misc
Version:     1.1

	Copyright (c) 2010 Matt Martz (http://sivel.net/)
	Test Head Footer is released under the GNU General Public License (GPL)
	http://www.gnu.org/licenses/gpl-2.0.txt
*/

class IncPopupAddon_HeaderFooter {

	/**
	 * Initialize the addon.
	 *
	 * @since  4.6
	 */
	static public function init() {
		// If test-head query var exists hook into wp_head
		if ( isset( $_GET['test-head'] ) ) {
			add_action(
				'wp_head',
				array( __CLASS__, 'test_head' ),
				99999 // Some obscene priority, make sure we run last
			);
		}

		// If test-footer query var exists hook into wp_footer
		if ( isset( $_GET['test-footer'] ) ) {
			add_action(
				'wp_footer',
				array( __CLASS__, 'test_footer' ),
				99999 // Some obscene priority, make sure we run last
			);
		}
	}

	/**
	 * Echo a string that we can search for later into the head of the document
	 * This should end up appearing directly before </head>
	 *
	 * @since  1.0.0
	 */
	static public function test_head() {
		self::test_shortcodes();
		echo '<!--wp_head-->';
	}

	/**
	 * Echo a string that we can search for later into the footer of the document
	 * This should end up appearing directly before </body>
	 *
	 * @since  1.0.0
	 */
	static public function test_footer() {
		self::test_shortcodes();
		echo '<!--wp_footer-->';
	}

	/**
	 * Echo a list of all available shortcodes.
	 * This is used to check which shortcodes are available for loading method
	 * 'Page Footer'
	 *
	 * @since  1.1
	 */
	static public function test_shortcodes() {
		global $shortcode_tags;
		$shortcodes = array_keys( $shortcode_tags );
		echo '<!--shortcodes:[' . implode( ',', $shortcodes ) . ']-->';
	}

	/**
	 * Check for the existence of the strings where wp_head and wp_footer should
	 * have been called from.
	 *
	 * This is loading the front-page of the current installation via
	 * wp_remove_get and then parses the resonse to see if the header/footer
	 * comments exist in the HTML code.
	 *
	 * @since  1.0.0
	 */
	static public function check() {
		// Build the url to call, NOTE: uses home_url and thus requires WordPress 3.0
		$url = add_query_arg(
			array( 'test-head' => '', 'test-footer' => '' ),
			home_url()
		);

		// Perform the HTTP GET ignoring SSL errors
		$response = wp_remote_get(
			$url,
			array( 'sslverify' => false )
		);

		// Grab the response code and make sure the request was sucessful
		$code = (int) wp_remote_retrieve_response_code( $response );

		if ( $code !== 200 ) { return; }

		$resp = (object) array(
			'okay' => false,
			'msg' => array(),
			'shortcodes' => array(),
		);

		// Strip all tabs, line feeds, carriage returns and spaces
		$html = preg_replace(
			'/[\t\r\n\s]/',
			'',
			wp_remote_retrieve_body( $response )
		);

		if ( ! strstr( $html, '<!--wp_head-->' ) ) {
			// wp_head is missing
			$resp->msg[] = __(
				'Critical: Call to <code>wp_head();</code> is missing! It ' .
				'should appear directly before <code>&lt;/head&gt;</code>', PO_LANG
			);
		} else if ( ! strstr( $html, '<!--wp_head--></head>' ) ) {
			// wp_head is not in correct location.
			$resp->msg[] = __(
				'Notice: Call to <code>wp_head();</code> exists but it is ' .
				'not called directly before <code>&lt;/head&gt;</code>', PO_LANG
			);
		}

		if ( ! strstr( $html, '<!--wp_footer-->' ) ) {
			// wp_footer is missing.
			$resp->msg[] = __(
				'Critical: Call to <code>wp_footer();</code> is missing! It ' .
				'should appear directly before <code>&lt;/body&gt;</code>', PO_LANG
			);
		} else if ( ! strstr( $html, '<!--wp_footer--></body>' ) ) {
			// wp_footer is not in correct location.
			$resp->msg[] = __(
				'Notice: Call to <code>wp_footer();</code> exists but it is ' .
				'not called directly before <code>&lt;/body&gt;</code>', PO_LANG
			);
		}

		$matches = array();
		$has_shortcodes = preg_match( '/<!--shortcodes:\[([^\]]*)\]-->/', $html, $matches );
		if ( $has_shortcodes ) {
			$items = $matches[1];
			$resp->shortcodes = explode( ',', $items );
		}

		// Display any errors that we found.
		if ( empty( $resp->msg ) ) {
			$resp->okay = true;
			$resp->msg[] = __(
				'Okay: Your current theme uses <code>wp_head();</code> and ' .
				'<code>wp_footer();</code> correctly!', PO_LANG
			);
		}

		return $resp;
	}
};

IncPopupAddon_HeaderFooter::init();