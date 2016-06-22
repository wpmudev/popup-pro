<?php
// Load dependencies.
require_once PO_INC_DIR . 'class-popup-item.php';
require_once PO_INC_DIR . 'class-popup-database.php';
require_once PO_INC_DIR . 'class-popup-posttype.php';
require_once PO_INC_DIR . 'class-popup-rule.php';

// Load extra functions.
require_once PO_INC_DIR . 'addons/class-popup-addon-headerfooter.php';
require_once PO_INC_DIR . 'addons/class-popup-addon-anonymous.php';
require_once PO_INC_DIR . 'addons/class-popup-addon-geo-db.php';

/**
 * Defines common functions that are used in admin and frontpage.
 */
abstract class IncPopupBase {

	/**
	 * Holds the IncPopupDatabase instance.
	 * @var IncPopupDatabase
	 */
	protected $db = null;

	/**
	 * List of PopUps fetched by select_popup()
	 * @var array [IncPopupItem]
	 */
	protected $popups = array();

	/**
	 * Data collection for compatibility with other plugins.
	 * @var array
	 */
	protected $compat_data = array();


	/**
	 * Constructor
	 */
	protected function __construct() {
		$this->db = IncPopupDatabase::instance();

		// Prepare the URL
		if ( ! empty( $_REQUEST['orig_request_uri'] ) ) {
			$this->prepare_url();
			add_action( 'init', array( $this, 'revert_url' ), 999 ); // prevent 404.
			add_action( 'parse_query', array( $this, 'prepare_url' ) );
		}

		/*
		 * URLs for the "from" and "referer" fields are transmitted in reversed
		 * format (moc.elpmaxe//:ptth)
		 * Reason for this is that plugins like iThemes security might block
		 * incoming requests that contain the value "http://". This is how
		 * we bypass that security check.
		 */
		if ( ! empty( $_REQUEST['thefrom'] ) ) { $_REQUEST['thefrom'] = strrev( $_REQUEST['thefrom'] ); }
		if ( ! empty( $_REQUEST['thereferrer'] ) ) { $_REQUEST['thereferrer'] = strrev( $_REQUEST['thereferrer'] ); }

		// http://premium.wpmudev.org/forums/topic/ive-debugged-a-problem-in-the-latest-version-of-the-popup-pro-plugin
		if ( ! empty( $_GET['thefrom'] ) ) { $_GET['thefrom'] = strrev( $_GET['thefrom'] ); }
		if ( ! empty( $_GET['thereferrer'] ) ) { $_GET['thereferrer'] = strrev( $_GET['thereferrer'] ); }
		if ( ! empty( $_POST['thefrom'] ) ) { $_POST['thefrom'] = strrev( $_POST['thefrom'] ); }
		if ( ! empty( $_POST['thereferrer'] ) ) { $_POST['thereferrer'] = strrev( $_POST['thereferrer'] ); }

		// Register the popup post type.
		add_action(
			'init',
			array( 'IncPopupPosttype', 'instance' ),
			99
		);

		// Register the popup post type.
		add_action(
			'wp_loaded',
			array( 'IncPopupDatabase', 'check_db' )
		);

		// Load active add-ons.
		add_action(
			'init',
			array( 'IncPopup', 'load_optional_files' )
		);

		// Returns a list of all style infos (id, url, path, deprecated)
		add_filter(
			'popup-styles',
			array( 'IncPopupBase', 'style_infos' )
		);

		// Ajax handlers to load PopUp data (for logged in users).
		add_action(
			'wp_ajax_inc_popup',
			array( $this, 'ajax_load_popup' )
		);

		// Ajax handlers to load PopUp data (for guests).
		add_action(
			'wp_ajax_nopriv_inc_popup',
			array( $this, 'ajax_load_popup' )
		);

		// Compatibility with plugins
		add_filter(
			'popup-output-data',
			array( $this, 'compat_init' ),
			999, 2
		);

		if ( function_exists( 'get_rocket_option' ) && get_rocket_option( 'minify_js' ) ) {
			foreach ( array( 'edit-inc_popup', 'inc_popup', 'inc_popup_page_settings' ) as $screen ) {
				lib3()->ui->admin_message(
					__(
						'You are using WP Rocket with JS Minification, which has ' .
						'caused some issues in the past. We recommend to disable ' .
						'the JS Minification setting in WP Rocket to avoid problems.',
						'popover'
					),
					'err',
					$screen
				);
			}
		}

		// Tell Add-ons and extensions that we are set up.
		do_action( 'popup-init' );
	}

	/**
	 * If the plugin operates on global multisite level.
	 *
	 * @since  4.6
	 * @return bool
	 */
	static public function use_global() {
		$State = null;

		if ( null === $State ) {
			$State = is_multisite() && PO_GLOBAL;
		}

		return $State;
	}

	/**
	 * If the the user currently is on correct level (global vs blog)
	 *
	 * @since  4.6
	 * @return bool
	 */
	static public function correct_level() {
		$State = null;

		if ( null === $State ) {
			$State = false;
			if ( ! self::use_global() ) {
				if ( is_admin() && ! is_network_admin() ) {
					$State = true;
				}
			} else {
				$blog_id = defined( 'BLOG_ID_CURRENT_SITE' )  ? BLOG_ID_CURRENT_SITE : 0;
				if ( is_network_admin() || get_current_blog_id() == $blog_id ) {
					$State = true;
				}
			}
		}

		return $State;
	}

	/**
	 * Returns an array with all core popup styles.
	 *
	 * @since  4.6
	 * @return array Core Popup styles.
	 */
	static protected function _get_styles() {
		$Styles = null;

		if ( null === $Styles ) {
			$Styles = array();
			if ( $handle = opendir( PO_TPL_DIR ) ) {
				while ( false !== ( $entry = readdir( $handle ) ) ) {
					if ( '.' == $entry || '..' == $entry ) { continue; }
					$style_file = PO_TPL_DIR . $entry . '/style.php';
					if ( ! file_exists( $style_file ) ) { continue; }

					$info = (object) array();
					include $style_file;

					$Styles[ $entry ] = $info;
				}
				closedir( $handle );
			}
		}

		return $Styles;
	}

	/**
	 * Returns a list of all style infos (id, url, path, deprecated)
	 * Handles filter `popup-styles`
	 *
	 * @since  4.6
	 * @param  array $list
	 * @return array The updated list.
	 */
	static public function style_infos( $list = array() ) {
		$core_styles = self::_get_styles();
		$urls = apply_filters( 'popup-available-styles-url', array() );
		$paths = apply_filters( 'popup-available-styles-directory', array() );

		if ( ! is_array( $list ) ) { $list = array(); }

		// Add core styles to the response.
		foreach ( $core_styles as $key => $data ) {
			lib3()->array->equip( $data, 'pro', 'deprecated', 'name' );

			$list[ $key ] = (object) array(
				'url' => trailingslashit( PO_TPL_URL . $key ),
				'dir' => trailingslashit( PO_TPL_DIR . $key ),
				'name' => $data->name,
				'pro' => $data->pro,
				'deprecated' => (bool) $data->deprecated,
			);
			if ( isset( $urls[ $data->name ] ) ) { unset( $urls[ $data->name ] ); }
			if ( isset( $paths[ $data->name ] ) ) { unset( $paths[ $data->name ] ); }
		}

		// Add custom styles to the response.
		foreach ( $urls as $key => $url ) {
			if ( ! isset( $paths[ $key ] ) ) { continue; }

			$list[ $key ] = (object) array(
				'url' => $url,
				'dir' => $paths[ $key ],
				'name' => $key,
				'pro' => false,
				'deprecated' => false,
			);
		}

		return $list;
	}

	/**
	 * Returns a list with all available rule-files.
	 *
	 * @since  4.6
	 * @return array List of rule-files.
	 */
	static public function get_rules() {
		$List = null;

		if ( null === $List ) {
			$List = array();
			$base_len = strlen( PO_INC_DIR . 'rules/' );
			foreach ( glob( PO_INC_DIR . 'rules/*.php' ) as $path ) {
				$List[] = substr( $path, $base_len );
			}

			$List = apply_filters( 'popup-available-rules', $List );

			sort( $List );
		}

		return $List;
	}

	/**
	 * Loads the active Add-On files.
	 *
	 * @since  4.6
	 */
	static public function load_optional_files() {
		$settings = IncPopupDatabase::get_settings();

		if ( $settings['geo_db'] ) {
			IncPopupAddon_GeoDB::init();
		}

		// $available uses apply_filter to customize the results.
		$available = self::get_rules();

		foreach ( $available as $rule ) {
			$path = PO_INC_DIR . 'rules/'. $rule;
			if ( in_array( $rule, $settings['rules'] ) && file_exists( $path ) ) {
				include_once $path;
			}
		}
	}

	/**
	 * Returns an array with all relevant popup fields extracted from the
	 * submitted form data.
	 *
	 * @since  4.6
	 * @param  array $form Raw form array, submitted from WP admin.
	 * @return array Data extracted from the $form array.
	 */
	static protected function prepare_formdata( $form ) {
		if ( ! is_array( $form ) ) { $form = array(); }

		lib3()->array->equip(
			$form,
			'po_name',
			'po_content',
			'po_heading',
			'po_subheading',
			'po_cta',
			'po_cta_link',
			'po_cta_target',
			'po_image',
			'po_image_pos',
			'po_style',
			'po_color',
			'po_size_width',
			'po_size_height',
			'po_display',
			'po_display_data',
			'po_hide_expire',
			'po_rule',
			'po_custom_css',
			'po_animation_in',
			'po_animation_out',
			'po_form_submit'
		);

		$data = array(
			// Meta: Content
			'name' => stripslashes( $form['po_name'] ),
			'content' => stripslashes( $form['po_content'] ),
			'title' => stripslashes( $form['po_heading'] ),
			'subtitle' => stripslashes( $form['po_subheading'] ),
			'cta_label' => stripslashes( $form['po_cta'] ),
			'cta_link' => stripslashes( $form['po_cta_link'] ),
			'cta_target' => stripslashes( $form['po_cta_target'] ),
			'image' => stripslashes( $form['po_image'] ),
			'image_pos' => $form['po_image_pos'],
			'image_mobile' => ! isset( $form['po_image_no_mobile'] ),
			'active' => isset( $form['po_active'] ),

			// Meta: Appearance
			'style' => stripslashes( $form['po_style'] ),
			'round_corners' => ! isset( $form['po_no_round_corners'] ),
			'scroll_body' => isset( $form['po_scroll_body'] ),
			'custom_colors' => isset( $form['po_custom_colors'] ),
			'color' => $form['po_color'],
			'custom_size' => isset( $form['po_custom_size'] ),
			'size' => array(
				'width' => $form['po_size_width'],
				'height' => $form['po_size_height'],
			),
			'custom_css' => stripslashes( $form['po_custom_css'] ),
			'animation_in' => $form['po_animation_in'],
			'animation_out' => $form['po_animation_out'],

			// Meta: Behavior
			'display' => $form['po_display'],
			'display_data' => $form['po_display_data'],
			'can_hide' => isset( $form['po_can_hide'] ),
			'close_hides' => isset( $form['po_close_hides'] ),
			'hide_expire' => $form['po_hide_expire'],
			'overlay_close' => ! isset( $form['po_overlay_close'] ),
			'form_submit' => $form['po_form_submit'],

			// Meta: Rules:
			'rule' => $form['po_rule'],
			'rule_data' => apply_filters( 'popup-save-rules', array() ),
		);

		if ( ! is_array( $data['rule'] ) ) { $data['rule'] = array(); }

		return $data;
	}

	/**
	 * Returns the popup content as JSON object and then ends the request.
	 *
	 * @since  4.6
	 */
	public function ajax_load_popup() {
		lib3()->array->equip_request( 'do', 'data' );
		$action = $_REQUEST['do'];

		switch ( $action ) {
			case 'get_data':
				if ( isset( $_REQUEST['data']['post_type'] )
					&& IncPopupItem::POST_TYPE == $_REQUEST['data']['post_type']
				) {
					$this->popups = array();
					$this->popups[0] = new IncPopupItem();
					$data = self::prepare_formdata( $_REQUEST['data'] );
					$this->popups[0]->populate( $data );
				} else {
					$this->select_popup();
				}

				if ( ! empty( $this->popups ) ) {
					$data = $this->get_popup_data();
					header( 'Content-Type: application/javascript' );
					echo 'po_data(' . json_encode( $data ) . ')';
				}
				die();
		}
	}

	/**
	 * Processes the popups array and returns a serializeable object with all
	 * popup details. The object can be JSON-encoded and interpreted by the
	 * javascript library.
	 *
	 * @since  4.6
	 * @return array
	 */
	public function get_popup_data() {
		$data = array();
		$is_preview = ! empty( $_REQUEST['preview'] );

		foreach ( $this->popups as $item ) {
			$item_data = $item->get_script_data( $is_preview );
			$data[] = $item_data;
		}

		return $data;
	}

	/**
	 * Returns an object that contains all available PopUp loading/unloading
	 * animations.
	 *
	 * @since  4.8.0.0
	 * @return object {
	 *         Hierarchial lists of PopUp animations.
	 *
	 *         $in .. Loading animatins
	 *         $out .. Unloading animations
	 * }
	 */
	public static function get_animations() {
		$pro_only = ' - ' . __( 'PRO Version', PO_LANG );
		

		$animations_in = array(
			'' => array(
				'' => __( '(No Animation)', 'popover' ),
			),
			__( 'Attention Seekers', 'popover' ) => array(
				'bounce' => __( 'Bounce', 'popover' ),
				'flash' => __( 'Flash', 'popover' ),
				'pulse' => __( 'Pulse', 'popover' ),
				'rubberBand' => __( 'Rubber Band', 'popover' ),
				'shake' => __( 'Shake', 'popover' ),
				'swing' => __( 'Swing', 'popover' ),
				'tada' => __( 'Tada', 'popover' ),
				'wobble' => __( 'Wobble', 'popover' ),
			),
			__( 'Bouncing Entrances', 'popover' ) => array(
				'bounceIn' => __( 'Bounce In', 'popover' ) . $pro_only,
				'bounceInDown' => __( 'Bounce In Down', 'popover' ) . $pro_only,
				'bounceInLeft' => __( 'Bounce In Left', 'popover' ) . $pro_only,
				'bounceInRight' => __( 'Bounce In Right', 'popover' ) . $pro_only,
				'bounceInUp' => __( 'Bounce In Up', 'popover' ) . $pro_only,
			),
			__( 'Fading Entrances', 'popover' ) => array(
				'fadeIn' => __( 'Fade In', 'popover' ) . $pro_only,
				'fadeInDown' => __( 'Fade In Down', 'popover' ) . $pro_only,
				'fadeInDownBig' => __( 'Fade In Down Big', 'popover' ) . $pro_only,
				'fadeInLeft' => __( 'Fade In Left', 'popover' ) . $pro_only,
				'fadeInLeftBig' => __( 'Fade In Left Big', 'popover' ) . $pro_only,
				'fadeInRight' => __( 'Fade In Right', 'popover' ) . $pro_only,
				'fadeInRightBig' => __( 'Fade In Right Big', 'popover' ) . $pro_only,
				'fadeInUp' => __( 'Fade In Up', 'popover' ) . $pro_only,
				'fadeInUpBig' => __( 'Fade In Up Big', 'popover' ) . $pro_only,
			),
			__( 'Flippers', 'popover' ) => array(
				'flip' => __( 'Flip', 'popover' ) . $pro_only,
				'flipInX' => __( 'Flip In X', 'popover' ) . $pro_only,
				'flipInY' => __( 'Flip In Y', 'popover' ) . $pro_only,
			),
			__( 'Lightspeed', 'popover' ) => array(
				'lightSpeedIn' => __( 'Light Speed In', 'popover' ) . $pro_only,
			),
			__( 'Rotating Entrances', 'popover' ) => array(
				'rotateIn' => __( 'Rotate In', 'popover' ) . $pro_only,
				'rotateInDownLeft' => __( 'Rotate In Down Left', 'popover' ) . $pro_only,
				'rotateInDownRight' => __( 'Rotate In Down Right', 'popover' ) . $pro_only,
				'rotateInUpLeft' => __( 'Rotate In Up Left', 'popover' ) . $pro_only,
				'rotateInUpRight' => __( 'Rotate In Up Right', 'popover' ) . $pro_only,
			),
			__( 'Specials', 'popover' ) => array(
				'rollIn' => __( 'Roll In', 'popover' ) . $pro_only,
			),
			__( 'Zoom Entrances', 'popover' ) => array(
				'zoomIn' => __( 'Zoom In', 'popover' ) . $pro_only,
				'zoomInDown' => __( 'Zoom In Down', 'popover' ) . $pro_only,
				'zoomInLeft' => __( 'Zoom In Left', 'popover' ) . $pro_only,
				'zoomInRight' => __( 'Zoom In Right', 'popover' ) . $pro_only,
				'zoomInUp' => __( 'Zoom In Up', 'popover' ) . $pro_only,
			),
		);

		$animations_out = array(
			'' => array(
				'' => __( '(No Animation)', 'popover' ),
			),
			__( 'Bouncing Exits', 'popover' ) => array(
				'bounceOut' => __( 'Bounce Out', 'popover' ),
				'bounceOutDown' => __( 'Bounce Out Down', 'popover' ),
				'bounceOutLeft' => __( 'Bounce Out Left', 'popover' ),
				'bounceOutRight' => __( 'Bounce Out Right', 'popover' ),
				'bounceOutUp' => __( 'Bounce Out Up', 'popover' ),
			),
			__( 'Fading Exits', 'popover' ) => array(
				'fadeOut' => __( 'Fade Out', 'popover' ),
				'fadeOutDown' => __( 'Fade Out Down', 'popover' ),
				'fadeOutDownBig' => __( 'Fade Out Down Big', 'popover' ),
				'fadeOutLeft' => __( 'Fade Out Left', 'popover' ),
				'fadeOutLeftBig' => __( 'Fade Out Left Big', 'popover' ),
				'fadeOutRight' => __( 'Fade Out Right', 'popover' ),
				'fadeOutRightBig' => __( 'Fade Out Right Big', 'popover' ),
				'fadeOutUp' => __( 'Fade Out Up', 'popover' ),
				'fadeOutUpBig' => __( 'Fade Out Up Big', 'popover' ),
			),
			__( 'Flippers', 'popover' ) => array(
				'flipOutX' => __( 'Flip Out X', 'popover' ) . $pro_only,
				'flipOutY' => __( 'Flip Out Y', 'popover' ) . $pro_only,
			),
			__( 'Lightspeed', 'popover' ) => array(
				'lightSpeedOut' => __( 'Light Speed Out', 'popover' ) . $pro_only,
			),
			__( 'Rotating Exits', 'popover' ) => array(
				'rotateOut' => __( 'Rotate Out', 'popover' ) . $pro_only,
				'rotateOutDownLeft' => __( 'Rotate Out Down Left', 'popover' ) . $pro_only,
				'rotateOutDownRight' => __( 'Rotate Out Down Right', 'popover' ) . $pro_only,
				'rotateOutUpLeft' => __( 'Rotate Out Up Left', 'popover' ) . $pro_only,
				'rotateOutUpRight' => __( 'Rotate Out Up Right', 'popover' ) . $pro_only,
			),
			__( 'Specials', 'popover' ) => array(
				'hinge' => __( 'Hinge', 'popover' ) . $pro_only,
				'rollOut' => __( 'Roll Out', 'popover' ) . $pro_only,
			),
			__( 'Zoom Exits', 'popover' ) => array(
				'zoomOut' => __( 'Zoom Out', 'popover' ) . $pro_only,
				'zoomOutDown' => __( 'Zoom Out Down', 'popover' ) . $pro_only,
				'zoomOutLeft' => __( 'Zoom Out Left', 'popover' ) . $pro_only,
				'zoomOutRight' => __( 'Zoom Out Right', 'popover' ) . $pro_only,
				'zoomOutUp' => __( 'Zoom Out Up', 'popover' ) . $pro_only,
			),
		);

		return (object) array(
			'in' => $animations_in,
			'out' => $animations_out,
		);
	}


	/*==================================*\
	======================================
	==                                  ==
	==           SELECT POPUP           ==
	==                                  ==
	======================================
	\*==================================*/


	/**
	 * Returns an array with popup details.
	 *
	 * @since  4.6
	 * @return array
	 */
	protected function select_popup() {
		$data = array();
		$items = $this->find_popups();

		/**
		 * Filter the popup list so other modules can modify the popup details.
		 *
		 * @since  4.8.0.0
		 */
		$items = apply_filters( 'popup-select-popups', $items, $this );

		if ( empty( $items ) ) {
			return;
		}

		foreach ( $items as $item ) {
			$this->popups[] = $item;
		}
	}

	/**
	 * Returns an array of PopUp objects that should be displayed for the
	 * current page/user. The PopUps are in the order in which they are defined
	 * in the admin list.
	 *
	 * @since  4.6
	 * @return array List of all popups that fit the current page.
	 */
	protected function find_popups() {
		$popups = array();
		lib3()->array->equip_request( 'po_id', 'preview' );

		/**
		 * Allow other modules to provide a single popup ID to display.
		 * The value 0 will use the default logic, which evaluates all active
		 * Popups instead of displaying a single Popup.
		 *
		 * @since 4.8.0.0
		 */
		$popup_id = apply_filters(
			'popup-find-popup-single',
			absint( $_REQUEST['po_id'] )
		);

		if ( $popup_id ) {
			// Check for forced popup.
			$popup_ids = array( $popup_id );
		} else {
			$popup_ids = IncPopupDatabase::get_active_ids();
		}

		/**
		 * Filter the list of Popup IDs that will be considered for display.
		 * These Popups will be loaded and their rules evaluated in the next step.
		 *
		 * @since  4.8.0.0
		 */
		$popup_ids = apply_filters(
			'popup-active-popup-ids',
			$popup_ids,
			$popup_id,
			$this
		);

		$popup_ids = lib3()->array->get( $popup_ids );
		foreach ( $popup_ids as $id ) {
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
			if ( ! $_REQUEST['preview'] && $this->is_hidden( $id ) ) { continue; }

			$popups[] = $popup;
		}

		return $popups;
	}


	/**
	 * Tests if a popup is marked as hidden ("never see this message again").
	 * This flag is stored as a cookie on the users computer.
	 *
	 * @since  4.6
	 * @param  int $id PopUp ID
	 * @return bool
	 */
	protected function is_hidden( $id ) {
		$name = 'po_h-' . $id;
		$name_old = 'popover_never_view_' . $id;

		return isset( $_COOKIE[ $name ] ) || isset( $_COOKIE[ $name_old ] );
	}

	/**
	 * Loads the specified view (template from views/ directory).
	 * The list of args is extracted so the view can access the variables.
	 *
	 * @since  4.7.2.0
	 * @param  string $name Name of the view (filename without extension).
	 * @param  array  $args Optional list of variables to provide.
	 */
	static public function load_view( $name, $args = array() ) {
		if ( is_array( $args ) ) {
			extract( $args );
		}

		$path = PO_DIR . 'views/' . strtolower( $name );
		if ( 'pro' == PO_VERSION ) {
			if ( file_exists( $path . '-premium.php' ) ) {
				$path .= '-premium';
			}
		}

		include $path . '.php';
	}


	/*===================================*\
	=======================================
	==                                   ==
	==           COMPATIBILITY           ==
	==                                   ==
	=======================================
	\*===================================*/


	/**
	 * Adds compatibility code for other plugins/shortcodes.
	 * Currently these plugins are aupported:
	 *   - Appointments+ (WPMU DEV)
	 *
	 * @since  4.6.1.1
	 * @param  array $data Data collection that is printed to javascript.
	 * @param  IncPopupItem $popup The original popup object.
	 * @return array Modified data collection.
	 */
	public function compat_init( $script_data, $popup ) {
		if ( class_exists( 'Appointments' ) ) {
			$this->compat_appointments_trigger();

			if ( ! isset( $script_data['script'] ) ) {
				$script_data['script'] = '';
			}

			$script_data['script'] .= $this->compat_data['script'];
		}

		return $script_data;
	}

	/**
	 * Triggers the WP_footer action.
	 *
	 * This is required so the Appointments+ plugin generates Javascript for the
	 * shortcodes. We will then intercept the javascript via the
	 * `compat_appointments_footer()` function below.
	 *
	 * @since  4.6.1.1
	 */
	public function compat_appointments_trigger() {
		global $appointments;

		add_filter(
			'app_footer_scripts',
			array( $this, 'compat_appointments_footer' ),
			999
		);

		$appointments->load_scripts_styles();
		$appointments->wp_footer();
	}

	/**
	 * Integration of Appointments+
	 * Inject the javascript used by Appointments plugin into the plugin.
	 *
	 * @since  4.6.1.1
	 * @param  string $script
	 * @return string
	 */
	public function compat_appointments_footer( $script ) {
		global $wp_scripts;
		if ( ! isset( $this->compat_data['script'] ) ) {
			$this->compat_data['script'] = '';
		}

		// 1. get the localized script data.
		if ( ! empty( $wp_scripts->registered['app-js-check']->extra['data'] ) ) {
			$this->compat_data['script'] .= $wp_scripts->registered['app-js-check']->extra['data'];
			$wp_scripts->registered['app-js-check']->extra['data'] = '';
		}

		// 2. get the footer script.
		$this->compat_data['script'] .= '(function($) {' . $script . '})(jQuery);';

		return '';
	}

	/**
	 * Change the Request-URI, so other plugins use the correct form action, etc.
	 *
	 * Example:
	 *  Contact-Form-7 is included as shortcode in a PopUp.
	 *  The PopUp is loaded via WordPress Ajax.
	 *  When the Contact form is generated, the CF7 plugin will use the AJAX-URL
	 *  for the contact form, instead of the URL of the host-page...
	 *
	 * @since  4.6.1.1
	 */
	public function prepare_url() {
		if ( empty( $_REQUEST['orig_request_uri'] ) ) { return; }

		if ( empty( $this->orig_url ) ) {
			$this->orig_url = $_SERVER['REQUEST_URI'];
		}

		// Remove internal commands from the query.
		$_SERVER['REQUEST_URI'] = strtok( $_REQUEST['orig_request_uri'], '#' );
	}

	/**
	 * Revert the Request-URI to the original value.
	 *
	 * @since  4.6.1.1
	 */
	public function revert_url() {
		if ( empty( $_REQUEST['orig_request_uri'] ) ) { return; }
		if ( empty( $this->orig_url ) ) { return; }

		$_SERVER['REQUEST_URI'] = $this->orig_url;
	}
};
