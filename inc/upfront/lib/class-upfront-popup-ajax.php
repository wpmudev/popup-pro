<?php
/**
 * The Popup Ajax server.
 * This class handles the server-side functionality of the Upfront integration.
 * Main purposes are to
 * (1) return updated preview markup during edit-mode and
 * (2) add the custom CSS styles to the styles in live-mode.
 *
 * @todo: Interface IUpfront_Server defines a static method. Static methods
 *        cannot be overwritten, so this is definition is pointless...
 *        http://stackoverflow.com/a/5986295/313501
 *
 * @uses Upfront_Server (class_upfront_server.php)
 * @uses IUpfront_Server (class_upfront_server.php)
 *
 * @since 4.8.0.0
 */
class Upfront_PopupAjax extends Upfront_Server {

	// -------------------------------------------------------------------------
	// -------------------------------------------------------- STATIC FUNCTIONS

	/**
	 * Returns the singleton object of the Upfront_PopupAjax server object.
	 *
	 * @since  4.8.0.0
	 * @api
	 *
	 * @return Upfront_PopupAjax
	 */
	public static function serve() {
		static $Instance = null;

		if ( null === $Instance ) {
			$Instance = new Upfront_PopupAjax();
		}

		return $Instance;
	}

	/**
	 * Modify the custom CSS code before it's saved to the DB.
	 *
	 * @since 4.8.0.0
	 * @internal
	 *
	 * @return string Modified CSS code.
	 */
	public static function save_styles( $style, $name, $element_type ) {
		if ( Upfront_PopupMain::TYPE == $element_type ) {
			$style = str_replace( '#page ', '.wdpu-container', $style );
		}

		return $style;
	}

	/**
	 * Revert CSS modification made in `save_styles` for the CSS editor.
	 * We do this, so the CSS editor will not modify the selectors by appending
	 * another #page selector to the front.
	 *
	 * @since  4.8.0.0
	 * @param  array $styles Array of custom CSS styles.
	 * @return array Modified list of styles.
	 */
	public static function theme_styles( $styles ) {
		if ( isset( $styles[Upfront_PopupMain::TYPE] ) ) {
			foreach ( $styles[Upfront_PopupMain::TYPE] as $key => $style ) {
				$style = str_replace( '.wdpu-container', '#page ', $style );
				$styles[Upfront_PopupMain::TYPE][ $key ] = $style;
			}
		}

		return $styles;
	}

	// -------------------------------------------------------------------------
	// -------------------------------------------------------- OBJECT FUNCTIONS

	/**
	 * Protected constructor: Singleton pattern.
	 *
	 * @since  4.8.0.0
	 * @internal
	 */
	protected function __construct() {
		parent::__construct();

		$this->_add_hooks();
	}

	/**
	 * Sets up the action hooks and filters.
	 * Only called by the __constructor()
	 *
	 * @since 4.8.0.0
	 * @internal
	 */
	private function _add_hooks() {
		// Set up Ajax handler
		add_action(
			'wp_ajax_upfront-popup_element-get_markup',
			array( $this, 'json_get_markup' )
		);
	}

	/**
	 * Ajax handler that returns the element contents in JSON format.
	 *
	 * This function is ONLY called during the edit mode when a new element is
	 * added to the layout.
	 *
	 * Output the response via `$this->_out( ... )`
	 * No need to `exit` or `die` in the end.
	 *
	 * @since  4.8.0.0
	 * @internal
	 */
	public function json_get_markup() {
		// Make sure $_POST['properties'] exists.
		lib2()->array->equip_post( 'properties' );

		// Flag properties to know we are in editor-mode.
		$_POST = upfront_set_property_value( '_is_editor', true, $_POST );

		// Make sure that $properties is an array.
		$properties = lib2()->array->get( $_POST['properties'] );

		// Fetch the HTML markup from the view.
		$markup = Upfront_PopupView::get_element_markup( $properties );

		// Convert markup to a JSON string.
		$json_response = new Upfront_JsonResponse_Success( $markup );

		// Return it! This function will also exit the request, so we're done.
		$this->_out( $json_response );
	}

}

// Initialize the Ajax Server instantly!
Upfront_PopupAjax::serve();