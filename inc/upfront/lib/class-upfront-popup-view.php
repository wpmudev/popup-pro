<?php
/**
 * The PopUp element.
 * This is how we integrate to Upfront :-)
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
			'class' => 'c24 upfront-popup_element-object',
			'has_settings' => 1,
			'id_slug' => 'upfront-popup_element',

			// Popup-specific defaults:
			'title' => '',
			'subtitle' => '',
			'style' => 'simple',
			'content' => __( 'See this new PopUp here?', PO_LANG ),
			'round_corners' => true,
			'cta_label' => '',
			'cta_link' => '',
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
		$properties = upfront_properties_to_array( $properties );

		lib2()->array->equip( $properties, '_is_editor' );

		// Flag "_is_editor" is set in Upfront_PopupAjax::json_get_markup()
		if ( $properties['_is_editor'] ) {
			/*
			 * This PopUp is a preview in the Upfront editor. We're going to
			 * make small adjustments that will not be made to the real PopUp.
			 */
			$properties['show_on_load'] = true;
			$properties['custom_class'][] = 'inline';
		}

		// Translate checkbox-values to usable data.
		$properties['round_corners'] = is_array( $properties['round_corners'] );

		// Create a populated PopUp item.
		$popup = new IncPopupItem( $properties );
		$data = $popup->get_script_data();

		// Prepare the response code.
		$code = sprintf(
			'<div class="upfront_popup">%1$s</div><style>%2$s</style>',
			$data['html'],
			$data['styles']
		);

		return apply_filters( 'po_upfront_element', $code, $properties );
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
			'dbl_click' => __( 'Double click to edit PopUp contents', PO_LANG ),
			'settings' => __( 'Settings', PO_LANG ),

			'panel_appearance' => __( 'Appearance', PO_LANG ),
			'round_corners' => __( 'Round corners', PO_LANG ),

			'panel_cta' => __( 'Call To Action', PO_LANG ),
			'cta_label' => __( 'Label', PO_LANG ),
			'cta_link' => __( 'Link', PO_LANG ),

			'title' => __( 'Enter title...', PO_LANG ),
			'subtitle' => __( 'Enter subtitle...', PO_LANG ),
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
	 * Returns a list of the popups on the current Upfront page.
	 *
	 * @since  4.8.0.0
	 * @param  array $list List of IncPopupItem objects.
	 * @param  IncPopup $base The base IncPopup object.
	 * @return array
	 */
	public static function select_popup( $list, $base ) {
		//TODO: check with Ve/Ivan if this process is okay:
		$resolved_ids = Upfront_EntityResolver::get_entity_ids();
		$output_obj = Upfront_Output::get_layout( $resolved_ids );
		$layout = $output_obj->get_layout_data();

		$popups = array();

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
						$popups[] = $object;
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

		// Possibility to add new hooks...
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
		// Demo code to illustrate how css/js files should be loaded.
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