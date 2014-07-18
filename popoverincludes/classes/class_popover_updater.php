<?php


/**
 * Updater class.
 * Will be conditionally loaded for porting old popovers.
 */
class Popover_Updater {

	private $_data;

	private function __construct () {
		$this->_data = Popover_Data::get_instance();
	}

	public static function serve () {
		$me = new self;
		$me->_add_hooks();
	}

	private function _add_hooks () {
		if (!is_admin()) return false;
		add_action('load-toplevel_page_popover', array($this, 'dispatch_required_update'));
		if (defined('POPOVER_LEGACY_JAVASCRIPT_DIFFERENTIATION') && POPOVER_LEGACY_JAVASCRIPT_DIFFERENTIATION) {
			add_action('admin_notices', array($this, 'deprecated_differentiation'));
		}
	}

	public function dispatch_required_update () {
		if ($this->_data->has_existing_popover()) {
			add_action('all_admin_notices', array($this, 'show_popover_transfer_offer'));
		}
		// Check for transfer
		if (isset($_GET['transfer'])) {
			$this->_handle_popover_transfer();
		}
	}

	public function show_popover_transfer_offer () {
		echo '<div class="updated fade below-h2"><p>' . 
			sprintf(
				__("Welcome to Popover, would you like to transfer your existing Popover to the new format? <a href='%s'>Yes please transfer it</a> / <a href='%s'>No thanks, I'll create a new one myself.</a>", 'popover'), 
				wp_nonce_url('admin.php?page=popover&amp;transfer=yes', 'transferpopover'), 
				wp_nonce_url('admin.php?page=popover&amp;transfer=no','notransferpopover')
			) . 
		'</p></div>';
	}

	public function deprecated_differentiation () {
		echo '<div class="error fade"><p>' .
			__('Legacy javascript differentiation has been deprecated for your popovers.', 'popover') .
		'</p></div>';
	}

	private function _handle_popover_transfer () {
		if(function_exists('get_site_option') && defined('PO_GLOBAL') && PO_GLOBAL == true) {
			$updateoption = 'update_site_option';
			$getoption = 'get_site_option';
		} else {
			$updateoption = 'update_option';
			$getoption = 'get_option';
		}

		switch($_GET['transfer']) {

			case 'yes':		
				check_admin_referer('transferpopover');
				$popover = array();

				$popover['popover_title'] = __('Transferred Popover', 'popover');
				$popover['popover_content'] = $getoption('popover_content');

				$popover['popover_settings'] = array();
				$popover['popover_settings']['popover_size'] = $getoption('popover_size');
				$popover['popover_settings']['popover_location'] = $getoption('popover_location');;
				$popover['popover_settings']['popover_colour'] = $getoption('popover_colour');
				$popover['popover_settings']['popover_margin'] = $getoption('popover_margin');
				$popover['popover_settings']['popover_check'] = $getoption('popover_check');

				$popover['popover_settings']['popover_count'] = $getoption('popover_count');
				$popover['popover_settings']['popover_usejs'] = $getoption('popover_usejs');

				$popover['popover_settings']['popover_style'] = 'Default';

				$popover['popover_settings'] = serialize($popover['popover_settings']);

				$popover['popover_active'] = 1;

				$this->_data->save($popover);
				wp_safe_redirect(remove_query_arg('transfer', remove_query_arg('_wpnonce')));
				break;

			case 'no':		
				check_admin_referer('notransferpopover');
				$updateoption('popover_notranfers', 'yes');
				wp_safe_redirect(remove_query_arg('transfer', remove_query_arg('_wpnonce')));
				break;
		}
	}

}