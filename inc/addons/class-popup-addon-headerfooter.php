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
		if ( isset( $_GET['popup-headerfooter-check'] )
			&& '1' == $_GET['popup-headerfooter-check']
		) {
			add_action(
				'wp_head',
				array( __CLASS__, 'test_head' ),
				999999 // Some obscene priority, make sure we run last
			);

			add_action(
				'wp_footer',
				array( __CLASS__, 'test_footer' ),
				999999 // Some obscene priority, make sure we run last
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
		echo '<wp_head>exists</wp_head>';
	}

	/**
	 * Echo a string that we can search for later into the footer of the document
	 * This should end up appearing directly before </body>
	 *
	 * @since  1.0.0
	 */
	static public function test_footer() {
		self::test_shortcodes();
		echo '<wp_footer>exists</wp_footer>';
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
		echo '<wp_shortcodes>' . implode( ',', $shortcodes ) . '</wp_shortcodes>';
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
		static $Init = false;
		static $Resp = null;

		if ( false === $Init ) {
			$Init = true;
			$Resp = (object) array(
				'okay' => false,
				'msg' => array(),
				'shortcodes' => array(),
			);

			// Build the url to call, NOTE: uses home_url and thus requires WordPress 3.0
			$url = esc_url_raw(
				add_query_arg(
					array( 'popup-headerfooter-check' => '1' ),
					home_url()
				)
			);

			// Perform the HTTP GET ignoring SSL errors
			$cookies = $_COOKIE;
			unset( $cookies['PHPSESSID'] );
			$response = wp_remote_get(
				$url,
				array(
					'sslverify' => false,
					'cookies' => $cookies,
				)
			);

			// Grab the response code and make sure the request was sucessful
			$code = (int) wp_remote_retrieve_response_code( $response );
			if ( is_wp_error( $response ) ) {
				lib2()->ui->admin_message( $response->get_error_message() );
				return $Resp;
			}

			if ( $code !== 200 ) { return $Resp; }

			// Strip all tabs, line feeds, carriage returns and spaces
			$html = preg_replace(
				'/[\t\r\n\s]/',
				'',
				wp_remote_retrieve_body( $response )
			);

			if ( ! strstr( $html, '<wp_head>exists</wp_head>' ) ) {
				// wp_head is missing
				$Resp->msg[] = __(
					'Critical: Call to <code>wp_head();</code> is missing! It ' .
					'should appear directly before <code>&lt;/head&gt;</code>', PO_LANG
				);
			}

			if ( ! strstr( $html, '<wp_footer>exists</wp_footer>' ) ) {
				// wp_footer is missing.
				$Resp->msg[] = __(
					'Critical: Call to <code>wp_footer();</code> is missing! It ' .
					'should appear directly before <code>&lt;/body&gt;</code>', PO_LANG
				);
			}

			$matches = array();
			$has_shortcodes = preg_match( '#<wp_shortcodes>([^\<]*)</wp_shortcodes>#', $html, $matches );
			if ( $has_shortcodes ) {
				$items = $matches[1];
				$Resp->shortcodes = explode( ',', $items );
			}

			// Display any errors that we found.
			if ( empty( $Resp->msg ) ) {
				$Resp->okay = true;
				$Resp->msg[] = __(
					'Okay: Your current theme uses <code>wp_head();</code> and ' .
					'<code>wp_footer();</code> correctly!', PO_LANG
				);
			}
		}

		return $Resp;
	}
};

IncPopupAddon_HeaderFooter::init();