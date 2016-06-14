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
		if ( is_admin() ) {
			// Called from the PopUp Settings screen.
			add_filter(
				'popup-settings-loading-method',
				array( __CLASS__, 'settings' )
			);
		} else {
			
		}

		
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
		$pro_only = ' - ' . __( 'PRO Verson', 'popover' );
		

		$loading_methods[] = (object) array(
			'id'    => self::METHOD,
			'label' => __( 'Anonymous Script', 'popover' ) . $pro_only,
			'info'  => __(
				'Drastically increase the chance to bypass ad-blockers. ' .
				'Loads PopUp like WordPress AJAX, but the URL to the ' .
				'JavaScript file is masked. ', 'popover'
			),
			'disabled' => ! ! $pro_only,
		);
		return $loading_methods;
	}

	
}

IncPopupAddon_AnonyousLoading::init();
