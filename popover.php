<?php
/*
Plugin Name: Popover plugin
Plugin URI: http://premium.wpmudev.org/project/the-pop-over-plugin/
Description: Allows you to display a fancy popup (powered as a popover!) to visitors sitewide or per blog, a *very* effective way of advertising a mailing list, special offer or running a plain old ad.
Author: WPMU DEV
Version: 4.5.4-BETA-3
Author URI: http://premium.wpmudev.org
WDP ID: 123

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

define('PO_SELF_DIRNAME', basename(dirname(__FILE__)), true);
require_once( dirname(__FILE__) . '/popoverincludes/includes/config.php');
require_once( dirname(__FILE__) . '/popoverincludes/includes/functions.php');

// Set up my location
set_popover_url(__FILE__);
set_popover_dir(__FILE__);

if(is_admin()) {
	include_once( dirname(__FILE__) . '/popoverincludes/external/wpmudev-dash-notification.php');

	require_once( dirname(__FILE__) . '/popoverincludes/includes/class_wd_help_tooltips.php');
	require_once( dirname(__FILE__) . '/popoverincludes/classes/popover.help.php');
	require_once( dirname(__FILE__) . '/popoverincludes/classes/popoveradmin.php');
	require_once( dirname(__FILE__) . '/popoverincludes/classes/popoverajax.php');

	$popover = new popoveradmin();
	$popoverajax = new popoverajax();
} else {
	// Adding ajax so we don't have to duplicate checking functionality in the public class
	// NOTE: it's not being used for ajax here
	require_once( dirname(__FILE__) . '/popoverincludes/classes/popoverajax.php');
	require_once( dirname(__FILE__) . '/popoverincludes/classes/popoverpublic.php');

	$popover = new popoverpublic();
	$popoverajax = new popoverajax();
}

load_popover_addons();