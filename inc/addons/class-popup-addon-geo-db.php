<?php
/*
Addon Name:  Use Local Geo Database
Plugin URI:  http://premium.wpmudev.org/project/the-pop-over-plugin/
Description: Switches the geo checking from using an external API to using a local database
Author:      Barry (Incsub)
Author URI:  http://premium.wpmudev.org
Type:        Caching
Version:     1.0
*/

class IncPopupAddon_GeoDB {

	/**
	 * Initialize the addon.
	 *
	 * @since  4.6
	 */
	static public function init() {
		static $Done = false;

		if ( ! $Done && self::table_exists() ) {
			$Done = true;

			add_filter(
				'popup-get-country',
				array( __CLASS__, 'get_country' ),
				10, 2
			);
		}
	}

	/**
	 * Checks if the lookup-table exists in local WordPress database.
	 *
	 * @since  1.0.0
	 * @return bool
	 */
	static public function table_exists() {
		static $Result = null;
		global $wpdb;

		if ( null === $Result ) {
			if ( ! defined( 'POPOVER_GEOLOOKUPTABLE' ) ) {
				define( 'POPOVER_GEOLOOKUPTABLE', 'countrylookupip' );
			}

			$sql = 'SHOW TABLES LIKE %s';
			$sql = $wpdb->prepare( $sql, POPOVER_GEOLOOKUPTABLE );
			$table = $wpdb->get_var( $sql );

			$Result = ( $table == POPOVER_GEOLOOKUPTABLE );
		}

		return $Result;
	}

	/**
	 * Searches the local cache table for the IP address and returns the
	 * associated country code.
	 *
	 * @since  1.0.0
	 * @param  string $country The default value suggested by the filter caller.
	 * @param  string $ip IP address to look up.
	 * @return string The country code of the IP address.
	 */
	static public function get_country( $country, $ip ) {
		global $wpdb;

		$sql = '
			SELECT ctry
			FROM ' . POPOVER_GEOLOOKUPTABLE . '
			WHERE ipfrom <= INET_ATON(%s)
				AND ipto >= INET_ATON(%s)
		';
		$sql = $wpdb->prepare( $sql, $ip, $ip );

		$data = $wpdb->get_row( $sql );

		if ( ! empty( $data ) ) {
			if ( $data->ctry == 'ZZ' ) {
				$country = 'XX';
			} else {
				$country = $data->ctry;
			}
		} else {
			$country = 'XX';
		}

		if ( $country == 'XX' && PO_DEFAULT_COUNTRY ) {
			$country = PO_DEFAULT_COUNTRY;
		}

		return $country;
	}
};
