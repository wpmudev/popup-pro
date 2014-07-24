<?php
// Load dependencies.
require_once PO_INC_DIR . 'class-popup-item.php';
require_once PO_INC_DIR . 'class-popup-database.php';
require_once PO_INC_DIR . 'class-popup-posttype.php';

require_once PO_INC_DIR . 'functions.php';

/**
 * Defines common functions that are used in admin and frontpage.
 */
abstract class IncPopupBase {

	/**
	 * Holds the IncPopupDatabase instance.
	 * @var IncPopupDatabase
	 */
	protected $db = null;

	/**
	 * Constructor
	 */
	protected function __construct() {
		$this->db = IncPopupDatabase::instance();

		// Register the popup post type.
		add_action(
			'init',
			array( 'IncPopupPosttype', 'instance' )
		);
	}

	/**
	 * Returns an IMG tag that displays the defined image.
	 *
	 * @since  4.6
	 * @param  string $image The image file name.
	 * @return string HTML code
	 */
	static function help_img( $image ) {
		return '<img src="' . esc_attr( PO_HELP_URL . 'img/' . $image ) . '" />';
	}

};