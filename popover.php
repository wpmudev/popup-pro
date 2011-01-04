<?php
/*
Plugin Name: Popover plugin
Plugin URI: http://premium.wpmudev.org
Description: This plugin adds a customisable popover to a site. The content, size, position can be changed and rules determining if the popover should show or not.
Author: Barry (Incsub)
Version: 3.0
Author URI: http://caffeinatedb.com
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

// a true setting for PO_GLOBAL means that this plugin operates on a global site-admin basis
// commenting out this line means that the plugin operates on a blog by blog basis
define('PO_GLOBAL',true);

require_once('popoverincludes/classes/functions.php');
// Set up my location
set_popover_url(__FILE__);
set_popover_dir(__FILE__);

if(is_admin()) {
	require_once('popoverincludes/classes/popoveradmin.php');

	$popover =& new popoveradmin();
} else {
	require_once('popoverincludes/classes/popoverpublic.php');

	$popover =& new popoverpublic();
}


?>