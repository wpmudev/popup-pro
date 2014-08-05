<?php

/**
 * Provides the database level functions for the popups.
 */
class IncPopupDatabase {

	// Use `self::db_prefix( IP_TABLE );` to get the full table name.
	const IP_TABLE = 'popover_ip_cache';

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
		$cur_version = self::_get_option( 'popover_installed', 0 );

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
		static $Prefixed = array();

		if ( ! isset( $Prefixed[$table] ) ) {
			$Prefixed[$table] = $table;

			if ( defined( 'PO_GLOBAL' ) && true == PO_GLOBAL ) {
				if ( ! empty( $wpdb->base_prefix ) ) {
					$Prefixed[$table] = $wpdb->base_prefix . $table;
				} else {
					$Prefixed[$table] = $wpdb->prefix . $table;
				}
			} else {
				$Prefixed[$table] = $wpdb->prefix . $table;
			}
		}

		return $Prefixed[$table];
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
			// Migrate to custom post type.
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
				'supporter' => 'no_prosite',
				'searchengine' => 'searchengine',
				'commented' => 'no_comment',
				'internal' => 'no_internal',
				'referrer' => 'referrer',
				'count' => 'count',
				'max_width' => 'width',
				'on_exit' => 'on_exit',
				'on_click' => 'on_click',
			);

			// Translate style to new keys
			$style_mapping = array(
				'Default' => 'old-default',
				'Default Fixed' => 'old-fixed',
				'Dark Background Fixed' => 'old-fullbackground',
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
				if ( isset( $style_mapping[ @$raw['popover_style'] ] ) ) {
					$style = $style_mapping[ @$raw['popover_style'] ];
				} else {
					$style = @$raw['popover_style'];
				}

				$data = array(
					'name'          => $item->popover_title,
					'content'       => $item->popover_content,
					'order'         => $item->popover_order,
					'active'        => $item->popover_active,
					'size'          => @$raw['popover_size'],
					'color'         => @$raw['popover_colour'],
					'style'         => $style,
					'can_hide'      => (true != @$raw['popoverhideforeverlink']),
					'close_hides'   => @$raw['popover_close_hideforever'],
					'hide_expire'   => @$raw['popover_hideforever_expiry'],
					'display'       => 'delay',
					'delay'         => @$raw['popoverdelay'],
					'rules'         => $checks,
					'rule_data' => array(
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

		// Migrate the Plugin Settings.
		$settings = IncPopupDatabase::get_settings();
		$cur_method = @$settings['loadingmethod'];
		switch ( $cur_method ) {
			case '':
			case 'external':     $cur_method = 'ajax'; break;
			case 'frontloading': $cur_method = 'front'; break;
		}
		$settings['loadingmethod'] = $cur_method;

		// Migrate Add-Ons to new settings.
		$addons = self::_get_option( 'popover_activated_addons', $default );
		$rules = array(
			'class-popup-rule-browser.php',
			'class-popup-rule-geo.php',
			'class-popup-rule-popup.php',
			'class-popup-rule-referer.php',
			'class-popup-rule-url.php',
			'class-popup-rule-user.php',
		);
		foreach ( $addons as $addon ) {
			switch ( $addon ) {
				case 'anonymous_loading.php':
				case 'testheadfooter.php':
					/* Integrated; no option. */ break;
				case 'localgeodatabase.php':
					$settings['geo_db'] = true; break;
				case 'rules-advanced_url.php':
					$rules[] = 'class-popup-rule-advurl.php'; break;
				case 'rules-categories.php':
					$rules[] = 'class-popup-rule-category.php'; break;
				case 'rules-max_width.php':
					$rules[] = 'class-popup-rule-width.php'; break;
				case 'rules-on_exit.php':
					$rules[] = 'class-popup-rule-events.php'; break;
				case 'rules-onclick.php':
					$rules[] = 'class-popup-rule-events.php'; break;
				case 'rules-post_types.php':
					$rules[] = 'class-popup-rule-posttype.php'; break;
				case 'rules-xprofile_value.php':
					$rules[] = 'class-popup-rule-xprofile.php'; break;
			}
		}
		$settings['rules'] = $rules;

		self::set_settings( $settings );

		// Save the new DB version to options table.
		self::_set_option( 'popover_installed', PO_BUILD );
	}

	/**
	 * Returns a popup object.
	 *
	 * @since  4.6
	 * @param  int $post_id ID of the Pop Up
	 * @return IncPopupItem
	 */
	public function get( $post_id ) {
		$post_id = absint( $post_id );
		$popup = wp_cache_get( $post_id, IncPopupItem::POST_TYPE );

		if ( false === $popup ) {
			$popup = new IncPopupItem( $post_id );
			wp_cache_set( $post_id, $popup, IncPopupItem::POST_TYPE );
		}
		return $popup;
	}

	/**
	 * Returns an array with all IDs of the active popups.
	 * The IDs are in the correct order (as defined in the admin list)
	 *
	 * @since  4.6
	 * @return array List of active popups.
	 */
	public function get_active_ids() {
		global $wpdb;
		static $List = null;

		if ( null === $List ) {
			$sql_get = "
				SELECT ID
				FROM {$wpdb->posts}
				WHERE post_type=%s
					AND post_status=%s
				ORDER BY menu_order
			";
			$sql = $wpdb->prepare(
				$sql_get,
				IncPopupItem::POST_TYPE,
				'publish'
			);
			$List = $wpdb->get_col( $sql );
		}

		return $List;
	}

	/**
	 * Returns the next available value for the popup position (order)
	 *
	 * @since  4.6
	 * @return int Next free order position; bottom of the list.
	 */
	public function next_order() {
		global $wpdb;

		$sql = "
			SELECT menu_order
			FROM {$wpdb->posts}
			WHERE post_type=%s
			ORDER BY menu_order DESC
			LIMIT 1
		";
		$sql = $wpdb->prepare(
			$sql,
			IncPopupItem::POST_TYPE
		);
		//wp_die( $sql );
		$pos = $wpdb->get_var( $sql );

		return absint( $pos ) + 1;
	}

	/**
	 * Updates the order of all popups that are not in trash.
	 *
	 * @since  4.6
	 */
	public function refresh_order() {
		global $wpdb;

		// 1. Set all trashed popups to order=999999.
		$sql_fix = "
			UPDATE {$wpdb->posts}
			SET menu_order=999999
			WHERE post_type=%s AND post_status=%s
		";
		$sql = $wpdb->prepare(
			$sql_fix,
			IncPopupItem::POST_TYPE,
			'trash'
		);
		$wpdb->query( $sql );

		// 2. Get all active/inactive popups in correct order.
		$sql_get = "
			SELECT ID, menu_order
			FROM {$wpdb->posts}
			WHERE post_type=%s
				AND post_status IN (%s, %s)
			ORDER BY menu_order
		";
		$sql = $wpdb->prepare(
			$sql_get,
			IncPopupItem::POST_TYPE,
			'publish',
			'draft'
		);
		$list = $wpdb->get_results( $sql );

		// 3. Update the menu order of all active/inactive popups.
		$sql_fix = "
			UPDATE {$wpdb->posts}
			SET menu_order=%d
			WHERE post_type=%s AND ID=%d
		";
		foreach ( $list as $ind => $data ) {
			$sql = $wpdb->prepare(
				$sql_fix,
				($ind + 1),
				IncPopupItem::POST_TYPE,
				$data->ID
			);
			$wpdb->query( $sql );
		}
	}

	/**
	 * Returns the plugin settings.
	 *
	 * @since  4.6
	 * @return array.
	 */
	public function get_settings() {
		$defaults = array(
			'loadingmethod' => 'ajax',
			'geo_db' => false,
			'rules' => array(
				'class-popup-rule-browser.php',
				'class-popup-rule-geo.php',
				'class-popup-rule-popup.php',
				'class-popup-rule-referer.php',
				'class-popup-rule-url.php',
				'class-popup-rule-user.php',
			)
		);

		$data = (array) self::_get_option( 'popover-settings', array() );

		if ( ! is_array( $data ) ) { $data = array(); }
		foreach ( $defaults as $key => $def_value ) {
			if ( ! isset( $data[$key] ) ) {
				$data[$key] = $def_value;
			}
		}

		return $data;
	}

	/**
	 * Saves the plugin settings.
	 *
	 * @since  4.6
	 * @param  array $value The value to save.
	 */
	public function set_settings( $value ) {
		self::_set_option( 'popover-settings', $value );
	}

	/**
	 * Internal function to get a option value from correct options table.
	 *
	 * @since  4.6
	 */
	static protected function _get_option( $key, $default ) {
		$value = $default;
		if ( IncPopup::use_global() ) {
			$value = get_site_option( $key, $default );
		} else {
			$value = get_option( $key, $default );
		}
		return $value;
	}

	/**
	 * Internal function to save a value to the correct options table.
	 *
	 * @since 4.6
	 */
	static protected function _set_option( $key, $value ) {
		if ( IncPopup::use_global() ) {
			update_site_option( $key, $value );
		} else {
			update_option( $key, $value );
		}
	}


	/*==============================*\
	==================================
	==                              ==
	==           IP CACHE           ==
	==                              ==
	==================================
	\*==============================*/


	/**
	 * Returns the country code that is associated with the IP Address from the
	 * local cache table.
	 *
	 * @since  4.6
	 * @param  string $ip The IP Address.
	 * @return string Associated country code (or empty string).
	 */
	static public function get_country( $ip ) {
		global $wpdb;
		$Country = array();

		if ( ! isset( $Country[$ip] ) ) {
			$ip_table = self::db_prefix( self::IP_TABLE );
			$sql = "
				SELECT country
				FROM {$ip_table}
				WHERE IP = %s
			";
			$sql = $wpdb->prepare( $sql, $ip );
			$Country[$ip] = $wpdb->get_var( $sql );

			if ( null === $Country[$ip] ) { $Country[$ip] = ''; }
		}

		return $Country[$ip];
	}

	/**
	 * Adds information to the IP-Cache table.
	 *
	 * @since 4.6
	 * @param string $ip The IP Address.
	 * @param string $country Country code.
	 */
	static public function add_ip( $ip, $country ) {
		global $wpdb;

		$ip_table = self::db_prefix( self::IP_TABLE );

		// Delete the cached data, if it already exists.
		$sql = "
			DELETE FROM $ip_table
			WHERE IP = %s
		";
		$sql = $wpdb->prepare( $sql, $ip );
		$wpdb->query( $sql );

		// Insert the new dataset.
		$sql = "
			INSERT INTO $ip_table (IP, country, cached)
			VALUES (%s, %s, %s)
		";
		$sql = $wpdb->prepare( $sql, $ip, $country, time() );
		$wpdb->query( $sql );
	}

}