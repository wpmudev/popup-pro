<?php
if(!class_exists('popoverajax')) {

	class popoverajax {

		var $mylocation = '';
		var $build = 3;
		var $db;

		var $tables = array( 'popover' );
		var $popover;

		var $activepopover = false;

		function __construct() {

			global $wpdb;

			$this->db =& $wpdb;

			foreach($this->tables as $table) {
				$this->$table = popover_db_prefix($this->db, $table);
			}

			//add_action('init', array(&$this, 'selective_message_display'), 1);

			add_action( 'plugins_loaded', array(&$this, 'load_textdomain'));

			$directories = explode(DIRECTORY_SEPARATOR,dirname(__FILE__));
			$this->mylocation = $directories[count($directories)-1];

			// Adding in Ajax calls - need to be in the admin area as it uses admin_url :/
			add_action('wp_ajax_po_popover', array(&$this, 'selective_message_display') );
			add_action('wp_ajax_nopriv_po_popover', array(&$this, 'selective_message_display') );

		}

		function popoverajax() {
			$this->__construct();
		}

		function load_textdomain() {

			$locale = apply_filters( 'popover_locale', get_locale() );
			$mofile = popover_dir( "popoverincludes/languages/popover-$locale.mo" );

			if ( file_exists( $mofile ) )
				load_textdomain( 'popover', $mofile );

		}

		function get_active_popovers() {
			$sql = $this->db->prepare( "SELECT * FROM {$this->popover} WHERE popover_active = 1 ORDER BY popover_order ASC" );

			return $this->db->get_results( $sql );
		}

		function selective_message_display() {

			die('hello');

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

					$popoverstyle = $popover->popover_settings['popover_style'];

					$popover_delay = $popover->popover_settings['popoverdelay'];

					$popover_onurl = $popover->popover_settings['onurl'];
					$popover_notonurl = $popover->popover_settings['notonurl'];

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
													if(!$this->is_fromsearchengine()) {
														$show = false;
													}
													break;

								case "internal":	$internal = str_replace('http://','',get_option('home'));
													if($this->referrer_matches(addcslashes($internal,"/"))) {
														$show = false;
													}
													break;

								case "referrer":	$match = $popover_ereg;
													if(!$this->referrer_matches(addcslashes($match,"/"))) {
														$show = false;
													}
													break;

								case "count":		if($this->has_reached_limit($popover_count)) {
														$show = false;
													}
													break;

								case 'onurl':		if(!$this->onurl( $popover_onurl )) {
														$show = false;
													}
													break;

								case 'notonurl':	if($this->onurl( $popover_notonurl )) {
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
					}

					if($show == true) {

						if($this->clear_forever()) {
							$show = false;
						}

					}

					if($show == true) {

						// Store the active popover so we know what we are using in the footer.
						$this->activepopover = $popover;

						wp_enqueue_script('jquery');

						$popover_messagebox = 'a' . md5(date('d')) . '-po';
						// Show the advert
						wp_enqueue_script('popoverjs', popover_url('popoverincludes/js/popover.js'), array('jquery'), $this->build);
						if(!empty($popover_delay) && $popover_delay != 'immediate') {
							// Set the delay
							wp_localize_script('popoverjs', 'popover', array(	'messagebox'		=>	'#' . $popover_messagebox,
																				'messagedelay'		=>	$popover_delay * 1000
																				));
						} else {
							wp_localize_script('popoverjs', 'popover', array(	'messagebox'		=>	'#' . $popover_messagebox,
																				'messagedelay'		=>	0
																				));
						}

						if($popover_usejs == 'yes') {
							wp_enqueue_script('popoveroverridejs', popover_url('popoverincludes/js/popoversizing.js'), array('jquery'), $this->build);
						}

						add_action('wp_head', array(&$this, 'page_header'));
						add_action('wp_footer', array(&$this, 'page_footer'));

						// Add the cookie
						if ( isset($_COOKIE['popover_view_'.COOKIEHASH]) ) {
							$count = intval($_COOKIE['popover_view_'.COOKIEHASH]);
							if(!is_numeric($count)) $count = 0;
							$count++;
						} else {
							$count = 1;
						}
						if(!headers_sent()) setcookie('popover_view_'.COOKIEHASH, $count , time() + 30000000, COOKIEPATH, COOKIE_DOMAIN);

						break;
					}


				}

			}

		}

		function sanitise_array($arrayin) {

			foreach($arrayin as $key => $value) {
				$arrayin[$key] = htmlentities(stripslashes($value) ,ENT_QUOTES, 'UTF-8');
			}

			return $arrayin;
		}

		function page_header() {

			if(!$this->activepopover) {
				return;
			}

			$popover = $this->activepopover;

			$popover_title = stripslashes($popover->popover_title);
			$popover_content = stripslashes($popover->popover_content);

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

			$popoverstyle = $popover->popover_settings['popover_style'];

			$popover_hideforever = $popover->popover_settings['popoverhideforeverlink'];

			$popover_delay = $popover->popover_settings['popoverdelay'];

			$popover_messagebox = 'a' . md5(date('d')) . '-po';

			$availablestyles = apply_filters( 'popover_available_styles_directory', array() );
			$availablestylesurl = apply_filters( 'popover_available_styles_url', array() );

			if( in_array($popoverstyle, array_keys($availablestyles)) ) {
				// Add the styles
				if(file_exists(trailingslashit($availablestyles[$popoverstyle]) . 'style.css')) {
					ob_start();
					include_once( trailingslashit($availablestyles[$popoverstyle]) . 'style.css' );
					$content = ob_get_contents();
					ob_end_clean();

					echo "<style type='text/css'>\n";
					$content = str_replace('#messagebox', '#' . $popover_messagebox, $content);
					$content = str_replace('%styleurl%', trailingslashit($availablestylesurl[$popoverstyle]), $content);
					echo $content;
					echo "</style>\n";
				}

			}

		}

		function page_footer() {

			if(!$this->activepopover) {
				return;
			}

			$popover = $this->activepopover;

			$popover_title = stripslashes($popover->popover_title);
			$popover_content = stripslashes($popover->popover_content);

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

			$popoverstyle = $popover->popover_settings['popover_style'];

			$popover_hideforever = $popover->popover_settings['popoverhideforeverlink'];

			$popover_delay = $popover->popover_settings['popoverdelay'];

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

			if( in_array($popoverstyle, array_keys($availablestyles)) ) {
				$popover_messagebox = 'a' . md5(date('d')) . '-po';

				if(file_exists(trailingslashit($availablestyles[$popoverstyle]) . 'popover.php')) {
					ob_start();
					include_once( trailingslashit($availablestyles[$popoverstyle]) . 'popover.php' );
					ob_end_flush();
				}
			}


		}

		function is_fromsearchengine() {
			$ref = $_SERVER['HTTP_REFERER'];

			$SE = array('/search?', '.google.', 'web.info.com', 'search.', 'del.icio.us/search', 'soso.com', '/search/', '.yahoo.', '.bing.' );

			foreach ($SE as $url) {
				if (strpos($ref,$url)!==false) return true;
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

		function referrer_matches($check) {

			if(preg_match( '/' . $check . '/i', $_SERVER['HTTP_REFERER'] )) {
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

		function onurl( $urllist = array() ) {

			$urllist = array_map( 'trim', $urllist );

			if(!empty($urllist)) {
				if(in_array($this->myURL(), $urllist)) {
					// we are on the list
					return true;
				} else {
					return false;
				}
			} else {
				return true;
			}

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