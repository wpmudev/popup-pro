<?php
/**
 * Contextual Plugin Help
 */

add_action( 'popup-init', array( 'IncPopupHelp', 'instance' ) );

class IncPopupHelp {

	/**
	 * Singleton getter.
	 *
	 * @since  4.6.1.1
	 */
	static public function instance() {
		static $Inst = null;

		if ( null === $Inst ) {
			$Inst = new IncPopupHelp();
		}

		return $Inst;
	}

	/**
	 * Private constructor.
	 *
	 * @since  4.6.1.1
	 */
	private function __construct() {
		add_filter(
			'contextual_help',
			array( $this, 'setup_help' ),
			5, 3
		);
	}

	/**
	 * Prepare the Help-Tab of the current page.
	 *
	 * @since  4.6.1.1
	 * @param  object $help_obj
	 * @param  object $screen_id
	 * @param  object $screen
	 * @return object Modified $help_obj object.
	 */
	public function setup_help( $help_obj, $screen_id, $screen ) {
		$included_screens = array(
			'inc_popup',
		);

		if ( ! in_array( $screen_id, $included_screens ) ) {
			return;
		}

		// -- Help Tab: Shortcodes -----
		$screen->add_help_tab(
			array(
				'id'       => 'help_shortcodes',
				'title'    => __( 'Shortcodes', PO_LANG ),
				'callback' => array( $this, 'content_shortcodes' ),
			)
		);

		return $help_obj;
	}

	/**
	 * Output help contents for section "Shortcodes"
	 *
	 * @since  4.6.1.1
	 */
	public function content_shortcodes() {
		include PO_VIEWS_DIR . 'info-shortcodes.php';
	}

};