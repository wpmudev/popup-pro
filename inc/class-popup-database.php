<?php

/**
 * Provides the database level functions for the popups.
 */
class IncPopupDatabase {

	/**
	 * Returns the singleton instance of the popup database class.
	 *
	 * @since  4.6
	 */
	static public function instance() {
		static $Inst = null;

		if ( null === $Inst ) {
			$Inst = new IncPopupDatabase();
		}

		return $Inst;
	}

	/**
	 * Checks the state of the database and returns true when the DB has a valid
	 * format for the current plugin version.
	 *
	 * @since  4.6
	 * @return boolean Database state (true = everything okay)
	 */
	static public function db_is_current() {
		// This value is only changed by function db_update() below.
		$cur_version = get_option( 'popover_installed', 0 );

		return $cur_version == PO_BUILD;
	}

	/**
	 * Adds the correct DB prefix to the table and returns the full name.
	 * Takes the setting PO_GLOBAL into account.
	 *
	 * @since  4.6
	 * @param  string $table Table name without prefix.
	 * @return string The prefixed table name.
	 */
	static public function db_prefix( $table ) {
		global $wpdb;

		if ( defined( 'PO_GLOBAL' ) && true == PO_GLOBAL ) {
			if ( ! empty( $wpdb->base_prefix ) ) {
				return $wpdb->base_prefix . $table;
			} else {
				return $wpdb->prefix . $table;
			}
		} else {
			return $wpdb->prefix . $table;
		}
	}

	/**
	 * Setup or migrate the database to current plugin version.
	 *
	 * @since  4.6
	 */
	public function db_update() {
		// Required for dbDelta()
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		global $wpdb;

		$charset_collate = '';

		if ( ! empty( $wpdb->charset ) ) {
			$charset_collate = ' DEFAULT CHARACTER SET ' . $wpdb->charset;
		}

		if ( ! empty( $wpdb->collate ) ) {
			$charset_collate .= ' COLLATE ' . $wpdb->collate;
		}

		$tbl_popover = self::db_prefix( 'popover' );
		$tbl_ip_cache = self::db_prefix( 'popover_ip_cache' );

		if ( $wpdb->get_var( 'SHOW TABLES LIKE "' . $tbl_popover . '" ' ) == $tbl_popover ) {
			// Migrate to custom post type
			$sql = "
			SELECT
				id,
				popover_title,
				popover_content,
				popover_settings,
				popover_order,
				popover_active
			FROM {$tbl_popover}
			";
			$res = $wpdb->get_results( $sql );

			// Name mapping of conditions/rules from build 5 -> 6.
			$mapping = array(
				'isloggedin' => 'login', 'loggedin' => 'no_login',
				'onurl' => 'url', 'notonurl' => 'no_url',
				'incountry' => 'country', 'notincountry' => 'no_country',
				'advanced_urls' => 'adv_url', 'not-advanced_urls' => 'no_adv_url',
				'categories' => 'category', 'not-categories' => 'no_category',
				'post_types' => 'posttype', 'not-post_types' => 'no_posttype',
				'xprofile_value' => 'xprofile', 'not-xprofile_value' => 'no_xprofile',
				'searchengine' => 'searchengine',
				'commented' => 'commented',
				'internal' => 'internal',
				'referrer' => 'referrer',
				'count' => 'count',
				'max_width' => 'max_width',
				'on_exit' => 'on_exit',
				'on_click' => 'on_click',
			);

			// Migrate data from build 5 to build 6!
			foreach ( $res as $item ) {
				$raw = unserialize( $item->popover_settings );
				$checks = explode( ',', @$raw['popover_check']['order'] );
				foreach ( $checks as $ind => $key ) {
					if ( empty ( $key ) ) {
						unset( $checks[$ind] );
					} else {
						$checks[$ind] = isset( $mapping[$key])  ? $mapping[$key] : $key;
					}
				}
				$data = array(
					'name'          => $item->popover_title,
					'content'       => $item->popover_content,
					'order'         => $item->popover_order,
					'active'        => $item->popover_active,
					'size'          => @$raw['popover_size'],
					'color'         => @$raw['popover_colour'],
					'style'         => @$raw['popover_style'],
					'can_hide'      => (true != @$raw['popoverhideforeverlink']),
					'close_is_hide' => @$raw['popover_close_hideforever'],
					'hide_expire'   => @$raw['popover_hideforever_expiry'],
					'display'       => 'delay',
					'delay'         => @$raw['popoverdelay'],
					'checks'        => $checks,
					'rules' => array(
						'count'          => @$raw['popover_count'],
						'ereg'           => @$raw['popover_ereg'],
						'min_width'      => @$raw['max_width'],
						'exit'           => @$raw['on_exit'],
						'click'          => @$raw['on_click'],
						'url'            => @$raw['onurl'],
						'no_url'         => @$raw['notonurl'],
						'adv_url'        => @$raw['advanced_urls'],
						'no_adv_url'     => @$raw['not-advanced_urls'],
						'country'        => @$raw['incountry'],
						'no_country'     => @$raw['notincountry'],
						'category'       => @$raw['categories'],
						'no_category'    => @$raw['not-categories'],
						'posttype'       => @$raw['post_types'],
						'no_posttype'    => @$raw['not-post_types'],
						'xprofile'       => @$raw['xprofile_value'],
						'no_xprofile'    => @$raw['not-xprofile_value'],
					)
				);
				// Save the popup as custom posttype.
				$popup = new IncPopupItem( $data );
				$popup->save();
			}
		}

		// Create or update the IP cache table.
		$sql = "
		CREATE TABLE {$tbl_ip_cache} (
			IP varchar(12) NOT NULL DEFAULT '',
			country varchar(2) DEFAULT NULL,
			cached bigint(20) DEFAULT NULL,
			PRIMARY KEY  (IP),
			KEY cached (cached)
		) {$charset_collate};";

		dbDelta( $sql );

		TheLib::message(
			__(
				'<strong>Pop Up!</strong><br />' .
				'Your installation was successfully updated to use the ' .
				'latest version of the plugin!', PO_LANG
			)
		);

		// Save the new DB version to options table.
		//update_option( 'popover_installed', PO_BUILD );
	}

}