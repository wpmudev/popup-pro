<?php
/*
Addon Name:  Anonymous loading method
Plugin URI:  http://premium.wpmudev.org/project/the-pop-over-plugin/
Description: Yet another loading method.
Author:      Ve (Incsub)
Author URI:  http://premium.wpmudev.org
Type:        Setting
Version:     1.0
*/

/**
 * Quick overview of how this works:
 * - The page initially does not contain PopUp data.
 * - This Add-On first enqueues the new <script> tag: The site home-URL with a
 *   random URL param.
 * - The Add-On also checks if the request contains the random URL param:
 *   a) When the param is not found: Output the page normally.
 *   b) When the param is found: Output javascript code with PopUp details
 *       instead of the page.
 *
 * The script that will be output contains the full public.js script with
 * settings to load the PopUp via Ajax. So this is a 2-step loading process:
 *
 *   1. Load anonymized "public.js" javascript
 *   2. Make ajax request to get PopUp data
 *
 * Example of the anonymous script URL:
 *   http://local.dev/demo?lppuorrymprpprypmvrly=cfbidebjde
 */

class IncPopupAddon_AnonyousLoading {

	const METHOD = 'anonymous';

	static private $_slug = '';

	/**
	 * Initialize the addon
	 *
	 * @since  4.6
	 */
	static public function init() {
		self::$_slug = self::_generate_slug();

		if ( is_admin() ) {
			// Called from the PopUp Settings screen.
			add_filter(
				'popup-settings-loading-method',
				array( __CLASS__, 'settings' )
			);
		} else {
			// Called when initializing custom loading method in the Front-End.
			add_action(
				'popup-init-loading-method',
				array( __CLASS__, 'init_public' ),
				10, 2
			);
		}

		// Modify the HTML/CSS code of the ajax response.
		add_filter(
			'popup-output-data',
			array( __CLASS__, 'filter_script_data' ),
			10, 2
		);
	}

	/**
	 * Filter that returns a modified version of the loading methods
	 * (displayed in the settings page)
	 *
	 * @since  4.6
	 * @param  array $loading_methods
	 * @return array
	 */
	static public function settings( $loading_methods ) {
		$loading_methods[] = (object) array(
			'id'    => self::METHOD,
			'label' => __( 'Anonymous Script', PO_LANG ),
			'info'  => __(
				'Drastically increase the chance to bypass ad-blockers. ' .
				'Loads PopUp like WordPress AJAX, but the URL to the ' .
				'JavaScript file is masked. ', PO_LANG
			),
		);
		return $loading_methods;
	}

	/**
	 * Enqueue the anonymous script.
	 * Action: `popup-init-loading-method`
	 *
	 * @since  4.6
	 * @param  string $method The option saved on the settings screen.
	 * @param  IncPopup $handler Public IncPopup object that called the action.
	 */
	static public function init_public( $method, $handler ) {
		if ( self::METHOD != $method ) { return false; }

		// Generate a random Script URL.
		$slug = self::$_slug;
		$val = self::_rot( time(), rand( 1, 22 ) );
		$script_url = esc_url_raw(
			add_query_arg( array( $slug => $val ), lib2()->net->current_url() )
		);

		// The script is the home URL with a special URL-param.
		wp_enqueue_script(
			$slug,
			$script_url,
			array( 'jquery' )
		);

		// Enable animations in 'Anonymous Script'
		lib2()->ui->add( PO_CSS_URL . 'animate.min.css', 'front' );

		// This checks if the current URL contains the special URL-param.
		// If the param is found then the PopUp details are output instead of the page.
		add_action(
			'template_redirect',
			array( __CLASS__, 'apply')
		);
	}

	/**
	 * Replaces selectors in the PopUp HTML/CSS code
	 *
	 * @since  1.0.0
	 * @param  array $data Data collection that is printed to javascript.
	 * @param  IncPopupItem $popup The original popup object.
	 * @return array Modified data collection.
	 */
	static public function filter_script_data( $script_data, $popup ) {
		$settings = IncPopupDatabase::get_settings();
		if ( self::METHOD != $settings['loadingmethod'] ) {
			return $script_data;
		}

		if ( ! empty( $script_data['html'] ) && ! empty( $script_data['styles'] ) ) {
			$script_data['html'] = self::_filter( $script_data['html'], true );
			$script_data['styles'] = self::_filter( $script_data['styles'] );
		}
		return $script_data;
	}

	/**
	 * Replaces some selectors in the input code
	 *
	 * @since  1.0.0
	 * @param  string $code Original code (HTML/CSS/JS)
	 * @param  bool $is_html Set to true, when $code is HTML code.
	 * @return string The modified code.
	 */
	static private function _filter( $code, $is_html = false ) {
		$selectors = array(
			'closebox',
			'message',
			'clearforever',
		);

		$salt = home_url();

		if ( $is_html ) {
			$pfx = '';
			$delim_start = '[\'"]';
			$delim_end = $delim_start;
		} else {
			$pfx = '#';
			$delim_start = '#';
			$delim_end = '\b';
		}

		foreach ( $selectors as $selector ) {
			$hash = md5( $selector . $salt );
			$len = strlen( $salt );

			if ( $len < 5 ) {
				$len = 5;
			} else if ( $len >= 32 ) {
				$len = (int) $len / 2;
			}

			$value = 'p' . self::_rot( $hash, $len );
			$value = $pfx . $value;
			$code = preg_replace(
				'/' . $delim_start . preg_quote( $selector, '/' ) . $delim_end . '/',
				$value,
				$code
			);
		}
		return $code;
	}

	/**
	 * Decide if we want to show the normal page or the PopUp javascript - if
	 * the URL param is set.
	 *
	 * @since  1.0.0
	 */
	static public function apply() {
		if ( ! self::has_fragment() ) {
			// The URL param is not set, this is the request for the page HTML.
			return false;
		}

		// The URL param is set, this is the <script> request.
		self::render_script();
		ob_start();
	}

	/**
	 * [render_script description]
	 * @since  1.0.0
	 * @return [type] [description]
	 */
	static public function render_script() {
		if ( ! did_action( 'wp' ) ) {
			// We have to make sure that wp is fully initialized:
			// Some rules that use filter 'popup-ajax-data' depend on this.
			add_action(
				'wp',
				array( __CLASS__, 'render_script' )
			);
			return;
		}

		$file = PO_JS_DIR . 'public.min.js';
		$popup_data = array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'do' => 'get_data',
			'orig_request_uri' => $_SERVER['REQUEST_URI'],
		);

		$popup_data = apply_filters( 'popup-ajax-data', $popup_data );

		$data = sprintf(
			'window._popup_data = %s',
			json_encode( $popup_data )
		);
		$script = self::_filter( file_get_contents( $file ) );

		while ( ob_get_level() ) { ob_end_clean(); }

		// Output the modified script.
		header( 'Content-type: text/javascript' );
		echo ';' . $data;
		echo ';' . $script;
		die();
	}

	/**
	 * Checks if the current request contains the URL param that identifies the
	 * request as <script> tag.
	 *
	 * @since  1.0.0
	 * @return bool
	 */
	static public function has_fragment() {
		$slug = self::$_slug;
		return ! empty( $_GET[$slug] );
	}

	/**
	 * Generate the random URL param.
	 *
	 * @since  1.0.0
	 * @return string
	 */
	static private function _generate_slug() {
		$info = str_split( home_url() );
		$raw = serialize( $info );
		$len = count( $info );

		if ( $len < 5 ) {
			$len = 5;
		} else if ( $len >= 32 ) {
			$len = (int) $len / 2;
		}

		return substr( self::_rot( md5( $raw ), $len ), 0, $len );
	}

	/**
	 * Some basic encryption/randomization of the input string.
	 * All letters (a-z, 0-9) will be shifted by $offset places.
	 *
	 * E.g.
	 *   $offset = 1; then "and" will become "boe" (a+1 = b, ...)
	 *   $offset = 3; then "and" will become "dqg"
	 *
	 * @since  1.0.0
	 * @param  string $str Original string.
	 * @param  int $offset Shift characters by x positions.
	 * @return string Modified string.
	 */
	static private function _rot( $str, $offset ) {
		// We're not interested in having the exact thing back
		$letters = 'abcdefghijklmnopqrstuvwxyz0123456789';
		$shifted = substr( $letters, $offset ) . substr( $letters, 0, $offset );
		return strtr( $str, $letters, $shifted );
	}
}

IncPopupAddon_AnonyousLoading::init();