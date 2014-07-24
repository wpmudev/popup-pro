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

		// Save changes from settings page.
		add_action(
			'load-inc_popup_page_settings',
			array( 'IncPopup', 'handle_settings_update' )
		);

		// Save changes from addons page (activate/deactivate).
		add_action(
			'load-inc_popup_page_addons',
			array( 'IncPopup', 'handle_addons_update' )
		);

		add_action(
			'current_screen',
			array( 'IncPopup', 'setup_module_specific' )
		);
	}

	/**
	 * Initializes stuff that is only needed on the plugin screen
	 *
	 * @since  4.6
	 */
	static public function setup_module_specific( $hook ) {
		if ( IncPopupItem::POST_TYPE === @$hook->post_type ) {
			TheLib::add_ui( PO_CSS_URL . 'popup-admin.css' );
		}
	}

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

	/**
	 * Displays the settings page
	 *
	 * @since  4.6
	 */
	static public function handle_settings_page() {
		include PO_VIEWS_DIR . 'settings.php';
	}

	/**
	 * Displays the addons page
	 *
	 * @since  4.6
	 */
	static public function handle_addons_page() {
		include PO_VIEWS_DIR . 'addons.php';
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

};