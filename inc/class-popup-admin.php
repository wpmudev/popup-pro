<?php
// Load dependencies.
require_once PO_INC_DIR . 'class-popup-base.php';

/**
 * Defines the popup class for admin pages.
 *
 * @since  4.6
 */
class IncPopup extends IncPopupBase {

	/**
	 * Returns the singleton instance of the popup (admin) class.
	 *
	 * @since  4.6
	 */
	static public function instance() {
		static $Inst = null;

		if ( null === $Inst ) {
			$Inst = new IncPopup();
		}

		return $Inst;
	}

	/**
	 * Private constructor (singleton)
	 *
	 * @since  4.6
	 */
	protected function __construct() {
		parent::__construct();
	}

};