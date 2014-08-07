<?php

/**
 * This class represents a single popup object.
 * Data is stored in database as custom posttype.
 */
class IncPopupItem {

	// Popups are stored in DB as custom post-types. This is the post type key.
	const POST_TYPE = 'inc_popup';

	// The styles available for this popup.
	public $styles = null;

	// The available options for the display setting.
	static public $display_opts = array(
		'delay'  /* show popup after X seconds */,
		'scroll' /* show popup when user scrolls X % down */,
		'anchor' /* show popup when user scrolls past element X */,
	);

	// Internal Popup ID.
	public $id = 0;

	// Internal Popup title.
	public $name = '';

	// Popup order.
	public $order = 0;

	// Status: Active/Inactive/Trash
	public $status = 'inactive';

	// Original status (used while saving to check if status changed)
	private $orig_status = 'inactive';

	// -- Content

	// Popup HTML content.
	public $content = '';

	// Original HTML content (used while saving to check if the content changed)
	private $orig_content = '';

	// Popup title.
	public $title = '';

	// Popup subtitle.
	public $subtitle = '';

	// Label of the CTA button.
	public $cta_label = '';

	// Link for the CTA button.
	public $cta_link = '';

	// Image dispalyed in the popup.
	public $image = '';

	// Image position (left/right)
	public $image_pos = '';

	// -- Appearance

	// CSS style of the popup.
	public $style = 'default';

	// Info if the used popup-style is old (4.5 or earlier)
	public $deprecated_style = false;

	// Checkbox: Use custom size.
	public $custom_size = false;

	// Popup size (width, height of the box).
	public $size = array();

	// Checkbox: Use custom colors.
	public $custom_colors = false;

	// Colors (background, font).
	public $color = array();

	// CSS option "no rounded corners".
	public $round_corners = true;

	// -- "Never show again" options

	// Add button "Never show popup again".
	public $can_hide = false;

	// "Close button acts as 'Never show popup again'".
	public $close_is_hide = false;

	// Expiration of "Never show popup again" (in days).
	public $hide_expire = 365;

	// Close popup when user clicks on the background overlay?
	public $overlay_close = true;

	// -- Display options

	// When to display the popup (delay/scroll/anchor).
	public $display = 'delay';

	// Collection of additional options for the $display option (e.g. delay, ...)
	public $display_data = array();

	// -- Conditions

	// Conditions that need to be true in order to use the popup.
	public $rule = array();

	// Specifies which rule-files are needed to handle all popup conditions.
	public $rule_files = array();

	// Extra arguments for the conditions
	// (e.g. "rule[0] = count" and "rule_data[count] = 3")
	public $rule_data = array();

	// This is used to store dynamic properties used by templates, such as the
	// div-ID string or custom css styles.
	public $code = null;

	// Public collection of details that are passed to frontend javascript to
	// render the popup.
	public $script_data = array();

	// -------------------------------------------------------------------------

	/**
	 * Create and populate a new popup object.
	 *
	 * @since 4.6
	 * @param mixed $data Data to populate the new object with.
	 *                Not specified .. An empty/new popup will be created.
	 *                Array .. Popup will be populated with values from array.
	 *                Number .. Popup will be populated from DB (data = post_id)
	 */
	public function __construct( $data = null ) {
		if ( is_int( $data ) ) {
			$this->load( $data );
		} else {
			if ( is_array( $data ) || is_object( $data ) ) {
				$this->populate( $data );
			} else {
				$this->reset();
			}
		}
	}

	/**
	 * Reset the popup object to default values.
	 *
	 * @since  4.6
	 */
	public function reset() {
		$this->id = 0;
		$this->name = '';
		$this->order = 0;
		$this->status = 'inactive';
		$this->orig_status = 'inactive';
		$this->content = '';
		$this->orig_content = '';
		$this->title = '';
		$this->subtitle = '';
		$this->cta_label = '';
		$this->cta_link = '';
		$this->image = '';
		$this->image_pos = 'right';
		$this->custom_size = false;
		$this->size = array(
			'width' => '',
			'height' => '',
		);
		$this->custom_colors = false;
		$this->color = array(
			'back' => '',
			'fore' => '',
		);
		$this->style = 'default';
		$this->deprecated_style = false;
		$this->round_corners = true;
		$this->can_hide = false;
		$this->close_hides = false;
		$this->hide_expire = 365;
		$this->overlay_close = true;
		$this->display = 'delay';
		$this->display_data = array(
			'delay' => 0,
			'delay_type' => 0,
			'scroll' => 0,
			'scroll_type' => '%',
			'anchor' => '',
		);
		$this->rule = array();
		$this->rule_files = array();
		$this->rule_data = array();

		$this->code = (object) array();
		$this->code->id     = 'a' . md5( date( 'dis' ) );
		$this->code->colors = '';
	}

	/**
	 * Populate the object from the specified data collection.
	 *
	 * @since  4.6
	 * @param  array|object $data Describes the data to populate the popup with.
	 */
	public function populate( $data ) {
		if ( is_object( $data ) ) {
			$data = (array) $data;
		}
		if ( ! is_array( $data ) ) {
			return;
		}
		$this->reset();

		$styles = apply_filters( 'popup-styles', array() );
		$style_keys = array_keys( $styles );

		isset( $data['id'] ) && $this->id = $data['id'];
		isset( $data['name'] ) && $this->name = $data['name'];
		isset( $data['order'] ) && $this->order = $data['order'];
		isset( $data['active'] ) && $this->status = $data['active'] ? 'active' : 'draft';
		isset( $data['status'] ) && $this->status = $data['status'];

		isset( $data['content'] ) && $this->content = $data['content'];
		isset( $data['image'] ) && $this->image = $data['image'];
		isset( $data['image_pos'] ) && $this->image_pos = $data['image_pos'];
		isset( $data['title'] ) && $this->title = $data['title'];
		isset( $data['subtitle'] ) && $this->subtitle = $data['subtitle'];
		isset( $data['cta_label'] ) && $this->cta_label = $data['cta_label'];
		isset( $data['cta_link'] ) && $this->cta_link = $data['cta_link'];
		isset( $data['custom_size'] ) && $this->custom_size = $data['custom_size'];

		isset( $data['size']['width'] ) && $this->size['width'] = $data['size']['width'];
		isset( $data['size']['height'] ) && $this->size['height'] = $data['size']['height'];
		is_numeric( $this->size['width'] ) && $this->size['width'] .= 'px';
		is_numeric( $this->size['height'] ) && $this->size['height'] .= 'px';

		isset( $data['color']['back'] ) && $this->color['back'] = $data['color']['back'];
		isset( $data['color']['fore'] ) && $this->color['fore'] = $data['color']['fore'];
		if ( isset( $data['custom_colors'] ) ) {
			$this->custom_colors = (true == $data['custom_colors']);
		} else {
			$this->custom_colors = ( ! empty( $this->color['back'] ) && ! empty( $this->color['fore'] ) );
		}

		in_array( @$data['style'], $style_keys ) && $this->style = $data['style'];
		isset( $data['round_corners'] ) && $this->round_corners = (true == $data['round_corners']);
		isset( $data['can_hide'] ) && $this->can_hide = (true == $data['can_hide']);
		isset( $data['close_hides'] ) && $this->close_hides = (true == $data['close_hides']);
		is_numeric( @$data['hide_expire'] ) && $this->hide_expire = absint( $data['hide_expire'] );
		isset( $data['overlay_close'] ) && $this->overlay_close = ( true == $data['overlay_close'] );

		in_array( @$data['display'], self::$display_opts ) && $this->display = $data['display'];

		is_array( @$data['display_data'] ) || $data['display_data'] = array();
		$this->display_data = $data['display_data'];

		is_array( @$data['rule'] ) && $this->rule = $data['rule'];
		is_array( @$data['rule_data'] ) && $this->rule_data = $data['rule_data'];

		$this->validate_data();
	}

	/**
	 * Validates and sanitizes the current popup details.
	 *
	 * @since  4.6
	 */
	protected function validate_data() {
		$styles = apply_filters( 'popup-styles', array() );

		// Color.
		if ( ! is_array( $this->color ) ) { $this->color = array(); }
		if ( ! isset( $this->color['back'] ) ) { $this->color['back'] = ''; }
		if ( ! isset( $this->color['fore'] ) ) { $this->color['fore'] = ''; }
		if ( ! empty( $this->color['back'] ) && $this->color['back'][0] !== '#' ) {
			$this->color['back'] = '#' . $this->color['back'];
		}
		if ( ! empty( $this->color['fore'] ) && $this->color['fore'][0] !== '#' ) {
			$this->color['fore'] = '#' . $this->color['fore'];
		}

		// Size.
		if ( ! is_array( $this->size ) ) { $this->size = array(); }
		if ( ! isset( $this->size['width'] ) ) { $this->size['width'] = ''; }
		if ( ! isset( $this->size['height'] ) ) { $this->size['height'] = ''; }

		// Style.
		if ( ! isset( $styles[ $this->style ] ) ) { $this->style = 'simple'; } // default style.
		$this->deprecated_style = @$styles[ $this->style ]->deprecated;

		// Boolean types.
		$this->custom_size = (true == @$this->custom_size);
		$this->custom_colors = (true == @$this->custom_colors);
		$this->deprecated_style = (true == @$this->deprecated_style);
		$this->round_corners = (true == @$this->round_corners);
		$this->can_hide = (true == @$this->can_hide);
		$this->close_hides = (true == @$this->close_hides);
		$this->overlay_close = (true == @$this->overlay_close);

		// Numeric types.
		$this->hide_expire = absint( $this->hide_expire );
		$this->display_data['delay'] = absint( @$this->display_data['delay'] );
		$this->display_data['scroll'] = absint( @$this->display_data['scroll'] );
		$this->display_data['delay_type'] = @$this->display_data['delay_type'];
		$this->display_data['scroll_type'] = @$this->display_data['scroll_type'];
		$this->display_data['anchor'] = @$this->display_data['anchor'];

		// Display behavior.
		if ( ! in_array( $this->display, self::$display_opts ) ) { $this->display = 'delay'; }
		if ( 'm' != $this->display_data['delay_type'] ) { $this->display_data['delay_type'] = 's'; }
		if ( 'px' != $this->display_data['scroll_type'] ) { $this->display_data['scroll_type'] = '%'; }

		// Rules.
		if ( ! is_array( $this->rule ) ) { $this->rule = array(); }
		$this->rule_files = array();
		foreach ( $this->rule as $ind => $key ) {
			if ( empty( $key ) ) { unset( $this->rule[$ind] ); }

			// Set rule-files.
			$file = IncPopupRules::file_for_rule( $key );
			if ( $file && ! in_array( $file, $this->rule_files ) ) {
				$this->rule_files[] = $file;
			}
		}
		if ( ! is_array( $this->rule_data ) ) { $this->rule_data = array(); }
		foreach ( $this->rule_data as $ind => $key ) {
			if ( empty( $key ) ) { unset( $this->rule_data[$ind] ); }
		}

		// Display data (legacy code for old styles).
		if ( $this->custom_colors ) {
			$this->code->colors = 'color:' . $this->color['fore'] . ';background:' . $this->color['back'] . ';';
		} else {
			$this->code->colors = '';
		}

		// Display data.
		$this->code->color1 = $this->color['back'];
		$this->code->color2 = $this->color['fore'];
		if ( empty ( $this->code->color1 ) ) { $this->code->color1 = '#488CFD'; }
		if ( empty ( $this->code->color2 ) ) { $this->code->color2 = '#FFFFFF'; }

		$this->script_data['html_id'] = $this->code->id;
		$this->script_data['popup_id'] = $this->id;
		$this->script_data['close_hide'] = $this->close_hides;
		$this->script_data['expiry'] = $this->hide_expire;
		$this->script_data['custom_size'] = $this->custom_size;
		$this->script_data['width'] = $this->size['width'];
		$this->script_data['height'] = $this->size['height'];
		$this->script_data['overlay_close'] = $this->overlay_close;
		$this->script_data['display'] = $this->display;
		$this->script_data['display_data'] = $this->display_data;

		// Validation only done when editing popups.
		if ( is_admin() ) {
			// Name.
			if ( empty( $this->name ) ) {
				$this->name = __( 'New Pop Up', PO_LANG );
			}

			// Order.
			if ( empty( $this->id ) || empty( $this->order ) ) {
				$this->order = IncPopupDatabase::next_order();
			}

			// Rule-files.
			$this->rule_files = array();
			foreach ( $this->rule as $ind => $key ) {
				$file = IncPopupRules::file_for_rule( $key );
				if ( $file && ! in_array( $file, $this->rule_files ) ) {
					$this->rule_files[] = $file;
				}
			}

			// Check if the "id" is valid!
			if ( $this->id > 0 && self::POST_TYPE !== get_post_type( $this->id ) ) {
				$this->id = 0;
			}
		}
	}

	/**
	 * Populate current popup object from DB.
	 *
	 * @since  4.6
	 * @param  int $id The post_id of the popup in database.
	 */
	public function load( $id ) {
		$post = get_post( $id );

		$this->reset();

		// Item does not exist.
		if ( ! $post || 'auto-draft' == $post->post_status ) {
			return;
		}

		// Item is a different post type.
		if ( ! self::POST_TYPE == $post->post_type ) {
			return;
		}

		switch ( $post->post_status ) {
			case 'publish':  $status = 'active'; break;
			case 'draft':    $status = 'inactive'; break;
			case 'trash':    $status = 'trash'; break;
			default:         $status = 'inactive'; break;
		}

		$styles = apply_filters( 'popup-styles', array() );

		$this->id = $post->ID;
		$this->name = $post->post_title;
		$this->status = $status;
		$this->orig_status = $status;
		$this->content = $post->post_content;
		$this->orig_content = $post->post_content;
		$this->order = $post->menu_order;

		// Read metadata of the popup.
		$this->title = get_post_meta( $this->id, 'po_title', true );
		$this->image = get_post_meta( $this->id, 'po_image', true );
		$this->image_pos = get_post_meta( $this->id, 'po_image_pos', true );
		$this->subtitle = get_post_meta( $this->id, 'po_subtitle', true );
		$this->cta_label = get_post_meta( $this->id, 'po_cta_label', true );
		$this->cta_link = get_post_meta( $this->id, 'po_cta_link', true );
		$this->custom_size = get_post_meta( $this->id, 'po_custom_size', true );
		$this->size = get_post_meta( $this->id, 'po_size', true );
		$this->color = get_post_meta( $this->id, 'po_color', true );
		$this->custom_colors = get_post_meta( $this->id, 'po_custom_colors', true );
		$this->style = get_post_meta( $this->id, 'po_style', true );
		$this->round_corners = get_post_meta( $this->id, 'po_round_corners', true );
		$this->can_hide = get_post_meta( $this->id, 'po_can_hide', true );
		$this->close_hides = get_post_meta( $this->id, 'po_close_hides', true );
		$this->hide_expire = get_post_meta( $this->id, 'po_hide_expire', true );
		$this->overlay_close = get_post_meta( $this->id, 'po_overlay_close', true );
		$this->display = get_post_meta( $this->id, 'po_display', true );
		$this->display_data = get_post_meta( $this->id, 'po_display_data', true );
		$this->rule = get_post_meta( $this->id, 'po_rule', true );
		$this->rule_files = get_post_meta( $this->id, 'po_rule_files', true );
		$this->rule_data = get_post_meta( $this->id, 'po_rule_data', true );

		$this->validate_data();
	}

	/**
	 * Save the current popup to the database.
	 *
	 * @since  4.6
	 * @param  bool $show_message If true then a success message will be
	 *                displayed. Set to false when saving via ajax.
	 */
	public function save( $show_message = true ) {
		global $allowedposttags;

		$this->validate_data();

		if ( ! did_action( 'wp_loaded' ) ) {
			add_action( 'wp_loaded', array( $this, 'save' ) );
			return false;
		}

		switch ( $this->status ) {
			case 'active':   $status = 'publish'; break;
			case 'inactive': $status = 'draft'; break;
			case 'trash':    $status = 'trash'; break;
			default:         $status = 'draft'; break;
		}

		// When the content changed make sure to only allow valid code!
		if ( $this->content != $this->orig_content && ! current_user_can( 'unfiltered_html' ) ) {
			$this->content = wp_kses( $this->content, $allowedposttags );
		}

		$post = array(
			'ID' => (0 == $this->id ? '' : $this->id),
			'post_title' => $this->name,
			'post_status' => $status,
			'post_type' => self::POST_TYPE,
			'post_content' => $this->content,
			'menu_order' => $this->order,
		);

		// Save the main popup item.
		$res = wp_insert_post( $post );

		if ( ! empty( $res ) ) {
			$this->id = $res;

			// Save metadata of the popup.
			update_post_meta( $this->id, 'po_title', $this->title );
			update_post_meta( $this->id, 'po_image', $this->image );
			update_post_meta( $this->id, 'po_image_pos', $this->image_pos );
			update_post_meta( $this->id, 'po_subtitle', $this->subtitle );
			update_post_meta( $this->id, 'po_cta_label', $this->cta_label );
			update_post_meta( $this->id, 'po_cta_link', $this->cta_link );
			update_post_meta( $this->id, 'po_custom_size', $this->custom_size );
			update_post_meta( $this->id, 'po_size', $this->size );
			update_post_meta( $this->id, 'po_color', $this->color );
			update_post_meta( $this->id, 'po_custom_colors', $this->custom_colors );
			update_post_meta( $this->id, 'po_style', $this->style );
			update_post_meta( $this->id, 'po_round_corners', $this->round_corners );
			update_post_meta( $this->id, 'po_can_hide', $this->can_hide );
			update_post_meta( $this->id, 'po_close_hides', $this->close_hides );
			update_post_meta( $this->id, 'po_hide_expire', $this->hide_expire );
			update_post_meta( $this->id, 'po_overlay_close', $this->overlay_close );
			update_post_meta( $this->id, 'po_display', $this->display );
			update_post_meta( $this->id, 'po_display_data', $this->display_data );
			update_post_meta( $this->id, 'po_rule', $this->rule );
			update_post_meta( $this->id, 'po_rule_files', $this->rule_files );
			update_post_meta( $this->id, 'po_rule_data', $this->rule_data );
		}

		if ( $show_message ) {
			if ( ! empty( $res ) ) {
				if ( $this->orig_status === $this->status ) {
					$msg = __( 'Saved Pop Up "<strong>%1$s</strong>"', PO_LANG );
				} else {
					switch ( $status ) {
						case 'publish':
							$msg = __( 'Activated Pop Up "<strong>%1$s</strong>".', PO_LANG );
							break;

						case 'draft':
							$msg = __( 'Deactivated Pop Up "<strong>%1$s</strong>".', PO_LANG );
							break;

						case 'trash':
							$msg = __( 'Moved Pop Up "<strong>%1$s</strong>" to trash.', PO_LANG );
							break;

						default:
							$msg = __( 'Saved Pop Up "<strong>%1$s</strong>".', PO_LANG );
							break;
					}
				}
				TheLib::message( sprintf( $msg, $this->name ) );
			} else {
				TheLib::message( __( 'Could not save Pop Up.', PO_LANG ), 'err' );
			}
		}

		return true;
	}

	/**
	 * Checks whether the current popup uses the specified rule or not.
	 *
	 * @since  4.6
	 * @param  string $key Rule-ID.
	 * @return bool
	 */
	public function uses_rule( $key ) {
		$active = false;

		foreach ( $this->rule as $ind => $rule_key ) {
			if ( $key == $rule_key ) {
				$active = true;
				break;
			}
		}

		return $active;
	}

	/**
	 * Load the Pop Up HTML code from the popup.php template.
	 *
	 * @since  4.6
	 * @return string HTML code.
	 */
	protected function load_html() {
		static $Html = null;

		if ( null === $Html ) {
			$styles = apply_filters( 'popup-styles', array() );
			$details = $styles[$this->style];

			$Html = '';
			$tpl_file = $details->dir . 'popover.php';

			if ( file_exists( $tpl_file ) ) {
				ob_start();
				include_once( $tpl_file );
				$Html = ob_get_contents();
				ob_end_clean();

				$Html = str_replace( array( "\t", "\r", "\n", '     ' ), ' ', $Html );
				$Html = str_replace( array( '    ', '   ', '  ' ), ' ', $Html );
				$Html = str_replace( '#000001', $this->code->color1, $Html );
				$Html = str_replace( '#000002', $this->code->color2, $Html );
			}
		}

		return $Html;
	}

	/**
	 * Load the Pop Up CSS styles from the style.css template.
	 *
	 * @since  4.6
	 * @return string CSS code.
	 */
	protected function load_styles() {
		static $Code = null;

		if ( null === $Code ) {
			$styles = apply_filters( 'popup-styles', array() );
			$details = $styles[$this->style];

			$Code = '';
			$tpl_file = $details->dir . 'style.css';

			if ( file_exists( $tpl_file ) ) {
				ob_start();
				include_once( $tpl_file );
				$Code = ob_get_contents();
				ob_end_clean();

				$Code = str_replace( '#messagebox', '#' . $this->code->id, $Code );
				$Code = str_replace( '%styleurl%', $details->url, $Code );
				$Code = str_replace( '#000001', $this->code->color1, $Code );
				$Code = str_replace( '#000002', $this->code->color2, $Code );
			}
		}
		return $Code;
	}

	/**
	 * Returns the script-data collection.
	 *
	 * @since  4.6
	 * @return array
	 */
	public function get_script_data() {
		static $Data = null;

		#if ( null === $Data ) {
			$Data = $this->script_data;
			$Data['html'] = $this->load_html();
			$Data['styles'] = $this->load_styles();

			$Data = apply_filters( 'popup-output-data', $Data, $this );
		#}

		return $Data;
	}

	/**
	 * Change some script_data properties for displaying a popup-preview.
	 *
	 * @since  4.6
	 * @param  array $data The Pop Up data collection.
	 * @return array Modified data collection.
	 */
	public function preview_mode( $data ) {
		$data['popup_id'] = 'preview-' . $this->id;
		$data['display'] = 'delay';
		$data['display_data']['delay'] = 0;
		$data['display_data']['click_multi'] = false;
		$data['close_hide'] = false;
		$data['preview'] = true;
		return $data;
	}


	/*======================================*\
	==========================================
	==                                      ==
	==           STATIC Functions           ==
	==                                      ==
	==========================================
	\*======================================*/


	/**
	 * Returns an string with the translated condition label.
	 *
	 * @since  4.6
	 * @param  string $key Rule ID.
	 * @return string Translated rule name.
	 */
	static public function condition_label( $key = null ) {
		switch ( $key ) {
			case 'login':        return __( 'Visitor is logged in', PO_LANG );
			case 'no_login':     return __( 'Visitor is not logged in', PO_LANG );
			case 'url':          return __( 'On specific URL', PO_LANG );
			case 'no_url':       return __( 'Not on specific URL', PO_LANG );
			case 'country':      return __( 'In a specific country', PO_LANG );
			case 'no_country':   return __( 'Not in a specific country', PO_LANG );
			case 'prosite':      return __( 'Site is not a Pro-site', PO_LANG );
			case 'searchengine': return __( 'Visit via a search engine', PO_LANG );
			case 'no_comment':   return __( 'Visitor has never commented', PO_LANG );
			case 'no_internal':  return __( 'Visit not via an Internal link', PO_LANG );
			case 'referrer':     return __( 'Visit via specific referer', PO_LANG );
			case 'count':        return __( 'Popover shown less than x times', PO_LANG );
			default:             return apply_filters( 'popup-rule-label', $key, $key );
		}
	}

	/**
	 * Returns the status-label of the specified status key.
	 *
	 * @since  4.6
	 * @param  string $key Status Key.
	 * @return string Translated status label.
	 */
	static public function status_label( $key ) {
		switch ( $key ) {
			case 'active':    return __( 'Active', PO_LANG );
			case 'inactive':  return __( 'Inactive', PO_LANG );
			case 'trash':     return __( 'Trashed', PO_LANG );
			default:          return $key;
		}
	}


}