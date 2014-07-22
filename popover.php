<?php
/*
Plugin Name: Popover plugin
Plugin URI:  http://premium.wpmudev.org/project/the-pop-over-plugin/
Description: Allows you to display a fancy popup (powered as a popover!) to visitors sitewide or per blog, a *very* effective way of advertising a mailing list, special offer or running a plain old ad.
Version:     4.5.4-BETA-3
Author:      WPMU DEV
Author URI:  http://premium.wpmudev.org
Textdomain:  inc-popups
WDP ID:      123

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
	// used for more readable i18n functions: __( 'text', CSB_LANG );
	define( 'PO_LANG', 'inc-popups' );

	$plugin_dir = trailingslashit( dirname( __FILE__ ) );
	$plugin_dir_rel = trailingslashit( dirname( plugin_basename( __FILE__ ) ) );
	$plugin_url = plugin_dir_url( __FILE__ );

	define( 'PO_LANG_DIR', $plugin_dir_rel . 'lang/' );
	define( 'PO_VIEWS_DIR', $plugin_dir . 'views/' );
	define( 'PO_INC_DIR', $plugin_dir . 'inc/' );
	define( 'PO_HELP_DIR', $plugin_dir . 'help/' );
	define( 'PO_JS_DIR', $plugin_dir . 'js/' );
	define( 'PO_CSS_DIR', $plugin_dir . 'css/' );

	define( 'PO_JS_URL', $plugin_url . 'js/' );
	define( 'PO_CSS_URL', $plugin_url . 'css/' );
	define( 'PO_IMG_URL', $plugin_url . 'img/' );
	define( 'PO_HELP_URL', $plugin_url . 'help/' );

	require_once( PO_INC_DIR . 'config.php');
	require_once( PO_INC_DIR . 'functions.php');

	if ( is_admin() ) {
		require_once( PO_INC_DIR . 'class_wd_help_tooltips.php');
		require_once( PO_INC_DIR . 'classes/popover.help.php');
		require_once( PO_INC_DIR . 'classes/popoveradmin.php');
		require_once( PO_INC_DIR . 'classes/popoverajax.php');

		$popover = new popoveradmin();
		$popoverajax = new popoverajax();
	} else {
		// Adding ajax so we don't have to duplicate checking functionality in the public class
		// NOTE: it's not being used for ajax here
		require_once( PO_INC_DIR . 'classes/popoverajax.php');
		require_once( PO_INC_DIR . 'classes/popoverpublic.php');

		$popover = new popoverpublic();
		$popoverajax = new popoverajax();
	}

	TheLib::translate_plugin( PO_LANG, PO_LANG_DIR );

	load_popover_addons();
} else {
	$something_fishy = true;
}

// Include function library
if ( file_exists( PO_INC_DIR . 'external/wpmu-lib/core.php' ) ) {
	require_once PO_INC_DIR . 'external/wpmu-lib/core.php';
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
				'popover',
			),
		);
		require_once PO_INC_DIR . 'external/wpmudev-dashboard/wpmudev-dash-notification.php';
	}
}

