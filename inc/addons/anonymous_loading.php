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

class IncPopup_SettingAnonyousLoading {

	const METHOD = 'anonymous';

	/**
	 * Initialize the addon
	 *
	 * @since  4.6
	 */
	static public function init () {
		self::$_slug = self::_generate_slug();

		// -- Called from the Pop Up Settings screen -----

		add_filter(
			'popup-settings-loading-method',
			array( __CLASS__, 'settings' )
		);

		// -- Called when initializing the Front-End -----

		add_action(
			'popup-init-loading-method',
			array( __CLASS__, 'init_public' )
		);

		add_action(
			'popup-ajax-loading-method',
			array( __CLASS__, 'init_ajax' ),
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
			'label' => 'Anonymous',
			'info'  => '...',
		);
		return $loading_methods;
	}

	/**
	 * Initialize the loading method on the front-end.
	 *
	 * @since  4.6
	 * @param  string $method The option saved on the settings screen.
	 */
	static public function init_public ($method) {
		if ( self::METHOD != $method ) {
			return false;
		}

		$slug = self::$_slug;
		$val = self::_rot(time(), rand(1, 22));
		wp_enqueue_script(
			$slug, add_query_arg(array($slug => $val), home_url()), array('jquery')
		);

		add_action(
			'template_redirect',
			array( __CLASS__, 'apply')
		);
	}

	static public function init_ajax ($method, $ajax) {
		if ($method != self::METHOD) return false;
		add_action('wp_ajax_popover_selective_ajax', array($ajax, 'ajax_selective_message_display'));
		add_action('wp_ajax_nopriv_popover_selective_ajax', array($ajax, 'ajax_selective_message_display'));
		add_action('popup-output-data', array( __CLASS__, 'filter_popover'));
	}

	static public function filter_popover( $pop ) {
		if ( ! empty( $pop["html"] ) && ! empty( $pop["style"] ) ) {
			$pop["html"] = self::_filter( $pop["html"], true );
			$pop["style"] = self::_filter( $pop["style"] );
		}
		return $pop;
	}

	static private function _filter( $code, $is_markup = false ) {
		$selectors = array(
			"closebox",
			"message",
			"clearforever"
		);
		$salt = home_url();
		$pfx = $is_markup ? '' : '#';
		$opening_delimiter = $is_markup ? '[\'"]' : '#';
		$closing_delimiter = $is_markup ? $opening_delimiter : '\b';
		foreach ($selectors as $selector) {
			$hash = md5("{$selector}{$salt}");
			$len = strlen($salt);
			$len = $len < 5
				? 5
				: ( $len >= 32 ? (int)$len/2 : $len )
			;
			$value = 'p' . self::_rot( $hash, $len );
			$value = $is_markup
				? "'{$value}'"
				: "#{$value}"
			;
			$code = preg_replace('/' . $opening_delimiter . preg_quote($selector, '/') . $closing_delimiter . '/', $value, $code);
		}
		return $code;
	}

	static public function apply() {
		if ( ! self::has_fragment() ) return false;
		self::render_script();
		die();
	}

	static public function render_script() {
		$file = PO_JS_DIR . 'public.min.js';

		$data = sprintf(
			'var _popup_data=%s',
			json_encode(
				array(
					'endpoint' => admin_url( 'admin-ajax.php' ),
					'action' => 'popover_selective_ajax',
				)
			)
		);

		header("Content-type: text/javascript");
		echo "{$data}\n";
		echo self::_filter( file_get_contents( $file ) );
	}

	static public function has_fragment() {
		$slug = self::$_slug;
		return ! empty( $_GET[$slug] );
	}

	static private function _generate_slug() {
		$info = str_split( home_url() );
		$raw = serialize( $info );
		$len = count( $info );
		$len = $len < 5
			? 5
			: ( $len >= 32 ? (int) $len / 2 : $len )
		;
		return substr( self::_rot( md5( $raw ), $len ), 0, $len );
	}

	static private function _rot( $str, $len ) {
		// We're not interested in having the exact thing back
		$letters = join( '', range( 'a','z' ) ) . join( '', range( 0,9 ) );
		return strtr( $str, $letters, substr( $letters, $len ) . substr( $letters, 0, $len ) );
	}
}


IncPopup_SettingAnonyousLoading::init();