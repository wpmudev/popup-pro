<?php
/**
 * Defines default plugin configuration values.
 * These values can be overridden inside wp-config.php
 */

// Multi-Site setting
//   True .. the plugin operates on a global site-admin basis
//   False .. the plugin operates on a blog by blog basis
if ( ! defined( 'PO_GLOBAL' ) ) {
	define( 'PO_GLOBAL', false, true );
}

// The url that we are using to return the country.
// It should only return the country code for the passed IP address!
if ( ! defined( 'PO_REMOTE_IP_URL' ) ) {
	define( 'PO_REMOTE_IP_URL', 'http://api.hostip.info/country.php?ip=%ip%', true );
}

// Fallback value in case the PO_REMOTE_IP_URL did not return any value.
// Either set to a valid country code (US, DE, AU, ...)
// Or set to False to not show the popup when country could not be resolved.
if ( ! defined( 'PO_DEFAULT_COUNTRY' ) ) {
	define( 'PO_DEFAULT_COUNTRY', 'US', true );
}

// Set default cookie expiry time (in days).
if ( ! defined( 'PO_DEFAULT_EXPIRY' ) ) {
	define( 'PO_DEFAULT_EXPIRY', 365, true );
}
