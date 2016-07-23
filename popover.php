<?php

/**
 * Plugin Name: WordPress PopUp
 * Plugin URI:  http://premium.wpmudev.org/project/the-pop-over-plugin/
 * Description: Allows you to display a fancy PopUp to visitors sitewide or per blog. A *very* effective way of  * advertising a mailing list, special offer or running a plain old ad.
 * Version:     4.8.0.0
 * Author:      WPMU DEV
 * Author URI:  http://premium.wpmudev.org
 * Textdomain:  popover
 * WDP ID:      123
 */ 

/**

/**
 * Copyright notice
 *
 * @copyright Incsub (http://incsub.com/)
 *
 * Authors: Philipp Stracker, Fabio Jun Onishi, Victor Ivanov, Jack Kitterhing, Rheinard Korf, Ashok Kumar Nath
 * Contributors: Joji Mori, Patrick Cohen
 *
 * @license http://opensource.org/licenses/GPL-2.0 GNU General Public License, version 2 (GPL-2.0)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston,
 * MA 02110-1301 USA
 */

function inc_popup_init() {
	// Check if the PRO plugin is present and activated.
	if ( defined( 'PO_VERSION' ) ) {
		return false;
	}

	define(
		'PO_VERSION'
		
		,'free'
	);

	/**
	 * The current DB/build version. NOT THE SAME AS THE PLUGIN VERSION!
	 * Increase this when DB structure changes, migration code is required, etc.
	 * See IncPopupDatabase: db_is_current() and db_update()
	 */
	define( 'PO_BUILD', 6 );

	$externals = array();
	$plugin_dir = trailingslashit( dirname( __FILE__ ) );
	$plugin_dir_rel = trailingslashit( dirname( plugin_basename( __FILE__ ) ) );
	$plugin_url = plugin_dir_url( __FILE__ );

	define( 'PO_LANG_DIR', $plugin_dir_rel . 'lang/' );
	define( 'PO_DIR', $plugin_dir );
	define( 'PO_TPL_DIR', $plugin_dir . 'css/tpl/' );
	define( 'PO_INC_DIR', $plugin_dir . 'inc/' );
	define( 'PO_JS_DIR', $plugin_dir . 'js/' );
	define( 'PO_CSS_DIR', $plugin_dir . 'css/' );

	define( 'PO_TPL_URL', $plugin_url . 'css/tpl/' );
	define( 'PO_JS_URL', $plugin_url . 'js/' );
	define( 'PO_CSS_URL', $plugin_url . 'css/' );
	define( 'PO_IMG_URL', $plugin_url . 'img/' );

	// Include function library.
	$modules[] = PO_INC_DIR . 'external/wpmu-lib/core.php';
	$modules[] = PO_INC_DIR . 'external/wdev-frash/module.php';
	$modules[] = PO_INC_DIR . 'config-defaults.php';

	if ( is_admin() ) {
		// Defines class 'IncPopup'.
		$modules[] = PO_INC_DIR . 'class-popup-admin.php';
	} else {
		// Defines class 'IncPopup'.
		$modules[] = PO_INC_DIR . 'class-popup-public.php';
	}

	
	// Free-version configuration
	$cta_label = __( 'Get Tips!', 'popover' );
	$drip_param = 'Popup';
	

	

	foreach ( $modules as $path ) {
		if ( file_exists( $path ) ) { require_once $path; }
	}

	// Register the current plugin, for pro and free plugins!
	do_action(
		'wdev-register-plugin',
		/*             Plugin ID */ plugin_basename( __FILE__ ),
		/*          Plugin Title */ 'PopUp',
		/* https://wordpress.org */ '/plugins/wordpress-popup/',
		/*      Email Button CTA */ $cta_label,
		/*  getdrip Plugin param */ $drip_param
	);

	// Initialize the plugin as soon as we have identified the current user.
	IncPopup::instance();
}

/* start:pro */
inc_popup_init();



// Init free after all plugins are loaded, in case both
// Pro and Free versions are installed.
add_action(
	'plugins_loaded',
	'inc_popup_init'
);


// Translation.
function inc_popup_init_translation() {
	if ( defined( 'PO_LANG_DIR' ) ) {
		load_plugin_textdomain(
			'popover',
			false,
			PO_LANG_DIR
		);
	}
}
add_action( 'plugins_loaded', 'inc_popup_init_translation' );
