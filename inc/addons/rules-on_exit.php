<?php
/*
Addon Name:  Show on exit rule
Plugin URI:  http://premium.wpmudev.org/project/the-pop-over-plugin/
Description: Hides Pop Up until the user is about to leave the page.
Author:      Ve (Incsub)
Author URI:  http://premium.wpmudev.org
Type:        Rule
Version:     1.0
*/

class IncPopup_Rules_Rule_OnExit extends IncPopupRule {

	const RULE = 'on_exit';

	protected $_defaults = array(
		"fire_on_exit" => 'yes',
	);

	public static function add () {
		$me = new self;
		return $me;
	}

	protected function __construct () {
		$this->_id = self::RULE;
		$this->_info = array(
			"title" => __('Show on user exit attempt', 'popover'),
			"message" => __('Shows the popover on user exit attempt.', 'popover'),
		);
		$this->_action = __('Show', 'popover');
		parent::__construct();
	}

	protected function _add_hooks () {
		parent::_add_hooks();
		add_filter('popup-output-data', array($this, 'append_data'), 10, 2);
	}

	public function append_data ($data, $popover) {
		$settings = $popover->popover_settings;
		if (empty($settings[$this->_id]) || empty($settings[$this->_id]["fire_on_exit"])) return $data;
		$data["wait_for_event"] = true;
		$data["fire_on_exit"] = true;
		return $data;
	}

	public function apply_rule ($show, $popover) { return true; }

	public function get_admin_interface ($data) {
		$data = wp_parse_args($data[$this->_id], $this->_defaults);
		$markup = '';

		$markup .= '<input type="hidden" name="' . $this->_get_field_name("fire_on_exit") . '" value="' . esc_attr($data["fire_on_exit"]) . '" />';
		$markup .= '<p><em><small>' . __('The popover won\'t be shown until the user tries to leave the page', 'popover') . '</small></em></p>';

		return $markup;
	}

	public function save_settings ($settings) {
		if (empty($_POST[$this->_id])) {
			if (!empty($settings[$this->_id])) unset($settings[$this->_id]);
			return $settings;
		}

		$data = stripslashes_deep($_POST[$this->_id]);
		$result = array();
		$keys = array_keys($this->_defaults);
		foreach ($keys as $key) {
			if (empty($data[$key])) continue;
			$result[$key] = $data[$key];
		}
		$settings[$this->_id] = $result;
		return $settings;
	}

}

class IncPopup_Rules_OnExit {

	private function __construct () {
		IncPopup_Rules_Rule_OnExit::add();
	}

	public static function serve () {
		$me = new self;
		$me->_add_hooks();
	}

	public function legacy_js_notice () {
		echo '<div class="error"><p>' . __('&quot;Show on exit rule&quot; add-on won\'t work with legacy javascript', 'popover') . '</p></div>';
	}

	private function _add_hooks () {
		add_action('wp_footer', array($this, 'inject_script_exit'));
	}

	public function inject_script_exit () {
		echo <<<EOJS
<script>
(function ($) {
$(document).on("popover-init", function (e, popover, data) {
	var data = data || {};
	if (!data.wait_for_event || !data.fire_on_exit) return true;

	$(document).one("mouseleave", function () {
		popover.resolve();
		return false;
	});
});
})(jQuery);
</script>
EOJS;
	}
}
IncPopup_Rules_OnExit::serve();