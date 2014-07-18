<?php

class Popover {
	
	private function __construct () {
		// Set up my location
		set_popover_url(__FILE__);
		set_popover_dir(__FILE__);

		require_once(popover_dir('/popoverincludes/classes/class_popover_data.php'));
		require_once(popover_dir('/popoverincludes/classes/popover_rules.php'));


		if(is_admin()) {

			require_once(popover_dir('/popoverincludes/includes/class_wd_help_tooltips.php'));
			require_once(popover_dir('/popoverincludes/classes/popover.help.php'));
			require_once(popover_dir('/popoverincludes/classes/popoveradmin.php'));
			require_once(popover_dir('/popoverincludes/classes/popoverajax.php'));

			require_once(popover_dir('/popoverincludes/classes/class_popover_updater.php'));

			$popover = new popoveradmin();
			$popoverajax = new popoverajax();
			
			if (file_exists(popover_dir('/popoverincludes/external/wpmudev-dash-notification.php'))) {
				// Dashboard notification
				global $wpmudev_notices;
				if (!is_array($wpmudev_notices)) $wpmudev_notices = array();
				$wpmudev_notices[] = array(
					'id' => 123,
					'name' => 'Popover plugin',
					'screens' => array(
						//'toplevel_page_popover', // Exclude the working pages
						'pop-overs_page_popoversettings',
						'pop-overs_page_popoveraddons',
					),
				);
				include_once(popover_dir('/popoverincludes/external/wpmudev-dash-notification.php'));
			}
		} else {
			require_once(popover_dir('/popoverincludes/classes/popoverajax.php'));
			require_once(popover_dir('/popoverincludes/classes/popoverpublic.php'));

			$popover = new popoverpublic();
			$popoverajax = new popoverajax();
		}

		load_popover_addons();
	}

	public static function serve () {
		$me = new self;
		$me->_add_hooks();
	}

	private function _add_hooks () {
		add_action('plugins_loaded', array($this, 'load_textdomain'));
	}

	public function load_textdomain () {
		$locale = apply_filters('popover_locale', get_locale());
		$mofile = popover_dir("popoverincludes/languages/popover-{$locale}.mo");
		if (file_exists($mofile)) load_textdomain('popover', $mofile);
	}

}