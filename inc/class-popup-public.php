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
			'wp',   // "wp", not "init"!
			array( $this, 'init_public' )
		);
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
		// Load plugin settings.
		$settings = IncPopupDatabase::get_settings();

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
				do_action( 'popup-init-loading-method', $cur_method, $this );
				break;
		}
	}

	/**
	 * Enqueues the Pop Up javascripts and data.
	 *
	 * @since  4.6
	 */
	public function load_scripts() {
		if ( ! did_action( 'wp' ) ) {
			// We have to make sure that wp is fully initialized:
			// Some rules that use filter 'popup-ajax-data' depend on this.
			add_action(
				'wp',
				array( __CLASS__, 'load_scripts' )
			);
			return;
		}

		wp_register_script(
			'popup-public',
			PO_JS_URL . 'public.min.js',
			array( 'jquery' ),
			false,
			true
		);

		$popup_data = apply_filters( 'popup-ajax-data', $this->script_data );

		wp_localize_script(
			'popup-public',
			'_popup_data',
			$popup_data
		);

		wp_enqueue_script( 'popup-public' );
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
		/**
		 * Set up the rquest information from here.
		 * These values are used by some rules and need to be set manually here
		 * In an ajax request they would already be defined by the ajax url.
		 */
		$_REQUEST['thereferrer'] = @$_SERVER['HTTP_REFERER'];
		$_REQUEST['thefrom'] = WDev()->current_url();

		// Populates $this->popup
		$this->select_popup();

		if ( empty( $this->popup ) ) { return; }

		$data = $this->popup->get_script_data();
		unset( $data['html'] );
		unset( $data['styles'] );
		$this->script_data['popup'] = $data;
		$this->load_scripts();

		add_action(
			'wp_head',
			array( $this, 'show_header')
		);

		add_action(
			'wp_footer',
			array( $this, 'show_footer')
		);
	}

	/**
	 * Used by "load_method_footer" to print the popup CSS styles.
	 *
	 * @since  4.6
	 */
	public function show_header() {
		if ( empty( $this->popup ) ) { return; }

		$data = $this->popup->get_script_data();
		echo '<style type="text/css">' . $data['styles'] . '</style>';
	}

	/**
	 * Used by "load_method_footer" to print the popup HTML code.
	 *
	 * @since  4.6
	 */
	public function show_footer() {
		if ( empty( $this->popup ) ) { return; }

		$data = $this->popup->get_script_data();
		echo '' . $data['html'];
	}

};