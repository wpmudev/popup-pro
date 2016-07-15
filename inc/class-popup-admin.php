<?php
// Load dependencies.
require_once PO_INC_DIR . 'class-popup-base.php';
require_once PO_INC_DIR . 'class-popup-help.php';

/**
 * Defines the popup class for admin pages.
 *
 * @since  4.6.0
 */
class IncPopup extends IncPopupBase {

	/**
	 * Returns the singleton instance of the popup (admin) class.
	 *
	 * @since  4.6.0
	 */
	static public function instance() {
		static $Inst = null;

		// We can initialize the plugin once we know the current user:
		// There is a preference for global popups that needs to know the user.
		if ( ! did_action( 'set_current_user' ) ) {
			add_action( 'set_current_user', array( __CLASS__, 'instance' ) );
			return null;
		}

		if ( null === $Inst ) {
			$Inst = new IncPopup();
		}

		return $Inst;
	}

	/**
	 * Private constructor (singleton)
	 *
	 * @since  4.6.0
	 */
	protected function __construct() {
		parent::__construct();

		// Add admin menus.
		add_action(
			'admin_menu',
			array( 'IncPopup', 'admin_menus' )
		);

		add_action(
			'network_admin_menu',
			array( 'IncPopup', 'admin_menus' )
		);

		// Initialize hooks that are used only in the current module.
		add_action(
			'current_screen',
			array( 'IncPopup', 'setup_module_specific' )
		);

		// Handles all admin ajax requests.
		add_action(
			'wp_ajax_po-ajax',
			array( 'IncPopup', 'handle_ajax' )
		);

		// Every time a popup is changed we validate "order" value of all popups.
		add_action(
			'save_post_' . IncPopupItem::POST_TYPE,
			array( 'IncPopup', 'post_check_order' ),
			99, 3
		);

		// -- SETTINGS --------------------------

		// Save changes from settings page.
		add_action(
			'load-inc_popup_page_settings',
			array( 'IncPopup', 'handle_settings_update' )
		);
	}

	/**
	 * Initializes stuff that is only needed on the plugin screen
	 *
	 * @since  4.6.0
	 */
	static public function setup_module_specific( $hook ) {
		lib3()->array->equip( $hook, 'post_type', 'base' );
		lib3()->array->equip_request( 'post_status' );

		if ( IncPopupItem::POST_TYPE === $hook->post_type ) {
			// WordPress core scripts
			lib3()->ui->js( 'jquery-ui-slider' );
			lib3()->ui->js( 'jquery-ui-sortable' );

			lib3()->ui->add( 'core' );
			lib3()->ui->add( 'select' );
			lib3()->ui->add( 'animate' ); // For Preview.

			lib3()->ui->add( PO_CSS_URL . 'popup-admin.min.css' );
			lib3()->ui->add( PO_JS_URL . 'popup-admin.min.js' );
			lib3()->ui->add( PO_JS_URL . 'ace.js' ); // CSS editor.
			lib3()->ui->add( PO_JS_URL . 'public.min.js' ); // For Preview.

			if ( 'trash' != $_REQUEST['post_status'] ) {
				lib3()->ui->data(
					'po_bulk',
					array(
						'activate' => __( 'Activate', 'popover' ),
						'deactivate' => __( 'Deactivate', 'popover' ),
						'toggle' => __( 'Toggle activation', 'popover' ),
					)
				);
			}

			// For Preview
			lib3()->ui->data(
				'_popup_data',
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'do'      => 'get_data',
					'popup'   => '',
					'noinit'  => true,
					'preview' => true,
				)
			);

			// -- PopUp LIST -----------------------

			if ( 'edit' === $hook->base ) {
				// Customize the columns in the popup list.
				add_filter(
					'manage_' . IncPopupItem::POST_TYPE . '_posts_columns',
					array( 'IncPopup', 'post_columns' )
				);

				// Added for WordPress 4.3: Define main column of the list.
				add_filter(
					'list_table_primary_column',
					array( 'IncPopup', 'primary_column' )
				);

				// Added for WordPress 4.3: Define custom row actions.
				add_filter(
					'post_row_actions',
					array( 'IncPopup', 'post_row_actions' ),
					10, 2
				);

				// Returns the content for the custom columns.
				add_action(
					'manage_' . IncPopupItem::POST_TYPE . '_posts_custom_column',
					array( 'IncPopup', 'post_column_content' ),
					10, 2
				);

				// Defines the Quick-Filters above the popup table
				add_filter(
					'views_edit-' . IncPopupItem::POST_TYPE,
					array( 'IncPopup', 'post_views' )
				);

				// Defines the Bulk-Actions (available in the select box)
				add_filter(
					'bulk_actions-edit-' . IncPopupItem::POST_TYPE,
					array( 'IncPopup', 'post_actions' )
				);

				// Add our own PopUp update messages.
				add_filter(
					'bulk_post_updated_messages',
					array( 'IncPopup', 'post_update_messages' ),
					10, 2
				);

				// Process custom actions in the post-list (e.g. activate/deactivate)
				add_action(
					'load-edit.php',
					array( 'IncPopup', 'post_list_edit' ),
					1
				);

				// Modify the post query to avoid pagination on the popup list.
				add_action(
					'pre_get_posts',
					array( 'IncPopup', 'post_query' )
				);

				// Filter should return the custom "items per page" value.
				add_filter(
					'edit_posts_per_page',
					array( 'IncPopup', 'post_item_per_page' ),
					10, 2
				);

				// Remove the posts-per-page filter from screen options.
				add_action(
					'admin_head',
					array( 'IncPopup', 'post_screenoptions' )
				);
			}

			// -- PopUp EDITOR -----------------

			if ( 'post' === $hook->base ) {
				lib3()->ui->css( 'wp-color-picker' ); // WordPress core script
				lib3()->ui->js( 'wp-color-picker' ); // WordPress core script

				// See if a custom action should be executed (e.g. duplicate)
				self::form_check_actions();

				// Display the "PopUp Title" field in top of the form.
				add_action(
					'edit_form_after_title',
					array( 'IncPopup', 'form_title' )
				);

				// Add custom meta-boxes to the popup-editor
				add_action(
					'add_meta_boxes_' . IncPopupItem::POST_TYPE,
					array( 'IncPopup', 'form_metabox' )
				);

				// Add custom meta-boxes to the popup-editor
				add_action(
					'save_post_' . IncPopupItem::POST_TYPE,
					array( 'IncPopup', 'form_save' ),
					10, 3
				);
			}
		}
	}


	/*===================================*\
	=======================================
	==                                   ==
	==           GENERAL ADMIN           ==
	==                                   ==
	=======================================
	\*===================================*/


	/**
	 * Register additional menu items in the dashboard.
	 *
	 * @since  4.6.0
	 */
	static public function admin_menus() {
		global $submenu;

		// correct_level checks:
		// PO_GLOBAL is true .. We have to be on network-admin/main blog.
		// PO_GLOBAL is false .. we have to be NOT on network-admin.
		if ( ! self::correct_level() ) { return; }

		if ( is_network_admin() ) {
			lib3()->array->equip_request( 'popup_network' );
			if ( 'hide' == $_REQUEST['popup_network'] ) {
				IncPopupDatabase::set_flag( 'network_dismiss', true );
				wp_safe_redirect( admin_url( 'network' ) );
				die();
			} elseif ( 'show' == $_REQUEST['popup_network'] ) {
				IncPopupDatabase::set_flag( 'network_dismiss', false );
			}

			if ( true == IncPopupDatabase::get_flag( 'network_dismiss' ) ) {
				return;
			}

			add_menu_page(
				__( 'PopUp', 'popover' ),
				__( 'PopUp', 'popover' ),
				IncPopupPosttype::$perms,
				IncPopupItem::POST_TYPE . '-list',
				array( 'IncPopup', 'network_menu_notice' ),
				PO_IMG_URL . 'icon.png',
				IncPopupPosttype::$menu_pos
			);

			add_submenu_page(
				IncPopupItem::POST_TYPE . '-list',
				__( 'Add New', 'popover' ),
				__( 'Add New', 'popover' ),
				IncPopupPosttype::$perms,
				IncPopupItem::POST_TYPE . '-create',
				array( 'IncPopup', 'network_menu_notice' )
			);

			add_submenu_page(
				IncPopupItem::POST_TYPE . '-list',
				__( 'Settings', 'popover' ),
				__( 'Settings', 'popover' ),
				IncPopupPosttype::$perms,
				IncPopupItem::POST_TYPE . '-settings',
				array( 'IncPopup', 'network_menu_notice' )
			);

			$submenu[ IncPopupItem::POST_TYPE . '-list' ][0][0] = _x( 'Global PopUps', 'Post Type General Name', 'popover' );
		} else {
			add_submenu_page(
				'edit.php?post_type=' . IncPopupItem::POST_TYPE,
				__( 'Settings', 'popover' ),
				__( 'Settings', 'popover' ),
				IncPopupPosttype::$perms,
				'settings',
				array( 'IncPopup', 'handle_settings_page' )
			);
		}
	}

	/**
	 * The Post-Editor does not work on Multisite Network dashboard.
	 * So display a notice and tell the user to go to the Main Site.
	 *
	 * @since  4.6.0
	 */
	static public function network_menu_notice() {
		self::load_view( 'network' );
	}

	/**
	 * Handles all admin ajax calls.
	 *
	 * @since  4.6.0
	 */
	static public function handle_ajax() {
		lib3()->array->equip_post( 'do', 'order' );

		$action = $_POST['do'];

		switch ( $action ) {
			case 'order':
				$order = explode( ',', $_POST['order'] );
				self::post_order( $order );
				break;

			

			default:
				/**
				 * Allow other modules to handle their own ajax requests.
				 *
				 * @since  4.6.1.1
				 */
				do_action( 'popup-ajax-' . $action );
				return;
		}

		die();
	}


	/*==============================*\
	==================================
	==                              ==
	==           SETTINGS           ==
	==                              ==
	==================================
	\*==============================*/


	/**
	 * Displays the settings page
	 *
	 * @since  4.6.0
	 */
	static public function handle_settings_page() {
		self::load_view( 'settings' );
	}

	/**
	 * Called every time the settings page is loaded. Saves changes.
	 *
	 * @since  4.6.0
	 */
	static public function handle_settings_update() {
		lib3()->array->equip_post( 'action', 'po_option' );

		if ( 'updatesettings' == $_POST['action'] ) {
			check_admin_referer( 'update-popup-settings' );

			$old_settings = IncPopupDatabase::get_settings();

			$settings = array();
			$settings['loadingmethod'] = $_POST['po_option']['loadingmethod'];

			if ( isset( $_POST['po_option']['geo_lookup'] ) ) {
				$settings['geo_lookup'] = $_POST['po_option']['geo_lookup'];
				$settings['geo_db'] = ( 'geo_db' == $settings['geo_lookup'] );
			}

			$rules = $_POST['po_option']['rules'];
			if ( ! is_array( $rules ) ) { $rules = array(); }
			$settings['rules'] = array_keys( $rules );

			IncPopupDatabase::set_settings( $settings );

			/* start:pro*/
			// When the Lookup-source was changed we want to clear the cache.
			if ( $old_settings['geo_lookup'] != $settings['geo_lookup'] ) {
				IncPopupDatabase::clear_ip_cache();
				lib3()->ui->admin_message( __( 'Country Lookup changed: The lookup-cache was cleared.', 'popover' ) );
			}
			/* end:pro*/

			lib3()->ui->admin_message( __( 'Your settings have been updated.', 'popover' ) );
			$redirect_url = esc_url_raw(
				remove_query_arg( array( 'message', 'count' ), wp_get_referer() )
			);
			wp_safe_redirect( $redirect_url );
			die();
		}
	}


	/*=================================*\
	=====================================
	==                                 ==
	==           PopUp LIST           ==
	==                                 ==
	=====================================
	\*=================================*/


	/**
	 * Filter. Returns the columns for the item list of the popup post-type.
	 *
	 * @since  4.6.0
	 * @param  array $post_columns
	 * @return array
	 */
	static public function post_columns( $post_columns ) {
		lib3()->array->equip_request( 'post_status' );

		$new_columns = array();

		// Only allow re-ordering when ALL popups are visible
		if ( empty( $_REQUEST['s'] ) && empty( $_REQUEST['post_status'] ) ) {
			$new_columns['po_order'] = '';
		}

		$new_columns['cb'] = $post_columns['cb'];
		$new_columns['po_name'] = __( 'PopUp Name', 'popover' );
		$new_columns['po_cond'] = __( 'Conditions', 'popover' );

		if ( 'trash' !== $_REQUEST['post_status'] ) {
			$new_columns['po_pos'] = __( 'Order', 'popover' );
			$new_columns['po_state'] = __( 'Active', 'popover' );
		}

		return $new_columns;
	}

	/**
	 * Define the column that gets the action links in the list table.
	 *
	 * @since  4.7.1.1
	 * @param  string $column WordPress choice of the column ID.
	 * @return string Column ID
	 */
	static public function primary_column( $column ) {
		return 'po_name';
	}

	/**
	 * Filter. Define our own row-actions for the popup list.
	 *
	 * @since  4.7.1.1
	 * @param  array $actions
	 * @param  WP_Post $post
	 * @return array New list of row-actions.
	 */
	static public function post_row_actions( $actions, $post ) {
		// Actions are returned as part of the po_name column contents below.
		return array();
	}

	/**
	 * Outputs the contents of a specific column.
	 *
	 * @since  4.6.0
	 * @param  string $column The column-key (defined in post_columns above).
	 * @param  int $post_id The ID of the popup.
	 */
	static public function post_column_content( $column, $post_id ) {
		$popup = IncPopupDatabase::get( $post_id );

		switch ( $column ) {
			case 'po_name':
				$can_edit = current_user_can( 'edit_post', $post_id ) && 'trash' !== $popup->status;
				$post_type_object = get_post_type_object( IncPopupItem::POST_TYPE );
				$actions = array();

				if ( $can_edit ) {
					$actions['edit'] = array(
						'url' => get_edit_post_link( $post_id ),
						'title' => __( 'Edit this PopUp', 'popover' ),
						'attr' => '',
						'label' => __( 'Edit', 'popover' ),
					);
				}

				if ( $can_edit && 'active' === $popup->status ) {
					$the_url = 'edit.php?post_type=%1$s&post_id=%2$s&action=deactivate';
					$the_url = admin_url( sprintf( $the_url, IncPopupItem::POST_TYPE, $post_id ) );
					$the_url = wp_nonce_url( $the_url, 'deactivate-post_' . $post_id );
					$actions['deactivate'] = array(
						'url' => $the_url,
						'title' => __( 'Deactivate this PopUp', 'popover' ),
						'attr' => '',
						'label' => __( 'Deactivate', 'popover' ),
					);
				}

				if ( $can_edit && 'inactive' === $popup->status ) {
					$the_url = 'edit.php?post_type=%1$s&post_id=%2$s&action=activate';
					$the_url = admin_url( sprintf( $the_url, IncPopupItem::POST_TYPE, $post_id ) );
					$the_url = wp_nonce_url( $the_url, 'activate-post_' . $post_id );
					$actions['activate'] = array(
						'url' => $the_url,
						'title' => __( 'Activate this PopUp', 'popover' ),
						'attr' => '',
						'label' => __( 'Activate', 'popover' ),
					);
				}

				$actions['popup_preview'] = array(
					'url' => '#',
					'title' => __( 'Preview this PopUp', 'popover' ),
					'attr' => 'class="po-preview" data-id="' . $post_id . '"',
					'label' => __( 'Preview', 'popover' ),
				);

				if ( current_user_can( 'delete_post', $post_id ) ) {
					if ( 'trash' === $popup->status ) {
						$the_url = $post_type_object->_edit_link . '&amp;action=untrash';
						$the_url = admin_url( sprintf( $the_url, $post_id ) );
						$the_url = wp_nonce_url( $the_url, 'untrash-post_' . $post_id );
						$actions['untrash'] = array(
							'url' => $the_url,
							'title' => __( 'Restore this PopUp from the Trash', 'popover' ),
							'attr' => '',
							'label' => __( 'Restore', 'popover' ),
						);
					} elseif ( EMPTY_TRASH_DAYS ) {
						$actions['trash'] = array(
							'url' => get_delete_post_link( $post_id ),
							'title' => __( 'Move this PopUp to the Trash', 'popover' ),
							'attr' => 'class="submitdelete"',
							'label' => __( 'Trash', 'popover' ),
						);
					}
					if ( 'trash' === $popup->status || ! EMPTY_TRASH_DAYS ) {
						$actions['delete'] = array(
							'url' => get_delete_post_link( $post_id, '', true ),
							'title' => __( 'Delete this PopUp permanently', 'popover' ),
							'attr' => 'class="submitdelete"',
							'label' => __( 'Delete Permanently', 'popover' ),
						);
					}
				}

				if ( $can_edit ) : ?>
					<a href="<?php echo esc_url( get_edit_post_link( $post_id ) ); ?>"
						title="<?php _e( 'Edit this PopUp', 'popover' ); ?>">
						<span class="the-title"><?php echo esc_html( $popup->name ); ?></span>
					</a>
				<?php else : ?>
					<span class="the-title"><?php echo esc_html( $popup->name ); ?></span>
				<?php endif; ?>
				<div class="row-actions">
				<?php
				$action_count = count( $actions );
				$i = 0;
				foreach ( $actions as $action => $item ) {
					$i += 1;
					$sep = ( $i == $action_count ) ? '' : ' | ';
					?>
					<span class="<?php echo esc_attr( $action ); ?>">
					<a href="<?php echo esc_url( $item['url'] ); ?>"
						title="<?php echo esc_attr( $item['title'] ); ?>"
						<?php echo '' . $item['attr']; ?>>
						<?php echo esc_html( $item['label'] ); ?>
					</a><?php echo esc_html( $sep ); ?>
					</span>
					<?php
				} ?>
				</div>
				<?php
				break;

			case 'po_cond':
				$rule_count = 0;
				?>
				<div class="rules">
				<?php foreach ( $popup->rule as $key ) : ?>
					<?php $label = IncPopupItem::condition_label( $key ); ?>
					<?php if ( empty( $label ) ) { continue; } ?>
					<?php $rule_count += 1; ?>
					<span class="rule"><?php echo esc_html( $label ); ?></span>
				<?php endforeach; ?>
				<?php if ( ! $rule_count ) : ?>
					<span class="rule-always"><?php _e( 'Always Show PopUp', 'popover' ); ?></span>
				<?php endif; ?>
				</div>
				<?php
				break;

			case 'po_pos':
				if ( 'trash' === $popup->status ) {
					echo '-';
				} else {
					$order = $popup->order;
					if ( ! is_numeric( $order ) || $order > 999999 ) { $order = ''; }
					?>
					<span class="the-pos"><?php echo esc_html( $order ); ?></span>
					<?php
				}
				break;

			case 'po_state':
				$title = $popup->status_label( $popup->status );
				?>
				<span title="<?php echo esc_attr( $title ); ?>">
					<i class="status-icon dashicons"></i>
				</span>
				<?php
				break;
		}
	}

	/**
	 * Filter. Defines the quick-filters above the popup-table.
	 *
	 * @since  4.6.0
	 * @param  array $views Default filters.
	 * @return array Modified filters.
	 */
	static public function post_views( $views ) {
		$new_views = array();
		$stati = array(
			'all' => __( 'All <span class="count">(%1$s)</span>', 'popover' ),
			'publish' => __( 'Active <span class="count">(%1$s)</span>', 'popover' ),
			'draft' => __( 'Inactive <span class="count">(%1$s)</span>', 'popover' ),
			'trash' => __( 'Trash <span class="count">(%1$s)</span>', 'popover' ),
		);
		$post_type = IncPopupItem::POST_TYPE;
		$num_posts = wp_count_posts( $post_type, 'readable' );
		$total_posts = 0;

		foreach ( $stati as $status => $label ) {
			if ( empty( $num_posts->$status ) ) {
				continue;
			}
			if ( 'trash' == $status ) {
				continue;
			}

			$total_posts += $num_posts->$status;
		}

		$class = empty( $_REQUEST['post_status'] ) ? ' class="current"' : '';
		$new_views['all'] =
			"<a href='edit.php?post_type=$post_type'$class>" .
				sprintf( $stati['all'], number_format_i18n( $total_posts ) ) .
			'</a>';

		foreach ( $stati as $status => $label ) {
			$class = '';

			if ( empty( $num_posts->$status ) ) {
				continue;
			}

			if ( isset( $_REQUEST['post_status'] ) && $status == $_REQUEST['post_status'] ) {
				$class = ' class="current"';
			}

			$new_views[ $status ] =
				"<a href='edit.php?post_status=$status&amp;post_type=$post_type'$class>" .
					sprintf( $label, number_format_i18n( $num_posts->$status ) ) .
				'</a>';
		}
		return $new_views;
	}

	/**
	 * Defines the Bulk Actions available for popups.
	 * Note: This filter can only be used to *remove* bulk actions.
	 *
	 * @since  4.6.0
	 * @param  array $actions Default list of bulk actions.
	 * @return array Modified action list.
	 */
	static public function post_actions( $actions ) {
		$new_actions = array();
		isset( $actions['trash'] ) && $new_actions['trash'] = $actions['trash'];
		isset( $actions['untrash'] ) && $new_actions['untrash'] = $actions['untrash'];
		isset( $actions['delete'] ) && $new_actions['delete'] = $actions['delete'];
		return $new_actions;
	}

	/**
	 * Define our PopUp update messages.
	 *
	 * @since  4.6.0
	 * @see    wp-admin/edit.php
	 * @param  array $messages Array of messages, by post-type.
	 * @param  array $counts
	 * @return array The modified $messages array
	 */
	static public function post_update_messages( $messages, $counts ) {
		$messages[ IncPopupItem::POST_TYPE ] = array(
			'updated'   => _n( 'One PopUp updated.', '%s PopUps updated.', $counts['updated'] ),
			'locked'    => _n( 'One PopUp not updated, somebody is editing it.', '%s PopUps not updated, somebody is editing them.', $counts['locked'] ),
			'deleted'   => _n( 'One PopUp permanently deleted.', '%s PopUps permanently deleted.', $counts['deleted'] ),
			'trashed'   => _n( 'One PopUp moved to the Trash.', '%s PopUps moved to the Trash.', $counts['trashed'] ),
			'untrashed' => _n( 'One PopUp restored from the Trash.', '%s PopUps restored from the Trash.', $counts['untrashed'] ),
		);
		return $messages;
	}

	/**
	 * Called when the file edit.php is loaded
	 *
	 * @since  4.6.0
	 */
	static public function post_list_edit() {
		$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
		$action = $wp_list_table->current_action();

		lib3()->array->equip_request( 'mode' );

		if ( $action ) {
			if ( 'list' === $_REQUEST['mode'] ) {
				// ----- Custom bulk-action.
				check_admin_referer( 'bulk-posts' );
				$post_ids = array();
				if ( isset( $_REQUEST['ids'] ) ) {
					$post_ids = explode( ',', $_REQUEST['ids'] );
				} elseif ( ! empty( $_REQUEST['post'] ) ) {
					$post_ids = array_map( 'intval', $_REQUEST['post'] );
				}

				$count = 0;
				foreach ( $post_ids as $post_id ) {
					$popup = IncPopupDatabase::get( $post_id );

					switch ( $action ) {
						case 'activate':
							$popup->status = 'active';
							break;

						case 'deactivate':
							$popup->status = 'inactive';
							break;

						case 'toggle':
							if ( 'inactive' === $popup->status ) {
								$popup->status = 'active';
							} else if ( 'active' === $popup->status ) {
								$popup->status = 'inactive';
							} else {
								$count -= 1;
							}
							break;
					}
					$popup->save( false );
					$count += 1;
				}

				switch ( $action ) {
					case 'activate':
						1 === $count ?
						$msg = __( 'One PopUp activated', 'popover' ) :
						$msg = __( '%1$s PopUps activated', 'popover' );
						break;

					case 'deactivate':
						1 === $count ?
						$msg = __( 'One PopUp deactivated', 'popover' ) :
						$msg = __( '%1$s PopUps deactivated', 'popover' );
						break;

					case 'toggle':
						1 === $count ?
						$msg = __( 'One PopUp toggled', 'popover' ) :
						$msg = __( '%1$s PopUps toggled', 'popover' );
						break;
				}

				if ( $count > 0 && ! empty( $msg ) ) {
					lib3()->ui->admin_message( sprintf( $msg, $count ) );
				}
			} else {
				lib3()->array->equip_request( '_wpnonce', 'post_id' );

				// ----- Custom row-action.
				$nonce = $_REQUEST['_wpnonce'];
				$post_id = absint( $_REQUEST['post_id'] );
				$popup = IncPopupDatabase::get( $post_id );

				if ( ! $popup ) { return; }
				if ( ! wp_verify_nonce( $nonce, $action . '-post_' . $post_id ) ) { return; }

				switch ( $action ) {
					case 'activate':
						$popup->status = 'active';
						$popup->save();
						break;

					case 'deactivate':
						$popup->status = 'inactive';
						$popup->save();
						break;
				}

				$back_url = esc_url_raw(
					remove_query_arg( array( 'action', 'post_id', '_wpnonce' ) )
				);
				wp_safe_redirect( $back_url );
				die();
			}
		}
	}

	/**
	 * Modify the main WP query to avoid pagination on the popup list and sort
	 * the popup list by the popup-order.
	 *
	 * @since  4.6.0
	 */
	static public function post_query( $query ) {
		if ( ! $query->is_main_query() ) { return; }

		$query->set( 'posts_per_page', -1 );
		$query->set( 'order', 'ASC' );
		$query->set( 'orderby', 'menu_order' );
	}

	/**
	 * Returns the custom value of "items-per-page".
	 * This value is used by WordPress to generate the pagination links.
	 *
	 * @since  4.6.0
	 * @param  int $value Default value set in Database
	 * @param  string $post_type
	 * @return int Customized value
	 */
	static public function post_item_per_page( $value, $post_type ) {
		if ( IncPopupItem::POST_TYPE == $post_type ) {
			// Setting to -1 works, but will not display text "17 items" in the top/right corner.
			$value = 100000;
		}
		return $value;
	}

	/**
	 * Remove the posts-per-page filter from screen options.
	 *
	 * @since  4.6.0
	 */
	static public function post_screenoptions() {
		$screen = get_current_screen();
		$screen->add_option( 'per_page', false );
	}

	/**
	 * Takes an array as input and updates the order of all popups according to
	 * the definition in the array.
	 *
	 * @since  4.6.0
	 * @param  array $order List of popup-IDs
	 */
	static public function post_order( $order ) {
		$ids = array();
		$new_order = array();

		// Don't do the full re-order after every popup is saved.
		remove_action(
			'save_post_' . IncPopupItem::POST_TYPE,
			array( 'IncPopup', 'post_check_order' ),
			99, 3
		);

		foreach ( $order as $item ) {
			if ( ! is_numeric( $item ) ) {
				$item = preg_replace( '/[^0-9,.]/', '', $item );
			}
			$ids[] = absint( $item );
		}

		foreach ( $ids as $pos => $id ) {
			$popup = IncPopupDatabase::get( $id );
			$popup->order = $pos + 1;
			$popup->save( false );
			$new_order[ $id ] = $popup->order;
		}

		echo json_encode( $new_order );
	}

	/**
	 * Save the popup data to database.
	 *
	 * Intentionally no nonce-check: We want to validate the popup-positions
	 * every time a popup is modified, regardless from where the change was
	 * initiated.
	 *
	 * @since  4.6.0
	 * @param  int $post_id Post ID that was saved/created
	 * @param  WP_Post $post Post object that was saved/created
	 * @param  bool $update True means the post was updated (not created)
	 */
	static public function post_check_order( $post_id, $post, $update ) {
		$popup = IncPopupDatabase::get( $post_id );

		// Autosave is not processed.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }

		// This save event is for a different post type... ??
		if ( $popup->id != $post_id ) { return; }

		// User does not have permissions for this.
		if ( ! current_user_can( IncPopupPosttype::$perms ) ) { return; }

		IncPopupDatabase::refresh_order();
	}


	/*===================================*\
	=======================================
	==                                   ==
	==           PopUp EDITOR           ==
	==                                   ==
	=======================================
	\*===================================*/


	/**
	 * Executes custom form actions, such as "duplicate PopUp"
	 *
	 * @since  4.6.0
	 */
	static protected function form_check_actions() {
		lib3()->array->equip_request( 'post', 'do' );

		$popup_id = absint( $_REQUEST['post'] );
		$action = $_REQUEST['do'];

		if ( empty( $popup_id ) || empty( $action ) ) { return; }

		switch ( $action ) {
			case 'duplicate':
				$item = IncPopupDatabase::get( $popup_id );
				$item->id = 0;
				$item->name = '(Copy) ' . $item->name;
				$item->save();

				// Show the new item in the editor.
				$new_url = esc_url_raw(
					add_query_arg(
						array( 'post' => $item->id, 'post_type' => IncPopupItem::POST_TYPE ),
						remove_query_arg( array( 'post', 'do' ) )
					)
				);
				wp_safe_redirect( $new_url );
				die();

				break;
		}
	}

	/**
	 * Register custom metaboxes for the PopUp editor
	 *
	 * @since  4.6.0
	 * @param  WP_Post $post The PopUp being edited.
	 */
	static public function form_metabox( $post ) {
		$po_meta = array(
			'submitdiv',
			'meta-content',
			'meta-appearance',
			'meta_behavior',
			'meta-rules',
			'meta-customcss',
			'meta-side-ads',
		);

		$meta_order = get_user_option( 'meta-box-order_' . IncPopupItem::POST_TYPE );
		$meta_order = lib3()->array->get( $meta_order );
		if ( empty( $meta_order['side'] ) ) { $meta_order['side'] = ''; }
		if ( empty( $meta_order['normal'] ) ) { $meta_order['normal'] = ''; }
		if ( empty( $meta_order['advanced'] ) ) { $meta_order['advanced'] = ''; }
		$meta_order['side'] = str_replace( $po_meta, '', $meta_order['side'] );
		$meta_order['normal'] = str_replace( $po_meta, '', $meta_order['normal'] );
		$meta_order['advanced'] = str_replace( $po_meta, '', $meta_order['advanced'] );
		update_user_option( get_current_user_id(), 'meta-box-order_' . IncPopupItem::POST_TYPE, $meta_order );

		// Remove core meta boxes.
		remove_meta_box( 'submitdiv', IncPopupItem::POST_TYPE, 'side' );
		remove_meta_box( 'slugdiv', IncPopupItem::POST_TYPE, 'normal' );

		// Add our own meta boxes.
		add_meta_box(
			'meta-content',
			__( 'PopUp Contents', 'popover' ),
			array( 'IncPopup', 'meta_content' ),
			IncPopupItem::POST_TYPE,
			'normal',
			'high'
		);

		add_meta_box(
			'meta-appearance',
			__( 'Appearance', 'popover' ),
			array( 'IncPopup', 'meta_appearance' ),
			IncPopupItem::POST_TYPE,
			'normal',
			'high'
		);

		add_meta_box(
			'meta-behavior',
			__( 'Behavior', 'popover' ),
			array( 'IncPopup', 'meta_behavior' ),
			IncPopupItem::POST_TYPE,
			'normal',
			'high'
		);

		add_meta_box(
			'meta-rules',
			__( 'Displaying Conditions (optional)', 'popover' ),
			array( 'IncPopup', 'meta_rules' ),
			IncPopupItem::POST_TYPE,
			'advanced',
			'default'
		);

		add_meta_box(
			'meta-customcss',
			__( 'Custom CSS (optional)', 'popover' ),
			array( 'IncPopup', 'meta_customcss' ),
			IncPopupItem::POST_TYPE,
			'advanced',
			'default'
		);

		add_meta_box(
			'submitdiv',
			__( 'Save PopUp', 'popover' ),
			array( 'IncPopup', 'meta_submitdiv' ),
			IncPopupItem::POST_TYPE,
			'side',
			'low'
		);

		add_meta_box(
			'meta-side-ads',
			__( 'Want More PopUp Power?', 'popover' ),
			array( 'IncPopup', 'meta_sideads' ),
			IncPopupItem::POST_TYPE,
			'side',
			'low'
		);
	}

	/**
	 * Called before the post-edit form is rendered.
	 * We add the field "PopUp Title" above the form.
	 *
	 * @since  4.6.0
	 * @param  WP_Post $post The PopUp being edited.
	 */
	static public function form_title( $post ) {
		$popup = IncPopupDatabase::get( $post->ID );

		wp_nonce_field( 'save-popup', 'popup-nonce' );
		?>
		<div id="titlediv">
			<div id="titlewrap">
				<label for="po_name"><?php _e( 'PopUp Name (not displayed on the PopUp)', 'popover' ); ?></label>
				<input type="text" id="po_name" name="po_name" required
					value="<?php echo esc_attr( $popup->name ); ?>"
					placeholder="<?php _e( 'Name this PopUp', 'popover' ); ?>" />
			</div>
		</div>
		<?php
	}

	/**
	 * Renders the metabox: Content
	 *
	 * @since  4.6.0
	 * @param  WP_Post $post The PopUp being edited.
	 */
	static public function meta_content( $post ) {
		$popup = IncPopupDatabase::get( $post->ID );
		self::load_view( 'meta-content', compact( 'popup' ) );
	}

	/**
	 * Renders the metabox: Appearance
	 *
	 * @since  4.6.0
	 * @param  WP_Post $post The PopUp being edited.
	 */
	static public function meta_appearance( $post ) {
		$popup = IncPopupDatabase::get( $post->ID );
		self::load_view( 'meta-appearance', compact( 'popup' ) );
	}

	/**
	 * Renders the metabox: Behavior
	 *
	 * @since  4.6.0
	 * @param  WP_Post $post The PopUp being edited.
	 */
	static public function meta_behavior( $post ) {
		$popup = IncPopupDatabase::get( $post->ID );
		self::load_view( 'meta-behavior', compact( 'popup' ) );
	}

	/**
	 * Renders the metabox: Conditions
	 *
	 * @since  4.6.0
	 * @param  WP_Post $post The PopUp being edited.
	 */
	static public function meta_rules( $post ) {
		$popup = IncPopupDatabase::get( $post->ID );
		self::load_view( 'meta-rules', compact( 'popup' ) );
	}

	/**
	 * Renders the metabox: Custom CSS
	 *
	 * @since  4.7.0
	 * @param  WP_Post $post The PopUp being edited.
	 */
	static public function meta_customcss( $post ) {
		$popup = IncPopupDatabase::get( $post->ID );
		self::load_view( 'meta-customcss', compact( 'popup' ) );
	}

	/**
	 * Renders the metabox: SubmitDiv (Save, Preview)
	 *
	 * @since  4.6.0
	 * @param  WP_Post $post The PopUp being edited.
	 */
	static public function meta_submitdiv( $post ) {
		$popup = IncPopupDatabase::get( $post->ID );
		self::load_view( 'meta-submitdiv', compact( 'popup' ) );
	}

	/**
	 * Renders the metabox: Side Ads
	 *
	 * @since  4.6.0
	 * @param  WP_Post $post The PopUp being edited.
	 */
	static public function meta_sideads( $post ) {
		$popup = IncPopupDatabase::get( $post->ID );
		self::load_view( 'meta-side-ads', compact( 'popup' ) );
	}

	/**
	 * Save the popup data to database
	 *
	 * @since  4.6.0
	 * @param  int $post_id Post ID that was saved/created
	 * @param  WP_Post $post Post object that was saved/created
	 * @param  bool $update True means the post was updated (not created)
	 */
	static public function form_save( $post_id, $post, $update ) {
		$popup = IncPopupDatabase::get( $post_id );

		// Make sure the POST collection contains all required fields.
		if ( 0 !== lib3()->array->equip_post( 'popup-nonce', 'post_type', 'po-action' ) ) { return; }

		// Autosave is not processed.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }

		// The nonce is invalid.
		if ( ! wp_verify_nonce( $_POST['popup-nonce'], 'save-popup' ) ) { return; }

		// This save event is for a different post type... ??
		if ( IncPopupItem::POST_TYPE != $_POST['post_type'] ) { return; }

		// Global PopUp modified in a Network-Blog that is not the Main-Blog.
		if ( ! IncPopup::correct_level() ) { return; }

		// User does not have permissions for this.
		if ( ! current_user_can( IncPopupPosttype::$perms ) ) { return; }

		$action = $_POST['po-action'];
		$status = false;
		switch ( $action ) {
			case 'save':
				// Don't force a status...
				break;

			case 'activate':
				$status = 'active';
				break;

			case 'deactivate':
				$status = 'inactive';
				break;

			default:
				// Unknown action.
				return;
		}

		// Populate the popup.
		$data = self::prepare_formdata( $_POST );
		$data['id'] = $post_id;
		$data['order'] = $popup->order;
		if ( $status ) { $data['status'] = $status; }
		$popup->populate( $data );

		// Prevent infinite loop when saving.
		remove_action(
			'save_post_' . IncPopupItem::POST_TYPE,
			array( 'IncPopup', 'form_save' ),
			10
		);

		$popup->save();

		add_action(
			'save_post_' . IncPopupItem::POST_TYPE,
			array( 'IncPopup', 'form_save' ),
			10, 3
		);

		// Removes the 'message' from the redirect URL.
		add_filter(
			'redirect_post_location',
			array( 'IncPopup', 'form_redirect' ),
			10, 2
		);

		// Update the PopUp object in WP-Cache.
		IncPopupDatabase::get( $post_id, true );
	}

	/**
	 * Removes the 'message' param from redirect URL.
	 * This prevents the default WordPress update-notice to be displayed.
	 *
	 * @since  4.6.0
	 * @param  string $url The redirect URL.
	 * @param  int $post_id Which post was updated.
	 * @return string The modified redirect URL.
	 */
	static public function form_redirect( $url, $post_id ) {
		return esc_url_raw( remove_query_arg( 'message', $url ) );
	}
};

