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
 * Called by hook: 'upfront-core-initialized'
 *
 * @since 4.8.0.0
 * @internal
 */
class Upfront_Module_Popup {

	/**
	 * Init module and load scripts
	 */
	static public function initialize() {
		static $Inst = null;

		if ( null === $Inst ) {
			$Inst = new Upfront_Module_Popup();
		}

		return $Inst;
	}

	/**
	 * Private constructor: Singleton pattern.
	 */
	private function __construct() {
		// Include the backend support stuff
		require_once dirname( __FILE__ ) . '/lib/class-upfront-popup-view.php';
		require_once dirname( __FILE__ ) . '/lib/class-upfront-popup-ajax.php';

		add_filter(
			'upfront_l10n',
			array( 'Upfront_Popup_View', 'add_l10n_strings' )
		);

		add_action(
			'wp_footer',
			array( $this, 'load_scripts' ),
			100
		);
	}


	/**
	 * Loads scripts
	 */
	public function load_scripts() {
		$module_js_url = upfront_relative_element_url( 'upfront/js/main.js', PO_UF_URL );

		?>
		<script type="text/javascript">
			Upfront.popup_config = {
				base_url: '<?php echo esc_js( PO_UF_URL ); ?>'
			};
		</script>
		<script src="<?php echo esc_url( $module_js_url ); ?>"></script>
		<?php
	}

}

// Initialize the entity when Upfront is good and ready
add_action(
	'upfront-core-initialized',
	array( 'Upfront_Module_Popup', 'initialize' )
);
