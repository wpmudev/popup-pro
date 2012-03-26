<?php
/*
Plugin Name: Popover plugin
Plugin URI: http://premium.wpmudev.org
Description: Allows you to display a fancy popup (powered as a popover!) to visitors sitewide or per blog, a *very* effective way of advertising a mailing list, special offer or running a plain old ad.
Author: Barry (Incsub)
Version: 4.2.3
Author URI: http://premium.wpmudev.org
WDP ID: 123

Copyright 2007-2010 Incsub (http://incsub.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

require_once('popoverincludes/includes/config.php');
require_once('popoverincludes/includes/functions.php');
// Set up my location
set_popover_url(__FILE__);
set_popover_dir(__FILE__);

if(is_admin()) {
	require_once('popoverincludes/includes/class_wd_help_tooltips.php');
	require_once('popoverincludes/classes/popover.help.php');
	require_once('popoverincludes/classes/popoveradmin.php');
	//require_once('popoverincludes/classes/popoverajax.php');

	$popover = new popoveradmin();
	//$popoverajax = new popoverajax();
} else {
	require_once('popoverincludes/classes/popoverpublic.php');
	//require_once('popoverincludes/classes/popoverajax.php');

	$popover = new popoverpublic();
	//$popoverajax = new popoverajax();
}

load_popover_addons();


?>