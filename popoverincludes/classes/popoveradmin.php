<?php

if(!class_exists('popoveradmin')) {

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

}

?>