<?php
/*
* The processing part of the popover plugin
* - was previously in popoverpublic.php
*/

if(!class_exists('popoverajax')) {

	class popoverajax {

		var $mylocation = '';
		var $build = 5;
		var $db;

		var $tables = array( 'popover', 'popover_ip_cache' );
		var $popover;
		var $popover_ip_cache;

		var $activepopover = false;

		function __construct() {

			global $wpdb;

			$this->db =& $wpdb;

			foreach($this->tables as $table) {
				$this->$table = popover_db_prefix($this->db, $table);
			}

			add_action('init', array(&$this, 'initialise_ajax'), 99);

			add_action( 'plugins_loaded', array(&$this, 'load_textdomain'));

			$directories = explode(DIRECTORY_SEPARATOR,dirname(__FILE__));
			$this->mylocation = $directories[count($directories)-1];

		}

		function popoverajax() {
			$this->__construct();
		}

		function initialise_ajax() {

			$settings = get_popover_option('popover-settings', array( 'loadingmethod' => 'external'));

			switch( $settings['loadingmethod'] ) {

				case 'external':		add_action( 'wp_ajax_popover_selective_ajax', array(&$this,'ajax_selective_message_display') );
										add_action( 'wp_ajax_nopriv_popover_selective_ajax', array(&$this,'ajax_selective_message_display') );
										break;

				case 'frontloading':	if( isset( $_GET['popoverajaxaction']) && $_GET['popoverajaxaction'] == 'popover_selective_ajax' ) {
											$this->ajax_selective_message_display();
										}
										break;
			}

		}

		function load_textdomain() {

			$locale = apply_filters( 'popover_locale', get_locale() );
			$mofile = popover_dir( "popoverincludes/languages/popover-$locale.mo" );

			if ( file_exists( $mofile ) )
				load_textdomain( 'popover', $mofile );

		}

		function get_active_popovers() {
			$sql = $this->db->prepare( "SELECT * FROM {$this->popover} WHERE popover_active = %d ORDER BY popover_order ASC", 1 );

			return $this->db->get_results( $sql );
		}

		function ajax_selective_message_display() {

			if(isset($_GET['callback'])) {
				$data = $this->selective_message_display();
				echo $_GET['callback'] . "(" . json_encode($data) . ")";
			}

			exit;

		}

		function selective_message_display() {

			if(function_exists('get_site_option') && defined('PO_GLOBAL') && PO_GLOBAL == true) {
				$updateoption = 'update_site_option';
				$getoption = 'get_site_option';
			} else {
				$updateoption = 'update_option';
				$getoption = 'get_option';
			}

			$popovers = $this->get_active_popovers();

			if(!empty($popovers)) {

				foreach( (array) $popovers as $popover ) {

					// We have an active popover so extract the information and test it
					$popover_title = stripslashes($popover->popover_title);
					$popover_content = stripslashes($popover->popover_content);
					$popover->popover_settings = unserialize($popover->popover_settings);

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

								case "loggedin":	if($this->is_loggedin()) {
														$show = false;
													}
													break;

								case "isloggedin":	if(!$this->is_loggedin()) {
														$show = false;
													}
													break;

								case "commented":	if($this->has_commented()) {
														$show = false;
													}
													break;

								case "searchengine":
													if(!$this->is_fromsearchengine( $_REQUEST['thereferrer'] )) {
														$show = false;
													}
													break;

								case "internal":	$internal = str_replace('^http://','',get_option('home'));
													if($this->referrer_matches( $internal, $_REQUEST['thereferrer'] )) {
														$show = false;
													}
													break;

								case "referrer":	$match = $popover_ereg;
													if(!$this->referrer_matches( $match, $_REQUEST['thereferrer'])) {
														$show = false;
													}
													break;

								case "count":		if($this->has_reached_limit($popover_count)) {
														$show = false;
													}
													break;

								case 'onurl':		if(!$this->onurl( $popover_onurl, $_REQUEST['thefrom'] )) {
														$show = false;
													}
													break;

								case 'notonurl':	if($this->onurl( $popover_notonurl, $_REQUEST['thefrom'] )) {
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
														if(!apply_filters( 'popover_process_rule_' . $key, false )) {
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

						if($this->clear_forever()) {
							$show = false;
						}

					}

					if($show == true) {

						// return the popover to the calling function

						$popover = array();

						$popover['name'] = 'a' . md5(date('d')) . '-po';

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

						$style = '';
						$backgroundstyle = '';

						if($popover_usejs == 'yes') {
							$style = 'z-index:999999;';
							$box = 'color: #' . $popover_colour['fore'] . '; background: #' . $popover_colour['back'] . ';';
							$style .= 'left: -1000px; top: =100px;';
						} else {
							$style =  'left: ' . $popover_location['left'] . '; top: ' . $popover_location['top'] . ';' . ' z-index:999999;';
							$style .= 'margin-top: ' . $popover_margin['top'] . '; margin-bottom: ' . $popover_margin['bottom'] . '; margin-right: ' . $popover_margin['right'] . '; margin-left: ' . $popover_margin['left'] . ';';

							$box = 'width: ' . $popover_size['width'] . '; height: ' . $popover_size['height'] . '; color: #' . $popover_colour['fore'] . '; background: #' . $popover_colour['back'] . ';';

						}

						if(!empty($popover_delay) && $popover_delay != 'immediate') {
							// Hide the popover initially
							$style .= ' visibility: hidden;';
							$backgroundstyle .= ' visibility: hidden;';
						}

						$availablestyles = apply_filters( 'popover_available_styles_directory', array() );

						$popover['html'] = '';
						if( in_array($popoverstyle, array_keys($availablestyles)) ) {
							$popover_messagebox = 'a' . md5(date('d')) . '-po';

							if(file_exists(trailingslashit($availablestyles[$popoverstyle]) . 'popover.php')) {
								ob_start();
								include_once( trailingslashit($availablestyles[$popoverstyle]) . 'popover.php' );
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
								include_once( trailingslashit($availablestyles[$popoverstyle]) . 'style.css' );
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


						return $popover;
						// Exit from the for - as we have sent a popover
						break;
					}
				}

			}

			// There is no popover to show - so send back a no-popover message
			return array( 'name' => 'nopopover' );


		}

		function sanitise_array($arrayin) {

			foreach( (array) $arrayin as $key => $value) {
				$arrayin[$key] = htmlentities(stripslashes($value) ,ENT_QUOTES, 'UTF-8');
			}

			return $arrayin;
		}

		function is_fromsearchengine( $ref = '') {

			$SE = array('/search?', '.google.', 'web.info.com', 'search.', 'del.icio.us/search', 'soso.com', '/search/', '.yahoo.', '.bing.' );

			foreach ($SE as $url) {
				if (strpos( $ref, $url) !== false ) {
					if($url == '.google.') {
						if( $this->is_googlesearch( $ref) ) {
							return true;
						} else {
							return false;
						}
					} else {
						return true;
					}
				}
			}
			return false;
		}

		function is_googlesearch( $ref = '' ) {
			$SE = array('.google.');

			foreach ($SE as $url) {
				if (strpos($ref,$url) !== false ) {
					// We've found a google referrer - get the query strings and check its a web source
					$qs = parse_url( $ref, PHP_URL_QUERY );
					$qget = array();
					foreach(explode('&', $qs) as $keyval) {
					    list( $key, $value ) = explode('=', $keyval);
					    $qget[ trim($key) ] = trim($value);
					}
					if(array_key_exists('source', $qget) && $qget['source'] == 'web') {
						return true;
					}
				}
			}
			return false;
		}

		function is_ie()
		{
		    if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false))
		        return true;
		    else
		        return false;
		}

		function is_loggedin() {
			return is_user_logged_in();
		}

		function has_commented() {
			if ( isset($_COOKIE['comment_author_'.COOKIEHASH]) ) {
				return true;
			} else {
				return false;
			}
		}

		function referrer_matches($check, $referer = '') {

			if(preg_match( '#' . $check . '#i', $referer )) {
				return true;
			} else {
				return false;
			}

		}

		function has_reached_limit($count = 3) {
			if ( isset($_COOKIE['popover_view_'.COOKIEHASH]) && addslashes($_COOKIE['popover_view_'.COOKIEHASH]) >= $count ) {
				return true;
			} else {
				return false;
			}
		}

		function myURL() {

		 	if ($_SERVER["HTTPS"] == "on") {
				$url .= "https://";
			} else {
				$url = 'http://';
			}

			if ($_SERVER["SERVER_PORT"] != "80") {
		  		$url .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
		 	} else {
		  		$url .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
		 	}

		 	return trailingslashit($url);
		}

		function onurl( $urllist = array(), $url = '' ) {

			$urllist = array_map( 'trim', $urllist );

			if(!empty($urllist)) {
				foreach( $urllist as $ul ) {
					if(preg_match( '#^' . $ul . '$#i', $url )) {
						return true;
					}
				}
				// if we are here then there hasn't been a match
				return false;
			} else {
				return true;
			}

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

		function clear_forever() {
			if ( isset($_COOKIE['popover_never_view']) ) {
				return true;
			} else {
				return false;
			}
		}

	}

}
?>