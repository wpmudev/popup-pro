<?php
/*
Plugin Name: Upfront PopUp element
Plugin URI: http://premium.wpmudev.org/project/upfront
Description: PopUp element
Version: 0.1
Text Domain: popover
Author: Philipp Stracker (Incsub)
Author URI: http://premium.wpmudev.org

Copyright 2009-2011 Incsub (http://incsub.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


/**
 * This is the entity entry point, where we inform Upfront of our existence.
 *
 * This integration uses the text-domain PO_LANG (const defined in popover.php)
 *
 * *** *** *** *** *** *** *** *** *** *** *** *** *** *** *** *** *** *** ***
 *
 * Important - naming convention!
 *
 * - PHP classes should stick to these names:
 *     Upfront_<module>Main
 *     Upfront_<module>View extends Upfront_Object
 *     Upfront_<module>Ajax extends Upfront_Server
 *
 * - JS classes should be registered as
 *     Upfront.Models.<module>Model
 *     Upfront.Views.<module>View
 *
 * Using other names might cause problems at some points.
 *
 * *** *** *** *** *** *** *** *** *** *** *** *** *** *** *** *** *** *** ***
 *
 * Called by hook: 'upfront-core-initialized'
 *
 * @since 4.8.0.0
 * @internal
 */
class Upfront_PopupMain {

	/**
	 * The element ID of our custom Element. It should be something unique!
	 * It is mainly used for JS and in PHP functions related to the CSS editor.
	 */
	const TYPE = 'popup';

	/**
	 * Init module and load scripts.
	 */
	static public function initialize() {
		static $Inst = null;

		if ( null === $Inst ) {
			$Inst = new Upfront_PopupMain();
		}

		return $Inst;
	}

	/**
	 * Private constructor: Singleton pattern.
	 */
	private function __construct() {
		// Only load our dependencies when we know that Upfront is loaded.
		require_once dirname( __FILE__ ) . '/lib/class-upfront-popup-view.php';
		require_once dirname( __FILE__ ) . '/lib/class-upfront-popup-ajax.php';

		// Tell Upfront about translations used by our element.
		add_filter(
			'upfront_l10n',
			array( 'Upfront_PopupView', 'add_l10n_strings' )
		);

		// Tell Upfront which JS data is needed for our element.
		add_filter(
			'upfront_data',
			array( 'Upfront_PopupView', 'upfront_data' ),
			100
		);

		// Add the element init/loading script to the page.
		add_action(
			'wp_footer',
			array( $this, 'load_scripts' ),
			100
		);
		// Also enqueue the lib2() javascript collection for wpmUi.popup()
		lib2()->ui->add( 'core', 'front' );

		// Modify the CSS before it's saved to the DB.
		add_filter(
			'upfront-save_styles',
			array( 'Upfront_PopupAjax', 'save_styles' ),
			10, 3
		);

		// Revert modification from save_styles for display in CSS Editor.
		add_filter(
			'upfront_get_theme_styles',
			array( 'Upfront_PopupAjax', 'theme_styles' )
		);

		// PopUp Pro logic: Make sure the right popup is displayed!
		add_filter(
			'popup-select-popups',
			array( 'Upfront_PopupView', 'select_popup' ),
			10, 2
		);
	}

	/**
	 * Load the upfront integration javascript.
	 *
	 * @since 4.8.0.0
	 */
	public function load_scripts() {
		/*
		 * main.js will initialize the Upfront element when Upfront switches to
		 * edit-mode. So that script is quite small.
		 *
		 * We need to pass it the URL to the actual Upfront editor integration
		 * via Upfront.popup_config, so that this file can be loaded on demand.
		 */
		$module_js_url = upfront_relative_element_url(
			'upfront/js/main.js',
			PO_UF_URL
		);
		?>
		<script type="text/javascript">
			if ( undefined === window._popup_uf_data ) { _popup_uf_data = {}; }
			_popup_uf_data.type = '<?php echo esc_js( Upfront_PopupMain::TYPE ); ?>';
			_popup_uf_data.label = '<?php _e( 'PopUp', PO_LANG ); ?>';
			_popup_uf_data.base_url = '<?php echo esc_js( PO_UF_URL ); ?>';
		</script>
		<script src="<?php echo esc_url( $module_js_url ); ?>"></script>
		<?php
	}
}

/**
 * Initialize the entity when Upfront is good and ready.
 */
add_action(
	'upfront-core-initialized',
	array( 'Upfront_PopupMain', 'initialize' )
);
