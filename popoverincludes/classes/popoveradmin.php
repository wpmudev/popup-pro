<?php

if(!class_exists('popoveradmin')) {

	class popoveradmin {

		var $build = 3;

		var $db;

		var $tables = array( 'popover' );
		var $popover;

		function __construct() {

			global $wpdb;

			$this->db =& $wpdb;

			foreach($this->tables as $table) {
				$this->$table = popover_db_prefix($this->db, $table);
			}

			add_action( 'admin_menu', array(&$this, 'add_menu_pages' ) );
			add_action( 'network_admin_menu', array(&$this, 'add_menu_pages' ) );

			add_action( 'plugins_loaded', array(&$this, 'load_textdomain'));

			// Add header files
			add_action('load-toplevel_page_popover', array(&$this, 'add_admin_header_popover_menu'));
			add_action('load-pop-overs_page_popoveraddons', array(&$this, 'add_admin_header_popover_addons'));

			// Ajax calls
			add_action( 'wp_ajax_popover_update_order', array(&$this, 'ajax_update_popover_order') );

			$installed = get_option('popover_installed', false);

			if($installed === false || $installed != $this->build) {
				$this->install();

				update_option('popover_installed', $this->build);
			}
		}

		function popoveradmin() {
			$this->__construct();
		}

		function install() {

			if($this->db->get_var( "SHOW TABLES LIKE '" . $this->popover . "' ") != $this->popover) {
				 $sql = "CREATE TABLE `" . $this->popover . "` (
				  	`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
					  `popover_title` varchar(250) DEFAULT NULL,
					  `popover_content` text,
					  `popover_settings` text,
					  `popover_order` bigint(20) DEFAULT '0',
					  `popover_active` int(11) DEFAULT '0',
					  PRIMARY KEY (`id`)
					)";

				$this->db->query($sql);

			}

		}

		function load_textdomain() {

			$locale = apply_filters( 'popover_locale', get_locale() );
			$mofile = popover_dir( "popoverincludes/languages/popover-$locale.mo" );

			if ( file_exists( $mofile ) )
				load_textdomain( 'popover', $mofile );

		}

		function add_menu_pages() {

			global $submenu;

			if(is_multisite() && (defined('PO_GLOBAL') && PO_GLOBAL == true)) {
				if(function_exists('is_network_admin') && is_network_admin()) {
					add_menu_page(__('Pop Overs','popover'), __('Pop Overs','popover'), 'manage_options',  'popover', array(&$this,'handle_popover_admin'), popover_url('popoverincludes/images/window.png'));
				}
			} else {
				if(!function_exists('is_network_admin') || !is_network_admin()) {
					add_menu_page(__('Pop Overs','popover'), __('Pop Overs','popover'), 'manage_options',  'popover', array(&$this,'handle_popover_admin'), popover_url('popoverincludes/images/window.png'));
				}
			}

			$addnew = add_submenu_page('popover', __('Create New Pop Over','popover'), __('Create New','popover'), 'manage_options', "popover&amp;action=add", array(&$this,'handle_addnewpopover_panel'));
			add_submenu_page('popover', __('Manage Add-ons Plugins','popover'), __('Add-ons','popover'), 'manage_options', "popoveraddons", array(&$this,'handle_addons_panel'));

		}

		function sanitise_array($arrayin) {

			foreach($arrayin as $key => $value) {
				$arrayin[$key] = htmlentities(stripslashes($value) ,ENT_QUOTES, 'UTF-8');
			}

			return $arrayin;
		}

		function ajax_update_popover_order() {

			if(check_ajax_referer( 'popover_order', '_ajax_nonce', false )) {
				$newnonce = wp_create_nonce('popover_order');

				$data = $_POST['data'];
				parse_str($data);
				foreach($dragbody as $key => $value) {
					$this->reorder_popovers( $value, $key );
				}
				die($newnonce);
			} else {
				die('fail');
			}

		}

		function update_admin_header_popover() {

			global $action, $page, $allowedposttags;

			wp_reset_vars( array('action', 'page') );

			if($action == 'updated') {
				check_admin_referer('update-popover');

				$usemsg = 1;

				if(function_exists('get_site_option') && defined('PO_GLOBAL') && PO_GLOBAL == true) {
					$updateoption = 'update_site_option';
					$getoption = 'get_site_option';
				} else {
					$updateoption = 'update_option';
					$getoption = 'get_option';
				}

				if(isset($_POST['popovercontent'])) {
					if ( !current_user_can('unfiltered_html') ) {
						if(wp_kses($_POST['popovercontent'], $allowedposttags) != $_POST['popovercontent']) {
							$usemsg = 2;
						}
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

				wp_safe_redirect( add_query_arg( array('msg' => $usemsg), wp_get_referer() ) );

			}

		}

		function add_admin_header_popover_menu() {

			if(in_array($_GET['action'], array('edit', 'add'))) {
				$this->add_admin_header_popover();
			} else {
				wp_enqueue_script('popoverdragadminjs', popover_url('popoverincludes/js/jquery.tablednd_0_5.js'), array('jquery'), $this->build);
				wp_enqueue_script('popoveradminjs', popover_url('popoverincludes/js/popovermenu.js'), array('jquery', 'popoverdragadminjs' ), $this->build);

				wp_localize_script('popoveradminjs', 'popover', array(	'ajaxurl'		=>	admin_url( 'admin-ajax.php' ),
				 														'ordernonce'	=>	wp_create_nonce('popover_order'),
																		'dragerror'		=>	__('An error occured updating the Pop Over order.','popover'),
																		'deletepopover'	=>	__('Are you sure you want to delete this Pop Over?','popover')
																	));

				wp_enqueue_style('popoveradmincss', popover_url('popoverincludes/css/popovermenu.css'), array(), $this->build);

				$this->update_popover_admin();
			}

		}

		function add_admin_header_popover() {

			global $wp_version;

			wp_enqueue_script('popoveradminjs', popover_url('popoverincludes/js/popoveradmin.js'), array( 'jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-droppable' ), $this->build);

			if(version_compare( preg_replace('/-.*$/', '', $wp_version), "3.3", '<')) {
				wp_enqueue_style('popoveradmincss', popover_url('popoverincludes/css/popoveradmin.css'), array('widgets'), $this->build);
			} else {
				wp_enqueue_style('popoveradmincss', popover_url('popoverincludes/css/popoveradmin.css'), array(), $this->build);
			}

			$this->update_admin_header_popover();
		}

		function add_admin_header_popover_addons() {
			$this->handle_addons_panel_updates();
		}

		function reorder_popovers( $popover_id, $order ) {

			$this->db->update( $this->popover, array( 'popover_order' => $order ), array( 'id' => $popover_id) );

		}

		function get_popovers() {

			$sql = $this->db->prepare( "SELECT * FROM {$this->popover} ORDER BY popover_order ASC" );

			return $this->db->get_results( $sql );

		}

		function get_popover( $id ) {
			return $this->db->get_row( $this->db->prepare("SELECT * FROM {$this->popover} WHERE id = %d", $id) );
		}

		function activate_popover( $id ) {
			return $this->db->update( $this->popover, array( 'popover_active' => 1 ), array( 'id' => $id) );
		}

		function deactivate_popover( $id ) {
			return $this->db->update( $this->popover, array( 'popover_active' => 0 ), array( 'id' => $id) );
		}

		function toggle_popover( $id ) {

			$sql = $this->db->prepare( "UPDATE {$this->popover} SET popover_active = NOT popover_active WHERE id = %d", $id );

			return $this->db->query( $sql );

		}

		function delete_popover( $id ) {

			return $this->db->query( $this->db->prepare( "DELETE FROM {$this->popover} WHERE id = %d", $id ) );

		}

		function add_popover( $data ) {

			global $action, $page, $allowedposttags;

			$popover = array();

			$popover['popover_title'] = $_POST['popover_title'];

			if ( !current_user_can('unfiltered_html') ) {
				$popover['popover_content'] = wp_kses($_POST['popover_content'], $allowedposttags);
			} else {
				$popover['popover_content'] = $_POST['popover_content'];
			}

			$popover['popover_settings'] = array();
			$popover['popover_settings']['popover_size'] = array( 'width' => $_POST['popoverwidth'], 'height' => $_POST['popoverheight'] );
			$popover['popover_settings']['popover_location'] = array( 'left' => $_POST['popoverleft'], 'top' => $_POST['popovertop'] );
			$popover['popover_settings']['popover_colour'] = array( 'back' => $_POST['popoverbackground'], 'fore' => $_POST['popoverforeground'] );
			$popover['popover_settings']['popover_margin'] = array( 'left' => $_POST['popovermarginleft'], 'top' => $_POST['popovermargintop'], 'right' => $_POST['popovermarginright'], 'bottom' => $_POST['popovermarginbottom'] );
			$popover['popover_settings']['popover_check'] = $_POST['popovercheck'];

			if(isset($_POST['popoverereg'])) {
				$popover['popover_settings']['popover_ereg'] = $_POST['popoverereg'];
			} else {
				$popover['popover_settings']['popover_ereg'] = '';
			}

			if(isset($_POST['popovercount'])) {
				$popover['popover_settings']['popover_count'] = $_POST['popovercount'];
			} else {
				$popover['popover_settings']['popover_count'] = 3;
			}

			if($_POST['popoverusejs'] == 'yes') {
				$popover['popover_settings']['popover_usejs'] = 'yes';
			} else {
				$popover['popover_settings']['popover_usejs'] = 'no';
			}

			$popover['popover_settings']['popover_style'] = $_POST['popoverstyle'];

			$popover['popover_settings'] = serialize($popover['popover_settings']);

			return $this->db->insert( $this->popover, $popover );

		}

		function update_popover( $id, $data ) {

			global $action, $page, $allowedposttags;

			$popover = array();

			$popover['popover_title'] = $_POST['popover_title'];

			if ( !current_user_can('unfiltered_html') ) {
				$popover['popover_content'] = wp_kses($_POST['popover_content'], $allowedposttags);
			} else {
				$popover['popover_content'] = $_POST['popover_content'];
			}

			$popover['popover_settings'] = array();
			$popover['popover_settings']['popover_size'] = array( 'width' => $_POST['popoverwidth'], 'height' => $_POST['popoverheight'] );
			$popover['popover_settings']['popover_location'] = array( 'left' => $_POST['popoverleft'], 'top' => $_POST['popovertop'] );
			$popover['popover_settings']['popover_colour'] = array( 'back' => $_POST['popoverbackground'], 'fore' => $_POST['popoverforeground'] );
			$popover['popover_settings']['popover_margin'] = array( 'left' => $_POST['popovermarginleft'], 'top' => $_POST['popovermargintop'], 'right' => $_POST['popovermarginright'], 'bottom' => $_POST['popovermarginbottom'] );
			$popover['popover_settings']['popover_check'] = $_POST['popovercheck'];

			if(isset($_POST['popoverereg'])) {
				$popover['popover_settings']['popover_ereg'] = $_POST['popoverereg'];
			} else {
				$popover['popover_settings']['popover_ereg'] = '';
			}

			if(isset($_POST['popovercount'])) {
				$popover['popover_settings']['popover_count'] = $_POST['popovercount'];
			} else {
				$popover['popover_settings']['popover_count'] = 3;
			}

			if($_POST['popoverusejs'] == 'yes') {
				$popover['popover_settings']['popover_usejs'] = 'yes';
			} else {
				$popover['popover_settings']['popover_usejs'] = 'no';
			}

			$popover['popover_settings']['popover_style'] = $_POST['popoverstyle'];

			$popover['popover_settings'] = serialize($popover['popover_settings']);

			return $this->db->update( $this->popover, $popover, array( 'id' => $id ) );

		}

		function update_popover_admin() {
			global $action, $page;

			wp_reset_vars( array('action', 'page') );

			if(isset($_REQUEST['action']) || isset($_REQUEST['action2'])) {

				if(!empty($_REQUEST['action2'])) {
					$_REQUEST['action'] = $_REQUEST['action2'];
				}

				switch($_REQUEST['action']) {


					case 'activate': 		$id = (int) $_GET['popover'];
											if(!empty($id)) {
												check_admin_referer('toggle-popover-' . $id);

												if( $this->activate_popover( $id ) ) {
													wp_safe_redirect( add_query_arg( 'msg', 3, wp_get_referer() ) );
												} else {
													wp_safe_redirect( add_query_arg( 'msg', 4, wp_get_referer() ) );
												}

											}
											break;


					case 'deactivate':		$id = (int) $_GET['popover'];
											if(!empty($id)) {
												check_admin_referer('toggle-popover-' . $id);

												if( $this->deactivate_popover( $id ) ) {
													wp_safe_redirect( add_query_arg( 'msg', 5, wp_get_referer() ) );
												} else {
													wp_safe_redirect( add_query_arg( 'msg', 6, wp_get_referer() ) );
												}

											}
											break;

					case 'toggle':			$ids = $_REQUEST['popovercheck'];

											if(!empty($ids)) {
												check_admin_referer('bulk-popovers');
												foreach( (array) $ids as $id ) {
													$this->toggle_popover( $id );
												}
												wp_safe_redirect( add_query_arg( 'msg', 7, wp_get_referer() ) );
											}
											break;

					case 'delete':			$id = (int) $_GET['popover'];

											if(!empty($id)) {
												check_admin_referer('delete-popover-' . $id);

												if( $this->delete_popover( $id ) ) {
													wp_safe_redirect( add_query_arg( 'msg', 8, wp_get_referer() ) );
												} else {
													wp_safe_redirect( add_query_arg( 'msg', 9, wp_get_referer() ) );
												}
											}
											break;

					case 'added':			$id = (int) $_POST['id'];
											if(empty($id)) {
												check_admin_referer('update-popover');
												if($this->add_popover( $_POST )) {
													wp_safe_redirect( add_query_arg( 'msg', 10, 'admin.php?page=popover' ) );
												} else {
													wp_safe_redirect( add_query_arg( 'msg', 11, 'admin.php?page=popover' ) );
												}
											}
											break;

					case 'updated':			$id = (int) $_POST['id'];
											if(!empty($id)) {
												check_admin_referer('update-popover');
												if($this->update_popover( $id, $_POST )) {
													wp_safe_redirect( add_query_arg( 'msg', 1, 'admin.php?page=popover' ) );
												} else {
													wp_safe_redirect( add_query_arg( 'msg', 2, 'admin.php?page=popover' ) );
												}
											}
											break;

				}


			}

		}

		function handle_popover_admin() {
			global $action, $page;

			if($action == 'edit') {
				if(isset($_GET['popover'])) {
					$id = (int) $_GET['popover'];
					$this->handle_popover_edit_panel( $id );
					return; // So we don't see the rest of this page
				}
			}

			if($action == 'add') {
				$this->handle_popover_edit_panel( false );
				return; // So we don't see the rest of this page
			}

			$messages = array();
			$messages[1] = __('Pop Over updated.','popover');
			$messages[2] = __('Pop Over not updated.','popover');

			$messages[3] = __('Pop Over activated.','popover');
			$messages[4] = __('Pop Over not activated.','popover');

			$messages[5] = __('Pop Over deactivated.','popover');
			$messages[6] = __('Pop Over not deactivated.','popover');

			$messages[7] = __('Pop Over activation toggled.','popover');

			$messages[8] = __('Pop Over deleted.','popover');
			$messages[9] = __('Pop Over not deleted.','popover');

			$messages[10] = __('Pop Over added.','popover');
			$messages[11] = __('Pop Over not added.','popover');
			?>
			<div class='wrap'>
				<div class="icon32" id="icon-themes"><br></div>
				<h2><?php _e('Edit Pop Overs','popover'); ?><a class="add-new-h2" href="admin.php?page=<?php echo $page; ?>&action=add"><?php _e('Add New','membership'); ?></a></h2>

				<?php
				if ( isset($_GET['msg']) ) {
					echo '<div id="message" class="updated fade"><p>' . $messages[(int) $_GET['msg']] . '</p></div>';
					$_SERVER['REQUEST_URI'] = remove_query_arg(array('message'), $_SERVER['REQUEST_URI']);
				}

				?>

				<form method="get" action="?page=<?php echo esc_attr($page); ?>" id="posts-filter">

				<input type='hidden' name='page' value='<?php echo esc_attr($page); ?>' />

				<div class="tablenav">

				<div class="alignleft actions">
				<select name="action">
				<option selected="selected" value=""><?php _e('Bulk Actions', 'popover'); ?></option>
				<option value="toggle"><?php _e('Toggle activation', 'popover'); ?></option>
				</select>
				<input type="submit" class="button-secondary action" id="doaction" name="doaction" value="<?php _e('Apply', 'popover'); ?>">

				</div>

				<div class="alignright actions"></div>

				<br class="clear">
				</div>

				<div class="clear"></div>

				<?php
					wp_original_referer_field(true, 'previous'); wp_nonce_field('bulk-popovers');

					$columns = array(	"name"		=>	__('Pop Over Name', 'popover'),
										"rules" 	=> 	__('Rules','popover'),
										"active"	=>	__('Active','popover')
									);

					$columns = apply_filters('popover_columns', $columns);

					$popovers = $this->get_popovers();

				?>

				<table cellspacing="0" class="widefat fixed" id="dragtable">
					<thead>
					<tr>

					<th style="width: 20px;" class="manage-column column-drag" id="cb" scope="col"></th>
					<th style="" class="manage-column column-cb check-column" id="cb" scope="col"><input type="checkbox"></th>

					<?php
						foreach($columns as $key => $col) {
							?>
							<th style="" class="manage-column column-<?php echo $key; ?>" id="<?php echo $key; ?>" scope="col"><?php echo $col; ?></th>
							<?php
						}
					?>
					</tr>
					</thead>

					<tfoot>
					<tr>

					<th style="" class="manage-column column-drag" scope="col"></th>
					<th style="" class="manage-column column-cb check-column" scope="col"><input type="checkbox"></th>

					<?php
						reset($columns);
						foreach($columns as $key => $col) {
							?>
							<th style="" class="manage-column column-<?php echo $key; ?>" id="<?php echo $key; ?>" scope="col"><?php echo $col; ?></th>
							<?php
						}
					?>
					</tr>
					</tfoot>

					<tbody id='dragbody'>
						<?php
						if($popovers) {
							$popovercount = 0;
							foreach($popovers as $key => $popover) {
								?>
								<tr valign="middle" class="alternate draghandle" id="<?php echo $popover->id; ?>">

									<td class="check-drag" scope="row">
										&nbsp;
									</td>
									<td class="check-column" scope="row"><input type="checkbox" value="<?php echo $popover->id; ?>" name="popovercheck[]"></td>

									<td class="column-name">
										<strong><a href='<?php echo "?page=" . $page . "&amp;action=edit&amp;popover=" . $popover->id . ""; ?>'><?php echo esc_html($popover->popover_title); ?></a></strong>
										<?php
											$actions = array();

											$actions['edit'] = "<span class='edit'><a href='?page=" . $page . "&amp;action=edit&amp;popover=" . $popover->id . "'>" . __('Edit', 'popover') . "</a></span>";

											if($popover->popover_active) {
												$actions['toggle'] = "<span class='edit activate'><a href='" . wp_nonce_url("?page=" . $page. "&amp;action=deactivate&amp;popover=" . $popover->id . "", 'toggle-popover-' . $popover->id) . "'>" . __('Deactivate', 'popover') . "</a></span>";
											} else {
												$actions['toggle'] = "<span class='edit deactivate'><a href='" . wp_nonce_url("?page=" . $page. "&amp;action=activate&amp;popover=" . $popover->id . "", 'toggle-popover-' . $popover->id) . "'>" . __('Activate', 'popover') . "</a></span>";
											}

											$actions['delete'] = "<span class='delete'><a href='" . wp_nonce_url("?page=" . $page. "&amp;action=delete&amp;popover=" . $popover->id . "", 'delete-popover-' . $popover->id) . "'>" . __('Delete', 'popover') . "</a></span>";
										?>
										<br><div class="row-actions"><?php echo implode(" | ", $actions); ?></div>
										</td>

									<td class="column-name">
										<?php
											$p = maybe_unserialize($popover->popover_settings);
											$rules = $p['popover_check'];
											foreach( (array) $rules as $key => $value ) {
												if($key == 'order') {
													continue;
												}
												switch($key) {

													case 'supporter':		_e('Blog is not a supporter', 'popover');
																			break;

													case 'isloggedin':		_e('Visitor is logged in', 'popover');
																			break;

													case 'loggedin':		_e('Visitor is not logged in', 'popover');
																			break;

													case 'commented':		_e('Visitor has never commented', 'popover');
																			break;

													case 'searchengine':	_e('Visit via a search engine', 'popover');
																			break;

													case 'internal':		_e('Visit not via an Internal link', 'popover');
																			break;

													case 'referrer':		_e('Visit via specific referer', 'popover');
																			break;

													case 'count':			_e('Popover shown less than', 'popover');
																			break;

													default:				echo apply_filters('popover_nice_rule_name', $key);
																			break;
											}
											echo "<br/>";
										}
										?>
										</td>
									<td class="column-active">
										<?php
											if($popover->popover_active) {
												echo "<strong>" . __('Active', 'popover') . "</strong>";
											} else {
												echo __('Inactive', 'popover');
											}
										?>
									</td>
							    </tr>
								<?php
							}
						} else {
							$columncount = count($columns) + 2;
							?>
							<tr valign="middle" class="alternate" >
								<td colspan="<?php echo $columncount; ?>" scope="row"><?php _e('No Pop Overs were found.','popover'); ?></td>
						    </tr>
							<?php
						}
						?>

					</tbody>
				</table>


				<div class="tablenav">

				<div class="alignleft actions">
				<select name="action2">
					<option selected="selected" value=""><?php _e('Bulk Actions', 'popover'); ?></option>
					<option value="toggle"><?php _e('Toggle activation', 'popover'); ?></option>
				</select>
				<input type="submit" class="button-secondary action" id="doaction2" name="doaction2" value="<?php _e('Apply', 'popover'); ?>">
				</div>
				<div class="alignright actions"></div>
				<br class="clear">
				</div>

				</form>

			</div> <!-- wrap -->
			<?php
		}


		function handle_popover_edit_panel( $id = false ) {

			global $page;

			if(function_exists('get_site_option') && defined('PO_GLOBAL') && PO_GLOBAL == true) {
				$updateoption = 'update_site_option';
				$getoption = 'get_site_option';
			} else {
				$updateoption = 'update_option';
				$getoption = 'get_option';
			}


			if($id !== false) {
				$popover = $this->get_popover( $id );

				$popover->popover_settings = unserialize($popover->popover_settings);
			} else {
				$popover = new stdClass;
				$popover->popover_title = __('New Pop Over','popover');
				$popover->popover_content = "";
			}

			$popover_title = stripslashes($popover->popover_title);

			$popover_content = stripslashes($popover->popover_content);

			if(empty($popover->popover_settings)) {
				$popover->popover_settings = array(	'popover_size'		=>	array('width' => '500px', 'height' => '200px'),
													'popover_location'	=>	array('left' => '100px', 'top' => '100px'),
													'popover_colour'	=>	array('back' => 'FFFFFF', 'fore' => '000000'),
													'popover_margin'	=>	array('left' => '0px', 'top' => '0px', 'right' => '0px', 'bottom' => '0px'),
													'popover_check'		=>	array(),
													'popover_ereg'		=>	'',
													'popover_count'		=>	3,
													'popover_usejs'		=>	'no'
													);
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

			$popoverstyle = $popover->popover_settings['popover_style'];

			?>
			<div class='wrap nosubsub'>
				<div class="icon32" id="icon-themes"><br></div>
				<?php if($id !== false) { ?>
					<h2><?php echo __('Edit Pop Over','popover'); ?></h2>
				<?php } else { ?>
					<h2><?php echo __('Add Pop Over','popover'); ?></h2>
				<?php } ?>
				<div class='popover-liquid-left'>

					<div id='popover-left'>
						<form action='?page=<?php echo $page; ?>' name='popoveredit' method='post'>

							<input type='hidden' name='id' id='id' value='<?php echo $id; ?>' />
							<input type='hidden' name='beingdragged' id='beingdragged' value='' />
							<input type='hidden' name='popovercheck[order]' id='in-positive-rules' value='<? echo esc_attr($popover_check['order']); ?>' />

						<div id='edit-popover' class='popover-holder-wrap'>
							<div class='sidebar-name no-movecursor'>
								<h3><?php echo __('Pop Over Settings','popover'); ?></h3>
							</div>
							<div class='popover-holder'>

								<div class='popover-details'>

								<label for='popover_title'><?php _e('Popover title','popover'); ?></label><br/>
								<input name='popover_title' id='popover_title' style='width: 97%; border: 1px solid; border-color: #DFDFDF;' value='<?php echo stripslashes($popover_title); ?>' /><br/><br/>

								<label for='popovercontent'><?php _e('Popover content','popover'); ?></label><br/>
								<textarea name='popover_content' id='popover_content' style='width: 98%' rows='5' cols='10'><?php echo stripslashes($popover_content); ?></textarea>

								</div>

								<h3><?php _e('Active rules','popover'); ?></h3>
								<p class='description'><?php _e('These are the rules that will determine if a popover should show when a visitor arrives at your website ALL rules must be true for the popover to show.','popover'); ?></p>
								<div id='positive-rules-holder'>
									<?php

										$order = explode(',', $popover_check['order']);

										foreach($order as $key) {

											switch($key) {

												case 'supporter':		//if( function_exists('is_supporter') ) $this->admin_main('supporter','Blog is not a supporter', 'Shows the popover if the blog is not a supporter.', true);
																		break;

												case 'isloggedin':		$this->admin_main('isloggedin','Visitor is logged in', 'Shows the popover if the user is logged in to your site.', true);
																		break;
												case 'loggedin':		$this->admin_main('loggedin','Visitor is not logged in', 'Shows the popover if the user is <strong>not</strong> logged in to your site.', true);
																		break;
												case 'commented':		$this->admin_main('commented','Visitor has never commented', 'Shows the popover if the user has never left a comment.', true);
																		break;
												case 'searchengine':	$this->admin_main('searchengine','Visit via a search engine', 'Shows the popover if the user arrived via a search engine.', true);
																		break;
												case 'internal':		$this->admin_main('internal','Visit not via an Internal link', 'Shows the popover if the user did not arrive on this page via another page on your site.', true);
																		break;
												case 'referrer':		$this->admin_referer('referrer','Visit via specific referer', 'Shows the popover if the user arrived via the following referrer:', $popover_ereg);
																		break;
												case 'count':			$this->admin_viewcount('count','Popover shown less than', 'Shows the popover if the user has only seen it less than the following number of times:', $popover_count);
																		break;

												default:				do_action('popover_active_rule_' . $key);
																		do_action('popover_active_rule', $key);
																		break;

											}

										}


									?>
								</div>
								<div id='positive-rules' class='droppable-rules popovers-sortable'>
									<?php _e('Drop here','membership'); ?>
								</div>

								<h3><?php _e('Appearance settings','popover'); ?></h3>
								<table class='form-table' style=''>
									<tr>
										<th valign='top' scope='row' style='width: 25%;'><strong><?php _e('Pop Over Size','popover'); ?></strong></th>
										<td valign='top'>
											<?php _e('Width:','popover'); ?>&nbsp;
											<input type='text' name='popoverwidth' id='popoverwidth' style='width: 5em;' value='<?php echo $popover_size['width']; ?>' />&nbsp;
											<?php _e('Height:','popover'); ?>&nbsp;
											<input type='text' name='popoverheight' id='popoverheight' style='width: 5em;' value='<?php echo $popover_size['height']; ?>' />
										</td>
									</tr>

									<tr>
										<th valign='top' scope='row' style='width: 25%;'><strong><?php _e('Pop Over Position','popover'); ?></strong></th>
										<td valign='top'>
											<?php _e('Left:','popover'); ?>&nbsp;
											<input type='text' name='popoverleft' id='popoverleft' style='width: 5em;' value='<?php echo $popover_location['left']; ?>' />&nbsp;
											<?php _e('Top:','popover'); ?>&nbsp;
											<input type='text' name='popovertop' id='popovertop' style='width: 5em;' value='<?php echo $popover_location['top']; ?>' />
										</td>
									</tr>

									<tr>
										<th valign='top' scope='row' style='width: 25%;'><strong><?php _e('Pop Over Margins','popover'); ?></strong></th>
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
											<?php _e('or use Javascript to center the popover','popover'); ?>&nbsp;<input type='checkbox' name='popoverusejs' id='popoverusejs' value='yes' <?php if($popover_usejs == 'yes') echo "checked='checked'"; ?> />
										</td>
									</tr>

									</table>
									<table class='form-table'>

									<tr>
										<th valign='top' scope='row' style='width: 25%;'><strong><?php _e('Background Colour','popover'); ?></strong></th>
										<td valign='top'>
											<?php _e('Hex:','popover'); ?>&nbsp;#
											<input type='text' name='popoverbackground' id='popoverbackground' style='width: 10em;' value='<?php echo $popover_colour['back']; ?>' />
										</td>
									</tr>

									<tr>
										<th valign='top' scope='row' style='width: 25%;'><strong><?php _e('Font Colour','popover'); ?></strong></th>
										<td valign='top'>
											<?php _e('Hex:','popover'); ?>&nbsp;#
											<input type='text' name='popoverforeground' id='popoverforeground' style='width: 10em;' value='<?php echo $popover_colour['fore']; ?>' />
										</td>
									</tr>

								</table>

								<h3><?php _e('Pop Over Style','popover'); ?></h3>
								<table class='form-table'>

								<tr>
									<th valign='top' scope='row' style='width: 25%;'><strong><?php _e('Use Style','popover'); ?></strong></th>
									<td valign='top'>
										<?php
										$availablestyles = apply_filters( 'popover_available_styles_directory', array( 'Default' => popover_dir('popoverincludes/css/default')) );
										?>
										<select name='popoverstyle'>
										<?php
										foreach( (array) $availablestyles as $key => $location ) {
												?>
												<option value='<?php echo $key; ?>' <?php selected($key, $popoverstyle); ?>><?php echo $key; ?></option>
												<?php
										}
										?>
										</select>

									</td>
								</tr>

							</table>

								<div class='buttons'>
										<?php
										wp_original_referer_field(true, 'previous'); wp_nonce_field('update-popover');
										?>
										<?php if($id !== false) { ?>
											<input type='submit' value='<?php _e('Update', 'popover'); ?>' class='button-primary' />
											<input type='hidden' name='action' value='updated' />
										<?php } else { ?>
											<input type='submit' value='<?php _e('Add', 'popover'); ?>' class='button-primary' />
											<input type='hidden' name='action' value='added' />
										<?php } ?>

								</div>

							</div>
						</div>
						</form>
					</div>


					<div id='hiden-actions'>
					<?php
						/*
						if(!isset($popover_check['supporter']) && function_exists('is_supporter')) {
							$this->admin_main('supporter','Blog is not a supporter', 'Shows the popover if the blog is not a supporter.', true);
						}
						*/

						if(!isset($popover_check['isloggedin'])) {
							$this->admin_main('isloggedin','Visitor is logged in', 'Shows the popover if the user is logged in to your site.', true);
						}

						if(!isset($popover_check['loggedin'])) {
							$this->admin_main('loggedin','Visitor is not logged in', 'Shows the popover if the user is <strong>not</strong> logged in to your site.', true);
						}

						if(!isset($popover_check['commented'])) {
							$this->admin_main('commented','Visitor has never commented', 'Shows the popover if the user has never left a comment.', true);
						}

						if(!isset($popover_check['searchengine'])) {
							$this->admin_main('searchengine','Visit via a search engine', 'Shows the popover if the user arrived via a search engine.', true);
						}

						if(!isset($popover_check['internal'])) {
							$this->admin_main('internal','Visit not via an Internal link', 'Shows the popover if the user did not arrive on this page via another page on your site.', true);
						}

						if(!isset($popover_check['referrer'])) {
							$this->admin_referer('referrer','Visit via specific referer', 'Shows the popover if the user arrived via the following referrer:', $popover_ereg);
						}

						//$popover_count
						if(!isset($popover_check['count'])) {
							$this->admin_viewcount('count','Popover shown less than', 'Shows the popover if the user has only seen it less than the following number of times:', $popover_count);
						}

						do_action('popover_additional_rules_main');

					?>
					</div> <!-- hidden-actions -->

				</div> <!-- popover-liquid-left -->

				<div class='popover-liquid-right'>
					<div class="popover-holder-wrap">

						<div class="sidebar-name no-movecursor">
							<h3><?php _e('Rules', 'popover'); ?></h3>
						</div>
						<div class="section-holder" id="sidebar-rules" style="min-height: 98px;">
							<ul class='popovers popovers-draggable'>
								<?php
									/*
									if(isset($popover_check['supporter']) && function_exists('is_supporter')) {
										$this->admin_sidebar('supporter','Blog is not a supporter', 'Shows the popover if the blog is not a supporter.', true);
									} elseif(function_exists('is_supporter')) {
										$this->admin_sidebar('supporter','Blog is not a supporter', 'Shows the popover if the blog is not a supporter.', false);
									}
									*/

									if(isset($popover_check['isloggedin'])) {
										$this->admin_sidebar('isloggedin','Visitor is logged in', 'Shows the popover if the user is logged in to your site.', true);
									} else {
										$this->admin_sidebar('isloggedin','Visitor is logged in', 'Shows the popover if the user is logged in to your site.', false);
									}

									if(isset($popover_check['loggedin'])) {
										$this->admin_sidebar('loggedin','Visitor is not logged in', 'Shows the popover if the user is <strong>not</strong> logged in to your site.', true);
									} else {
										$this->admin_sidebar('loggedin','Visitor is not logged in', 'Shows the popover if the user is <strong>not</strong> logged in to your site.', false);
									}

									if(isset($popover_check['commented'])) {
										$this->admin_sidebar('commented','Visitor has never commented', 'Shows the popover if the user has never left a comment.', true);
									} else {
										$this->admin_sidebar('commented','Visitor has never commented', 'Shows the popover if the user has never left a comment.', false);
									}

									if(isset($popover_check['searchengine'])) {
										$this->admin_sidebar('searchengine','Visit via a search engine', 'Shows the popover if the user arrived via a search engine.', true);
									} else {
										$this->admin_sidebar('searchengine','Visit via a search engine', 'Shows the popover if the user arrived via a search engine.', false);
									}

									if(isset($popover_check['internal'])) {
										$this->admin_sidebar('internal','Visit not via an Internal link', 'Shows the popover if the user did not arrive on this page via another page on your site.', true);
									} else {
										$this->admin_sidebar('internal','Visit not via an Internal link', 'Shows the popover if the user did not arrive on this page via another page on your site.', false);
									}

									if(isset($popover_check['referrer'])) {
										$this->admin_sidebar('referrer','Visit via specific referer', 'Shows the popover if the user arrived via a specific referrer.', true);
									} else {
										$this->admin_sidebar('referrer','Visit via specific referer', 'Shows the popover if the user arrived via a specific referrer.', false);
									}

									//$popover_count
									if(isset($popover_check['count'])) {
										$this->admin_sidebar('count','Popover shown less than', 'Shows the popover if the user has only seen it less than a specific number of times.', true);
									} else {
										$this->admin_sidebar('count','Popover shown less than', 'Shows the popover if the user has only seen it less than a specific number of times.', false);
									}

									do_action('popover_additional_rules_sidebar');
								?>
							</ul>
						</div>

					</div> <!-- popover-holder-wrap -->

				</div> <!-- popover-liquid-left -->

			</div> <!-- wrap -->

			<?php
		}

		function admin_sidebar($id, $title, $message, $data = false) {
			?>
			<li class='popover-draggable' id='<?php echo $id; ?>' <?php if($data === true) echo "style='display:none;'"; ?>>

				<div class='action action-draggable'>
					<div class='action-top closed'>
					<a href="#available-actions" class="action-button hide-if-no-js"></a>
					<?php _e($title,'popover'); ?>
					</div>
					<div class='action-body closed'>
						<?php if(!empty($message)) { ?>
							<p>
								<?php _e($message, 'popover'); ?>
							</p>
						<?php } ?>
						<p>
							<a href='#addtopopover' class='action-to-popover' title='<?php _e('Add this rule to the popover.','popover'); ?>'><?php _e('Add this rule to the popover.','popover'); ?></a>
						</p>
					</div>
				</div>

			</li>
			<?php
		}

		function admin_main($id, $title, $message, $data = false) {
			if(!$data) $data = array();
			?>
			<div class='popover-operation' id='main-<?php echo $id; ?>'>
				<h2 class='sidebar-name'><?php _e($title, 'popover');?><span><a href='#remove' class='removelink' id='remove-<?php echo $id; ?>' title='<?php _e("Remove $title tag from this rules area.",'popover'); ?>'><?php _e('Remove','popover'); ?></a></span></h2>
				<div class='inner-operation'>
					<p><? _e($message, 'popover'); ?></p>
					<input type='hidden' name='popovercheck[<?php echo $id; ?>]' value='yes' />
				</div>
			</div>
			<?php
		}

		function admin_referer($id, $title, $message, $data = false) {
			if(!$data) $data = array();
			?>
			<div class='popover-operation' id='main-<?php echo $id; ?>'>
				<h2 class='sidebar-name'><?php _e($title, 'popover');?><span><a href='#remove' class='removelink' id='remove-<?php echo $id; ?>' title='<?php _e("Remove $title tag from this rules area.",'popover'); ?>'><?php _e('Remove','popover'); ?></a></span></h2>
				<div class='inner-operation'>
					<p><? _e($message, 'popover'); ?></p>
					<input type='text' name='popoverereg' id='popoverereg' style='width: 10em;' value='<?php echo esc_html($data); ?>' />
					<input type='hidden' name='popovercheck[<?php echo $id; ?>]' value='yes' />
				</div>
			</div>
			<?php
		}

		function admin_viewcount($id, $title, $message, $data = false) {
			if(!$data) $data = array();
			?>
			<div class='popover-operation' id='main-<?php echo $id; ?>'>
				<h2 class='sidebar-name'><?php _e($title, 'popover');?><span><a href='#remove' class='removelink' id='remove-<?php echo $id; ?>' title='<?php _e("Remove $title tag from this rules area.",'popover'); ?>'><?php _e('Remove','popover'); ?></a></span></h2>
				<div class='inner-operation'>
					<p><? _e($message, 'popover'); ?></p>
					<input type='text' name='popovercount' id='popovercount' style='width: 2em;' value='<?php echo esc_html($data); ?>' />&nbsp;
					<?php _e('times','popover'); ?>
					<input type='hidden' name='popovercheck[<?php echo $id; ?>]' value='yes' />
				</div>
			</div>
			<?php
		}

		function handle_admin_panelold() {

			global $allowedposttags;

			if(is_multisite() && defined('PO_GLOBAL')) {
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

		function handle_addons_panel_updates() {
			global $action, $page;

			wp_reset_vars( array('action', 'page') );

			if(isset($_GET['doaction']) || isset($_GET['doaction2'])) {
				if(addslashes($_GET['action']) == 'toggle' || addslashes($_GET['action2']) == 'toggle') {
					$action = 'bulk-toggle';
				}
			}

			$active = get_option('popover_activated_addons', array());

			switch(addslashes($action)) {

				case 'deactivate':	$key = addslashes($_GET['addon']);
									if(!empty($key)) {
										check_admin_referer('toggle-addon-' . $key);

										$found = array_search($key, $active);
										if($found !== false) {
											unset($active[$found]);
											update_option('popover_activated_addons', array_unique($active));
											wp_safe_redirect( add_query_arg( 'msg', 5, wp_get_referer() ) );
										} else {
											wp_safe_redirect( add_query_arg( 'msg', 6, wp_get_referer() ) );
										}
									}
									break;

				case 'activate':	$key = addslashes($_GET['addon']);
									if(!empty($key)) {
										check_admin_referer('toggle-addon-' . $key);

										if(!in_array($key, $active)) {
											$active[] = $key;
											update_option('popover_activated_addons', array_unique($active));
											wp_safe_redirect( add_query_arg( 'msg', 3, wp_get_referer() ) );
										} else {
											wp_safe_redirect( add_query_arg( 'msg', 4, wp_get_referer() ) );
										}
									}
									break;

				case 'bulk-toggle':
									check_admin_referer('bulk-addons');
									foreach($_GET['addoncheck'] AS $key) {
										$found = array_search($key, $active);
										if($found !== false) {
											unset($active[$found]);
										} else {
											$active[] = $key;
										}
									}
									update_option('popover_activated_addons', array_unique($active));
									wp_safe_redirect( add_query_arg( 'msg', 7, wp_get_referer() ) );
									break;

			}
		}

		function handle_addons_panel() {
			global $action, $page;

			wp_reset_vars( array('action', 'page') );

			$messages = array();
			$messages[1] = __('Add-on updated.','popover');
			$messages[2] = __('Add-on not updated.','popover');

			$messages[3] = __('Add-on activated.','popover');
			$messages[4] = __('Add-on not activated.','popover');

			$messages[5] = __('Add-on deactivated.','popover');
			$messages[6] = __('Add-on not deactivated.','popover');

			$messages[7] = __('Add-on activation toggled.','popover');

			?>
			<div class='wrap'>
				<div class="icon32" id="icon-plugins"><br></div>
				<h2><?php _e('Edit Add-ons','popover'); ?></h2>

				<?php
				if ( isset($_GET['msg']) ) {
					echo '<div id="message" class="updated fade"><p>' . $messages[(int) $_GET['msg']] . '</p></div>';
					$_SERVER['REQUEST_URI'] = remove_query_arg(array('message'), $_SERVER['REQUEST_URI']);
				}

				?>

				<form method="get" action="?page=<?php echo esc_attr($page); ?>" id="posts-filter">

				<input type='hidden' name='page' value='<?php echo esc_attr($page); ?>' />

				<div class="tablenav">

				<div class="alignleft actions">
				<select name="action">
				<option selected="selected" value=""><?php _e('Bulk Actions', 'popover'); ?></option>
				<option value="toggle"><?php _e('Toggle activation', 'popover'); ?></option>
				</select>
				<input type="submit" class="button-secondary action" id="doaction" name="doaction" value="<?php _e('Apply', 'popover'); ?>">

				</div>

				<div class="alignright actions"></div>

				<br class="clear">
				</div>

				<div class="clear"></div>

				<?php
					wp_original_referer_field(true, 'previous'); wp_nonce_field('bulk-addons');

					$columns = array(	"name"		=>	__('Add-on Name', 'popover'),
										"file" 		=> 	__('Add-on File','popover'),
										"active"	=>	__('Active','popover')
									);

					$columns = apply_filters('popover_addoncolumns', $columns);

					$addons = get_popover_addons();

					$active = get_option('popover_activated_addons', array());

				?>

				<table cellspacing="0" class="widefat fixed">
					<thead>
					<tr>
					<th style="" class="manage-column column-cb check-column" id="cb" scope="col"><input type="checkbox"></th>
					<?php
						foreach($columns as $key => $col) {
							?>
							<th style="" class="manage-column column-<?php echo $key; ?>" id="<?php echo $key; ?>" scope="col"><?php echo $col; ?></th>
							<?php
						}
					?>
					</tr>
					</thead>

					<tfoot>
					<tr>
					<th style="" class="manage-column column-cb check-column" scope="col"><input type="checkbox"></th>
					<?php
						reset($columns);
						foreach($columns as $key => $col) {
							?>
							<th style="" class="manage-column column-<?php echo $key; ?>" id="<?php echo $key; ?>" scope="col"><?php echo $col; ?></th>
							<?php
						}
					?>
					</tr>
					</tfoot>

					<tbody>
						<?php
						if($addons) {
							foreach($addons as $key => $addon) {
								$default_headers = array(
									                'Name' => 'Addon Name',
													'Author' => 'Author',
													'Description'	=>	'Description',
													'AuthorURI' => 'Author URI'
									        );

								$addon_data = get_file_data( popover_dir('popoverincludes/addons/' . $addon), $default_headers, 'plugin' );

								if(empty($addon_data['Name'])) {
									continue;
								}

								?>
								<tr valign="middle" class="alternate" id="addon-<?php echo $addon; ?>">
									<th class="check-column" scope="row"><input type="checkbox" value="<?php echo esc_attr($addon); ?>" name="addoncheck[]"></th>
									<td class="column-name">
										<strong><?php echo esc_html($addon_data['Name']) . "</strong>" . __(' by ', 'popover') . "<a href='" . esc_attr($addon_data['AuthorURI']) . "'>" . esc_html($addon_data['Author']) . "</a>"; ?>
										<?php if(!empty($addon_data['Description'])) {
											?><br/><?php echo esc_html($addon_data['Description']);
											}

											$actions = array();

											if(in_array($addon, $active)) {
												$actions['toggle'] = "<span class='edit activate'><a href='" . wp_nonce_url("?page=" . $page. "&amp;action=deactivate&amp;addon=" . $addon . "", 'toggle-addon-' . $addon) . "'>" . __('Deactivate', 'popover') . "</a></span>";
											} else {
												$actions['toggle'] = "<span class='edit deactivate'><a href='" . wp_nonce_url("?page=" . $page. "&amp;action=activate&amp;addon=" . $addon . "", 'toggle-addon-' . $addon) . "'>" . __('Activate', 'popover') . "</a></span>";
											}
										?>
										<br><div class="row-actions"><?php echo implode(" | ", $actions); ?></div>
										</td>

									<td class="column-name">
										<?php echo esc_html($addon); ?>
										</td>
									<td class="column-active">
										<?php
											if(in_array($addon, $active)) {
												echo "<strong>" . __('Active', 'popover') . "</strong>";
											} else {
												echo __('Inactive', 'popover');
											}
										?>
									</td>
							    </tr>
								<?php
							}
						} else {
							$columncount = count($columns) + 1;
							?>
							<tr valign="middle" class="alternate" >
								<td colspan="<?php echo $columncount; ?>" scope="row"><?php _e('No Add-ons where found for this install.','popover'); ?></td>
						    </tr>
							<?php
						}
						?>

					</tbody>
				</table>


				<div class="tablenav">

				<div class="alignleft actions">
				<select name="action2">
					<option selected="selected" value=""><?php _e('Bulk Actions', 'popover'); ?></option>
					<option value="toggle"><?php _e('Toggle activation', 'popover'); ?></option>
				</select>
				<input type="submit" class="button-secondary action" id="doaction2" name="doaction2" value="<?php _e('Apply', 'popover'); ?>">
				</div>
				<div class="alignright actions"></div>
				<br class="clear">
				</div>

				</form>

			</div> <!-- wrap -->
			<?php
		}

	}

}

?>