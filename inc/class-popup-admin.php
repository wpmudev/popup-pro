<?php
// Load dependencies.
require_once PO_INC_DIR . 'class-popup-base.php';

/**
 * Defines the popup class for admin pages.
 *
 * @since  4.6
 */
class IncPopup extends IncPopupBase {

	/**
	 * Returns the singleton instance of the popup (admin) class.
	 *
	 * @since  4.6
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
	 * @since  4.6
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
	 * @since  4.6
	 */
	static public function setup_module_specific( $hook ) {
		if ( IncPopupItem::POST_TYPE === @$hook->post_type ) {
			// WordPress core scripts
			WDev()->add_js( 'jquery-ui-slider' );
			WDev()->add_js( 'jquery-ui-sortable' );

			WDev()->add_ui( 'core' );
			WDev()->add_ui( 'select' );

			WDev()->add_ui( PO_CSS_URL . 'popup-admin.css' );
			WDev()->add_ui( PO_JS_URL . 'popup-admin.min.js' );
			WDev()->add_ui( PO_JS_URL . 'public.min.js' ); // For Preview.

			if ( @$_REQUEST['post_status'] != 'trash' ) {
				WDev()->add_data(
					'po_bulk',
					array(
						'activate' => __( 'Activate', PO_LANG ),
						'deactivate' => __( 'Deactivate', PO_LANG ),
						'toggle' => __( 'Toggle activation', PO_LANG ),
					)
				);
			}

			// For Preview
			WDev()->add_data(
				'_popup_data',
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'do'      => 'get-data',
					'popup'   => '',
					'noinit'  => true,
					'preview' => true,
				)
			);


			// -- PopUp LIST -----------------------

			if ( 'edit' === @$hook->base ) {
				// Customize the columns in the popup list.
				add_filter(
					'manage_' . IncPopupItem::POST_TYPE . '_posts_columns',
					array( 'IncPopup', 'post_columns' )
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

			if ( 'post' === @$hook->base ) {
				WDev()->add_css( 'wp-color-picker' ); // WordPress core script
				WDev()->add_js( 'wp-color-picker' ); // WordPress core script

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
	 * @since  4.6
	 */
	static public function admin_menus() {
		// correct_level checks:
		// PO_GLOBAL is true .. We have to be on network-admin/main blog.
		// PO_GLOBAL is false .. we have to be NOT on network-admin.
		if ( ! self::correct_level() ) { return; }

		if ( is_network_admin() ) {
			if ( 'hide' == @$_REQUEST['popup_network'] ) {
				IncPopupDatabase::set_flag( 'network_dismiss', true );
				wp_safe_redirect( admin_url( 'network' ) );
				die();
			} else if ( 'show' == @$_REQUEST['popup_network'] ) {
				IncPopupDatabase::set_flag( 'network_dismiss', false );
			}

			if ( true == IncPopupDatabase::get_flag( 'network_dismiss' ) ) {
				return;
			}

			add_menu_page(
				__( 'PopUp', PO_LANG ),
				__( 'PopUp', PO_LANG ),
				IncPopupPosttype::$perms,
				IncPopupItem::POST_TYPE . '-list',
				array( 'IncPopup', 'network_menu_notice' ),
				PO_IMG_URL . 'icon.png',
				IncPopupPosttype::$menu_pos
			);

			add_submenu_page(
				IncPopupItem::POST_TYPE . '-list',
				__( 'Add New', PO_LANG ),
				__( 'Add New', PO_LANG ),
				IncPopupPosttype::$perms,
				IncPopupItem::POST_TYPE . '-create',
				array( 'IncPopup', 'network_menu_notice' )
			);

			add_submenu_page(
				IncPopupItem::POST_TYPE . '-list',
				__( 'Settings', PO_LANG ),
				__( 'Settings', PO_LANG ),
				IncPopupPosttype::$perms,
				IncPopupItem::POST_TYPE . '-settings',
				array( 'IncPopup', 'network_menu_notice' )
			);

			global $submenu;
			$submenu[IncPopupItem::POST_TYPE . '-list'][0][0] = _x( 'Global PopUps', 'Post Type General Name', PO_LANG );
		} else {
			add_submenu_page(
				'edit.php?post_type=' . IncPopupItem::POST_TYPE,
				__( 'Settings', PO_LANG ),
				__( 'Settings', PO_LANG ),
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
	 * @since  4.6
	 */
	static public function network_menu_notice() {
		include PO_VIEWS_DIR . 'network.php';
	}

	/**
	 * Handles all admin ajax calls.
	 *
	 * @since  4.6
	 */
	static public function handle_ajax() {
		$action = @$_POST['do'];

		switch ( $action ) {
			case 'order':
				$order = explode( ',', @$_POST['order'] );
				self::post_order( $order );
				break;

			default:
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
	 * @since  4.6
	 */
	static public function handle_settings_page() {
		include PO_VIEWS_DIR . 'settings.php';
	}

	/**
	 * Called every time the settings page is loaded. Saves changes.
	 *
	 * @since  4.6
	 */
	static public function handle_settings_update() {
		if ( @$_POST['action'] == 'updatesettings' ) {
			check_admin_referer( 'update-popup-settings' );

			$settings = array();
			$settings['loadingmethod'] = @$_POST['po_option']['loadingmethod'];
			$settings['geo_db'] = isset( $_POST['po_option']['geo_db'] );

			$rules = @$_POST['po_option']['rules'];
			if ( ! is_array( $rules ) ) { $rules = array(); }
			$settings['rules'] = array_keys( $rules );

			IncPopupDatabase::set_settings( $settings );

			WDev()->message( __( 'Your settings have been updated.', PO_LANG ) );
			$redirect_url = remove_query_arg( array( 'message', 'count' ), wp_get_referer() );
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
	 * @since  4.6
	 * @param  array $post_columns
	 * @return array
	 */
	static public function post_columns( $post_columns ) {

		$new_columns = array();

		// Only allow re-ordering when ALL popups are visible
		if ( empty( $_REQUEST['s'] ) && empty( $_REQUEST['post_status'] ) ) {
			$new_columns['po_order'] = '';
		}

		$new_columns['cb'] = $post_columns['cb'];
		$new_columns['po_name'] = __( 'PopUp Name', PO_LANG );
		$new_columns['po_cond'] = __( 'Conditions', PO_LANG );

		if ( 'trash' != @$_REQUEST['post_status'] ) {
			$new_columns['po_pos'] = __( 'Order', PO_LANG );
			$new_columns['po_state'] = __( 'Active', PO_LANG );
		}

		return $new_columns;
	}

	/**
	 * Outputs the contents of a specific column.
	 *
	 * @since  4.6
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
						'title' => __( 'Edit this PopUp', PO_LANG ),
						'label' => __( 'Edit', PO_LANG )
					);
				}

				if ( $can_edit && 'active' === $popup->status ) {
					$the_url = 'edit.php?post_type=%1$s&post_id=%2$s&action=deactivate';
					$the_url = admin_url( sprintf( $the_url, IncPopupItem::POST_TYPE, $post_id ) );
					$the_url = wp_nonce_url( $the_url, 'deactivate-post_' . $post_id );
					$actions['deactivate'] = array(
						'url' => $the_url,
						'title' => __( 'Deactivate this PopUp', PO_LANG ),
						'label' => __( 'Deactivate', PO_LANG )
					);
				}

				if ( $can_edit && 'inactive' === $popup->status ) {
					$the_url = 'edit.php?post_type=%1$s&post_id=%2$s&action=activate';
					$the_url = admin_url( sprintf( $the_url, IncPopupItem::POST_TYPE, $post_id ) );
					$the_url = wp_nonce_url( $the_url, 'activate-post_' . $post_id );
					$actions['activate'] = array(
						'url' => $the_url,
						'title' => __( 'Activate this PopUp', PO_LANG ),
						'label' => __( 'Activate', PO_LANG )
					);
				}

				$actions['popup_preview'] = array(
					'url' => '#',
					'title' => __( 'Preview this PopUp', PO_LANG ),
					'attr' => 'class="po-preview" data-id="' . $post_id . '"',
					'label' => __( 'Preview', PO_LANG )
				);

				if ( current_user_can( 'delete_post', $post_id ) ) {
					if ( 'trash' === $popup->status ) {
						$the_url = $post_type_object->_edit_link . '&amp;action=untrash';
						$the_url = admin_url( sprintf( $the_url, $post_id ) );
						$the_url = wp_nonce_url( $the_url, 'untrash-post_' . $post_id );
						$actions['untrash'] = array(
							'url' => $the_url,
							'title' => __( 'Restore this PopUp from the Trash', PO_LANG ),
							'label' => __( 'Restore', PO_LANG )
						);
					} elseif ( EMPTY_TRASH_DAYS ) {
						$actions['trash'] = array(
							'url' => get_delete_post_link( $post_id ),
							'title' => __( 'Move this PopUp to the Trash', PO_LANG ),
							'attr' => 'class="submitdelete"',
							'label' => __( 'Trash', PO_LANG )
						);
					}
					if ( 'trash' === $popup->status || ! EMPTY_TRASH_DAYS ) {
						$actions['delete'] = array(
							'url' => get_delete_post_link( $post_id, '', true ),
							'title' => __( 'Delete this PopUp permanently', PO_LANG ),
							'attr' => 'class="submitdelete"',
							'label' => __( 'Delete Permanently', PO_LANG )
						);
					}
				}

				if ( $can_edit ) : ?>
					<a href="<?php echo esc_url( get_edit_post_link( $post_id ) ); ?>"
						title="<?php _e( 'Edit this PopUp', PO_LANG ); ?>">
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
					<a href="<?php echo esc_url( @$item['url'] ); ?>"
						title="<?php echo esc_attr( @$item['title'] ); ?>"
						<?php echo '' . @$item['attr']; ?>>
						<?php echo esc_html( @$item['label'] ); ?>
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
					<span class="rule-always"><?php _e( 'Always Show PopUp', PO_LANG ); ?></span>
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
	 * @since  4.6
	 * @param  array $views Default filters.
	 * @return array Modified filters.
	 */
	static public function post_views( $views ) {
		$new_views = array();
		$stati = array(
			'all' => __( 'All <span class="count">(%1$s)</span>', PO_LANG ),
			'publish' => __( 'Active <span class="count">(%1$s)</span>', PO_LANG ),
			'draft' => __( 'Inactive <span class="count">(%1$s)</span>', PO_LANG ),
			'trash' => __( 'Trash <span class="count">(%1$s)</span>', PO_LANG ),
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

			$new_views[$status] =
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
	 * @since  4.6
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
	 * @since  4.6
	 * @see    wp-admin/edit.php
	 * @param  array $messages Array of messages, by post-type.
	 * @param  array $counts
	 * @return array The modified $messages array
	 */
	static public function post_update_messages( $messages, $counts ) {
		$messages[IncPopupItem::POST_TYPE] = array(
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
	 * @since  4.6
	 */
	static public function post_list_edit() {
		$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
		$action = $wp_list_table->current_action();

		if ( $action ) {
			if ( 'list' === @$_REQUEST['mode'] ) {
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
						$msg = __( 'One PopUp activated', PO_LANG ) :
						$msg = __( '%1$s PopUps activated', PO_LANG );
						break;

					case 'deactivate':
						1 === $count ?
						$msg = __( 'One PopUp deactivated', PO_LANG ) :
						$msg = __( '%1$s PopUps deactivated', PO_LANG );
						break;

					case 'toggle':
						1 === $count ?
						$msg = __( 'One PopUp toggled', PO_LANG ) :
						$msg = __( '%1$s PopUps toggled', PO_LANG );
						break;
				}

				if ( $count > 0 && ! empty( $msg ) ) {
					WDev()->message( sprintf( $msg, $count ) );
				}
			}
			else {
				// ----- Custom row-action.
				$nonce = @$_REQUEST['_wpnonce'];
				$post_id = absint( @$_REQUEST['post_id'] );
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

				$back_url = remove_query_arg( array( 'action', 'post_id', '_wpnonce' ) );
				wp_safe_redirect( $back_url );
				die();
			}
		}
	}

	/**
	 * Modify the main WP query to avoid pagination on the popup list and sort
	 * the popup list by the popup-order.
	 *
	 * @since  4.6
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
	 * @since  4.6
	 * @param  int $value Default value set in Database
	 * @param  string $post_type
	 * @return int Customized value
	 */
	static public function post_item_per_page( $value, $post_type ) {
		if ( $post_type == IncPopupItem::POST_TYPE ) {
			// Setting to -1 works, but will not display text "17 items" in the top/right corner.
			$value = 100000;
		}
		return $value;
	}

	/**
	 * Remove the posts-per-page filter from screen options.
	 *
	 * @since  4.6
	 */
	static public function post_screenoptions() {
		$screen = get_current_screen();
		$screen->add_option( 'per_page', false );
	}

	/**
	 * Takes an array as input and updates the order of all popups according to
	 * the definition in the array.
	 *
	 * @since  4.6
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
	 * Save the popup data to database
	 *
	 * @since  4.6
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
	 * @since  4.6
	 */
	static protected function form_check_actions() {
		$popup_id = absint( @$_REQUEST['post'] );
		$action = @$_REQUEST['do'];

		if ( empty( $popup_id ) || empty( $action ) ) { return; }

		switch ( $action ) {
			case 'duplicate':
				$item = IncPopupDatabase::get( $popup_id );
				$item->id = 0;
				$item->name = '(Copy) ' . $item->name;
				$item->save();

				// Show the new item in the editor.
				$new_url = remove_query_arg( array( 'post', 'do' ) );
				$new_url = add_query_arg( array( 'post' => $item->id ), $new_url );
				wp_safe_redirect( $new_url );
				die();

				break;
		}
	}

	/**
	 * Register custom metaboxes for the PopUp editor
	 *
	 * @since  4.6
	 * @param  WP_Post $post The PopUp being edited.
	 */
	static public function form_metabox( $post ) {
		// Remove core meta boxes.
		remove_meta_box( 'submitdiv', IncPopupItem::POST_TYPE, 'side' );
		remove_meta_box( 'slugdiv', IncPopupItem::POST_TYPE, 'normal' );

		// Add our own meta boxes.
		add_meta_box(
			'meta-content',
			__( 'PopUp Contents', PO_LANG ),
			array( 'IncPopup', 'meta_content' ),
			IncPopupItem::POST_TYPE,
			'advanced',
			'high'
		);

		add_meta_box(
			'meta-appearance',
			__( 'Appearance', PO_LANG ),
			array( 'IncPopup', 'meta_appearance' ),
			IncPopupItem::POST_TYPE,
			'advanced',
			'high'
		);

		add_meta_box(
			'meta-behavior',
			__( 'Behavior', PO_LANG ),
			array( 'IncPopup', 'meta_behavior' ),
			IncPopupItem::POST_TYPE,
			'advanced',
			'high'
		);

		add_meta_box(
			'meta-rules',
			__( 'Displaying Conditions (optional)', PO_LANG ),
			array( 'IncPopup', 'meta_rules' ),
			IncPopupItem::POST_TYPE,
			'advanced',
			'default'
		);

		add_meta_box(
			'submitdiv',
			__( 'Save PopUp', PO_LANG ),
			array( 'IncPopup', 'meta_submitdiv' ),
			IncPopupItem::POST_TYPE,
			'side',
			'low'
		);
	}

	/**
	 * Called before the post-edit form is rendered.
	 * We add the field "PopUp Title" above the form.
	 *
	 * @since  4.6
	 * @param  WP_Post $post The PopUp being edited.
	 */
	static public function form_title( $post ) {
		$popup = IncPopupDatabase::get( $post->ID );

		wp_nonce_field( 'save-popup', 'popup-nonce' );
		?>
		<div id="titlediv">
			<div id="titlewrap">
				<label for="po_name"><?php _e( 'PopUp Name (not displayed on the PopUp)', PO_LANG ); ?></label>
				<input type="text" id="po_name" name="po_name" required
					value="<?php echo esc_attr( $popup->name ); ?>"
					placeholder="<?php _e( 'Name this PopUp', PO_LANG ); ?>" />
			</div>
		</div>
		<?php
	}

	/**
	 * Renders the metabox: Content
	 *
	 * @since  4.6
	 * @param  WP_Post $post The PopUp being edited.
	 */
	static public function meta_content( $post ) {
		$popup = IncPopupDatabase::get( $post->ID );
		include PO_VIEWS_DIR . 'meta-content.php';
	}

	/**
	 * Renders the metabox: Appearance
	 *
	 * @since  4.6
	 * @param  WP_Post $post The PopUp being edited.
	 */
	static public function meta_appearance( $post ) {
		$popup = IncPopupDatabase::get( $post->ID );
		include PO_VIEWS_DIR . 'meta-appearance.php';
	}

	/**
	 * Renders the metabox: Behavior
	 *
	 * @since  4.6
	 * @param  WP_Post $post The PopUp being edited.
	 */
	static public function meta_behavior( $post ) {
		$popup = IncPopupDatabase::get( $post->ID );
		include PO_VIEWS_DIR . 'meta-behavior.php';
	}

	/**
	 * Renders the metabox: Conditions
	 *
	 * @since  4.6
	 * @param  WP_Post $post The PopUp being edited.
	 */
	static public function meta_rules( $post ) {
		$popup = IncPopupDatabase::get( $post->ID );
		include PO_VIEWS_DIR . 'meta-rules.php';
	}

	/**
	 * Renders the metabox: SubmitDiv (Save, Preview)
	 *
	 * @since  4.6
	 * @param  WP_Post $post The PopUp being edited.
	 */
	static public function meta_submitdiv( $post ) {
		$popup = IncPopupDatabase::get( $post->ID );
		include PO_VIEWS_DIR . 'meta-submitdiv.php';
	}

	/**
	 * Save the popup data to database
	 *
	 * @since  4.6
	 * @param  int $post_id Post ID that was saved/created
	 * @param  WP_Post $post Post object that was saved/created
	 * @param  bool $update True means the post was updated (not created)
	 */
	static public function form_save( $post_id, $post, $update ) {
		$popup = IncPopupDatabase::get( $post_id );

		// Autosave is not processed.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }

		// The nonce is invalid.
		if ( ! wp_verify_nonce( @$_POST['popup-nonce'], 'save-popup' ) ) { return; }

		// This save event is for a different post type... ??
		if ( IncPopupItem::POST_TYPE != @$_POST['post_type'] ) { return; }

		// Global PopUp modified in a Network-Blog that is not the Main-Blog.
		if ( ! IncPopup::correct_level() ) { return; }

		// User does not have permissions for this.
		if ( ! current_user_can( IncPopupPosttype::$perms ) ) { return; }

		$action = @$_POST['po-action'];
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
	}

	/**
	 * Removes the 'message' param from redirect URL.
	 * This prevents the default WordPress update-notice to be displayed.
	 *
	 * @since  4.6
	 * @param  string $url The redirect URL.
	 * @param  int $post_id Which post was updated.
	 * @return string The modified redirect URL.
	 */
	static public function form_redirect( $url, $post_id ) {
		return remove_query_arg( 'message', $url );
	}

};