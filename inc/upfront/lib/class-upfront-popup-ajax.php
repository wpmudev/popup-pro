<?php
/**
 * The Popup Ajax server.
 * This class handles the server-side functionality of the Upfront integration!
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
class Upfront_Popup_Ajax extends Upfront_Server {

	// -------------------------------------------------------------------------
	// -------------------------------------------------------- STATIC FUNCTIONS

	/**
	 * Returns the singleton object of the Upfront_Popup_Ajax server object.
	 *
	 * @since  4.8.0.0
	 * @api
	 *
	 * @return Upfront_Popup_Ajax
	 */
	public static function serve() {
		static $Instance = null;

		if ( null === $Instance ) {
			$Instance = new Upfront_Popup_Ajax();
		}

		return $Instance;
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
	 * This function is called during the edit mode when a new element is added
	 * to the layout.
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

		// Make sure that $properties is an array.
		$properties = lib2()->array->get( $_POST['properties'] );

		// Fetch the HTML markup from the view.
		$markup = Upfront_Popup_View::get_element_markup( $properties );

		// Convert markup to a JSON string.
		$json_response = new Upfront_JsonResponse_Success( $markup );

		// Return it! This function will also exit the request, so we're done.
		$this->_out( $json_response );
	}
}

// Initialize the Ajax Server instantly!
Upfront_Popup_Ajax::serve();