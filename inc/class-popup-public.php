<?php
// Load dependencies.
require_once PO_INC_DIR . 'class-popup-base.php';

/**
 * Defines the popup class for front end pages
 *
 * @since  4.6
 */
class IncPopup extends IncPopupBase {

	/**
	 * Defines the current popup that is displayed.
	 * Used by load_method_footer
	 * @var IncPopupItem
	 */
	protected $popup = null;

	/**
	 * Data added to the page via wp_localize_script()
	 * @var array
	 */
	protected $script_data = array();

	/**
	 * Returns the singleton instance of the popup (front end) class.
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

		// Init loading-process of the pop up.
		add_action(
			'init',
			array( $this, 'init_public' )
		);

		// Ajax handlers to load Pop Up data (for logged in users).
		add_action(
			'wp_ajax_inc_popup',
			array( 'IncPopup', 'ajax_load_popup' )
		);

		// Ajax handlers to load Pop Up data (for guests).
		add_action(
			'wp_ajax_nopriv_inc_popup',
			array( 'IncPopup', 'ajax_load_popup' )
		);
	}

	/**
	 * Loads the active Add-On files.
	 *
	 * @since  4.6
	 */
	protected function load_addons() {
		$active = get_option( 'popover_activated_addons', array() );
		$active = (array) $active;

		if ( empty( $active ) ) { return; }

		// $available uses apply_filter to customize the results:
		$available = $this->get_addons();

		foreach ( $available as $addon ) {
			$path = PO_INC_DIR . 'addons/'. $addon;
			if ( in_array( $addon, $active ) && file_exists( $path ) ) {
				include_once $path;
			}
		}
	}


	/*==================================*\
	======================================
	==                                  ==
	==           LOAD METHODS           ==
	==                                  ==
	======================================
	\*==================================*/


	/**
	 * Initialize the public part of the plugin on every front-end page:
	 * Determine how the popup is loaded.
	 *
	 * @since  4.6
	 */
	public function init_public() {
		// Load the active add-ons.
		$this->load_addons();

		// Load plugin settings.
		$settings = IncPopupDatabase::get_option(
			'popover-settings',
			array( 'loadingmethod' => 'ajax' )
		);

		// Initialize javascript-data.
		$this->script_data['ajaxurl'] = '';
		$this->script_data['do'] = 'get-data';

		// Find the current loading method.
		$cur_method = @$settings['loadingmethod'];
		if ( empty( $cur_method ) ) { $cur_method = 'ajax'; }

		/*
		 * Apply the specific loading method to include the popup on the page.
		 * Details to the loading methods are documented in the header comment
		 * of the "load_method_xyz()" functions.
		 */
		switch ( $cur_method ) {
			case 'ajax': // former 'external'
				$this->load_method_ajax();
				break;

			case 'front': // former 'frontloading'
				$this->load_method_front();

				if ( @$_GET['action'] == 'inc_popup' ) {
					$this->ajax_load_popup();
				}
				break;

			case 'footer':
				$this->load_method_footer();
				break;

			default:
				/**
				 * Custom loading handler can be processed by an add-on.
				 */
				do_action( 'popover-init-loading-method', $cur_method );
				do_action( 'popover-ajax-loading-method', $cur_method, $this );

				// Legacy action handler.
				do_action( 'popover-init-loading_method', $cur_method );
				do_action( 'popover-ajax-loading_method', $cur_method, $this );
				break;
		}
	}

	/**
	 * Enqueues the Pop Up javascripts and data.
	 *
	 * @since  4.6
	 */
	protected function load_scripts() {
		wp_register_script(
			'popover-public',
			PO_JS_URL . 'public.min.js',
			array( 'jquery' ),
			false,
			true
		);

		wp_localize_script(
			'popover-public',
			'_popover_data',
			$this->script_data
		);

		wp_enqueue_script( 'popover-public' );
	}

	/**
	 * Load-Method: External
	 *
	 * Pop Up data is loaded via a normal WordPress ajax request, directed at
	 * the admin-ajax.php handler.
	 *
	 * @since  4.6
	 */
	protected function load_method_ajax() {
		global $pagenow;

		if ( ! in_array( $pagenow, array( 'wp-login.php', 'wp-register.php' ) ) ) {
			// Data is loaded via a normal WordPress ajax request.
			$this->script_data['ajaxurl'] = admin_url( 'admin-ajax.php' );
			$this->load_scripts();
		}
	}

	/**
	 * Load-Method: Front/Frontloading
	 *
	 * Pop Up data is loaded in an ajax request. The ajax request is directed
	 * at the same URL that is currently displayed, but a few URL-parameters are
	 * added to instruct the plugin to return popup-data instead the the normal
	 * webpage.
	 *
	 * @since  4.6
	 */
	protected function load_method_front() {
		global $pagenow;

		if ( ! in_array( $pagenow, array( 'wp-login.php', 'wp-register.php' ) ) ) {
			/*
			 * Data is loaded via the public URL of the page, simply by adding
			 * some URL parameters.
			 */
			$this->script_data['ajaxurl'] = '';
			$this->load_scripts();
		}
	}

	/**
	 * Load-Method: Footer
	 *
	 * The Pop Up styles and html is directly injected into the webpage header
	 * and footer. The Pop Up is ready when the page is loaded. No ajax request
	 * is made.
	 *
	 * @since  4.6
	 */
	protected function load_method_footer() {
		global $popoverajax;

		// Set up the rquest information from here.
		// This is passed in using the standard JS interface so we need to fake it.
		$_REQUEST['thereferrer'] = @$_SERVER['HTTP_REFERER'];
		$_REQUEST['thefrom'] = TheLib::current_url();

		$this->popup = $popoverajax->get_popup_data();

		if ( isset( $this->popup['name'] ) && $this->popup['name'] != 'nopopover' ) {
			$data = $this->popup;

			// These two are included via wp_head and wp_foot below.
			unset( $data['style'] );
			unset( $data['html'] );

			$this->script_data['popup'] = $data;
			$this->load_scripts();

			add_action(
				'wp_head',
				array( 'IncPopup', 'show_header')
			);

			add_action(
				'wp_footer',
				array( 'IncPopup', 'show_footer')
			);
		}
	}

	/**
	 * Used by "load_method_footer" to print the popup CSS styles.
	 *
	 * @since  4.6
	 */
	static public function show_header() {
		?>
		<style type="text/css"><?php echo @$this->popup['style']; ?></style>
		<?php
	}

	/**
	 * Used by "load_method_footer" to print the popup HTML code.
	 *
	 * @since  4.6
	 */
	static public function show_footer() {
		echo @$this->popup['html'];
	}

	/**
	 * Returns the popup content as JSON object and then ends the request.
	 *
	 * @since  4.6
	 */
	static public function ajax_load_popup() {
		$action = @$_REQUEST['do'];

		switch ( $action ) {
			case 'get-data':
				$data = $this->get_popup_data();
				echo 'po_data(' . json_encode( $data ) . ')';
				die();
		}
	}


	/*==================================*\
	======================================
	==                                  ==
	==           SELECT POPUP           ==
	==                                  ==
	======================================
	\*==================================*/


	protected function get_popup_data() {
		$items = $this->find_popups();

		if ( empty( $items ) ) {
			return array( 'name' => 'nopopover' );
		}

		$popup = reset( $items );

		// return the popover to the calling function
		$data = array(
			'popover_id' => $popup->id,
			'close_hide' => $popup->close_hides,
			'expiry' => $popup->hide_expire,
		);

		$data['name'] = 'a' . md5( date( 'd' ) ) . '-po';

		switch ( $popup->delay_type ) {
			case 'm': $data['delay'] = $popup->delay * 60000;
			case 's':
			default:  $data['delay'] = $popup->delay * 1000;
		}

		$data['html'] = $popup->load_html();
		$data['html'] = $popup->load_styles();

		// Increase the popup counter.
		$count = absint( @$_COOKIE['popover_view_' . COOKIEHASH] );
		$count += 1;
		if ( ! headers_sent() ) {
			setcookie(
				'popover_view_' . COOKIEHASH,
				$count ,
				time() + 30000000,
				COOKIEPATH,
				COOKIE_DOMAIN
			);
		}

		// Filter and return the popup details
		return apply_filters( 'popover-output-popover', $data, $popup );
	}

	/**
	 * Returns an array of Pop Up objects that should be displayed for the
	 * current page/user. The Pop Ups are in the order in which they are defined
	 * in the admin list.
	 *
	 * @since  4.6
	 * @return array List of all popups that fit the current page.
	 */
	protected function find_popups() {
		$popup_id = false;
		$popups = array();

		if ( isset( $_REQUEST['po_id'] ) ) {
			// Check for forced popup.
			$popup_id = absint( $_REQUEST['po_id'] );
			$active_ids = array( $popup_id );
		} else {
			$active_ids = IncPopupDatabase::get_active_ids();
		}

		foreach ( $active_ids as $id ) {
			$popup = IncPopupDatabase::get( $id );

			if ( $popup_id ) {
				// Forced popup ignores all conditions.
				$show = true;
			} else {
				// Apply the conditions to decide if the popup should be displayed.
				$show = apply_filters( 'popup-apply-rules', true, $popup );
			}

			// Stop here if the popup failed in some conditions.
			if ( ! $show ) { continue; }

			// Stop here if the user did choose to hide the popup.
			if ( $this->is_hidden( $id ) ) { continue; }

			$popups[] = $popup;
		}

		return $popups;
	}


	/**
	 * Tests if a popup is marked as hidden ("never see this message again").
	 * This flag is stored as a cookie on the users computer.
	 *
	 * @since  4.6
	 * @param  int $id Pop Up ID
	 * @return bool
	 */
	protected function is_hidden( $id ) {
		$name = 'popover_never_view_' . $id;
		return isset( $_COOKIE[$name] );
	}

};