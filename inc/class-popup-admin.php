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

		// -- SETTINGS --------------------------

		// Save changes from settings page.
		add_action(
			'load-inc_popup_page_settings',
			array( 'IncPopup', 'handle_settings_update' )
		);

		// -- ADD ONS ---------------------------

		// Save changes from addons page (activate/deactivate).
		add_action(
			'load-inc_popup_page_addons',
			array( 'IncPopup', 'handle_addons_update' )
		);

		// -- POP UP LIST -----------------------

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
	}

	/**
	 * Initializes stuff that is only needed on the plugin screen
	 *
	 * @since  4.6
	 */
	static public function setup_module_specific( $hook ) {
		if ( IncPopupItem::POST_TYPE === @$hook->post_type ) {
			TheLib::add_ui( 'core' );
			TheLib::add_ui( PO_CSS_URL . 'popup-admin.css' );
			TheLib::add_ui( PO_JS_URL . 'popup-admin.min.js' );

			// -- POP UP EDITOR -----------------

			// Display the "Pop Up Title" field in top of the form.
			add_action(
				'edit_form_after_title',
				array( 'IncPopup', 'form_title' )
			);

			// Add custom meta-boxes to the popup-editor
			add_action(
				'add_meta_boxes_' . IncPopupItem::POST_TYPE,
				array( 'IncPopup', 'form_metabox' )
			);
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
		add_submenu_page(
			'edit.php?post_type=' . IncPopupItem::POST_TYPE,
			__( 'Manage Add-ons Plugins', PO_LANG ),
			__( 'Add-ons', PO_LANG ),
			IncPopupPosttype::$perms,
			'addons',
			array( 'IncPopup', 'handle_addons_page' )
		);

		add_submenu_page(
			'edit.php?post_type=' . IncPopupItem::POST_TYPE,
			__( 'Settings', PO_LANG ),
			__( 'Settings', PO_LANG ),
			IncPopupPosttype::$perms,
			'settings',
			array( 'IncPopup', 'handle_settings_page' )
		);
	}

	/**
	 * Displays a predefined message in the admin screen.
	 *
	 * @since  4.6
	 * @param  int $id Message-ID.
	 * @param  array $args Placeholders that may be used in the message.
	 */
	static protected function show_message( $id, $args ) {
		$messages = array(
			1 => 'Your settings have been updated.',
			2 => '%1$d Add-on(s) activated.',
			3 => '%1$d Add-on(s) deactivated.',
			4 => '%1$d Add-on(s) toggled.',
		);
		$msg = __( @$messages[$id], PO_LANG );
		$msg = vsprintf( $msg, $args );
		TheLib::message( $msg );
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
		if ( is_numeric( @$_GET['message'] ) ) {
			self::show_message( $_GET['message'] );
		}

		if ( @$_POST['action'] == 'updatesettings' ) {
			check_admin_referer( 'update-popover-settings' );
			update_popover_option( 'popover-settings', $_POST );
			wp_safe_redirect( add_query_arg( 'message', 1, wp_get_referer() ) );
		}
	}


	/*=============================*\
	=================================
	==                             ==
	==           ADD ONS           ==
	==                             ==
	=================================
	\*=============================*/


	/**
	 * Displays the addons page
	 *
	 * @since  4.6
	 */
	static public function handle_addons_page() {
		include PO_VIEWS_DIR . 'addons.php';
	}

	/**
	 * Called every time the addons page is loaded. Saves changes.
	 *
	 * @since  4.6
	 */
	static public function handle_addons_update() {
		if ( is_numeric( @$_GET['message'] ) ) {
			self::show_message( $_GET['message'], array( @$_GET['count'] ) );
		}

		$action = false;
		if ( isset( $_REQUEST['do_action_1'] ) ) {
			$action = @$_REQUEST['action_1'];
		} else if ( isset( $_REQUEST['do_action_2'] ) ) {
			$action = @$_REQUEST['action_2'];
		} else {
			$action = @$_REQUEST['action'];
		}
		if ( empty( $action ) ) { return; }

		$keys = @$_REQUEST['addon'];
		if ( is_string( $keys ) ) {
			$keys = array( $keys );
		}
		check_admin_referer( 'popup-addon' );
		if ( empty( $keys ) ) { return; }

		$active_addons = get_option( 'popover_activated_addons', array() );
		$count = 0;

		foreach ( $keys as $key ) {
			$addon_ind = array_search( $key, $active_addons );
			$is_active = false !== $addon_ind;

			switch ( $action ) {
				case 'activate':
					$message = 2;
					if ( ! $is_active ) {
						$active_addons[] = $key;
						$count += 1;
					}
					break;

				case 'deactivate':
					$message = 3;
					if ( $is_active ) {
						unset( $active_addons[$addon_ind] );
						$count += 1;
					}
					break;

				case 'toggle':
					$message = 4;
					if ( $is_active ) {
						unset( $active_addons[$addon_ind] );
					} else {
						$active_addons[] = $key;
					}
					$count += 1;
					break;
			}
		}

		if ( $count > 0 ) {
			update_option( 'popover_activated_addons', array_unique( $active_addons ) );

			$args = array(
				'message' => $message,
				'count' => $count,
			);
			wp_safe_redirect( add_query_arg( $args, wp_get_referer() ) );
		}
	}


	/*=================================*\
	=====================================
	==                                 ==
	==           POP UP LIST           ==
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
		$new_columns['cb'] = $post_columns['cb'];
		$new_columns['po_name'] = __( 'Pop Up Name', PO_LANG );
		$new_columns['po_cond'] = __( 'Conditions', PO_LANG );
		$new_columns['po_state'] = __( 'Active', PO_LANG );
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
				if ( 'trash' == $popup->status ) : ?>
					<span class="the-title"><?php echo esc_html( $popup->name ); ?></span>
				<?php else : ?>
					<a href="<?php echo esc_url( get_edit_post_link( $post_id ) ); ?>">
						<span class="the-title"><?php echo esc_html( $popup->name ); ?></span>
					</a>
				<?php endif;
				break;

			case 'po_cond':
				foreach ( $popup->checks as $key ) :
					$label = IncPopupItem::condition_label( $key );
					?>
					<span class="rule"><?php echo esc_html( $label ); ?></span>
					<?php
				endforeach;
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


	/*===================================*\
	=======================================
	==                                   ==
	==           POP UP EDITOR           ==
	==                                   ==
	=======================================
	\*===================================*/


	/**
	 * Register custom metaboxes for the Pop Up editor
	 *
	 * @since  4.6
	 * @param  WP_Post $post The Pop Up being edited.
	 */
	static public function form_metabox( $post ) {
		// Remove core meta boxes.
		remove_meta_box( 'submitdiv', IncPopupItem::POST_TYPE, 'side' );
		remove_meta_box( 'slugdiv', IncPopupItem::POST_TYPE, 'normal' );

		// Add our own meta boxes.
		add_meta_box(
			'meta-content',
			__( 'Pop Up Contents', PO_LANG ),
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
			__( 'Save Pop Up', PO_LANG ),
			array( 'IncPopup', 'meta_submitdiv' ),
			IncPopupItem::POST_TYPE,
			'side',
			'low'
		);
	}

	/**
	 * Called before the post-edit form is rendered.
	 * We add the field "Pop Up Title" above the form.
	 *
	 * @since  4.6
	 * @param  WP_Post $post The Pop Up being edited.
	 */
	static public function form_title( $post ) {
		$popup = IncPopupDatabase::get( $post->ID );
		?>
		<div id="titlediv">
			<div id="titlewrap">
				<label for="popup_name"><?php _e( 'Pop Up Name (not displayed on the Pop Up)', PO_LANG ); ?></label>
				<input type="text" id="popup_name" name="popup_name" required
					value="<?php echo esc_attr( $popup->name ); ?>"
					placeholder="<?php _e( 'Name this Pop Up', PO_LANG ); ?>" />
			</div>
		</div>
		<?php
	}

	/**
	 * Renders the metabox: Content
	 *
	 * @since  4.6
	 * @param  WP_Post $post The Pop Up being edited.
	 */
	static public function meta_content( $post ) {
		$popup = IncPopupDatabase::get( $post->ID );
		include PO_VIEWS_DIR . 'meta-content.php';
	}

	/**
	 * Renders the metabox: Appearance
	 *
	 * @since  4.6
	 * @param  WP_Post $post The Pop Up being edited.
	 */
	static public function meta_appearance( $post ) {
		$popup = IncPopupDatabase::get( $post->ID );
		include PO_VIEWS_DIR . 'meta-appearance.php';
	}

	/**
	 * Renders the metabox: Behavior
	 *
	 * @since  4.6
	 * @param  WP_Post $post The Pop Up being edited.
	 */
	static public function meta_behavior( $post ) {
		$popup = IncPopupDatabase::get( $post->ID );
		include PO_VIEWS_DIR . 'meta-behavior.php';
	}

	/**
	 * Renders the metabox: Conditions
	 *
	 * @since  4.6
	 * @param  WP_Post $post The Pop Up being edited.
	 */
	static public function meta_rules( $post ) {
		$popup = IncPopupDatabase::get( $post->ID );
		include PO_VIEWS_DIR . 'meta-rules.php';
	}

	/**
	 * Renders the metabox: SubmitDiv (Save, Preview)
	 *
	 * @since  4.6
	 * @param  WP_Post $post The Pop Up being edited.
	 */
	static public function meta_submitdiv( $post ) {
		$popup = IncPopupDatabase::get( $post->ID );
		include PO_VIEWS_DIR . 'meta-submitdiv.php';
	}

};