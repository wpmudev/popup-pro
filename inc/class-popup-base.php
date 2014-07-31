<?php
// Load dependencies.
require_once PO_INC_DIR . 'class-popup-item.php';
require_once PO_INC_DIR . 'class-popup-database.php';
require_once PO_INC_DIR . 'class-popup-posttype.php';
require_once PO_INC_DIR . 'class-popup-rule.php';

// Load core rules.
require_once PO_INC_DIR . 'class-popup-rule-url.php';
require_once PO_INC_DIR . 'class-popup-rule-geo.php';
require_once PO_INC_DIR . 'class-popup-rule-popup.php';
require_once PO_INC_DIR . 'class-popup-rule-referer.php';
require_once PO_INC_DIR . 'class-popup-rule-user.php';

require_once PO_INC_DIR . 'functions.php';

/**
 * Defines common functions that are used in admin and frontpage.
 */
abstract class IncPopupBase {

	/**
	 * Holds the IncPopupDatabase instance.
	 * @var IncPopupDatabase
	 */
	protected $db = null;

	/**
	 * Constructor
	 */
	protected function __construct() {
		$this->db = IncPopupDatabase::instance();

		// Register the popup post type.
		add_action(
			'init',
			array( 'IncPopupPosttype', 'instance' )
		);

		// Return a list of all popup style URLs.
		add_filter(
			'popover_available_styles_url',
			array( 'IncPopupBase', 'style_urls' )
		);
		add_filter(
			'popover-available-styles-url',
			array( 'IncPopupBase', 'style_urls' )
		);

		// Return a list of all popup style paths (absolute directory).
		add_filter(
			'popover_available_styles_directory',
			array( 'IncPopupBase', 'style_paths' )
		);
		add_filter(
			'popover-available-styles-directory',
			array( 'IncPopupBase', 'style_paths' )
		);

		// Returns a list of all style infos (id, url, path, deprecated)
		add_filter(
			'popover-styles',
			array( 'IncPopupBase', 'style_infos' )
		);
	}

	/**
	 * If the plugin operates on global multisite level.
	 *
	 * @since  4.6
	 * @return bool
	 */
	static public function use_global() {
		$State = null;

		if ( null === $State ) {
			$State = is_multisite() && PO_GLOBAL;
		}

		return $State;
	}

	/**
	 * If the the user currently is on correct level (global vs blog)
	 *
	 * @since  4.6
	 * @return bool
	 */
	static public function correct_level() {
		$State = null;

		if ( null === $State ) {
			$State = self::use_global() === is_network_admin();
		}

		return $State;
	}

	/**
	 * Returns an array with all core popup styles.
	 *
	 * @since  4.6
	 * @return array Core Popup styles.
	 */
	static protected function _get_styles( ) {
		$Styles = null;

		if ( null === $Styles ) {
			$Styles = array();
			if ( $handle = opendir( PO_TPL_DIR ) ) {
				while ( false !== ( $entry = readdir( $handle ) ) ) {
					if ( $entry === '.' || $entry === '..' ) { continue; }
					$style_file = PO_TPL_DIR . $entry . '/style.php';
					if ( ! file_exists( $style_file ) ) { continue; }

					$info = (object) array();
					include $style_file;

					$Styles[ $entry ] = $info;
				}
				closedir( $handle );
			}
		}

		return $Styles;
	}

	/**
	 * Returns a list of all core popup styles (URL to each style dir)
	 * Handles filter `popover_available_styles_url`
	 *
	 * @since  4.6
	 * @param  array $list
	 * @return array The updated list.
	 */
	static public function style_urls( $list = array() ) {
		$core_styles = self::_get_styles();

		if ( ! is_array( $list ) ) { $list = array(); }
		foreach ( $core_styles as $key => $data ) {
			$list[ $data->name ] = PO_TPL_URL . $key;
		}

		return $list;
	}

	/**
	 * Returns a list of all core popup styles (absolute path to each style dir)
	 * Handles filter `popover_available_styles_directory`
	 *
	 * @since  4.6
	 * @param  array $list
	 * @return array The updated list.
	 */
	static public function style_paths( $list = array() ) {
		$core_styles = self::_get_styles();

		if ( ! is_array( $list ) ) { $list = array(); }
		foreach ( $core_styles as $key => $data ) {
			$list[ $data->name ] = PO_TPL_DIR . $key;
		}

		return $list;
	}

	/**
	 * Returns a list of all style infos (id, url, path, deprecated)
	 * Handles filter `popover-tyles`
	 *
	 * @since  4.6
	 * @param  array $list
	 * @return array The updated list.
	 */
	static public function style_infos( $list = array() ) {
		$core_styles = self::_get_styles();
		$urls = apply_filters( 'popover-available-styles-url', array() );
		$paths = apply_filters( 'popover-available-styles-directory', array() );

		if ( ! is_array( $list ) ) { $list = array(); }

		// Add core styles to the response.
		foreach ( $core_styles as $key => $data ) {
			$list[ $key ] = (object) array(
				'url' => PO_TPL_URL . $key,
				'dir' => PO_TPL_DIR . $key,
				'name' => $data->name,
				'deprecated' => (true == @$data->deprecated),
			);
			if ( isset( $urls[$data->name] ) ) { unset( $urls[$data->name] ); }
			if ( isset( $paths[$data->name] ) ) { unset( $paths[$data->name] ); }
		}

		// Add custom styles to the response.
		foreach ( $urls as $key => $url ) {
			$list[ $key ] = (object) array(
				'url' => $url,
				'dir' => @$paths[$key],
				'name' => $key,
				'deprecated' => false,
			);
		}

		return $list;
	}

};