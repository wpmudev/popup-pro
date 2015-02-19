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
function upfront_popup_initialize() {
	// Include the backend support stuff
	require_once dirname( __FILE__ ) . '/lib/class-upfront-popup-view.php';
	require_once dirname( __FILE__ ) . '/lib/class-upfront-popup-ajax.php';

	add_filter(
		'upfront_l10n',
		array( 'Upfront_Popup_View', 'add_l10n_strings' )
	);

	// Expose our JavaScript definitions to the Upfront API
	upfront_add_layout_editor_entity(
		'upfront_popup',
		upfront_relative_element_url( 'js/upfront-element', __FILE__ )
	);
}

// Initialize the entity when Upfront is good and ready
add_action( 'upfront-core-initialized', 'upfront_popup_initialize' );