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
	 * Checks the database version and migrates to latest version is required.
	 *
	 * @since  4.6
	 */
	static public function check_db() {
		// Update the DB if required.
		if ( ! IncPopupDatabase::db_is_current() ) {
			IncPopupDatabase::db_update();
		}
	}

	/**
	 * Checks the state of the database and returns true when the DB has a valid
	 * format for the current plugin version.
	 *
	 * @since  4.6
	 * @return boolean Database state (true = everything okay)
	 */
	static public function db_is_current() {
		$cur_version = self::_get_option( 'popover_installed', 0 );

		// When no DB-Values exist yet then don't even show the notice in
		// the Network dashboard.
		if ( ! $cur_version ) {
			IncPopupDatabase::set_flag( 'network_dismiss', true );
		}

		return PO_BUILD == $cur_version;
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

		// Handle issues where the blog is switched in code (switch_to_blog)
		if ( is_multisite() ) {
			global $blog_id;
		} else {
			$blog_id = 0;
		}

		if ( ! isset( $Prefixed[ $blog_id ] ) ) {
			$Prefixed[ $blog_id ] = array();
		}

		if ( ! isset( $Prefixed[ $blog_id ][ $table ] ) ) {
			$Prefixed[ $table ] = $table;

			if ( defined( 'PO_GLOBAL' ) && true == PO_GLOBAL ) {
				if ( ! empty( $wpdb->base_prefix ) ) {
					$Prefixed[ $blog_id ][ $table ] = $wpdb->base_prefix . $table;
				} else {
					$Prefixed[ $blog_id ][ $table ] = $wpdb->prefix . $table;
				}
			} else {
				$Prefixed[ $blog_id ][ $table ] = $wpdb->prefix . $table;
			}
		}

		return $Prefixed[ $blog_id ][ $table ];
	}

	/**
	 * Setup or migrate the database to current plugin version.
	 *
	 * This function uses error suppression on purpose.
	 *
	 * @since  4.6
	 */
	static public function db_update() {
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
		$count = 0;

		if ( $wpdb->get_var( 'SHOW TABLES LIKE "' . $tbl_popover . '" ' ) == $tbl_popover ) {
			// Create a column in old table to monitor migration status.
			$sql = "CREATE TABLE {$tbl_popover} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				popover_title varchar(250) DEFAULT NULL,
				popover_content text,
				popover_settings text,
				popover_order bigint(20) DEFAULT '0',
				popover_active int(11) DEFAULT '0',
				migrated tinyint DEFAULT '0',
				PRIMARY KEY  (id)
			) {$charset_collate};";

			dbDelta( $sql );

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
			WHERE migrated=0
			";
			$res = $wpdb->get_results( $sql );

			// Name mapping of conditions/rules from build 5 -> 6.
			$mapping = array(
				'isloggedin' => 'login',
				'loggedin' => 'no_login',
				'onurl' => 'url',
				'notonurl' => 'no_url',
				'incountry' => 'country',
				'notincountry' => 'no_country',
				'advanced_urls' => 'adv_url',
				'not-advanced_urls' => 'no_adv_url',
				'categories' => 'category',
				'not-categories' => 'no_category',
				'post_types' => 'posttype',
				'not-post_types' => 'no_posttype',
				'xprofile_value' => 'xprofile',
				'not-xprofile_value' => 'no_xprofile',
				'supporter' => 'no_prosite',
				'searchengine' => 'searchengine',
				'commented' => 'no_comment',
				'internal' => 'no_internal',
				'referrer' => 'referrer',
				'count' => 'count',
				'max_width' => 'width',
				'wp_roles_rule' => 'role',
				'membership_level' => 'membership',
				//'on_exit' => 'on_exit',
				//'on_click' => 'on_click',
			);

			// Translate style to new keys
			$style_mapping = array(
				'Default' => 'old-default',
				'Default Fixed' => 'old-fixed',
				'Dark Background Fixed' => 'old-fullbackground',
			);

			// Migrate data from build 5 to build 6!
			foreach ( $res as $item ) {
				// Confirm the item was not migrated, just to be sure...
				// This is one-time code, we don't care for performance here.
				$sql = "
					SELECT 1 status
					FROM {$tbl_popover}
					WHERE id=%s AND migrated=0
				";
				$sql = $wpdb->prepare( $sql, $item->id );
				$status = $wpdb->get_var( $sql );
				if ( '1' != $status ) { continue; }

				$raw = maybe_unserialize( $item->popover_settings );
				$checks = explode( ',', @$raw['popover_check']['order'] );
				foreach ( $checks as $ind => $key ) {
					if ( isset( $mapping[ $key ] ) ) {
						$checks[ $ind ] = $mapping[ $key ];
					} else {
						unset( $checks[ $ind ] );
					}
				}

				if ( isset( $style_mapping[ @$raw['popover_style'] ] ) ) {
					$style = $style_mapping[ @$raw['popover_style'] ];
				} else {
					$style = @$raw['popover_style'];
				}

				$colors = array(
					'col1' => @$raw['popover_colour']['back'],
					'col2' => @$raw['popover_colour']['fore'],
				);

				$display = 'delay';
				if ( isset( $raw['on_exit'] ) ) { $display = 'leave'; }
				if ( isset( $raw['on_click'] ) ) { $display = 'click'; }

				$custom_colors = false;
				if ( 'FFFFFF' != $colors['col1'] ) { $custom_colors = true; }
				if ( '000000' != $colors['col2'] ) { $custom_colors = true; }

				$custom_size = true;
				if ( ! empty( $raw['popover_size']['usejs'] ) ) { $custom_size = false; }
				if ( 'no' != @$raw['popover_usejs'] ) { $custom_size = false; }

				$data = array(
					'name'          => $item->popover_title,
					'content'       => $item->popover_content,
					'order'         => $item->popover_order,
					'active'        => (true == $item->popover_active),
					'size'          => @$raw['popover_size'],
					'color'         => $colors,
					'custom_colors' => $custom_colors,
					'custom_size'   => $custom_size,
					'style'         => $style,
					'can_hide'      => ('no' == @$raw['popoverhideforeverlink']),
					'close_hides'   => ('no' != @$raw['popover_close_hideforever']),
					'hide_expire'   => absint( @$raw['popover_hideforever_expiry'] ),
					'display'       => $display,
					'display_data'  => array(
						'delay'      => absint( @$raw['popoverdelay'] ),
						'delay_type' => 's',
						'click'      => @$raw['on_click']['selector'],
						'click_multi' => ! empty( $raw['on_click']['selector'] ),
					),
					'rule'          => $checks,
					'rule_data' => array(
						'count'          => @$raw['popover_count'],
						'referrer'       => @$raw['popover_ereg'],
						'exit'           => @$raw['on_exit'],
						'url'            => @$raw['onurl'],
						'no_url'         => @$raw['notonurl'],
						'adv_url'        => @$raw['advanced_urls']['urls'],
						'no_adv_url'     => @$raw['not-advanced_urls']['urls'],
						'country'        => @$raw['incountry'],
						'no_country'     => @$raw['notincountry'],
						'category'       => @$raw['categories'],
						'no_category'    => @$raw['not-categories'],
						'posttype'       => @$raw['post_types'],
						'no_posttype'    => @$raw['not-post_types'],
						'xprofile'       => @$raw['xprofile_value'],
						'no_xprofile'    => @$raw['not-xprofile_value'],
						'width' => array(
							'min'        => @$raw['max_width']['width'],
						),
					),
				);

				// Save the popup as custom posttype.
				$popup = new IncPopupItem( $data );
				$popup->save( false );

				// Mark Popup as migrated
				$sql = "
					UPDATE {$tbl_popover}
					SET migrated=1
					WHERE id=%s
				";
				$sql = $wpdb->prepare( $sql, $item->id );
				$wpdb->query( $sql );

				// Advance counter.
				$count += 1;
			}
		}

		self::refresh_order();

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

		if ( $count > 0 ) {
			
			$plugin_name = 'WordPress PopUp';
			lib3()->ui->admin_message(
				sprintf(
					__(
						'<strong>%s</strong><br />' .
						'Your installation was successfully updated to use the ' .
						'latest version of the plugin!<br />' .
						'<em>Note: Some PopUp options changed or were replaced. ' .
						'You should have a look at your %sPopUps%s ' .
						'to see if they still look as intended.</em>', 'popover'
					),
					$plugin_name,
					'<a href="' . admin_url( 'edit.php?post_type=' . IncPopupItem::POST_TYPE ) . '">',
					'</a>'
				)
			);
		}

		// Migrate the Plugin Settings.
		$old_settings = IncPopupDatabase::_get_option( 'popover-settings', array() );
		$settings = array();
		$cur_method = @$old_settings['loadingmethod'];
		switch ( $cur_method ) {
			case '':
			case 'external':     $cur_method = 'ajax'; break;
			case 'frontloading': $cur_method = 'front'; break;
		}
		$settings['loadingmethod'] = $cur_method;

		// Migrate Add-Ons to new settings.
		// Add-Ons were always saved in the local Options-table by old version.
		self::before_db();
		$addons = get_option( 'popover_activated_addons', array() );
		self::after_db();

		$rules = array(
			'class-popup-rule-browser.php',
			'class-popup-rule-geo.php',
			'class-popup-rule-popup.php',
			'class-popup-rule-referrer.php',
			'class-popup-rule-url.php',
			'class-popup-rule-user.php',
			'class-popup-rule-prosite.php',
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
				case 'rules-membership.php':
					$rules[] = 'class-popup-rule-membership.php'; break;
				case 'rules-wp_roles.php':
					$rules[] = 'class-popup-rule-role.php'; break;
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
	 * @param  int $post_id ID of the PopUp
	 * @param  bool $clear If TRUE then a new object will be fetched from DB and
	 *                not from cache.
	 * @return IncPopupItem
	 */
	static public function get( $post_id, $clear = false ) {
		$post_id = absint( $post_id );

		if ( $clear ) {
			$popup = false;
		} else {
			$popup = wp_cache_get( $post_id, IncPopupItem::POST_TYPE );
		}

		if ( false === $popup ) {
			self::before_db();
			$popup = new IncPopupItem( $post_id );
			self::after_db();
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
	static public function get_active_ids() {
		global $wpdb;
		static $List = null;

		if ( null === $List ) {
			self::before_db();
			// Using get_posts() adds support for WPML
			$active_posts = get_posts(
				array(
					'post_type' => IncPopupItem::POST_TYPE,
					'suppress_filters' => 0,
					'posts_per_page' => -1,
				)
			);
			foreach ( $active_posts as $post ) {
				$List[] = $post->ID;
			}
			self::after_db();
		}

		return $List;
	}

	/**
	 * Returns the next available value for the popup position (order)
	 *
	 * @since  4.6
	 * @return int Next free order position; bottom of the list.
	 */
	static public function next_order() {
		global $wpdb;
		self::before_db();

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
		self::after_db();

		return absint( $pos ) + 1;
	}

	/**
	 * Updates the order of all popups that are not in trash.
	 *
	 * @since  4.6
	 */
	static public function refresh_order() {
		global $wpdb;
		self::before_db();

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
		self::after_db();
	}

	/**
	 * Count active PopUps
	 *
	 * @since  4.6
	 * @param  int $id Optional. Don't count this PopUp in the results.
	 * @return int Number of active PopUps
	 */
	static public function count_active( $id = '' ) {
		global $wpdb;

		if ( ! is_scalar( $id ) ) { $id = ''; }
		$sql = "
			SELECT COUNT(1)
			FROM {$wpdb->posts}
			WHERE post_type=%s AND post_status=%s AND ID!=%s
		";
		$sql = $wpdb->prepare( $sql, IncPopupItem::POST_TYPE, 'publish', $id );
		$count = $wpdb->get_var( $sql );

		return $count;
	}

	/**
	 * Deactivate all active PopUps
	 *
	 * @since  4.6
	 */
	static public function deactivate_all() {
		global $wpdb;

		$sql = "
			UPDATE {$wpdb->posts}
			SET post_status=%s
			WHERE post_type=%s AND post_status=%s
		";
		$sql = $wpdb->prepare( $sql, 'draft', IncPopupItem::POST_TYPE, 'publish' );
		$wpdb->query( $sql );
	}


	/*==============================*\
	==================================
	==                              ==
	==           SETTINGS           ==
	==                              ==
	==================================
	\*==============================*/

	/**
	 * Returns the list of available loading methods.
	 *
	 * @since  4.6.1.1
	 * @return array Loading methods displayed in the Settings screen.
	 */
	static public function get_loading_methods() {
		static $Loading_methods = null;

		if ( null === $Loading_methods ) {
			$Loading_methods = array();

			$Loading_methods[] = (object) array(
				'id'    => 'footer',
				'label' => __( 'Page Footer', 'popover' ),
				'info'  => __(
					'Include PopUp as part of your site\'s HTML (no AJAX call).',
					'popover'
				),
			);

			$Loading_methods[] = (object) array(
				'id'    => 'ajax',
				'label' => __( 'WordPress AJAX', 'popover' ),
				'info'  => __(
					'Load PopUp separately from the page via a WordPress AJAX call. ' .
					'This is the best option if you use caching.',
					'popover'
				),
			);

			$Loading_methods[] = (object) array(
				'id'    => 'front',
				'label' => __( 'Custom AJAX', 'popover' ),
				'info'  => __(
					'Load PopUp separately from the page via a custom front-end AJAX call.',
					'popover'
				),
			);

			/**
			 * Allow addons to register additional loading methods.
			 *
			 * @var array
			 */
			$Loading_methods = apply_filters(
				'popup-settings-loading-method',
				$Loading_methods
			);
		}

		return $Loading_methods;
	}

	/**
	 * Returns the plugin settings.
	 *
	 * @since  4.6
	 * @return array.
	 */
	static public function get_settings() {
		$defaults = array(
			'loadingmethod' => 'ajax',
			'geo_lookup' => 'hostip',
			'geo_db' => false,
			'rules' => array(
				'class-popup-rule-browser.php',
				'class-popup-rule-geo.php',
				'class-popup-rule-popup.php',
				'class-popup-rule-referrer.php',
				'class-popup-rule-url.php',
				'class-popup-rule-user.php',
				'class-popup-rule-prosite.php',
			),
		);

		$data = (array) self::_get_option( 'inc_popup-config', array() );

		if ( ! is_array( $data ) ) { $data = array(); }
		foreach ( $defaults as $key => $def_value ) {
			if ( ! isset( $data[ $key ] ) ) {
				$data[ $key ] = $def_value;
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
	static public function set_settings( $value ) {
		self::_set_option( 'inc_popup-config', $value );
	}

	/**
	 * Returns the value of a named flag of the current user.
	 * Table: User-Meta
	 *
	 * @since  4.6
	 * @param  string $key
	 * @return mixed
	 */
	static public function get_flag( $key ) {
		$data = get_user_meta( get_current_user_id(), 'po_data', true );
		if ( is_object( $data ) ) { $data = (array) $data; }
		if ( ! is_array( $data ) ) { $data = array(); }

		return isset( $data[ $key ] ) ? $data[ $key ] : '';
	}

	/**
	 * Saves a flag for the current user.
	 * Table: User-Meta
	 *
	 * @since 4.6
	 * @param string $key
	 * @param mixed $value
	 */
	static public function set_flag( $key, $value ) {
		$data = get_user_meta( get_current_user_id(), 'po_data', true );
		if ( is_object( $data ) ) { $data = (array) $data; }
		if ( ! is_array( $data ) ) { $data = array(); }
		$data[ $key ] = $value;

		update_user_meta( get_current_user_id(), 'po_data', $data );
	}

	/**
	 * Internal function to get a option value from correct options table.
	 * Table: Blog-Options
	 *
	 * @since  4.6
	 */
	static protected function _get_option( $key, $default ) {
		$value = $default;
		self::before_db();

		if ( IncPopup::use_global() ) {
			$value = get_site_option( $key, $default );
		} else {
			$value = get_option( $key, $default );
		}

		self::after_db();
		return $value;
	}

	/**
	 * Internal function to save a value to the correct options table.
	 * Table: Blog-Options
	 *
	 * @since 4.6
	 */
	static protected function _set_option( $key, $value ) {
		self::before_db();

		if ( IncPopup::use_global() ) {
			update_site_option( $key, $value );
		} else {
			update_option( $key, $value );
		}

		self::after_db();
	}

	/**
	 * Selects the correct database, in case the PO_GLOBAL flag is true.
	 *
	 * @since  4.6
	 */
	static public function before_db() {
		if ( IncPopup::use_global() ) {
			switch_to_blog( BLOG_ID_CURRENT_SITE );
		}
	}

	/**
	 * Selects the correct database, in case the PO_GLOBAL flag is true.
	 *
	 * @since  4.6
	 */
	static public function after_db() {
		if ( IncPopup::use_global() ) {
			restore_current_blog();
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

		if ( ! isset( $Country[ $ip ] ) ) {
			$ip_table = self::db_prefix( self::IP_TABLE );
			$sql = "
				SELECT country
				FROM {$ip_table}
				WHERE IP = %s
			";
			$sql = $wpdb->prepare( $sql, $ip );
			$Country[ $ip ] = $wpdb->get_var( $sql );

			if ( null === $Country[ $ip ] ) { $Country[ $ip ] = ''; }
		}

		return $Country[ $ip ];
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

	/**
	 * Clears the IP-Country cache
	 *
	 * @since  4.6.1.1
	 */
	static public function clear_ip_cache() {
		global $wpdb;

		$ip_table = self::db_prefix( self::IP_TABLE );

		// Delete the cached data, if it already exists.
		$sql = "TRUNCATE TABLE $ip_table";
		$wpdb->query( $sql );
	}

	/**
	 * Returns a list of available ip-resolution services.
	 *
	 * @since  4.6.1.1
	 * @return array List of available webservices.
	 */
	static public function get_geo_services() {
		static $Geo_service = null;

		if ( null === $Geo_service ) {
			$Geo_service = array();

			$Geo_service['hostip'] = (object) array(
				'label' => 'Host IP',
				'url'   => 'http://api.hostip.info/country.php?ip=%ip%',
				'type'  => 'text',
			);

			$Geo_service['telize'] = (object) array(
				'label' => 'Telize',
				'url'   => 'http://www.telize.com/geoip/%ip%',
				'type'  => 'json',
				'field' => 'country_code',
			);

			$Geo_service['freegeo'] = (object) array(
				'label' => 'Free Geo IP',
				'url'   => 'http://freegeoip.net/json/%ip%',
				'type'  => 'json',
				'field' => 'country_code',
			);

			/**
			 * Allow other modules/plugins to register a geo service.
			 */
			$Geo_service = apply_filters( 'popup-geo-services', $Geo_service );
		}

		return $Geo_service;
	}
}
