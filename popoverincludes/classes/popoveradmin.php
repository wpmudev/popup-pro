<?php

if(!class_exists('popoveradmin')) {

	class popoveradmin {

		private $_data;

		function __construct() {
			add_action('admin_menu', array($this, 'add_menu_pages'));
			add_action('network_admin_menu', array($this, 'add_menu_pages'));

			// Add header files
			add_action('load-toplevel_page_popover', array($this, 'add_admin_header_popover_menu'));
			add_action('load-pop-overs_page_popoveraddons', array($this, 'add_admin_header_popover_addons'));
			add_action('load-pop-overs_page_popoversettings', array($this, 'add_admin_header_popover_settings'));

			// Ajax calls
			add_action( 'wp_ajax_popover_update_order', array($this, 'ajax_update_popover_order') );

			// Work with refactored transfer
			Popover_Updater::serve();

			// Work with the data model
			$this->_data = Popover_Data::get_instance();
		}

		function popoveradmin() {
			$this->__construct();
		}

		function add_menu_pages() {

			global $submenu;

			$perms = apply_filters('popover-admin-access_capability', 'manage_options');

			if(is_multisite() && (defined('PO_GLOBAL') && PO_GLOBAL == true)) {
				if(function_exists('is_network_admin') && is_network_admin()) {
					add_menu_page(__('Pop Overs', 'popover'), __('Pop Overs', 'popover'), $perms,  'popover', array($this, 'handle_popover_admin'), popover_url('popoverincludes/images/window.png'));
				}
			} else {
				if(!function_exists('is_network_admin') || !is_network_admin()) {
					add_menu_page(__('Pop Overs', 'popover'), __('Pop Overs', 'popover'), $perms,  'popover', array($this, 'handle_popover_admin'), popover_url('popoverincludes/images/window.png'));
				}
			}

			$addnew = add_submenu_page('popover', __('Create New Pop Over', 'popover'), __('Create New', 'popover'), $perms, "popover&amp;action=add", array($this, 'handle_addnewpopover_panel'));
			add_submenu_page('popover', __('Manage Add-ons Plugins', 'popover'), __('Add-ons','popover'), $perms, "popoveraddons", array($this, 'handle_addons_panel'));

			add_submenu_page('popover', __('Settings', 'popover'), __('Settings', 'popover'), $perms, "popoversettings", array($this, 'handle_settings_page'));

		}

		function sanitise_array($arrayin) {

			foreach( (array) $arrayin as $key => $value) {
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

		function add_admin_header_popover_menu() {

			$this->add_admin_header_core();

			if(isset($_GET['action']) && in_array($_GET['action'], array('edit', 'add'))) {
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

		function add_admin_header_popover_settings() {

			global $action, $page;

			wp_reset_vars( array('action', 'page') );

			$this->update_settings_page();

		}

		function add_admin_header_core() {
			// Add in help pages
			$screen = get_current_screen();
			$help = new Popover_Help( $screen );
			$help->attach();

		}

		function add_admin_header_popover() {
			global $wp_version;
			wp_enqueue_script('popoveradminjs', popover_url('popoverincludes/js/popoveradmin.js'), array( 'jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-droppable' ), $this->build);
			if(version_compare( preg_replace('/-.*$/', '', $wp_version), "3.3", '<')) {
				wp_enqueue_style('popoveradmincss', popover_url('popoverincludes/css/popoveradmin.css'), array('widgets'), $this->build);
			} else {
				wp_enqueue_style('popoveradmincss', popover_url('popoverincludes/css/popoveradmin.css'), array(), $this->build);
			}
		}

		function add_admin_header_popover_addons() {
			$this->add_admin_header_core();
			$this->handle_addons_panel_updates();
		}

		function add_popover( $data ) {
			$popover = $this->_prepare_popover();
			if(isset($_POST['addandactivate'])) {
				$popover['popover_active'] = 1;
			}
			return $this->_data->save_popover($popover);
		}

		function update_popover( $id, $data ) {
			$popover = $this->_prepare_popover();
			$popover['id'] = $id;
			return $this->_data->save_popover($popover);

		}

		private function _prepare_popover () {
			global $action, $page, $allowedposttags;

			$popover = array();

			$popover['popover_title'] = $_POST['popover_title'];

			if ( !current_user_can('unfiltered_html') ) {
				$popover['popover_content'] = wp_kses($_POST['popover_content'], $allowedposttags);
			} else {
				$popover['popover_content'] = $_POST['popover_content'];
			}

			$popover['popover_settings'] = array();
			$popover['popover_settings']['popover_size'] = array( 'width' => $_POST['popoverwidth'], 'height' => $_POST['popoverheight'], 'usejs' => $_POST['popover-usejs-size'] );
			$popover['popover_settings']['popover_location'] = array( 'left' => $_POST['popoverleft'], 'top' => $_POST['popovertop'], 'usejs' => $_POST['popover-usejs-position'] );
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

			if(isset($_POST['popoverhideforeverlink']) && $_POST['popoverhideforeverlink'] == 'yes') {
				$popover['popover_settings']['popoverhideforeverlink'] = 'yes';
			} else {
				$popover['popover_settings']['popoverhideforeverlink'] = 'no';
			}
			$popover['popover_settings']['popover_close_hideforever'] = isset($_POST['popover_close_hideforever'])
				? 'yes'
				: 'no'
			;
			$popover['popover_settings']['popover_hideforever_expiry'] = isset($_POST['popover_hideforever_expiry']) && (int)$_POST['popover_hideforever_expiry']
				? (int)$_POST['popover_hideforever_expiry']
				: (defined('PO_DEFAULT_EXPIRY') && PO_DEFAULT_EXPIRY ? PO_DEFAULT_EXPIRY : 365)
			;

			if(isset($_POST['popoverdelay'])) {
				$popover['popover_settings']['popoverdelay'] = $_POST['popoverdelay'];
			}

			if(isset($_POST['popoveronurl'])) {
				$popover['popover_settings']['onurl'] = explode("\n", $_POST['popoveronurl']);
			}

			if(isset($_POST['popovernotonurl'])) {
				$popover['popover_settings']['notonurl'] = explode("\n", $_POST['popovernotonurl']);
			}

			if(isset($_POST['popoverincountry'])) {
				$popover['popover_settings']['incountry'] = $_POST['popoverincountry'];
			}

			if(isset($_POST['popovernotincountry'])) {
				$popover['popover_settings']['notincountry'] = $_POST['popovernotincountry'];
			}


			$popover['popover_settings'] = apply_filters('popover-data-save', $popover['popover_settings']);
			$popover['popover_settings'] = serialize($popover['popover_settings']);

			return $popover;
		}

		function update_popover_admin() {
			global $action, $page;

			wp_reset_vars( array('action', 'page') );

			if(isset($_REQUEST['action']) || isset($_REQUEST['action2'])) {

				if(!empty($_REQUEST['action2'])) {
					$_REQUEST['action'] = $_REQUEST['action2'];
				}

				switch($_REQUEST['action']) {


					case 'activate': 		$id = (int)$_GET['popover'];
											if (!empty($id)) {
												check_admin_referer('toggle-popover-' . $id);

												if ($this->_data->activate_popover($id)) {
													wp_safe_redirect(add_query_arg('msg', 3, wp_get_referer()));
												} else {
													wp_safe_redirect(add_query_arg( 'msg', 4, wp_get_referer()));
												}

											}
											break;


					case 'deactivate':		$id = (int)$_GET['popover'];
											if (!empty($id)) {
												check_admin_referer('toggle-popover-' . $id);

												if ($this->_data->deactivate_popover($id)) {
													wp_safe_redirect(add_query_arg( 'msg', 5, wp_get_referer()));
												} else {
													wp_safe_redirect(add_query_arg( 'msg', 6, wp_get_referer()));
												}

											}
											break;

					case 'toggle':			$ids = $_REQUEST['popovercheck'];

											if (!empty($ids)) {
												check_admin_referer('bulk-popovers');
												foreach ((array)$ids as $id) {
													$this->_data->toggle_popover($id);
												}
												wp_safe_redirect( add_query_arg('msg', 7, wp_get_referer()));
											}
											break;

					case 'delete':			$id = (int)$_GET['popover'];

											if (!empty($id)) {
												check_admin_referer('delete-popover-' . $id);

												if ($this->_data->delete_popover($id)) {
													wp_safe_redirect(add_query_arg('msg', 8, wp_get_referer()));
												} else {
													wp_safe_redirect(add_query_arg('msg', 9, wp_get_referer()));
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
			//global $action, $page;
			global $page;

			$action = $_GET['action'];

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
										"rules" 	=> 	__('Conditions','popover'),
										"active"	=>	__('Active','popover')
									);

					$columns = apply_filters('popover_columns', $columns);

					$popovers = $this->_data->get_popovers();

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

													case 'supporter':		_e('Site is not a Pro-site', 'popover');
																			break;

													case 'incountry':		_e('In a specific country', 'popover');
																			break;

													case 'notincountry':	_e('Not in a specific country', 'popover');
																			break;

													default:				echo apply_filters('popover_nice_rule_name', '', $key);
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

			if(function_exists('get_site_option') && defined('PO_GLOBAL') && PO_GLOBAL == true) {
				$updateoption = 'update_site_option';
				$getoption = 'get_site_option';
			} else {
				$updateoption = 'update_option';
				$getoption = 'get_option';
			}


			if($id !== false) {
				$popover = $this->_data->get_popover($id);

				$popover->popover_settings = unserialize($popover->popover_settings);
			} else {
				$popover = new stdClass;
				$popover->popover_title = __('New Pop Over','popover');
				$popover->popover_content = "";
			}

			$popover_title = stripslashes($popover->popover_title);

			$popover_content = stripslashes($popover->popover_content);

			if(empty($popover->popover_settings)) {
				$popover->popover_settings = array(	
					'popover_size' => array('width' => '500px', 'height' => '200px', 'usejs' => 0),
					'popover_location' => array('left' => '100px', 'top' => '100px', 'usejs' => 0),
					'popover_colour' => array('back' => 'FFFFFF', 'fore' => '000000'),
					'popover_margin' => array('left' => '0px', 'top' => '0px', 'right' => '0px', 'bottom' => '0px'),
					'popover_check' => array(),
					'popover_ereg' => '',
					'popover_count' => 3,
					'popover_usejs' => 'no'
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

			$popoverstyle = (isset($popover->popover_settings['popover_style'])) ? $popover->popover_settings['popover_style'] : '';

			$popover_hideforever = (isset($popover->popover_settings['popoverhideforeverlink'])) ? $popover->popover_settings['popoverhideforeverlink'] : '';
			$popover_close_hideforever = (isset($popover->popover_settings['popover_close_hideforever'])) ? $popover->popover_settings['popover_close_hideforever'] : '';
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
						<form action='?page=popover' name='popoveredit' method='post'>

							<input type='hidden' name='id' id='id' value='<?php echo $id; ?>' />
							<input type='hidden' name='beingdragged' id='beingdragged' value='' />
							<input type='hidden' name='popovercheck[order]' id='in-positive-rules' value='<?php echo esc_attr($popover_check['order']); ?>' />

						<div id='edit-popover' class='popover-holder-wrap'>
							<div class='sidebar-name no-movecursor'>
								<h3><?php echo __('Pop Over Settings','popover'); ?></h3>
							</div>
							<div class='popover-holder'>

								<div class='popover-details'>

								<label for='popover_title'><?php _e('Popover title','popover'); ?></label><br/>
								<input name='popover_title' id='popover_title' style='width: 97%; border: 1px solid; border-color: #DFDFDF;' value='<?php echo stripslashes($popover_title); ?>' /><br/><br/>

								<label for='popovercontent'><?php _e('Popover content','popover'); ?></label><br/>
								<?php
								$args = array("textarea_name" => "popover_content", "textarea_rows" => 5);
								wp_editor( stripslashes($popover_content), "popover_content", $args );
								/*
								?>
								<textarea name='popover_content' id='popover_content' style='width: 98%' rows='5' cols='10'><?php echo stripslashes($popover_content); ?></textarea>
								<?php
								*/
								?>
								</div>

								<h3><?php _e('Active conditions','popover'); ?></h3>
								<p class='description'><?php _e('These are the rules that will determine if a popover should show when a visitor arrives at your website ALL rules must be true for the popover to show.','popover'); ?></p>
								<div id='positive-rules-holder'>
									<?php

										if(!empty($popover_check['order'])) {
											$order = explode(',', $popover_check['order']);
										} else {
											$order = array();
										}

										foreach($order as $key) {

											switch($key) {

												case 'supporter':		if( function_exists('is_pro_site') ) $this->admin_main('supporter','Site is not a Pro-site', 'Shows the popover if the site is not a Pro-site.', true);
																		break;

												case 'incountry':		$this->admin_countrylist('incountry','In a specific Country', 'Shows the popover if the user is in a certain country.', $popover_incountry);
																		break;
												case 'notincountry':	$this->admin_countrylist('notincountry','Not in a specific Country', 'Shows the popover if the user is not in a certain country.', $popover_notincountry);
																		break;

												default:				if ($key) do_action('popover_active_rule_' . $key, $popover, array($key =>1));
																		if ($key) do_action('popover_active_rule', $key, $popover, array($key =>1));
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

											<?php if (!(defined('POPOVER_LEGACY_JAVASCRIPT_DIFFERENTIATION') && POPOVER_LEGACY_JAVASCRIPT_DIFFERENTIATION)) { ?>
												<label for="popover-usejs-size">
													<?php _e('... <i>OR</i> use Javascript to set my pop over size', 'popover'); ?>
													<input type='hidden' name='popover-usejs-size' value='' />
													<input type='checkbox' name='popover-usejs-size' id='popover-usejs-size' value='1' <?php checked($popover_size['usejs'], 1); ?> />
												</label>
											<?php } ?>
										</td>
									</tr>

									<tr>
										<th valign='top' scope='row' style='width: 25%;'><strong><?php _e('Pop Over Position','popover'); ?></strong></th>
										<td valign='top'>
											<?php _e('Left:','popover'); ?>&nbsp;
											<input type='text' name='popoverleft' id='popoverleft' style='width: 5em;' value='<?php echo $popover_location['left']; ?>' />&nbsp;
											<?php _e('Top:','popover'); ?>&nbsp;
											<input type='text' name='popovertop' id='popovertop' style='width: 5em;' value='<?php echo $popover_location['top']; ?>' />
											
											<?php if (!(defined('POPOVER_LEGACY_JAVASCRIPT_DIFFERENTIATION') && POPOVER_LEGACY_JAVASCRIPT_DIFFERENTIATION)) { ?>
												<label for="popover-usejs-position">
													<?php _e('... <i>OR</i> use Javascript to position my pop over', 'popover'); ?>
													<input type='hidden' name='popover-usejs-position' value='' />
													<input type='checkbox' name='popover-usejs-position' id='popover-usejs-position' value='1' <?php checked($popover_location['usejs'], 1); ?> />
												</label>
											<?php } ?>
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
											<?php _e('or use Javascript to resize and center the popover','popover'); ?>&nbsp;<input type='checkbox' name='popoverusejs' id='popoverusejs' value='yes' <?php if($popover_usejs == 'yes') echo "checked='checked'"; ?> />
										</td>
									</tr>

									</table>
									<table class='form-table'>

									<tr>
										<th valign='top' scope='row' style='width: 25%;'><strong><?php _e('Background Color','popover'); ?></strong></th>
										<td valign='top'>
											<?php _e('Hex:','popover'); ?>&nbsp;#
											<input type='text' name='popoverbackground' id='popoverbackground' style='width: 10em;' value='<?php echo $popover_colour['back']; ?>' />
										</td>
									</tr>

									<tr>
										<th valign='top' scope='row' style='width: 25%;'><strong><?php _e('Font Color','popover'); ?></strong></th>
										<td valign='top'>
											<?php _e('Hex:','popover'); ?>&nbsp;#
											<input type='text' name='popoverforeground' id='popoverforeground' style='width: 10em;' value='<?php echo $popover_colour['fore']; ?>' />
										</td>
									</tr>

								</table>

								<?php
								$availablestyles = apply_filters( 'popover_available_styles_directory', array() );

								if(count($availablestyles) > 1) {
									?>
									<h3><?php _e('Pop Over Style','popover'); ?></h3>
									<table class='form-table'>

									<tr>
										<th valign='top' scope='row' style='width: 25%;'><strong><?php _e('Use Style','popover'); ?></strong></th>
										<td valign='top'>
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
									<?php
								} else {
									foreach( (array) $availablestyles as $key => $location ) {
										// There's only one - but it's easy to get the key this way :)
										?>
										<input type='hidden' name='popoverstyle' value='<?php echo $key; ?>' />
										<?php
									}
								}
								?>

								<h3><?php _e('Remove Hide Forever Link','popover'); ?></h3>
								<table class='form-table' style=''>
									<tr>
										<th valign='top' scope='row' style='width: 25%;'><strong><?php _e('Remove the "Never see this message again" link','popover'); ?></strong></th>
										<td valign='top'>
											<input type='checkbox' name='popoverhideforeverlink' id='popoverhideforeverlink' value='yes' <?php if($popover_hideforever == 'yes') { echo "checked='checked'"; } ?> />
										</td>
									</tr>
									<tr>
										<th valign='top' scope='row' style='width: 25%;'><strong><?php _e('Regular close button acts as &quot;Never see this message again&quot; link', 'popover'); ?></strong></th>
										<td valign='top'>
											<input type='checkbox' name='popover_close_hideforever' id='popover_close_hideforever' value='yes' <?php if($popover_close_hideforever == 'yes') { echo "checked='checked'"; } ?> />
										</td>
									</tr>
									<tr>
										<th valign='top' scope='row' style='width: 25%;'><strong><?php _e('Expiry time', 'popover'); ?></strong></th>
										<td valign='top'>
											<input type='number' name='popover_hideforever_expiry' id='popover_hideforever_expiry' value='<?php echo (int)$popover_hideforever_expiry; ?>' />
											<?php _e('days', 'popover'); ?>
										</td>
									</tr>
								</table>

								<h3><?php _e('Pop over appearance delays','popover'); ?></h3>
								<table class='form-table' style=''>
									<tr>
										<th valign='top' scope='row' style='width: 25%;'><strong><?php _e('Show Pop Over','popover'); ?></strong></th>
										<td valign='top'>
											<select name='popoverdelay'>
												<option value='immediate' <?php selected('immediate', $popover_delay); ?>><?php _e('immediately','popover'); ?></option>
												<?php
													for($n=1; $n <= 120; $n++) {
														?>
														<option value='<?php echo $n; ?>' <?php selected($n, $popover_delay); ?>><?php echo __('after','popover') . ' ' . $n . ' ' . __('seconds', 'popover') ; ?></option>
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
											<input type='submit' value='<?php _e('Add', 'popover'); ?>' class='button' name='add' />&nbsp;<input type='submit' value='<?php _e('Add and Activate', 'popover'); ?>' class='button-primary' name='addandactivate' />
											<input type='hidden' name='action' value='added' />
										<?php } ?>

								</div>

							</div>
						</div>
						</form>
					</div>


					<div id='hiden-actions'>
					<?php
						if(!isset($popover_check['supporter']) && function_exists('is_pro_site')) {
							$this->admin_main('supporter','Site is not a Pro-site', 'Shows the popover if the site is not a Pro-site.', true);
						}

						if(!isset($popover_check['incountry'])) {
							$this->admin_countrylist('incountry','In a specific Country', 'Shows the popover if the user is in a certain country.', $popover_incountry);
						}

						if(!isset($popover_check['notincountry'])) {
							$this->admin_countrylist('notincountry','Not in a specific Country', 'Shows the popover if the user is not in a certain country.', $popover_notincountry);
						}

						do_action('popover_additional_rules_main', $popover, $popover_check);

					?>
					</div> <!-- hidden-actions -->

				</div> <!-- popover-liquid-left -->

				<div class='popover-liquid-right'>
					<div class="popover-holder-wrap">

						<div class="sidebar-name no-movecursor">
							<h3><?php _e('Conditions', 'popover'); ?></h3>
						</div>
						<div class="section-holder" id="sidebar-rules" style="min-height: 98px;">
							<ul class='popovers popovers-draggable'>
								<?php

									if(isset($popover_check['supporter']) && function_exists('is_pro_site')) {
										$this->admin_sidebar('supporter','Site is not a Pro-site', 'Shows the popover if the site is not a Pro-site.', true);
									} elseif(function_exists('is_pro_site')) {
										$this->admin_sidebar('supporter','Site is not a Pro-site', 'Shows the popover if the site is not a Pro-site.', false);
									}


									if(isset($popover_check['incountry'])) {
										$this->admin_sidebar('incountry','In a specific Country', 'Shows the popover if the user is in a certain country.', true);
									} else {
										$this->admin_sidebar('incountry','In a specific Country', 'Shows the popover if the user is in a certain country.', false);
									}

									if(isset($popover_check['notincountry'])) {
										$this->admin_sidebar('notincountry','Not in a specific Country', 'Shows the popover if the user is not in a certain country.', true);
									} else {
										$this->admin_sidebar('notincountry','Not in a specific Country', 'Shows the popover if the user is not in a certain country.', false);
									}

									do_action('popover_additional_rules_sidebar', $popover_check);
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
					<p><?php _e($message, 'popover'); ?></p>
					<input type='hidden' name='popovercheck[<?php echo $id; ?>]' value='yes' />
				</div>
			</div>
			<?php
		}

		function admin_referer($id, $title, $message, $data = false) {
			if(!$data) $data = ''
			?>
			<div class='popover-operation' id='main-<?php echo $id; ?>'>
				<h2 class='sidebar-name'><?php _e($title, 'popover');?><span><a href='#remove' class='removelink' id='remove-<?php echo $id; ?>' title='<?php _e("Remove $title tag from this rules area.",'popover'); ?>'><?php _e('Remove','popover'); ?></a></span></h2>
				<div class='inner-operation'>
					<p><?php _e($message, 'popover'); ?></p>
					<input type='text' name='popoverereg' id='popoverereg' style='width: 10em;' value='<?php echo esc_html($data); ?>' />
					<input type='hidden' name='popovercheck[<?php echo $id; ?>]' value='yes' />
				</div>
			</div>
			<?php
		}

		function admin_viewcount($id, $title, $message, $data = false) {
			if(!$data) $data = '';
			?>
			<div class='popover-operation' id='main-<?php echo $id; ?>'>
				<h2 class='sidebar-name'><?php _e($title, 'popover');?><span><a href='#remove' class='removelink' id='remove-<?php echo $id; ?>' title='<?php _e("Remove $title tag from this rules area.",'popover'); ?>'><?php _e('Remove','popover'); ?></a></span></h2>
				<div class='inner-operation'>
					<p><?php _e($message, 'popover'); ?></p>
					<input type='text' name='popovercount' id='popovercount' style='width: 5em;' value='<?php echo esc_html($data); ?>' />&nbsp;
					<?php _e('times','popover'); ?>
					<input type='hidden' name='popovercheck[<?php echo $id; ?>]' value='yes' />
				</div>
			</div>
			<?php
		}

		function admin_urllist($id, $title, $message, $data = false) {
			if(!$data) $data = array();

			$data = implode("\n", $data);

			?>
			<div class='popover-operation' id='main-<?php echo $id; ?>'>
				<h2 class='sidebar-name'><?php _e($title, 'popover');?><span><a href='#remove' class='removelink' id='remove-<?php echo $id; ?>' title='<?php _e("Remove $title tag from this rules area.",'popover'); ?>'><?php _e('Remove','popover'); ?></a></span></h2>
				<div class='inner-operation'>
					<p><?php _e($message, 'popover'); ?></p>
					<textarea name='popover<?php echo $id; ?>' id='popover<?php echo $id; ?>' style=''><?php echo esc_html($data); ?></textarea>
					<input type='hidden' name='popovercheck[<?php echo $id; ?>]' value='yes' />
				</div>
			</div>
			<?php
		}

		function admin_countrylist($id, $title, $message, $data = false) {
			if(!$data) $data = '';


			?>
			<div class='popover-operation' id='main-<?php echo $id; ?>'>
				<h2 class='sidebar-name'><?php _e($title, 'popover');?><span><a href='#remove' class='removelink' id='remove-<?php echo $id; ?>' title='<?php _e("Remove $title tag from this rules area.",'popover'); ?>'><?php _e('Remove','popover'); ?></a></span></h2>
				<div class='inner-operation'>
					<p><?php _e($message, 'popover'); ?></p>
					<?php $countries = P_CountryList(); ?>
					<select name='popover<?php echo $id; ?>' id='popover<?php echo $id; ?>' style=''>
						<option value='' <?php selected('', $data); ?>><?php _e('Select a country from the list below' , 'popover') ?></option>
						<?php
							foreach( (array) $countries as $code => $country ) {
								?>
								<option value='<?php echo $code; ?>' <?php selected($code, $data); ?>><?php echo $country; ?></option>
								<?php
							}
						?>
					</select>
					<input type='hidden' name='popovercheck[<?php echo $id; ?>]' value='yes' />
				</div>
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

				case 'deactivate':	
					$key = addslashes($_GET['addon']);
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

				case 'activate':
					$key = addslashes($_GET['addon']);
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


		function handle_settings_page() {

			global $action, $page;

			$messages = array();
			$messages[1] = __('Your settings have been updated.','popover');

			?>
			<div class='wrap nosubsub'>

				<div class="icon32" id="icon-options-general"><br></div>
				<h2><?php _e('Pop Over Settings','popover'); ?></h2>

				<?php
				if ( isset($_GET['msg']) ) {
					echo '<div id="message" class="updated fade"><p>' . $messages[(int) $_GET['msg']] . '</p></div>';
					$_SERVER['REQUEST_URI'] = remove_query_arg(array('message'), $_SERVER['REQUEST_URI']);
				}
				?>
				<div id="poststuff" class="metabox-holder m-settings">
				<form action='?page=<?php echo $page; ?>' method='post'>

					<input type='hidden' name='page' value='<?php echo $page; ?>' />
					<input type='hidden' name='action' value='updatesettings' />

					<?php
						wp_nonce_field('update-popover-settings');
					?>

					<div class="postbox">
						<h3 class="hndle" style='cursor:auto;'><span><?php _e('Pop Over loading method','popover'); ?></span></h3>
						<div class="inside">
							<p><?php _e('Select the loading method you want to use for your Pop Overs.','popover'); ?></p>
							<ul>
								<li><em><?php _e('- Page Footer : The pop over is included as part of the page html.','popover'); ?></em></li>
								<li><em><?php _e('- External Load : The pop over is loaded separately from the page, this is the best option if you are running a caching system.','popover'); ?></em></li>
								<li><em><?php _e('- Custom Load : The pop over is loaded separately from the page via a custom front end ajax call.','popover'); ?></em></li>
							</ul>

							<table class="form-table">
							<tbody>
								<tr valign="top">
									<th scope="row"><?php _e('Pop Over loaded using','popover'); ?></th>
									<td>
										<?php
											$settings = get_popover_option('popover-settings', array( 'loadingmethod' => 'frontloading'));
										?>
										<select name='loadingmethod' id='loadingmethod'>
											<option value="footer" <?php if($settings['loadingmethod'] == 'footer') echo "selected='selected'"; ?>><?php _e('Page Footer','popover'); ?></option>
											<option value="external" <?php if($settings['loadingmethod'] == 'external') echo "selected='selected'"; ?>><?php _e('External Load','popover'); ?></option>
											<option value="frontloading" <?php if($settings['loadingmethod'] == 'frontloading') echo "selected='selected'"; ?>><?php _e('Custom Load','popover'); ?></option>
											<?php do_action('popover-settings-loading_method', $settings['loadingmethod']); ?>
										</select>
									</td>
								</tr>
							</tbody>
							</table>
						</div>
					</div>

					<p class="submit">
						<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes', 'popover') ?>" />
					</p>

				</form>
				</div>
			</div> <!-- wrap -->
			<?php
		}

		function update_settings_page() {

			if(isset($_POST['action']) && $_POST['action'] == 'updatesettings') {

				check_admin_referer('update-popover-settings');

				update_popover_option( 'popover-settings', $_POST );

				wp_safe_redirect( add_query_arg('msg', 1, wp_get_referer()) );
			}

		}

	}

}