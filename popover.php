<?php
/*
Plugin Name: Popover plugin
Plugin URI: http://premium.wpmudev.org
Description: This plugin adds a customisable popover to a site. The content, size, position can be changed and rules determining if the popover should show or not.
Author: Barry (Incsub)
Version: 1.6
Author URI: http://caffeinatedb.com
WDP ID: 123

Copyright 2007-2010 Incsub (http://incsub.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

// a true setting for PO_GLOBAL means that this plugin operates on a global site-admin basis
// commenting out this line means that the plugin operates on a blog by blog basis
define('PO_GLOBAL',true);
// Comment the line below out if you don't want to use kses to validate your content.
define('PO_USEKSES',true);

class popoveradmin {

	var $build = 2;

	var $db;

	function __construct() {

		global $wpdb;

		$this->db =& $wpdb;

		add_action( 'admin_menu', array(&$this, 'add_menu_pages' ) );

	}

	function popoveradmin() {
		$this->__construct();
	}

	function add_menu_pages() {
		if(function_exists('is_site_admin') && defined('PO_GLOBAL')) {
			add_submenu_page('wpmu-admin.php', __('Pop Overs'), __('Pop Overs'), 10, 'popoverssadmin', array(&$this,'handle_admin_panel'));
		} else {
			add_submenu_page('options-general.php', __('Pop Overs'), __('Pop Overs'), 10, 'popoverssadmin', array(&$this,'handle_admin_panel'));
		}

	}

	function sanitise_array($arrayin) {

		foreach($arrayin as $key => $value) {
			$arrayin[$key] = htmlentities(stripslashes($value) ,ENT_QUOTES, 'UTF-8');
		}

		return $arrayin;
	}

	function handle_admin_panel() {

		global $allowedposttags;

		if(function_exists('get_site_option') && defined('PO_GLOBAL')) {
			$updateoption = 'update_site_option';
			$getoption = 'get_site_option';
		} else {
			$updateoption = 'update_option';
			$getoption = 'get_option';
		}

		if(isset($_POST['action']) && addslashes($_POST['action']) == 'updatepopover') {

			//print_r($_POST);

			if(isset($_POST['popovercontent'])) {
				if(defined('PO_USEKSES')) {
					$updateoption('popover_content', wp_kses($_POST['popovercontent'], $allowedposttags));
				} else {
					$updateoption('popover_content', $_POST['popovercontent']);
				}

			}

			if(isset($_POST['popoverwidth']) || isset($_POST['popoverheight'])) {

				$width = $_POST['popoverwidth'];
				$height = $_POST['popoverheight'];

				if($width == '') $width = '500px';
				if($height == '') $height = '200px';

				$updateoption('popover_size', array("width" => $width, "height" => $height));
			}

			if(isset($_POST['popoverleft']) || isset($_POST['popovertop'])) {

				$left = $_POST['popoverleft'];
				$top = $_POST['popovertop'];

				if($left == '') $left = '100px';
				if($top == '') $top = '100px';

				$updateoption('popover_location', array("left" => $left, "top" => $top));
			}

			if(isset($_POST['popovermargintop']) || isset($_POST['popovermarginleft']) || isset($_POST['popovermarginright']) || isset($_POST['popovermarginbottom'])) {

				$mleft = $_POST['popovermarginleft'];
				$mtop = $_POST['popovermargintop'];
				$mright = $_POST['popovermarginright'];
				$mbottom = $_POST['popovermarginbottom'];

				if($mleft == '') $mleft = '0px';
				if($mtop == '') $mtop = '0px';
				if($mright == '') $mright = '0px';
				if($mbottom == '') $mbottom = '0px';

				$updateoption('popover_margin', array('left' => $mleft, 'top' => $mtop, 'right' => $mright, 'bottom' => $mbottom));

			}

			if(isset($_POST['popoverbackground']) || isset($_POST['popoverforeground'])) {

				$back = $_POST['popoverbackground'];
				$fore = $_POST['popoverforeground'];

				if($back == '') $back = 'FFFFFF';
				if($fore == '') $fore = '000000';

				$updateoption('popover_colour', array("back" => $back, "fore" => $fore));
			}

			if(isset($_POST['popovercheck'])) {

				$updateoption('popover_check', $_POST['popovercheck']);

				if(isset($_POST['popoverereg'])) {
					$updateoption('popover_ereg', $_POST['popoverereg']);
				}

				if(isset($_POST['popovercount'])) {
					$updateoption('popover_count', intval($_POST['popovercount']) );
				}

			}

			if(isset($_POST['popoverusejs'])) {
				$updateoption('popover_usejs', 'yes' );
			} else {
				$updateoption('popover_usejs', 'no' );
			}

			echo '<div id="message" class="updated fade"><p>' . __('Your settings have been saved.', 'popover') . '</p></div>';

		}

		$popover_content = stripslashes($getoption('popover_content', ''));
		$popover_size = $getoption('popover_size', array('width' => '500px', 'height' => '200px'));
		$popover_location = $getoption('popover_location', array('left' => '100px', 'top' => '100px'));
		$popover_colour = $getoption('popover_colour', array('back' => 'FFFFFF', 'fore' => '000000'));
		$popover_margin = $getoption('popover_margin', array('left' => '0px', 'top' => '0px', 'right' => '0px', 'bottom' => '0px'));

		$popover_size = $this->sanitise_array($popover_size);
		$popover_location = $this->sanitise_array($popover_location);
		$popover_colour = $this->sanitise_array($popover_colour);
		$popover_margin = $this->sanitise_array($popover_margin);

		$popover_check = $getoption('popover_check', array());
		$popover_ereg = $getoption('popover_ereg', '');
		$popover_count = $getoption('popover_count', '3');

		$popover_usejs = $getoption('popover_usejs', 'no' );

		?>

		<div class='wrap'>

			<form action='' method='post'>
				<input type='hidden' name='action' value='updatepopover' />
				<?php wp_nonce_field('updatepopover'); ?>

			<h2><?php _e('Pop Over content settings','popover'); ?></h2>
			<p><?php _e('Use the settings below to modify the content of your pop over and the rules that will determine when, or if, it will be displayed.','popover'); ?></p>

			<h3><?php _e('Pop Over content','popover'); ?></h3>
			<p><?php _e('Enter the content for your pop over in the text area below. HTML is allowed.','popover'); ?></p>
			<textarea name='popovercontent' id='popovercontent' style='width: 90%' rows='10' cols='10'><?php echo stripslashes($popover_content); ?></textarea>

			<p class="submit">
			<input class="button" type="submit" name="go" value="<?php _e('Update content', 'popover'); ?>" />
			</p>

			<h3><?php _e('Pop Over display settings','popover'); ?></h3>
			<p><?php _e('Use the options below to determine the look, and display settings for the Pop Over.','popover'); ?></p>

			<table class='form-table'>

				<tr>
					<td valign='top' width='49%'>
						<h3><?php _e('Appearance Settings','popover'); ?></h3>

						<table class='form-table' style='border: 1px solid #ccc; padding-top: 10px; padding-bottom: 10px; margin-bottom: 10px;'>
							<tr>
								<th valign='top' scope='row' style='width: 25%;'><?php _e('Pop Over Size','popover'); ?></th>
								<td valign='top'>
									<?php _e('Width:','popover'); ?>&nbsp;
									<input type='text' name='popoverwidth' id='popoverwidth' style='width: 5em;' value='<?php echo $popover_size['width']; ?>' />&nbsp;
									<?php _e('Height:','popover'); ?>&nbsp;
									<input type='text' name='popoverheight' id='popoverheight' style='width: 5em;' value='<?php echo $popover_size['height']; ?>' />
								</td>
							</tr>

							<tr>
								<th valign='top' scope='row' style='width: 25%;'><?php _e('Pop Over Position','popover'); ?></th>
								<td valign='top'>
									<?php _e('Left:','popover'); ?>&nbsp;
									<input type='text' name='popoverleft' id='popoverleft' style='width: 5em;' value='<?php echo $popover_location['left']; ?>' />&nbsp;
									<?php _e('Top:','popover'); ?>&nbsp;
									<input type='text' name='popovertop' id='popovertop' style='width: 5em;' value='<?php echo $popover_location['top']; ?>' />
								</td>
							</tr>

							<tr>
								<th valign='top' scope='row' style='width: 25%;'><?php _e('Pop Over Margins','popover'); ?></th>
								<td valign='top'>
									<?php _e('Left:','popover'); ?>&nbsp;
									<input type='text' name='popovermarginleft' style='width: 5em;' value='<?php echo $popover_margin['left']; ?>' />&nbsp;
									<?php _e('Right:','popover'); ?>&nbsp;
									<input type='text' name='popovermarginright' style='width: 5em;' value='<?php echo $popover_margin['right']; ?>' /><br/>
									<?php _e('Top:','popover'); ?>&nbsp;
									<input type='text' name='popovermargintop' style='width: 5em;' value='<?php echo $popover_margin['top']; ?>' />&nbsp;
									<?php _e('Bottom:','popover'); ?>&nbsp;
									<input type='text' name='popovermarginbottom' style='width: 5em;' value='<?php echo $popover_margin['bottom']; ?>' />
								</td>
							</tr>

							<tr>
								<th valign='top' scope='row' style='width: 25%;'>&nbsp;</th>
								<td valign='top'>
									<?php _e('or just override the above with JS','popover'); ?>&nbsp;<input type='checkbox' name='popoverusejs' id='popoverusejs' value='yes' <?php if($popover_usejs == 'yes') echo "checked='checked'"; ?> />
								</td>
							</tr>

							</table>
							<table class='form-table'>



							<tr>
								<th valign='top' scope='row' style='width: 25%;'><?php _e('Background Colour','popover'); ?></th>
								<td valign='top'>
									<?php _e('Hex:','popover'); ?>&nbsp;#
									<input type='text' name='popoverbackground' id='popoverbackground' style='width: 10em;' value='<?php echo $popover_colour['back']; ?>' />
								</td>
							</tr>

							<tr>
								<th valign='top' scope='row' style='width: 25%;'><?php _e('Font Colour','popover'); ?></th>
								<td valign='top'>
									<?php _e('Hex:','popover'); ?>&nbsp;#
									<input type='text' name='popoverforeground' id='popoverforeground' style='width: 10em;' value='<?php echo $popover_colour['fore']; ?>' />
								</td>
							</tr>

						</table>

					</td>

					<td valign='top' width='49%'>
						<h3><?php _e('Display Rules','popover'); ?></h3>

							<p><?php _e('Show the Pop Over if <strong>one</strong> of the following checked rules is true.','popover'); ?></p>
							<input type='hidden' name='popovercheck[none]' value='none' />
							<table class='form-table'>
								<?php
									if(function_exists('is_supporter')) {
								?>
								<tr>
									<td valign='middle' style='width: 5%;'>
										<input type='checkbox' name='popovercheck[notsupporter]' <?php if(isset($popover_check['notsupporter'])) echo "checked='checked'"; ?> />
									</td>
									<th valign='bottom' scope='row'><?php _e('Visitor is not a supporter.','popover'); ?></th>
								</tr>
								<?php
									}
								?>
								<tr>
									<td valign='middle' style='width: 5%;'>
										<input type='checkbox' name='popovercheck[isloggedin]' <?php if(isset($popover_check['isloggedin'])) echo "checked='checked'"; ?> />
									</td>
									<th valign='bottom' scope='row'><?php _e('Visitor is logged in.','popover'); ?></th>
								</tr>
								<tr>
									<td valign='middle' style='width: 5%;'>
										<input type='checkbox' name='popovercheck[loggedin]' <?php if(isset($popover_check['loggedin'])) echo "checked='checked'"; ?> />
									</td>
									<th valign='bottom' scope='row'><?php _e('Visitor is not logged in.','popover'); ?></th>
								</tr>
								<tr>
									<td valign='middle' style='width: 5%;'>
										<input type='checkbox' name='popovercheck[commented]' <?php if(isset($popover_check['commented'])) echo "checked='checked'"; ?> />
									</td>
									<th valign='bottom' scope='row'><?php _e('Visitor has never commented here before.','popover'); ?></th>
								</tr>
								<tr>
									<td valign='middle' style='width: 5%;'>
										<input type='checkbox' name='popovercheck[searchengine]' <?php if(isset($popover_check['searchengine'])) echo "checked='checked'"; ?> />
									</td>
									<th valign='bottom' scope='row'><?php _e('Visitor came from a search engine.','popover'); ?></th>
								</tr>
								<tr>
									<td valign='middle' style='width: 5%;'>
										<input type='checkbox' name='popovercheck[internal]' <?php if(isset($popover_check['internal'])) echo "checked='checked'"; ?> />
									</td>
									<th valign='bottom' scope='row'><?php _e('Visitor did not come from an internal page.','popover'); ?></th>
								</tr>
								<tr>
									<td valign='middle' style='width: 5%;'>
										<input type='checkbox' name='popovercheck[referrer]' <?php if(isset($popover_check['referrer'])) echo "checked='checked'"; ?> />
									</td>
									<th valign='bottom' scope='row'><?php _e('Visitor referrer matches','popover'); ?>&nbsp;
									<input type='text' name='popoverereg' id='popoverereg' style='width: 10em;' value='<?php echo htmlentities($popover_ereg,ENT_QUOTES, 'UTF-8'); ?>' />
									</th>
								</tr>

								</table>

								<p><?php _e('And the visitor has seen the pop over less than','popover'); ?>&nbsp;
								<input type='text' name='popovercount' id='popovercount' style='width: 2em;' value='<?php echo htmlentities($popover_count,ENT_QUOTES, 'UTF-8'); ?>' />&nbsp;
								<?php _e('times','popover'); ?></p>

					</td>
				</tr>

			</table>

			<p class="submit">
			<input class="button" type="submit" name="goagain" value="<?php _e('Update settings', 'popover'); ?>" />
			</p>

			</form>

		</div>

		<?php
	}

}

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
			wp_enqueue_style('popovercss', $location . '/popoverincludes/popover.css', array(), $this->build);
			wp_enqueue_script('popoverjs', $location . '/popoverincludes/popover.js', array('jquery'), $this->build);

			if($getoption('popover_usejs', 'no') == 'yes') {
				wp_enqueue_script('popoveroverridejs', $location . '/popoverincludes/popoversizing.js', array('jquery'), $this->build);
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

if(is_admin()) {
	$popover =& new popoveradmin();
} else {
	$popover =& new popoverpublic();
}


?>