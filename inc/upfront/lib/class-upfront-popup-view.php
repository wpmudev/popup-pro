<?php
/**
 * The PopUp View element. Main PHP class needed for the integration.
 * Responsible to create the markup, parse parameters of a single element,
 * display the real PopUp in live mode, etc.
 *
 * @uses Upfront_Object (class_upfront_output.php)
 * @uses IUpfront_Server (class_upfront_output.php)
 *
 * @since 4.8.0.0
 */
class Upfront_PopupView extends Upfront_Object {

	// -------------------------------------------------------------------------
	// -------------------------------------------------------- STATIC FUNCTIONS

	/**
	 * Append plugin specific data to Upfront. We'll use this data later in
	 * the javascript module.
	 *
	 * @since  4.8.0.0
	 * @param  array $data The Upfront.data array.
	 * @return array Modified Upfront.data array.
	 */
	public function upfront_data( $data ) {
		$style_infos = IncPopup::style_infos();
		$animations = IncPopup::get_animations();

		$data['upfront_popup'] = array(
			'defaults' => self::default_properties(),
			'rules' => self::get_all_rules(),
			'animations' => $animations,
			'styles' => $style_infos,
		);

		return $data;
	}

	/**
	 * Injects details about the current Upfront page to PopUp Ajax requests.
	 * (Required for any ajax loading method)
	 *
	 * @since  4.8.0.0
	 */
	public static function inject_ajax_posttype( $data ) {
		$resolved_ids = Upfront_EntityResolver::get_entity_ids();

		$data['ajax_data'] = lib2()->array->get( $data['ajax_data'] );
		$data['ajax_data']['uf_ids'] = $resolved_ids;

		return $data;
	}

	/**
	 * Returns an array of properties that are passed to the Upfront editor
	 * and define defaults for each new element that is inserted to the page.
	 *
	 * These properties are stored in `Upfront.data.upfront_popup.defaults`
	 *
	 * @since  4.8.0.0
	 * @api
	 *
	 * @return array Collection of the default attributes
	 */
	public static function default_properties() {
		$defaults = array(
			// Upfront-specific defaults:
			'type' => 'PopupModel', // Has to match the JS class!
			'view_class' => 'PopupView', // Has to match the JS class + the PHP class!
			'class' => 'c24 upfront-popup_element_object',
			'has_settings' => 1,
			'id_slug' => 'upfront-popup_element',

			// Popup-specific defaults:
			'popup__title' => __( 'Your new PopUp', PO_LANG ),
			'popup__subtitle' => __( 'You\'ll love it!', PO_LANG ),
			'popup__style' => 'simple',
			'popup__content' => __( 'See this new PopUp here?', PO_LANG ),
			'popup__round_corners' => array( 'yes' ),
			'popup__cta_label' => '',
			'popup__cta_link' => '',
			'popup__image' => '',
			'popup__image_pos' => 'left',
			'popup__image_not_mobile' => '',
			'popup__custom_size' => '',
			'popup__rule' => array(),
			'popup__rule_data' => array(),
			'popup__display' => 'delay',
			'popup__display_data__delay' => '0',
			'popup__display_data__delay_type' => 's',
			'popup__display_data__scroll' => '0',
			'popup__display_data__scroll_type' => '%',
			'popup__display_data__anchor' => '',
			'popup__display_data__click' => '',
			'popup__display_data__click_multi' => '',
			'popup__can_hide' => '',
			'popup__close_hides' => '',
			'popup__hide_expire' => PO_DEFAULT_EXPIRY,
			'popup__overlay_close' => array( 'yes' ),
			'popup__form_submit' => 'default',
		);

		return apply_filters( 'po_upfront_defaults', $defaults );
	}

	/**
	 * Do the actual work of this plugin: Generate HTML Code that will display
	 * the PopUp!
	 *
	 * Keep in mind: This function is also invoked via Ajax.
	 * {@see Upfront_PopupAjax::json_get_markup()}
	 *
	 * CSS and JS files should be enqueued using `upfront_add_element_style`
	 * and `upfront_add_element_script` (don't use other methods!)
	 * This way the scripts are concatenated and cached. Yay!
	 *
	 * @since  4.8.0.0
	 * @api
	 *
	 * @param  array $properties Element properties.
	 *                           These are the default_properties defined above.
	 * @return string HTML Code to output on the page.
	 */
	public static function get_element_markup( $properties ) {
		// Sanitize the properties.
		$properties = upfront_properties_to_array( $properties );
		lib2()->array->equip( $properties, '_is_editor' );

		// Flag "_is_editor" is set in Upfront_PopupAjax::json_get_markup()
		if ( ! $properties['_is_editor'] ) { return ''; }

		// Extract the PopUp details and escape some values.
		$popup_args = self::extract_popup_args( $properties );

		// Generate a unique preview ID.
		$popup_args['id'] = -1 * rand( 1000, 9999 ) . date( 'disH' );

		/*
		 * This PopUp is a preview in the Upfront editor. We're going to
		 * make small adjustments that will not be made to the real PopUp.
		 */
		$popup_args['show_on_load'] = true;
		$popup_args['custom_class'][] = 'inline';

		// Filter provided by plugin "Upfront Debugger"
		$popup_args['content'] = apply_filters(
			'uf_debugger_mark_element',
			$popup_args['content']
		);

		// Create a populated PopUp item.
		$popup = new IncPopupItem( $popup_args );
		$data = $popup->get_script_data();

		ob_start();
		foreach ( IncPopupRules::$rules as $prio => $list ) {
			foreach ( $list as $key => $rule ) {
				$rule->obj->_admin_rule_form( $key, $rule, $popup );
			}
		}
		$rule_forms = ob_get_clean();

		// Prepare the response code.
		$code = sprintf(
			'<div class="upfront_popup">%1$s<ul class="forms" style="display:none">%3$s</ul></div><style>%2$s</style>',
			$data['html'] . '<hr>' . print_r( $popup->rule_data, true ) . '<hr>',
			$data['styles'],
			$rule_forms
		);

		return apply_filters(
			'po_upfront_element',
			$code,
			$popup_args,
			$properties
		);
	}

	/**
	 * Add the translations required by our JS library to the translation array.
	 *
	 * Called by hook: 'upfront_l10n' {@see loader.php}
	 *
	 * @since 4.8.0.0
	 * @internal
	 *
	 * @param array $translations
	 */
	public static function add_l10n_strings( $translations ) {
		if ( empty( $translations['popup_element'] ) ) {
			$translations['popup_element'] = self::_get_l10n();
		}

		return $translations;
	}

	/**
	 * Returns the translation strings that are needed by the JS library.
	 *
	 * @since  4.8.0.0
	 * @internal
	 *
	 * @param  string $key Optional. If specified then a single
	 *                translation element will be returned (identified by $key).
	 *                Otherwise the whole translation array is returned.
	 * @return string|array
	 */
	private static function _get_l10n( $key = null ) {
		$l10n = array(
			'element_name' => __( 'PopUp', PO_LANG ),
			'hold_on' => __( 'Please, hold on', PO_LANG ),
			'edit_text' => __( 'Edit Contents', PO_LANG ),

			'fields' => array(
				// Custom Field: ImageField
				'preparing_image' => __( 'Nice image!', PO_LANG ),
				'select_image' => __( 'Select Image', PO_LANG ),
				'remove_image' => __( 'Remove Image', PO_LANG ),

				// Custom Field: RulesField
			),

			'settings' => array(
				// TAB: Contents
				'tab_contents' => __( 'Contents', PO_LANG ),
				'group_title' => __( 'PopUp Title', PO_LANG ),
				'group_cta' => __( 'Call To Action', PO_LANG ),
				'title' => __( 'Enter title...', PO_LANG ),
				'subtitle' => __( 'Enter subtitle...', PO_LANG ),
				'cta_label' => __( 'Click here!', PO_LANG ),
				'cta_link' => __( 'http://www...', PO_LANG ),
				'cta_target' => __( '_self (link target)', PO_LANG ),

				// TAB: Appearance
				'tab_appearance' => __( 'Appearance', PO_LANG ),
				'group_template' => __( 'PopUp Template', PO_LANG ),
				'group_image' => __( 'Feature Image', PO_LANG ),
				'group_size' => __( 'Size', PO_LANG ),
				'group_scrolling' => __( 'Scroll behavior', PO_LANG ),
				'group_animation' => __( 'Animations', PO_LANG ),
				'round_corners' => __( 'Round corners', PO_LANG ),
				'pos_left' => __( 'Left Side', PO_LANG ),
				'pos_right' => __( 'Right Side', PO_LANG ),
				'image_not_mobile' => __( 'Hide image on mobile devices', PO_LANG ),
				'responsive_size' => __( 'Responsive PopUp', PO_LANG ),
				'custom_size' => __( 'Static PopUp size', PO_LANG ),
				'scroll_body' => __( 'Allow scrolling when PopUp is open', PO_LANG ),
				'animation_in' => __( 'Loading animation', PO_LANG ),
				'animation_out' => __( 'Closing animation', PO_LANG ),

				// TAB: Behavior
				'tab_behavior' => __( 'Behavior', PO_LANG ),
				'group_appears_on' => __( 'Appear after/on', PO_LANG ),
				'group_hide' => __( '"Don\'t show again"', PO_LANG ),
				'group_closing' => __( 'Closing conditions', PO_LANG ),
				'group_forms' => __( 'Form submit', PO_LANG ),
				'display_option' => __( 'When to display the Pop-up', PO_LANG ),
				'display_option_delay' => __( 'After a delay', PO_LANG ),
				'display_option_scroll' => __( 'After scrolling to position', PO_LANG ),
				'display_option_anchor' => __( 'After scrolling to an element', PO_LANG ),
				'display_option_leave' => __( 'When mouse leaves the browser', PO_LANG ),
				'display_option_click' => __( 'When user clicks on an element', PO_LANG ),
				'display_delay' => __( 'After Delay of', PO_LANG ),
				'delay_type_sec' => __( 'Seconds', PO_LANG ),
				'delay_type_min' => __( 'Minutes', PO_LANG ),
				'display_scroll' => __( 'At scroll position', PO_LANG ),
				'scroll_type_percent' => __( '%', PO_LANG ),
				'scroll_type_px' => __( 'px', PO_LANG ),
				'display_anchor' => __( 'When scrolled to element selector', PO_LANG ),
				'display_click' => __( 'Click on element selector', PO_LANG ),
				'click_repeat' => __( 'Repeated', PO_LANG ),
				'hide_button' => __( 'Add "Don\'t show again" link', PO_LANG ),
				'hide_always' => __( 'Close button is "Don\'t show again"', PO_LANG ),
				'hide_expire' => __( 'Expire time in days', PO_LANG ),
				'overlay_close' => __( 'Click on background closes PopUp', PO_LANG ),
				'form_submit' => __( 'When a form is submitted', PO_LANG ),
				'form_submit_default' => __( 'Refresh contents or close (default)', PO_LANG ),
				'form_submit_ignore' => __( 'Keep PopUp open (Ajax forms)', PO_LANG ),
				'form_submit_redirect' => __( 'Redirect to form target URL', PO_LANG ),
				'hint_selector' => __( '.class or #id', PO_LANG ),

				// TAB: Rules
				'tab_rules' => __( 'Conditions', PO_LANG ),
				'group_rules' => __( 'Rules', PO_LANG ),
			),

			// Translations used for the built-in CSS editor.
			'css' => array(
				'header_label' => __( 'PopUp Header', PO_LANG ),
				'header_info' => __( 'The header contains the title and subtitle', PO_LANG ),
				'title_label' => __( 'PopUp Title', PO_LANG ),
				'title_info' => __( 'The main title of the PopUp', PO_LANG ),
				'subtitle_label' => __( 'PopUp Subtitle', PO_LANG ),
				'subtitle_info' => __( 'The subtitle of the PopUp', PO_LANG ),
				'cta_label' => __( 'CTA Button', PO_LANG ),
				'cta_info' => __( 'The Call-To-Action button', PO_LANG ),
				'buttons_label' => __( 'Button Area', PO_LANG ),
				'buttons_info' => __( 'Button area that contains the close and CTA buttons', PO_LANG ),
				'content_label' => __( 'PopUp text', PO_LANG ),
				'content_info' => __( 'Text inside the PopUp', PO_LANG ),
				'outer_content_label' => __( 'PopUp contents', PO_LANG ),
				'outer_content_info' => __( 'This area contains the text and button area', PO_LANG ),
				'popup_label' => __( 'PopUp container', PO_LANG ),
				'popup_info' => __( 'The whole PopUp container', PO_LANG ),
			),
		);

		// Return the requested value.
		if ( empty( $key ) ) {
			return $l10n;
		} elseif ( isset( $l10n[ $key ] ) ) {
			return $l10n[$key];
		} else {
			return $key;
		}
	}

	/**
	 * Returns the layout data for the current page.
	 *
	 * @since  4.8.0.0
	 * @return array Upfront Layout data.
	 */
	protected static function get_layout_data() {
		static $Layout = null;

		if ( null === $Layout ) {
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX && ! empty( $_GET['uf_ids'] ) ) {
				$resolved_ids = $_GET['uf_ids'];
			} else {
				$resolved_ids = Upfront_EntityResolver::get_entity_ids();
			}

			$output_obj = Upfront_Output::get_layout( $resolved_ids );
			$Layout = $output_obj->get_layout_data();
		}

		return $Layout;
	}

	/**
	 * Returns an ordered array that contains all available PopUp rules.
	 *
	 * @since  4.8.0.0
	 * @return array An ordered array of rules. Each item is an object with
	 *               $key and $label elements.
	 */
	protected static function get_all_rules() {
		$rules = array();

		/**
		 * Some PopUp Conditions are not supported in Upfront or they don't
		 * make any sense. So we hide some rules in the Upfront Settings.
		 *
		 * @var   array
		 * @since 4.8.0.0
		 */
		$skipped_rules = apply_filters(
			'po_upfront_skipped_rules',
			array(
				'url',
				'no_url',
			)
		);

		foreach ( IncPopupRules::$rules as $prio => $list ) {
			foreach ( $list as $key => $rule ) {
				if ( in_array( $key, $skipped_rules ) ) { continue; }

				$rules[] = (object) array(
					'key' => $key,
					'label' => $rule->label,
					'exclude' => $rule->exclude,
					'description' => $rule->description,
				);
			}
		}

		return apply_filters(
			'po_upfront_all_rules',
			$rules
		);
	}

	/**
	 * Extracts and (un)escapes popup details from the Upfront property list.
	 *
	 * @since  4.8.0.0
	 * @param  array $properties
	 * @return array
	 */
	public static function extract_popup_args( $properties ) {
		$popup_args = array();

		foreach ( $properties as $key => $value ) {
			if ( 0 === strpos( $key, 'popup__' ) ) {
				$key = substr( $key, 7 );

				if ( 0 === strpos( $key, 'display_data__' ) ) {
					$key = substr( $key, 14 );
					$popup_args['display_data'][$key] = $value;
				} else {
					$popup_args[$key] = $value;
				}
			}
		}

		lib2()->array->strip_slashes(
			$popup_args,
			'cta_label',
			'title',
			'subtitle'
		);

		// Make sure that all properties have a value
		lib2()->array->equip(
			$popup_args,
			'round_corners',
			'image_not_mobile',
			'display_data',
			'can_hide',
			'close_hides',
			'overlay_close',
			'scroll_body',
			'custom_size'
		);
		$popup_args['display_data'] = lib2()->array->get( $popup_args['display_data'] );
		lib2()->array->equip(
			$popup_args['display_data'],
			'click_multi'
		);

		// Translate checkbox-values to usable data.
		$popup_args['round_corners'] = is_array( $popup_args['round_corners'] );
		$popup_args['image_not_mobile'] = is_array( $popup_args['image_not_mobile'] );
		$popup_args['display_data']['click_multi'] = is_array( $popup_args['display_data']['click_multi'] );
		$popup_args['can_hide'] = is_array( $popup_args['can_hide'] );
		$popup_args['close_hides'] = is_array( $popup_args['close_hides'] );
		$popup_args['overlay_close'] = is_array( $popup_args['overlay_close'] );
		$popup_args['scroll_body'] = is_array( $popup_args['scroll_body'] );
		$popup_args['custom_size'] = ! empty( $popup_args['custom_size'] );

		// Some flags are negated for better UX, we need to invert them again.
		$popup_args['image_mobile'] = ! $popup_args['image_not_mobile'];

		// Get the PopUp contents from the Content-Region
		$layout = self::get_layout_data();
		$region_id = empty( $properties['content_region'] ) ? '' : $properties['content_region'];
		$popup_args['content'] = '';

		$popup_args['is_upfront'] = true;

		// Loop all regions to find the linked Content-Region.
		foreach ( $layout['regions'] as $region ) {
			if ( $region_id == $region['name'] ) {
				// Found the Content-Region: Now get the content as HTML markup.
				$region_view = new Upfront_Region( $region );
				$markup = trim( $region_view->get_markup() );

				/*
				 * We need to strip the outermost tag, since that is the
				 * lightbox-wrapper that has fixed positioning and other styling
				 * that messes with the Popup.
				 */
				$contents = preg_replace( '/^<[^>]+>|<\/[^>]+>$/', '', $markup );

				$popup_args['content'] = $contents;
				break;
			}
		}

		return $popup_args;
	}

	/**
	 * Returns a list of the popups on the current Upfront page.
	 *
	 * @since  4.8.0.0
	 * @param  array $list List of IncPopupItem objects.
	 * @param  IncPopup $base The base IncPopup object.
	 * @return array
	 */
	public static function select_popup( $list, $base ) {
		// Do not display any PopUp when user is in edit-mode.
		if ( ! empty( $_GET['editmode'] ) && is_user_logged_in() ) {
			return array();
		}

		$layout = self::get_layout_data();

		/*
		 * Upfront object hierarchy is quite nested:
		 *
		 * Layout
		 *   +-- region1
		 *   |     +-- module1
		 *   |     |     +-- object1
		 *   |     |     +-- object2
		 *   |     +-- module2
		 *   |    ...
		 *   +-- region2
		 *  ...
		 */
		foreach ( $layout['regions'] as $r_id => $region ) {
			if ( empty ( $region['modules'] ) ) { continue; }
			if ( ! is_array( $region['modules'] ) ) { continue; }

			foreach ( $region['modules'] as $m_id => $module ) {
				if ( empty ( $module['objects'] ) ) { continue; }
				if ( ! is_array( $module['objects'] ) ) { continue; }

				foreach ( $module['objects'] as $o_id => $object ) {
					$view_class = upfront_get_property_value( 'view_class', $object );

					if ( 'PopupView' == $view_class ) {
						$data = upfront_properties_to_array( $object['properties'] );
						$obj_id = '1' . $r_id . $m_id . $o_id;
						$po_args = self::extract_popup_args( $data );
						$po_args['custom_class'][] = $data['theme_style'];
						$po_args['id'] = $obj_id;
						$list[] = new IncPopupItem( $po_args );
					}
				}
			}
		}

		return $list;
	}

	// -------------------------------------------------------------------------
	// -------------------------------------------------------- OBJECT FUNCTIONS

	/**
	 * Constructor hooks up some actions/filters
	 *
	 * @since  4.8.0.0
	 */
	public function __construct( $data ) {
		parent::__construct( $data );

		// Possibility to add some Upfront hooks and filters...
	}

	/**
	 * Return the actual HTML code that represents the element on the page.
	 *
	 * CSS and JS files should be enqueued using `upfront_add_element_style`
	 * and `upfront_add_element_script` (don't use other methods!)
	 * This way the scripts are concatenated and cached. Yay!
	 *
	 * This function is used by public frontend and also for edit mode.
	 *
	 * @since  4.8.0
	 * @return string Full HTML Code to represent the element.
	 */
	public function get_markup() {
		// Demo code to illustrate how custom css/js files should be loaded.
		/*
		upfront_add_element_style(
			'upfront_popup',
			array( 'css/public.css', dirname( __FILE__ ) )
		);
		upfront_add_element_script(
			'upfront_popup',
			array( 'js/public.js', dirname( __FILE__ ) )
		);
		*/

		$properties = lib2()->array->get( $this->_data['properties'] );

		// The juicy stuff is in another function:
		return self::get_element_markup( $properties );
	}
}