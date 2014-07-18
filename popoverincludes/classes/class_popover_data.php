<?php

/**
 * Main model class.
 * Used for all data interaction.
 */
class Popover_Data {

	private static $_instance;

	const BUILD = 5;

	private $_db;
	private $_tables;
	
	public static function get_instance () {
		if (!self::$_instance) self::$_instance = new self;
		return self::$_instance;
	}

	private function __clone () {}
	private function __wakeup () {}

	private function __construct () {
		global $wpdb;
		$this->_db = $wpdb;
		$tables = array(
			'popover', 
			'popover_ip_cache'
		);
		$this->_tables = new stdClass;
		foreach ($tables as $table) {
			$this->_tables->$table = popover_db_prefix($this->_db, $table);
		}

		// Check ourselves
		$installed = get_option('popover_installed', false);
		if (empty($installed) || $installed != self::BUILD) {
			$this->_install();
			update_option('popover_installed', self::BUILD);
		}
	}

	private function _install () {
		$charset_collate = '';

		if (!empty($this->_db->charset)) {
			$charset_collate = "DEFAULT CHARACTER SET " . $this->_db->charset;
		}

		if (!empty($this->_db->collate)) {
			$charset_collate .= " COLLATE " . $this->_db->collate;
		}

		if ($this->_db->get_var("SHOW TABLES LIKE '{$this->_tables->popover}' ") != $this->_tables->popover) {
			 $sql = "CREATE TABLE `{$this->_tables->popover}` (
			  	`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				  `popover_title` varchar(250) DEFAULT NULL,
				  `popover_content` text,
				  `popover_settings` text,
				  `popover_order` bigint(20) DEFAULT '0',
				  `popover_active` int(11) DEFAULT '0',
				  PRIMARY KEY (`id`)
				) $charset_collate;";
			$this->_db->query($sql);

		}

		// Add in IP cache table
		if ($this->_db->get_var( "SHOW TABLES LIKE '{$this->_tables->popover_ip_cache}' ") != $this->_tables->popover_ip_cache) {
			 $sql = "CREATE TABLE `{$this->_tables->popover_ip_cache}` (
			  	`IP` varchar(12) NOT NULL DEFAULT '',
				  `country` varchar(2) DEFAULT NULL,
				  `cached` bigint(20) DEFAULT NULL,
				  PRIMARY KEY (`IP`),
				  KEY `cached` (`cached`)
				) $charset_collate;";
			$this->_db->query($sql);
		}
	}

	public function has_existing_popover () {

		if(function_exists('get_site_option') && defined('PO_GLOBAL') && PO_GLOBAL == true) {
			$getoption = 'get_site_option';
		} else {
			$getoption = 'get_option';
		}

		$popsexist = $this->_db->get_var("SELECT COUNT(*) FROM {$this->_tables->popover}");

		if($popsexist == 0 && $getoption('popover_content','no') != 'no' && $getoption('popover_notranfers', 'no') == 'no') {
			// No pops - and one set in the options
			return true;
		} else {
			return false;
		}
	}

	public function reorder_popovers ($popover_id, $order) {
		$this->_db->update(
			$this->_tables->popover, 
			array('popover_order' => $order), 
			array('id' => $popover_id)
		);
	}

	public function get_popovers () {
		$sql = "SELECT * FROM {$this->_tables->popover} ORDER BY popover_order ASC";
		return $this->_db->get_results($sql);
	}

	public function get_popover ($id) {
		return $this->_db->get_row(
			$this->_db->prepare("SELECT * FROM {$this->_tables->popover} WHERE id = %d", $id)
		);
	}

	public function activate_popover ($id) {
		return $this->_db->update(
			$this->_tables->popover, 
			array('popover_active' => 1), 
			array( 'id' => $id)
		);
	}

	public function deactivate_popover ($id) {
		return $this->_db->update(
			$this->_tables->popover, 
			array('popover_active' => 0), 
			array( 'id' => $id) 
		);
	}

	public function toggle_popover ($id) {
		$sql = $this->_db->prepare("UPDATE {$this->_tables->popover} SET popover_active = NOT popover_active WHERE id = %d", $id);
		return $this->_db->query($sql);

	}

	public function delete_popover ($id) {
		return $this->_db->query(
			$this->_db->prepare("DELETE FROM {$this->_tables->popover} WHERE id = %d", $id)
		);
	}

	public function get_active_popovers () {
		$sql = $this->_db->prepare("SELECT * FROM {$this->_tables->popover} WHERE popover_active = %d ORDER BY popover_order ASC", 1);
		return $this->_db->get_results($sql);
	}

	public function save_popover ($popover) {
		return !empty($popover['id'])
			? $this->_db->update($this->_tables->popover, $popover, array('id' => $popover['id']))
			: $this->_db->insert($this->_tables->popover, $popover)
		;
	}

	public function get_applicable_popover_messages () {
		if(function_exists('get_site_option') && defined('PO_GLOBAL') && PO_GLOBAL == true) {
			$updateoption = 'update_site_option';
			$getoption = 'get_site_option';
		} else {
			$updateoption = 'update_option';
			$getoption = 'get_option';
		}

		$popovers = $this->get_active_popovers();
		$all_popovers = array();

		if(!empty($popovers)) {

			foreach( (array) $popovers as $popover ) {
				$pop = $this->_popover_to_message($popover);
				if (!empty($pop)) $all_popovers[] = $pop;
			}

		}

		return !empty($all_popovers)
			? array('name' => 'multiple', 'popovers' => $all_popovers)
			: array('name' => 'nopopover')
		;
	}

	private function _popover_to_message ($popover) {
		// We have an active popover so extract the information and test it
		$popover_title = stripslashes($popover->popover_title);
		$popover_content = stripslashes($popover->popover_content);
		$popover->popover_settings = unserialize($popover->popover_settings);
		
		if (defined('PO_ALLOW_CONTENT_FILTERING') && PO_ALLOW_CONTENT_FILTERING) {
			$popover_content = defined('PO_USE_FULL_CONTENT_FILTERING') && PO_USE_FULL_CONTENT_FILTERING
				? apply_filters('the_content', stripslashes($popover_content))
				: wptexturize(wpautop($popover_content))
			;
		}

		$popover_size = $popover->popover_settings['popover_size'];
		$popover_location = $popover->popover_settings['popover_location'];
		$popover_colour = $popover->popover_settings['popover_colour'];
		$popover_margin = $popover->popover_settings['popover_margin'];

		$popover_size = $this->sanitise_array($popover_size);
		$popover_location = $this->sanitise_array($popover_location);
		$popover_colour = $this->sanitise_array($popover_colour);
		$popover_margin = $this->sanitise_array($popover_margin);

		$popover_check = $popover->popover_settings['popover_check'];
		$popover_ereg = $popover->popover_settings['popover_ereg'];
		$popover_count = $popover->popover_settings['popover_count'];

		$popover_usejs = $popover->popover_settings['popover_usejs'];

		$popoverstyle = (isset($popover->popover_settings['popover_style'])) ? $popover->popover_settings['popover_style'] : '';

		$popover_hideforever = (isset($popover->popover_settings['popoverhideforeverlink'])) ? $popover->popover_settings['popoverhideforeverlink'] : '';
		$popover_close_hideforever = (isset($popover->popover_settings['popover_close_hideforever'])) ? ('yes' == $popover->popover_settings['popover_close_hideforever']) : false;
		$popover_hideforever_expiry = (isset($popover->popover_settings['popover_hideforever_expiry'])) 
			? (int)$popover->popover_settings['popover_hideforever_expiry'] 
			: (defined('PO_DEFAULT_EXPIRY') && PO_DEFAULT_EXPIRY ? PO_DEFAULT_EXPIRY : 365)
		;

		$popover_delay = (isset($popover->popover_settings['popoverdelay'])) ? $popover->popover_settings['popoverdelay'] : '';

		$popover_onurl = (isset($popover->popover_settings['onurl'])) ? $popover->popover_settings['onurl'] : '';
		$popover_notonurl = (isset($popover->popover_settings['notonurl'])) ? $popover->popover_settings['notonurl'] : '';

		$popover_incountry = (isset($popover->popover_settings['incountry'])) ? $popover->popover_settings['incountry'] : '';
		$popover_notincountry = (isset($popover->popover_settings['notincountry'])) ? $popover->popover_settings['notincountry'] : '';

		$popover_onurl = $this->sanitise_array($popover_onurl);
		$popover_notonurl = $this->sanitise_array($popover_notonurl);

		$show = true;

		if(!empty($popover_check)) {

			$order = explode(',', $popover_check['order']);

			foreach($order as $key) {

				switch ($key) {

					case "supporter":
										if(function_exists('is_pro_site') && is_pro_site()) {
											$show = false;
										}
										break;

					case 'incountry':	$incountry = $this->incountry( $popover_incountry );
										if(!$incountry || $incountry === 'XX') {
											$show = false;
										}
										break;

					case 'notincountry':
										$incountry = $this->incountry( $popover_notincountry );
										if($incountry || $incountry === 'XX') {
											$show = false;
										}
										break;

					default:			if(has_filter('popover_process_rule_' . $key)) {
											if(!apply_filters( 'popover_process_rule_' . $key, false, $popover )) {
												$show = false;
											}
										}
										break;

				}
			}

			// Check for forced popover and if set then output that one instead of any other
			if(isset($_REQUEST['active_popover']) && (int) $_REQUEST['active_popover'] != 0) {
				if($popover->id == (int) $_REQUEST['active_popover']) {
					$show = true;
				} else {
					$show = false;
				}
			}
		}

		if($show == true) {

			if($this->clear_forever($popover->id)) {
				$show = false;
			}

		}

		if (!$show) return false; // Short out

		// return the popover to the calling function
		$_original_popover = $popover;
		$popover = array(
			'popover_id' => $_original_popover->id,
			'close_hide' => $popover_close_hideforever,
			'expiry' => $popover_hideforever_expiry,
		);

		$popover['name'] = $this->_get_popover_name($popover);

		// Show the advert
		if(!empty($popover_delay) && $popover_delay != 'immediate') {
			// Set the delay
			$popover['delay'] = $popover_delay * 1000;
		} else {
			$popover['delay'] = 0;
		}

		if($popover_usejs == 'yes') {
			$popover['usejs'] = 'yes';
		} else {
			$popover['usejs'] = 'no';
		}

		$popover['size']['usejs'] = !empty($popover_size['usejs']);
		$popover['position']['usejs'] = !empty($popover_location['usejs']);

		$style = '';
		$backgroundstyle = '';

		if($popover_usejs == 'yes') {
			$style = 'z-index:999999;';
			$box = 'color: #' . $popover_colour['fore'] . '; background: #' . $popover_colour['back'] . ';';
			$style .= 'left: -1000px; top: =100px;';
		} else {
			$style = 'z-index:999999;';
			if (empty($popover_location['usejs'])) {
				$style .=  'left: ' . $popover_location['left'] . '; top: ' . $popover_location['top'] . ';';
			}
			$style .= 'margin-top: ' . $popover_margin['top'] . '; margin-bottom: ' . $popover_margin['bottom'] . '; margin-right: ' . $popover_margin['right'] . '; margin-left: ' . $popover_margin['left'] . ';';

			$box = 'color: #' . $popover_colour['fore'] . '; background: #' . $popover_colour['back'] . ';';
			if (empty($popover_size['usejs'])) {
				$box .= 'width: ' . $popover_size['width'] . '; height: ' . $popover_size['height'] . ';';
			}

		}

		if(!empty($popover_delay) && $popover_delay != 'immediate') {
			// Hide the popover initially
			$style .= ' visibility: hidden;';
			$backgroundstyle .= ' visibility: hidden;';
		}

		$availablestyles = apply_filters( 'popover_available_styles_directory', array() );

		$popover['html'] = '';
		if( in_array($popoverstyle, array_keys($availablestyles)) ) {
			//$popover_messagebox = 'a' . md5(date('d')) . '-po';
			$popover_messagebox = $popover['name'];

			if(file_exists(trailingslashit($availablestyles[$popoverstyle]) . 'popover.php')) {
				ob_start();
				include( trailingslashit($availablestyles[$popoverstyle]) . 'popover.php' );
				$popover['html'] = ob_get_contents();
				ob_end_clean();
			}
		}

		$availablestylesurl = apply_filters( 'popover_available_styles_url', array() );

		$popover['style'] = '';
		if( in_array($popoverstyle, array_keys($availablestyles)) ) {
			// Add the styles
			if(file_exists(trailingslashit($availablestyles[$popoverstyle]) . 'style.css')) {
				ob_start();
				include( trailingslashit($availablestyles[$popoverstyle]) . 'style.css' );
				$content = ob_get_contents();
				ob_end_clean();

				$popover['style'] = str_replace('#messagebox', '#' . $popover_messagebox, $content);
				$popover['style'] = str_replace('%styleurl%', trailingslashit($availablestylesurl[$popoverstyle]), $popover['style']);

			}

		}


		// Add the cookie
		if ( isset($_COOKIE['popover_view_'.COOKIEHASH]) ) {
			$count = intval($_COOKIE['popover_view_'.COOKIEHASH]);
			if(!is_numeric($count)) $count = 0;
			$count++;
		} else {
			$count = 1;
		}
		if(!headers_sent()) setcookie('popover_view_'.COOKIEHASH, $count , time() + 30000000, COOKIEPATH, COOKIE_DOMAIN);


		return apply_filters('popover-output-popover', $popover, $_original_popover); // Allowing multiple popovers in data array as default
	}

	function incountry( $countrycode ) {
		// Grab the users IP address
		$ip = $_SERVER["REMOTE_ADDR"];

		if( has_filter('popover_pre_incountry') ) {
			// We have an override for the ipcountry in place so ignore the rest
			return apply_filters('popover_pre_incountry', false, $ip, $countrycode );
		}

		$country = $this->get_country_from_cache( $ip );

		if(empty($country)) {
			// No country to get from API
			$country = $this->get_country_from_api( $ip );

			if($country !== false) {
				$this->put_country_in_cache( $ip, $country );
			} else {
				$country = 'XX';
			}
		}

		if($country == $countrycode) {
			return true;
		} else {
			return false;
		}

	}

	function get_country_from_cache( $ip ) {

		$country = $this->db->get_var( $this->db->prepare( "SELECT country FROM {$this->popover_ip_cache} WHERE IP = %s", $ip ) );

		return $country;

	}

	function put_country_in_cache( $ip, $country ) {

		return $this->insertonduplicate( $this->popover_ip_cache, array( 'IP' => $ip, 'country' => $country, 'cached' => time() ) );

	}

	function get_country_from_api( $ip ) {

		$url = str_replace('%ip%', $ip, PO_REMOTE_IP_URL);

		$response = wp_remote_get( $url );

		if(!is_wp_error($response) && $response['response']['code'] == '200' && $response['body'] != 'XX') {
			// cache the response for future use
			$country = trim($response['body']);
		} else {
			if(PO_DEFAULT_COUNTRY !== false) {
				return PO_DEFAULT_COUNTRY;
			} else {
				return false;
			}
		}

	}

	function insertonduplicate($table, $data) {

		global $wpdb;

		$fields = array_keys($data);
		$formatted_fields = array();
		foreach ( $fields as $field ) {
			$form = '%s';
			$formatted_fields[] = $form;
		}
		$sql = "INSERT INTO `$table` (`" . implode( '`,`', $fields ) . "`) VALUES ('" . implode( "','", $formatted_fields ) . "')";
		$sql .= " ON DUPLICATE KEY UPDATE ";

		$dup = array();
		foreach($fields as $field) {
			$dup[] = "`" . $field . "` = VALUES(`" . $field . "`)";
		}

		$sql .= implode(',', $dup);

		return $wpdb->query( $wpdb->prepare( $sql, $data) );
	}

	public function sanitise_array ($arrayin) {
		foreach ((array)$arrayin as $key => $value) {
			$arrayin[$key] = htmlentities(stripslashes($value) ,ENT_QUOTES, 'UTF-8');
		}
		return $arrayin;
	}

	public function clear_forever ($id=false) {
		$name = !empty($id) ? "popover_never_view_{$id}" : "popover_never_view";
		return isset($_COOKIE[$name]);
	}

	private function _get_popover_name ($popover) {
		$name = 'a' . md5(date('d') . serialize($popover)) . '-po';
		return apply_filters('popover-data-identifyer', $name, $popover);
	}
}