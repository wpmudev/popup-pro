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
	 * Data added to the page via wp_localize_script()
	 * @var array
	 */
	protected $script_data = array();

	/**
	 * Lists all popups that have been enqueued via the footer loading method.
	 * @var array
	 */
	protected $enqueued = array();

	/**
	 * Returns the singleton instance of the popup (front end) class.
	 *
	 * @since  4.6
	 */
	static public function instance() {
		static $Inst = null;

		// In theory the "public" class does not need this, but to avoid
		// unexpected problems we initialize only after current user is known...
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

		// Init loading-process of the PopUp.
		add_action(
			'wp',   // "wp", not "init"!
			array( $this, 'init_public' )
		);

		/**
		 * Experimental hook to dynamically create popups on your site.
		 * Call the action hook `wdev-popup` with param 1 being the content and
		 * param 2 is an optional array of popup options.
		 *
		 * Note that currently this plays nice with existing popups if FOOTER
		 * loading method is used, but all other loading methods will interfere
		 * with this hook and only show either the dynamic or predefined popups.
		 *
		 * @since  4.7.1
		 * @param  string $content The popup contents (HTML code allowed).
		 * @param  array $options Optional. List of popup configuration options.
		 */
		add_action(
			'wdev-popup',
			array( $this, 'show_popup' ),
			10, 2
		);
	}

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
		$this->script_data['do'] = 'get_data';
		$this->script_data['ajax_data'] = array();

		// Find the current loading method.
		$cur_method = isset( $settings['loadingmethod'] ) ? $settings['loadingmethod'] : 'ajax';
		if ( empty( $cur_method ) ) { $cur_method = 'ajax'; }

		if ( isset( $_POST['_po_method_'] ) ) { $cur_method = $_POST['_po_method_']; }

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

				if ( isset( $_GET['action'] )
					&& 'inc_popup' == $_GET['action']
				) {
					$this->ajax_load_popup();
				}
				break;

			case 'footer':
				$this->load_method_footer();
				break;

			case 'raw': // Set via form field "_po_method_"
				$this->load_method_raw();
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
	 * Action handler that allows us to add a popup via WordPress hook.
	 *
	 * @since  4.7.1
	 * @param  string $contents The PopUp contents.
	 * @param  array $options PopUp options.
	 */
	public function show_popup( $contents, $options = array() ) {
		$this->script_data['popup'] = lib2()->array->get( $this->script_data['popup'] );

		$popup = new IncPopupItem();
		$data = lib2()->array->get( $options );
		$data['content'] = $contents;
		$popup->populate( $data );
		$popup->script_data['manual'] = true;

		// 1. Add the popup to the global popup-list.
		$this->popups[] = $popup;

		// 2. Enqueue the popup in the page footer.
		$item = $popup->get_script_data( false );
		unset( $item['html'] );
		unset( $item['styles'] );
		$this->script_data['popup'][] = $item;

		$this->load_scripts();
		$this->enqueue_footer();
	}

	/**
	 * Enqueues the PopUp javascripts and data.
	 *
	 * @since  4.6
	 */
	public function load_scripts() {
		static $Loaded = false;

		if ( ! did_action( 'wp' ) ) {
			// We have to make sure that wp is fully initialized:
			// Some rules that use filter 'popup-ajax-data' depend on this.
			add_action(
				'wp',
				array( $this, 'load_scripts' )
			);
			return;
		}

		if ( ! $Loaded ) {
			if ( is_array( $this->script_data ) && ! empty( $this->script_data ) ) {
				$popup_data = apply_filters( 'popup-ajax-data', $this->script_data );
				lib2()->ui->data( '_popup_data', $popup_data, 'front' );

				$popup_data['popup'] = lib2()->array->get( $popup_data['popup'] );
				foreach ( $popup_data['popup'] as $item ) {
					$this->enqueued[] = $item['html_id'];
				}
			}

			lib2()->ui->add( PO_JS_URL . 'public.min.js', 'front' );
			lib2()->ui->add( PO_CSS_URL . 'animate.min.css', 'front' );
		} else {
			if ( is_array( $this->script_data ) && is_array( $this->script_data['popup'] ) ) {
				foreach ( $this->script_data['popup'] as $popup ) {
					if ( in_array( $popup['html_id'], $this->enqueued ) ) {
						continue;
					}

					$script = 'window._popup_data.popup.push(' . json_encode( $popup ) . ')';
					lib2()->ui->script( $script );
					$this->enqueued[] = $popup['html_id'];
				}
			}
		}
		$Loaded = true;
	}


	/*==================================*\
	======================================
	==                                  ==
	==           LOAD METHODS           ==
	==                                  ==
	======================================
	\*==================================*/


	/**
	 * Load-Method: External
	 *
	 * IS AJAX
	 * IS ADMIN
	 *
	 * PopUp data is loaded via a normal WordPress ajax request, directed at
	 * the admin-ajax.php handler.
	 *
	 * @since  4.6
	 */
	protected function load_method_ajax() {
		global $pagenow;

		if ( ! in_array( $pagenow, array( 'wp-login.php', 'wp-register.php' ) ) ) {
			// Data is loaded via a normal WordPress ajax request.
			$this->script_data['ajaxurl'] = admin_url( 'admin-ajax.php' );
			$this->script_data['ajax_data']['orig_request_uri'] = $_SERVER['REQUEST_URI'];
			$this->load_scripts();
		}
	}

	/**
	 * Load-Method: Front/Frontloading
	 *
	 * NOT AJAX
	 * NOT ADMIN
	 *
	 * PopUp data is loaded in an ajax request. The ajax request is directed
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
			$this->script_data['ajax_data']['request_uri'] = $_SERVER['REQUEST_URI'];
			$this->load_scripts();
		}
	}

	/**
	 * Load-Method: Footer
	 *
	 * NOT AJAX
	 * NOT ADMIN
	 *
	 * The PopUp styles and html is directly injected into the webpage header
	 * and footer. The PopUp is ready when the page is loaded. No ajax request
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
		$_REQUEST['thereferrer'] = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '';
		$_REQUEST['thefrom'] = lib2()->net->current_url();

		// Populates $this->popups
		$this->select_popup();

		if ( empty( $this->popups ) ) { return; }

		$data = $this->get_popup_data();
		foreach ( $data as $item ) {
			if ( ! empty( $item['manual'] ) ) { continue; }
			if ( in_array( $item['html_id'], $this->enqueued ) ) { continue; }

			unset( $item['html'] );
			unset( $item['styles'] );
			$this->script_data['popup'][] = $item;
		}
		$this->load_scripts();

		$this->enqueue_footer();
	}

	/**
	 * Load-Method: Raw
	 *
	 * This is used when a form is submitted inside a PopUp - it means that we
	 * should only return the contents of the PopUp(s) and not the whole page.
	 * Set via form field "_po_method_".
	 *
	 * @since  4.6.1.2
	 */
	protected function load_method_raw() {
		/**
		 * Set up the rquest information from here.
		 * These values are used by some rules and need to be set manually here
		 * In an ajax request they would already be defined by the ajax url.
		 */
		$_REQUEST['thereferrer'] = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '';
		$_REQUEST['thefrom'] = lib2()->net->current_url();

		// Populates $this->popups
		$this->select_popup();

		if ( empty( $this->popups ) ) { die(); }

		echo '<div>';
		$this->show_footer();
		echo '</div>';

		die();
	}


	/*======================================*\
	==========================================
	==                                      ==
	==           HELPER FUNCTIONS           ==
	==                                      ==
	==========================================
	\*======================================*/


	/**
	 * Adds the wp_header/wp_footer actions to the action queue.
	 *
	 * @since  4.7.1
	 */
	protected function enqueue_footer() {
		static $Did_Enqueue = false;

		if ( $Did_Enqueue ) { return; }
		$Did_Enqueue = true;

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
		if ( empty( $this->popups ) ) { return; }

		$code = '';
		$data = $this->get_popup_data();
		foreach ( $data as $ind => $item ) {
			$code .= $item['styles'];
		}
		echo '<style type="text/css">' . $code . '</style>';
	}

	/**
	 * Used by "load_method_footer" to print the popup HTML code.
	 *
	 * @since  4.6
	 */
	public function show_footer() {
		if ( empty( $this->popups ) ) { return; }

		$code = '';
		$data = $this->get_popup_data();
		foreach ( $data as $ind => $item ) {
			$code .= $item['html'];
		}
		echo $code;
	}

};