<?php
/*
Plugin Name: Pop Up!
Plugin URI:  http://premium.wpmudev.org/project/the-pop-over-plugin/
Description: Allows you to display a fancy Pop Up to visitors sitewide or per blog. A *very* effective way of advertising a mailing list, special offer or running a plain old ad.
Version:     4.6
Author:      WPMU DEV
Author URI:  http://premium.wpmudev.org
Textdomain:  popover
WDP ID:      123            : TODO THIS NEEDS TO BE UPDATED

Copyright 2007-2013 Incsub (http://incsub.com)
Author - Barry (Incsub)
Contributors - Marko Miljus (Incsub), Ve Bailovity (Incsub)
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
  the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

$something_fishy = false;
if ( ! defined( 'PO_LANG' ) ) {
	// Used for more readable i18n functions: __( 'text', PO_LANG );
	define( 'PO_LANG', 'popover' );

	/**
	 * The current DB/build version. NOT THE SAME AS THE PLUGIN VERSION!
	 * Increase this when DB structure changes, migration code is required, etc.
	 * See IncPopupDatabase: db_is_current() and db_update()
	 */
	define( 'PO_BUILD', 6 );

	$plugin_dir = trailingslashit( dirname( __FILE__ ) );
	$plugin_dir_rel = trailingslashit( dirname( plugin_basename( __FILE__ ) ) );
	$plugin_url = plugin_dir_url( __FILE__ );

	define( 'PO_LANG_DIR', $plugin_dir_rel . 'lang/' );
	define( 'PO_TPL_DIR', $plugin_dir . 'css/tpl/' );
	define( 'PO_INC_DIR', $plugin_dir . 'inc/' );
	define( 'PO_JS_DIR', $plugin_dir . 'js/' );
	define( 'PO_CSS_DIR', $plugin_dir . 'css/' );
	define( 'PO_VIEWS_DIR', $plugin_dir . 'views/' );

	define( 'PO_TPL_URL', $plugin_url . 'css/tpl/' );
	define( 'PO_JS_URL', $plugin_url . 'js/' );
	define( 'PO_CSS_URL', $plugin_url . 'css/' );
	define( 'PO_IMG_URL', $plugin_url . 'img/' );

	// Include function library.
	if ( file_exists( PO_INC_DIR . 'external/wpmu-lib/core.php' ) ) {
		require_once PO_INC_DIR . 'external/wpmu-lib/core.php';
	}

	require_once( PO_INC_DIR . 'config-defaults.php');
	if ( is_admin() ) {
		// Defines class 'IncPopup'.
		require_once( PO_INC_DIR . 'class-popup-admin.php');
	} else {
		// Defines class 'IncPopup'.
		require_once( PO_INC_DIR . 'class-popup-public.php');
	}

	// Initialize the plugin as soon as we have identified the current user.
	add_action( 'set_current_user', array( 'IncPopup', 'instance' ) );
} else {
	$something_fishy = true;
}

// Only Pro version: Notify user when a possibly collission is detected.
if ( $something_fishy && is_admin() ) {
	TheLib::message(
		sprintf(
			__(
				'<strong>Pop Up!</strong><br />' .
				'It seems that you have more than one version of the plugin ' .
				'installed. To avoid problems please check your ' .
				'<a href="%1$s">plugins</a> and deactivate other versions of ' .
				'this plugin!<br />' .
				'You might see this notice, because the free and pro '.
				'versions of this plugin are installed at the same time.',
				CSB_LANG
			),
			admin_url( 'plugins.php' )
		),
		'err'
	);
}

// Pro: Integrate WPMU Dev Dashboard
if ( is_admin() ) {
	if ( file_exists( PO_INC_DIR . 'external/wpmudev-dashboard/wpmudev-dash-notification.php' ) ) {
		global $wpmudev_notices;
		is_array( $wpmudev_notices ) || $wpmudev_notices = array();
		$wpmudev_notices[] = array(
			'id' => 123,
			'name' => 'Pop Up!',
			'screens' => array(
				// TODO: THIS NEEDS TO BE UPDATED!!!
				'popover',
			),
		);
		require_once PO_INC_DIR . 'external/wpmudev-dashboard/wpmudev-dash-notification.php';
	}
}
