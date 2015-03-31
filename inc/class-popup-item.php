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

	// Show image on mobile devices?
	public $image_mobile = true;

	// -- Appearance

	// CSS style of the popup.
	public $style = 'minimal';

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

	// Allow page to be scrolled while PopUp is open.
	public $scroll_body = true;

	// CSS code to customize this PopUp
	public $custom_css = '';

	// -- "Never show again" options

	// Add button "Never show popup again".
	public $can_hide = false;

	// "Close button acts as 'Never show popup again'".
	public $close_hides = false;

	// Expiration of "Never show popup again" (in days).
	public $hide_expire = 365;

	// -- Behavior options

	// Close popup when user clicks on the background overlay?
	public $overlay_close = true;

	// What do do when form is submitted inside PopUp
	public $form_submit = 'default';

	// -- Display options

	// When to display the popup (delay/scroll/anchor).
	public $display = 'delay';

	// Collection of additional options for the $display option (e.g. delay, ...)
	public $display_data = array();

	// Display animation
	public $animation_in = '';

	// Hiding animation
	public $animation_out = '';

	// If true, then the PopUp will be displayed instantly.
	public $show_on_load = false;

	// Adds a custom class to the popup.
	public $custom_class = array();

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

	// Flag that defines if the PopUp is displayed in preview-mode.
	public $is_preview = false;

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
		$this->image_mobile = true;
		$this->custom_size = false;
		$this->size = array(
			'width' => '',
			'height' => '',
		);
		$this->custom_colors = false;
		$this->color = array(
			'col1' => '',
			'col2' => '',
		);
		$this->style = 'minimal';
		$this->custom_css = '';
		$this->deprecated_style = false;
		$this->round_corners = true;
		$this->scroll_body = false;
		$this->can_hide = false;
		$this->close_hides = false;
		$this->hide_expire = 365;
		$this->overlay_close = true;
		$this->form_submit = 'default';
		$this->display = 'delay';
		$this->display_data = array(
			'delay' => 0,
			'delay_type' => 0,
			'scroll' => 0,
			'scroll_type' => '%',
			'anchor' => '',
		);
		$this->animation_in = '';
		$this->animation_out = '';
		$this->show_on_load = false;
		$this->custom_class = array();
		$this->rule = array();
		$this->rule_files = array();
		$this->rule_data = array();

		$this->code = (object) array(
			'id' => 'a' . md5( date( 'dis' ) ),
			'colors' => '',
			'color1' => '',
			'color2' => '',
		);
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

		$styles = apply_filters( 'popup-styles', array() );
		$style_keys = array_keys( $styles );

		isset( $data['id'] ) && $this->id = $data['id'];
		isset( $data['name'] ) && $this->name = $data['name'];
		isset( $data['order'] ) && $this->order = $data['order'];
		isset( $data['active'] ) && $this->status = $data['active'] ? 'active' : 'inactive';
		isset( $data['status'] ) && $this->status = $data['status'];

		isset( $data['content'] ) && $this->content = $data['content'];
		isset( $data['image'] ) && $this->image = $data['image'];
		isset( $data['image_pos'] ) && $this->image_pos = $data['image_pos'];
		isset( $data['image_mobile'] ) && $this->image_mobile = $data['image_mobile'];
		isset( $data['title'] ) && $this->title = $data['title'];
		isset( $data['subtitle'] ) && $this->subtitle = $data['subtitle'];
		isset( $data['cta_label'] ) && $this->cta_label = $data['cta_label'];
		isset( $data['cta_link'] ) && $this->cta_link = $data['cta_link'];
		isset( $data['custom_size'] ) && $this->custom_size = $data['custom_size'];
		isset( $data['custom_css'] ) && $this->custom_css = $data['custom_css'];
		isset( $data['animation_in'] ) && $this->animation_in = $data['animation_in'];
		isset( $data['animation_out'] ) && $this->animation_out = $data['animation_out'];
		isset( $data['show_on_load'] ) && $this->show_on_load = $data['show_on_load'];
		is_array( $data['custom_class'] ) && $this->custom_class = $data['custom_class'];

		isset( $data['size']['width'] ) && $this->size['width'] = $data['size']['width'];
		isset( $data['size']['height'] ) && $this->size['height'] = $data['size']['height'];
		is_numeric( @$this->size['width'] ) && $this->size['width'] .= 'px';
		is_numeric( @$this->size['height'] ) && $this->size['height'] .= 'px';

		is_array( @$data['color'] ) && $this->color = $data['color'];
		if ( isset( $data['custom_colors'] ) ) {
			$this->custom_colors = (true == $data['custom_colors']);
		}

		in_array( @$data['style'], $style_keys ) && $this->style = $data['style'];
		isset( $data['round_corners'] ) && $this->round_corners = (true == $data['round_corners']);
		isset( $data['scroll_body'] ) && $this->scroll_body = (true == $data['scroll_body']);
		isset( $data['can_hide'] ) && $this->can_hide = (true == $data['can_hide']);
		isset( $data['close_hides'] ) && $this->close_hides = (true == $data['close_hides']);
		absint( @$data['hide_expire'] ) > 0 && $this->hide_expire = absint( $data['hide_expire'] );
		isset( $data['overlay_close'] ) && $this->overlay_close = ( true == $data['overlay_close'] );
		isset( $data['form_submit'] ) && $this->form_submit = $data['form_submit'];

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
		if ( ! isset( $this->color['col1'] ) ) { $this->color['col1'] = ''; }
		if ( ! isset( $this->color['col2'] ) ) { $this->color['col2'] = ''; }
		if ( ! empty( $this->color['col1'] ) && $this->color['col1'][0] !== '#' ) {
			$this->color['col1'] = '#' . $this->color['col1'];
		}
		if ( ! empty( $this->color['col2'] ) && $this->color['col2'][0] !== '#' ) {
			$this->color['col2'] = '#' . $this->color['col2'];
		}

		// Size.
		if ( ! is_array( $this->size ) ) { $this->size = array(); }
		if ( ! isset( $this->size['width'] ) ) { $this->size['width'] = ''; }
		if ( ! isset( $this->size['height'] ) ) { $this->size['height'] = ''; }

		// Style.
		if ( ! isset( $styles[ $this->style ] ) ) { $this->style = 'minimal'; } // default style.
		$this->deprecated_style = @$styles[ $this->style ]->deprecated;

		// Boolean types.
		$this->custom_size = (true == $this->custom_size);
		$this->custom_colors = (true == $this->custom_colors);
		$this->deprecated_style = (true == $this->deprecated_style);
		$this->round_corners = (true == $this->round_corners);
		$this->scroll_body = (true == $this->scroll_body);
		$this->can_hide = (true == $this->can_hide);
		$this->close_hides = (true == $this->close_hides);
		$this->overlay_close = (true == $this->overlay_close);
		$this->show_on_load = (true == $this->show_on_load);

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

		// Generate unique ID.
		$this->code = (object) array();
		$this->code->id = 'a' . md5( $this->id . date( 'dis' ) );
		$this->code->cls = 'wdpu-' . $this->id;

		// Display data (legacy code for old styles).
		if ( $this->custom_colors ) {
			$this->code->colors = 'color:' . $this->color['col2'] . ';background:' . $this->color['col1'] . ';';
		} else {
			$this->code->colors = 'color:#000000;background:#FFFFFF;';
		}

		// Display data.
		if ( ! $this->custom_colors || empty ( $this->color['col1'] ) ) {
			$this->code->color1 = '#488CFD';
		} else {
			$this->code->color1 = $this->color['col1'];
		}
		if ( ! $this->custom_colors || empty ( $this->color['col2'] ) ) {
			$this->code->color2 = '#FFFFFF';
		} else {
			$this->code->color2 = $this->color['col2'];
		}

		// Very rough validation that makes sure that the field does not close
		// the <style> tag manually.
		$this->custom_css = str_replace( '</s', 's', $this->custom_css );

		$this->script_data['html_id'] = $this->code->id;
		$this->script_data['popup_id'] = $this->id;
		$this->script_data['close_hide'] = $this->close_hides;
		$this->script_data['expiry'] = $this->hide_expire;
		$this->script_data['custom_size'] = $this->custom_size;
		$this->script_data['width'] = trim( str_replace( 'px', '', $this->size['width'] ) );
		$this->script_data['height'] = trim( str_replace( 'px', '', $this->size['height'] ) );
		$this->script_data['overlay_close'] = $this->overlay_close;
		$this->script_data['display'] = $this->display;
		$this->script_data['display_data'] = $this->display_data;
		$this->script_data['scroll_body'] = $this->scroll_body;
		$this->script_data['form_submit'] = $this->form_submit;
		$this->script_data['animation_in'] = $this->animation_in;
		$this->script_data['animation_out'] = $this->animation_out;
		$this->script_data['show_on_load'] = $this->show_on_load;

		// Validation only done when editing popups.
		if ( is_admin() && $this->id >= 0 ) {
			// Name.
			if ( empty( $this->name ) ) {
				$this->name = __( 'New PopUp', PO_LANG );
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
		$this->image_mobile = get_post_meta( $this->id, 'po_image_mobile', true );
		$this->subtitle = get_post_meta( $this->id, 'po_subtitle', true );
		$this->cta_label = get_post_meta( $this->id, 'po_cta_label', true );
		$this->cta_link = get_post_meta( $this->id, 'po_cta_link', true );
		$this->custom_size = get_post_meta( $this->id, 'po_custom_size', true );
		$this->size = get_post_meta( $this->id, 'po_size', true );
		$this->color = get_post_meta( $this->id, 'po_color', true );
		$this->custom_colors = get_post_meta( $this->id, 'po_custom_colors', true );
		$this->style = get_post_meta( $this->id, 'po_style', true );
		$this->custom_css = get_post_meta( $this->id, 'po_custom_css', true );
		$this->animation_in = get_post_meta( $this->id, 'po_animation_in', true );
		$this->animation_out = get_post_meta( $this->id, 'po_animation_out', true );
		$this->show_on_load = get_post_meta( $this->id, 'po_show_on_load', true );
		$this->custom_class = get_post_meta( $this->id, 'po_custom_class', true );
		$this->round_corners = get_post_meta( $this->id, 'po_round_corners', true );
		$this->scroll_body = get_post_meta( $this->id, 'po_scroll_body', true );
		$this->can_hide = get_post_meta( $this->id, 'po_can_hide', true );
		$this->close_hides = get_post_meta( $this->id, 'po_close_hides', true );
		$this->hide_expire = get_post_meta( $this->id, 'po_hide_expire', true );
		$this->overlay_close = get_post_meta( $this->id, 'po_overlay_close', true );
		$this->form_submit = get_post_meta( $this->id, 'po_form_submit', true );
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
		if ( $this->content != $this->orig_content
			&& ! current_user_can( 'unfiltered_html' )
		) {
			$this->content = wp_kses( $this->content, $allowedposttags );
		}

		// Check if the content contains (potentially) incompatible shortcodes.
		self::validate_shortcodes( $this->content );

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
			update_post_meta( $this->id, 'po_image_mobile', $this->image_mobile );
			update_post_meta( $this->id, 'po_subtitle', $this->subtitle );
			update_post_meta( $this->id, 'po_cta_label', $this->cta_label );
			update_post_meta( $this->id, 'po_cta_link', $this->cta_link );
			update_post_meta( $this->id, 'po_custom_size', $this->custom_size );
			update_post_meta( $this->id, 'po_size', $this->size );
			update_post_meta( $this->id, 'po_color', $this->color );
			update_post_meta( $this->id, 'po_custom_colors', $this->custom_colors );
			update_post_meta( $this->id, 'po_style', $this->style );
			update_post_meta( $this->id, 'po_custom_css', $this->custom_css );
			update_post_meta( $this->id, 'po_animation_in', $this->animation_in );
			update_post_meta( $this->id, 'po_animation_out', $this->animation_out );
			update_post_meta( $this->id, 'po_show_on_load', $this->show_on_load );
			update_post_meta( $this->id, 'po_custom_class', $this->custom_class );
			update_post_meta( $this->id, 'po_round_corners', $this->round_corners );
			update_post_meta( $this->id, 'po_scroll_body', $this->scroll_body );
			update_post_meta( $this->id, 'po_can_hide', $this->can_hide );
			update_post_meta( $this->id, 'po_close_hides', $this->close_hides );
			update_post_meta( $this->id, 'po_hide_expire', $this->hide_expire );
			update_post_meta( $this->id, 'po_overlay_close', $this->overlay_close );
			update_post_meta( $this->id, 'po_form_submit', $this->form_submit );
			update_post_meta( $this->id, 'po_display', $this->display );
			update_post_meta( $this->id, 'po_display_data', $this->display_data );
			update_post_meta( $this->id, 'po_rule', $this->rule );
			update_post_meta( $this->id, 'po_rule_files', $this->rule_files );
			update_post_meta( $this->id, 'po_rule_data', $this->rule_data );
		}

		if ( $show_message ) {
			if ( ! empty( $res ) ) {
				if ( $this->orig_status === $this->status ) {
					$msg = __( 'Saved PopUp "<strong>%1$s</strong>"', PO_LANG );
				} else {
					switch ( $status ) {
						case 'publish':
							$msg = __( 'Activated PopUp "<strong>%1$s</strong>".', PO_LANG );
							break;

						case 'draft':
							$msg = __( 'Deactivated PopUp "<strong>%1$s</strong>".', PO_LANG );
							break;

						case 'trash':
							$msg = __( 'Moved PopUp "<strong>%1$s</strong>" to trash.', PO_LANG );
							break;

						default:
							$msg = __( 'Saved PopUp "<strong>%1$s</strong>".', PO_LANG );
							break;
					}
				}
				lib2()->ui->admin_message( sprintf( $msg, $this->name ) );
			} else {
				lib2()->ui->admin_message( __( 'Could not save PopUp.', PO_LANG ), 'err' );
			}
		}

		return true;
	}

	/**
	 * Parses the specified content and looks for shortcodes that are not
	 * compatible with the current PopUp loading method.
	 *
	 * The function does not return a value, but if incompatible shortcodes are
	 * detected a new Admin Notification will be generated which is displayed to
	 * the user after the page has finished loading.
	 *
	 * @since  4.7.0
	 * @param  string $content
	 */
	public static function validate_shortcodes( $content ) {
		$settings = IncPopupDatabase::get_settings();
		$method = isset( $settings['loadingmethod'] ) ? $settings['loadingmethod'] : 'ajax';

		// Check for specific/frequently used shortcodes.

		if ( 'footer' !== $method
			&& preg_match( '#\[gravityforms?(\s.*?\]|\])#', $content )
		) {
			lib2()->ui->admin_message(
				sprintf(
					__( 'You are using Gravity Forms inside this PopUp. It is best to switch to the <a href="%s">loading method</a> "Page Footer" to ensure the form works as expected.', PO_LANG ),
					'edit.php?post_type=' . IncPopupItem::POST_TYPE . '&page=settings'
				),
				'err'
			);
		}

		// General check for shortcode incompatibility

		switch ( $method ) {
			case 'ajax':
			case 'anonymous':
				// Check if the content contains any of the Front-Shortcodes:
				$check = IncPopupAddon_HeaderFooter::check();
				$content = do_shortcode( $content );
				foreach ( $check->shortcodes as $code ) {
					$match = array();
					if ( preg_match( '#\[' . $code . '(\s.*?\]|\])#', $content, $match ) ) {
						lib2()->ui->admin_message(
							sprintf(
								__( 'Shortcode <code>%s</code> requires a different <a href="%s">loading method</a> to work.<br />Try "Page Footer", though sometimes the method "Custom AJAX" also works (please test the result)', PO_LANG ),
								$match[0],
								'edit.php?post_type=' . IncPopupItem::POST_TYPE . '&page=settings'
							),
							'err'
						);
					}
				}
				break;

			case 'footer':
			case 'front':
				// Nothing needs to be validated here...
				break;

			default:
				//lib2()->ui->admin_message( 'Shortcode-Check not defined for: ' . $method );
		}
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
	 * Load the PopUp HTML code from the popup.php template.
	 *
	 * @since  4.6
	 * @return string HTML code.
	 */
	protected function load_html() {
		static $Html = array();

		if ( ! isset( $Html[ $this->id ] ) ) {
			$styles = apply_filters( 'popup-styles', array() );
			$details = $styles[$this->style];

			$Html[ $this->id ] = '';
			$tpl_file = $details->dir . 'template.php';

			if ( file_exists( $tpl_file ) ) {
				ob_start();
				include( $tpl_file );
				$Html[ $this->id ] = ob_get_contents();
				ob_end_clean();

				$Html[ $this->id ] = str_replace( array( "\t", "\r", "\n", '     ' ), ' ', $Html[ $this->id ] );
				$Html[ $this->id ] = str_replace( array( '    ', '   ', '  ' ), ' ', $Html[ $this->id ] );
				$Html[ $this->id ] = str_replace( '#000001', $this->code->color1, $Html[ $this->id ] );
				$Html[ $this->id ] = str_replace( '#000002', $this->code->color2, $Html[ $this->id ] );
			}
		}

		return $Html[ $this->id ];
	}

	/**
	 * Load the PopUp CSS styles from the style.css template.
	 *
	 * @since  4.6
	 * @return string CSS code.
	 */
	protected function load_styles() {
		static $Code = array();

		if ( ! isset( $Code[ $this->id ] ) ) {
			$styles = apply_filters( 'popup-styles', array() );
			$details = $styles[$this->style];

			$Code[ $this->id ] = '';
			$tpl_file = $details->dir . 'style.css';

			if ( file_exists( $tpl_file ) ) {
				ob_start();
				include( $tpl_file );
				$Code[ $this->id ] = ob_get_contents();
				ob_end_clean();

				$Code[ $this->id ] = str_replace( '#messagebox', '.' . $this->code->cls, $Code[ $this->id ] );
				$Code[ $this->id ] = str_replace( '%styleurl%', $details->url, $Code[ $this->id ] );
				$Code[ $this->id ] = str_replace( '#000001', $this->code->color1, $Code[ $this->id ] );
				$Code[ $this->id ] = str_replace( '#000002', $this->code->color2, $Code[ $this->id ] );
			}
			$custom_css = $this->custom_css;
			$custom_css = str_replace( '#popup', '#' . $this->code->id, $custom_css );
			$custom_css = str_replace( '#messagebox', '.' . $this->code->cls, $custom_css );
			$custom_css = str_replace( '%styleurl%', $details->url, $custom_css );
			$Code[ $this->id ] .= $custom_css;
		}
		return $Code[ $this->id ];
	}

	/**
	 * Returns the script-data collection.
	 *
	 * @since  4.6
	 * @param  bool $is_preview Optional. Defines if we display a preview of the
	 *                PopUp (Dashboard) or the real PopUp (Front End)
	 * @return array
	 */
	public function get_script_data( $is_preview = false ) {
		static $Data = array();

		if ( ! isset( $Data[ $this->id ] ) ) {
			$this->is_preview = $is_preview;
			$Data[ $this->id ] = $this->script_data;
			$Data[ $this->id ]['html'] = $this->load_html();
			$Data[ $this->id ]['styles'] = $this->load_styles();

			$Data[ $this->id ] = apply_filters( 'popup-output-data', $Data[ $this->id ], $this );

			if ( $is_preview ) {
				$Data[ $this->id ] = $this->preview_mode( $Data[ $this->id ] );
			}
		}

		return $Data[ $this->id ];
	}

	/**
	 * Change some script_data properties for displaying a popup-preview.
	 *
	 * @since  4.6
	 * @param  array $data The PopUp data collection.
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