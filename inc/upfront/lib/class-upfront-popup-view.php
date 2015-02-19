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
class Upfront_Popup_View extends Upfront_Object {

	// -------------------------------------------------------------------------
	// -------------------------------------------------------- STATIC FUNCTIONS

	/**
	 * Set the default properties when a new element is added to the layout.
	 *
	 * @since  4.8.0
	 * @api
	 *
	 * @return array Collection of the default attributes
	 */
	public static function default_properties() {
		$defaults = array(
			// Nothing there yet
		);

		return apply_filers( 'po_upfront_defaults', $defaults );
	}

	/**
	 * Do the actual work of this plugin: Generate HTML Code that will display
	 * the PopUp!
	 *
	 * Keep in mind: This function is also invoked via Ajax.
	 * {@see Upfront_Popup_Ajax::json_get_markup()}
	 *
	 * CSS and JS files should be enqueued using `upfront_add_element_style`
	 * and `upfront_add_element_script` (don't use other methods!)
	 * This way the scripts are concatenated and cached. Yay!
	 *
	 * @since  4.8.0.0
	 * @api
	 *
	 * @param  array $properties Element properties.
	 * @return string HTML Code to output on the page.
	 */
	public static function get_element_markup( $properties ) {
		// Prepare the response code.
		$code = sprintf(
			'<div class="upfront_popup">%1$s</div>',
			'Hi there, I\'m supposed to be a PopUp sometime in the future! Yay :-)'
		);

		return apply_filers( 'po_upfront_element', $code, $properties );
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
		/*
		 * @todo: Replace this with real translations...
		 */
		$l10n = array(
			'element_name' => __( 'PopUp', PO_LANG ),
			'click_here' => __( 'Click here to reset it', PO_LANG ),
			'css' => array(
				'containers' => __( 'Field containers', PO_LANG ),
				'containers_info' => __( 'Wrapper layer for every field', PO_LANG ),
				'labels' => __( 'Field labels', PO_LANG ),
				'labels_info' => __( 'Labels for the input fields', PO_LANG ),
				'inputs' => __( 'Input fields', PO_LANG ),
				'inputs_info' => __( 'Username and password fields', PO_LANG ),
				'button' => __( 'Login button', PO_LANG ),
				'button_info' => __( 'Login button', PO_LANG ),
				'remember' => __( 'Remember me checkbox', PO_LANG ),
				'remember_info' => __( 'Remember me checkbox input.', PO_LANG ),
				'pwd_wrap' => __( 'Lost password wrapper', PO_LANG ),
				'pwd_wrap_info' => __( 'Container wrapper for the lost pasword function.', PO_LANG ),
				'pwd_link' => __( 'Lost password link', PO_LANG ),
				'pwd_link_info' => __( 'Link for lost passwords', PO_LANG ),
				'close' => __( 'Closed login link', PO_LANG ),
				'close_info' => __( 'The link that allows to open the login when the dropdown or lightbox option is selected.', PO_LANG ),
			),
			'hold_on' => __( 'Please, hold on', PO_LANG ),
			'settings' => __( 'Login settings', PO_LANG ),
			'display' => __( 'Display', PO_LANG ),
			'show_on_hover' => __( 'Show on hover', PO_LANG ),
			'show_on_click' => __( 'Show on click', PO_LANG ),
			'behavior' => __( 'Display behavior', PO_LANG ),
			'on_page' => __( 'Form on page', PO_LANG ),
			'dropdown' => __( 'Drop down form', PO_LANG ),
			'in_lightbox' => __( 'Form in lightbox', PO_LANG ),
			'appearance' => __( 'Display Appearance', PO_LANG ),
			'trigger' => __( 'Trigger', PO_LANG ),
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

	// -------------------------------------------------------------------------
	// -------------------------------------------------------- OBJECT FUNCTIONS

	/**
	 * Return the actual HTML code that represents the element on the page.
	 *
	 * CSS and JS files should be enqueued using `upfront_add_element_style`
	 * and `upfront_add_element_script` (don't use other methods!)
	 * This way the scripts are concatenated and cached. Yay!
	 *
	 * @todo: Is this function also called in the editor mode?
	 *
	 * @since  4.8.0
	 * @return string Full HTML Code to represent the element.
	 */
	public function get_markup() {
		/* // Demo code
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
		return $this->get_element_markup( $properties );
	}
}