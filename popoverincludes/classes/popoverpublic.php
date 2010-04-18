<?php

if(!class_exists('popoverpublic')) {

	class popoverpublic {

		var $mylocation = '';
		var $build = 1;

		function __construct() {

			add_action('init', array(&$this, 'selective_message_display'), 1);
			add_action('wp_footer', array(&$this, 'selective_message_display'));

			$directories = explode(DIRECTORY_SEPARATOR,dirname(__FILE__));
			$this->mylocation = $directories[count($directories)-1];

		}

		function popoverpublic() {
			$this->__construct();
		}

		function selective_message_display() {

			if(function_exists('get_site_option') && defined('PO_GLOBAL')) {
				$getoption = 'get_site_option';
			} else {
				$getoption = 'get_option';
			}

			if($this->mylocation == 'mu-plugins') {
				$getoption = 'get_site_option';
				$location = WPMU_PLUGIN_URL;
			} else {
				$getoption = 'get_option';
				if($this->mylocation == 'plugins') {
					$location = WP_PLUGIN_URL;
				} else {
					$location = WP_PLUGIN_URL . "/" . $this->mylocation;
				}
			}

			$popover_check = $getoption('popover_check', array());

			$show = false;

			if(!empty($popover_check)) {
				foreach($popover_check as $key => $value) {
					switch ($key) {

						case "notsupporter":
											if(function_exists('is_supporter') && !is_supporter()) {
												$show = true;
											}
											break;

						case "loggedin":	if(!$this->is_loggedin()) {
												$show = true;
											}
											break;

						case "isloggedin":	if($this->is_loggedin()) {
												$show = true;
											}
											break;

						case "commented":	if(!$this->has_commented()) {
												$show = true;
											}
											break;

						case "searchengine":
											if($this->is_fromsearchengine()) {
												$show = true;
											}
											break;

						case "internal":	$internal = str_replace('http://','',get_option('siteurl'));
											if(!$this->referrer_matches(addcslashes($internal,"/"))) {
												$show = true;
											}
											break;

						case "referrer":	$match = $getoption('popover_ereg','');
											if($this->is_fromsearchengine(addcslashes($match,"/"))) {
												$show = true;
											}
											break;

					}
				}
			}

			if($show == true) {
				$popover_count = $getoption('popover_count', '3');
				if($this->has_reached_limit($popover_count)) {
					$show = false;
				}

				if($this->clear_forever()) {
					$show = false;
				}

			}

			if($show == true) {
				// Show the advert
				wp_enqueue_style('popovercss', popover_url('/popoverincludes/popover.css'), array(), $this->build);
				wp_enqueue_script('popoverjs', popover_url('/popoverincludes/popover.js'), array('jquery'), $this->build);

				if($getoption('popover_usejs', 'no') == 'yes') {
					wp_enqueue_script('popoveroverridejs', popover_url('/popoverincludes/popoversizing.js'), array('jquery'), $this->build);
				}

				add_action('wp_footer', array(&$this, 'page_footer'));
				wp_enqueue_script('jquery');

				// Add the cookie
				if ( isset($_COOKIE['popover_view_'.COOKIEHASH]) ) {
					$count = intval($_COOKIE['popover_view_'.COOKIEHASH]);
					if(!is_numeric($count)) $count = 0;
					$count++;
				} else {
					$count = 1;
				}
				if(!headers_sent()) setcookie('popover_view_'.COOKIEHASH, $count , time() + 30000000, COOKIEPATH, COOKIE_DOMAIN);
			}

		}

		function sanitise_array($arrayin) {

			foreach($arrayin as $key => $value) {
				$arrayin[$key] = htmlentities(stripslashes($value) ,ENT_QUOTES, 'UTF-8');
			}

			return $arrayin;
		}

		function page_footer() {

			if(function_exists('get_site_option') && defined('PO_GLOBAL')) {
				$getoption = 'get_site_option';
			} else {
				$getoption = 'get_option';
			}

			$content = stripslashes($getoption('popover_content', ''));

			$popover_size = $getoption('popover_size', array('width' => '500px', 'height' => '200px'));
			$popover_location = $getoption('popover_location', array('left' => '100px', 'top' => '100px'));
			$popover_colour = $getoption('popover_colour', array('back' => 'FFFFFF', 'fore' => '000000'));
			$popover_margin = $getoption('popover_margin', array('left' => '0px', 'top' => '0px', 'right' => '0px', 'bottom' => '0px'));

			$popover_size = $this->sanitise_array($popover_size);
			$popover_location = $this->sanitise_array($popover_location);
			$popover_colour = $this->sanitise_array($popover_colour);
			$popover_margin = $this->sanitise_array($popover_margin);

			if($getoption('popover_usejs', 'no') == 'yes') {
				$style = '';
				$box = 'color: #' . $popover_colour['fore'] . '; background: #' . $popover_colour['back'] . ';';

			} else {
				$style =  'left: ' . $popover_location['left'] . '; top: ' . $popover_location['top'] . ';';
				$style .= 'margin-top: ' . $popover_margin['top'] . '; margin-bottom: ' . $popover_margin['bottom'] . '; margin-right: ' . $popover_margin['right'] . '; margin-left: ' . $popover_margin['left'] . ';';

				$box = 'width: ' . $popover_size['width'] . '; height: ' . $popover_size['height'] . '; color: #' . $popover_colour['fore'] . '; background: #' . $popover_colour['back'] . ';';

			}


			?>
			<div id='messagebox' class='visiblebox' style='<?php echo $style; ?>'>
				<a href='' id='closebox' title='Close this box'></a>
				<div id='message' style='<?php echo $box; ?>'>
					<?php echo $content; ?>
					<div class='clear'></div>
					<div class='claimbutton hide'><a href='#' id='clearforever'><?php _e('Never see this message again.','popover'); ?></a></div>
				</div>
				<div class='clear'></div>
			</div>
			<?php
		}

		function is_fromsearchengine() {
			$ref = $_SERVER['HTTP_REFERER'];

			$SE = array('/search?', 'images.google.', 'web.info.com', 'search.', 'del.icio.us/search', 'soso.com', '/search/', '.yahoo.' );

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