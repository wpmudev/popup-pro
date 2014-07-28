<?php

/**
 * This class represents a single popup object.
 * Data is stored in database as custom posttype.
 */
class IncPopupItem {

	// Popups are stored in DB as custom post-types. This is the post type key.
	const POST_TYPE = 'inc_popup';

	// The styles available for this popup.
	public $styles = array(
		'default',
		'simple',
		'cabriolet',
	);

	// The available options for the display setting.
	public $display_opts = array(
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

	// -- Appearance

	// CSS style of the popup.
	public $style = 'default';

	// Checkbox: Use custom size.
	public $custom_size = false;

	// Popup size (width, height of the box).
	public $size = array();

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

	// Appear after <delay> seconds.
	public $delay = 0;

	// Appear after scrolling <scroll> % of the page.
	public $scroll = 0;

	// Appear after scrolling to element <anchor>.
	public $anchor = '';

	// -- Conditions

	// Conditions that need to be true in order to use the popup.
	public $checks = array();

	// Extra arguments for the conditions (e.g. "check[0] = count" and "rules[count] = 3")
	public $rules = array();

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
		$this->custom_size = false;
		$this->size = array(
			'width' => null,
			'height' => null,
		);
		$this->color = array(
			'back' => null,
			'fore' => null,
		);
		$this->style = 'default';
		$this->round_corners = true;
		$this->can_hide = false;
		$this->close_hides = false;
		$this->hide_expire = 365;
		$this->overlay_close = true;
		$this->display = 'delay';
		$this->delay = 0;
		$this->scroll = 0;
		$this->anchor = '';
		$this->checks = array();
		$this->rules = array();
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

		isset( $data['id'] ) && $this->id = $data['id'];
		isset( $data['name'] ) && $this->name = $data['name'];
		isset( $data['order'] ) && $this->order = $data['order'];
		isset( $data['active'] ) && $this->status = $data['active'] ? 'active' : 'draft';
		isset( $data['status'] ) && $this->status = $data['status'];

		isset( $data['content'] ) && $this->content = $data['content'];
		isset( $data['image'] ) && $this->image = $data['image'];
		isset( $data['title'] ) && $this->title = $data['title'];
		isset( $data['subtitle'] ) && $this->subtitle = $data['subtitle'];
		isset( $data['cta_label'] ) && $this->cta_label = $data['cta_label'];
		isset( $data['cta_link'] ) && $this->cta_link = $data['cta_link'];
		isset( $data['custom_size'] ) && $this->custom_size = $data['custom_size'];

		is_numeric( @$data['size']['width'] ) && $this->size['width'] = absint( $data['size']['width'] );
		is_numeric( @$data['size']['height'] ) && $this->size['height'] = absint( $data['size']['height'] );

		isset( $data['color']['back'] ) && $this->color['back'] = $data['color']['back'];
		isset( $data['color']['fore'] ) && $this->color['fore'] = $data['color']['fore'];

		in_array( @$data['style'], $this->styles ) && $this->style = $data['style'];
		isset( $data['round_corners'] ) && $this->round_corners = (true == $data['round_corners']);
		isset( $data['can_hide'] ) && $this->can_hide = (true == $data['can_hide']);
		isset( $data['close_is_hide'] ) && $this->close_is_hide = (true == $data['close_is_hide']);
		is_numeric( @$data['hide_expire'] ) && $this->hide_expire = absint( $data['hide_expire'] );
		isset( $data['overlay_close'] ) && $this->overlay_close = ( true == $data['overlay_close'] );

		in_array( @$data['display'], $this->display_opts ) && $this->display = $data['display'];
		is_numeric( @$data['delay'] ) && $this->delay = absint( $data['delay'] );
		is_numeric( @$data['scroll'] ) && $this->scroll = absint( $data['scroll'] );
		isset( $data['anchor'] ) && $this->anchor = (true == $data['anchor']);

		is_array( @$data['checks'] ) && $this->checks = $data['checks'];
		is_array( @$data['rules'] ) && $this->rules = $data['rules'];

		// Remove empty/invalid conditions.
		foreach ( $this->checks as $ind => $key ) {
			if ( empty( $key ) ) {
				unset( $this->checks[$ind] );
			}
		}
		foreach ( $this->rules as $ind => $key ) {
			if ( empty( $key ) ) {
				unset( $this->rules[$ind] );
			}
		}

		// Check if the "id" is valid!
		if ( $this->id > 0 && self::POST_TYPE !== get_post_type( $this->id ) ) {
			$this->id = 0;
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
		if ( ! $post ) {
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
		$this->subtitle = get_post_meta( $this->id, 'po_subtitle', true );
		$this->cta_label = get_post_meta( $this->id, 'po_cta_label', true );
		$this->cta_link = get_post_meta( $this->id, 'po_cta_link', true );
		$this->custom_size = get_post_meta( $this->id, 'po_custom_size', true );
		$this->size = get_post_meta( $this->id, 'po_size', true );
		$this->color = get_post_meta( $this->id, 'po_color', true );
		$this->style = get_post_meta( $this->id, 'po_style', true );
		$this->round_corners = get_post_meta( $this->id, 'po_round_corners', true );
		$this->can_hide = get_post_meta( $this->id, 'po_can_hide', true );
		$this->close_hides = get_post_meta( $this->id, 'po_close_hides', true );
		$this->hide_expire = get_post_meta( $this->id, 'po_hide_expire', true );
		$this->overlay_close = get_post_meta( $this->id, 'po_overlay_close', true );
		$this->display = get_post_meta( $this->id, 'po_display', true );
		$this->delay = get_post_meta( $this->id, 'po_delay', true );
		$this->scroll = get_post_meta( $this->id, 'po_scroll', true );
		$this->anchor = get_post_meta( $this->id, 'po_anchor', true );
		$this->checks = get_post_meta( $this->id, 'po_checks', true );
		$this->rules = get_post_meta( $this->id, 'po_rules', true );

		if ( empty( $this->name ) ) {
			$this->name = __( 'Unnamed Pop Up', PO_LANG );
		}
	}

	/**
	 * Save the current popup to the database.
	 *
	 * @since  4.6
	 */
	public function save() {
		global $allowedposttags;

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
			update_post_meta( $this->id, 'po_subtitle', $this->subtitle );
			update_post_meta( $this->id, 'po_cta_label', $this->cta_label );
			update_post_meta( $this->id, 'po_cta_link', $this->cta_link );
			update_post_meta( $this->id, 'po_custom_size', $this->custom_size );
			update_post_meta( $this->id, 'po_size', $this->size );
			update_post_meta( $this->id, 'po_color', $this->color );
			update_post_meta( $this->id, 'po_style', $this->style );
			update_post_meta( $this->id, 'po_round_corners', $this->round_corners );
			update_post_meta( $this->id, 'po_can_hide', $this->can_hide );
			update_post_meta( $this->id, 'po_close_hides', $this->close_hides );
			update_post_meta( $this->id, 'po_hide_expire', $this->hide_expire );
			update_post_meta( $this->id, 'po_overlay_close', $this->overlay_close );
			update_post_meta( $this->id, 'po_display', $this->display );
			update_post_meta( $this->id, 'po_delay', $this->delay );
			update_post_meta( $this->id, 'po_scroll', $this->scroll );
			update_post_meta( $this->id, 'po_anchor', $this->anchor );
			update_post_meta( $this->id, 'po_checks', $this->checks );
			update_post_meta( $this->id, 'po_rules', $this->rules );

			if ( $this->orig_status === $this->status ) {
				$msg = __( 'Saved Pop Up "<strong>%1$s</strong>"', PO_LANG );
			} else {
				switch ( $this->status ) {
					case 'active':
						$msg = __( 'Activated Pop Up "<strong>%1$s</strong>".', PO_LANG );
						break;

					case 'inactive':
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
		return true;
	}

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