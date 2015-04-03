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

		$data['upfront_popup'] = array(
			'defaults' => self::default_properties(),
			'styles' => $style_infos,
		);

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
			'content_region' => false,

			// Popup-specific defaults:
			'popup__title' => __( 'Your new PopUp', PO_LANG ),
			'popup__subtitle' => __( 'You\'ll love it!', PO_LANG ),
			'popup__style' => 'simple',
			'popup__content' => __( 'See this new PopUp here?', PO_LANG ),
			'popup__round_corners' => array( 'yes' ),
			'popup__cta_label' => '',
			'popup__cta_link' => '',
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

		// Translate checkbox-values to usable data.
		$popup_args['round_corners'] = is_array( $popup_args['round_corners'] );

		// Create a populated PopUp item.
		$popup = new IncPopupItem( $popup_args );
		$data = $popup->get_script_data();

		// Prepare the response code.
		$code = sprintf(
			'<div class="upfront_popup">%1$s</div><style>%2$s</style>',
			$data['html'],
			$data['styles']
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

			'title' => __( 'Enter title...', PO_LANG ),
			'subtitle' => __( 'Enter subtitle...', PO_LANG ),

			'settings' => array(
				// TAB: Contents
				'tab_contents' => __( 'Contents', PO_LANG ),
				'group_cta' => __( 'Call To Action', PO_LANG ),
				'cta_label' => __( 'Label', PO_LANG ),
				'cta_link' => __( 'Link', PO_LANG ),

				// TAB: Appearance
				'tab_appearance' => __( 'Appearance', PO_LANG ),
				'group_template' => __( 'Template', PO_LANG ),
				'round_corners' => __( 'Round corners', PO_LANG ),

				// TAB: Behavior
				'tab_behavior' => __( 'Behavior', PO_LANG ),
				'group_appears_on' => __( 'Appear after/on', PO_LANG ),

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
			$resolved_ids = Upfront_EntityResolver::get_entity_ids();
			$output_obj = Upfront_Output::get_layout( $resolved_ids );
			$Layout = $output_obj->get_layout_data();
		}

		return $Layout;
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
				$popup_args[$key] = $value;
			}
		}

		lib2()->array->strip_slashes(
			$popup_args,
			'cta_label',
			'title',
			'subtitle'
		);

		// Get the PopUp contents from the Content-Region
		$layout = self::get_layout_data();
		$region_id = $properties['content_region'];
		$popup_args['content'] = '';

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
			foreach ( $region['modules'] as $m_id => $module ) {
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