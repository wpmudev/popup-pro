<?php
/*
Addon Name: Show on click rule
Plugin URI: http://premium.wpmudev.org/project/the-pop-over-plugin/
Description: Hides popover initially and shows it on click
Author: Ve (Incsub)
Author URI: http://premium.wpmudev.org
Version: 1.0
*/

class Popover_Rules_Rule_OnClick extends Popover_Rules_Rule {

	const RULE = 'on_click';

	protected $_defaults = array(
		"selector" => 'a',
		"multi_open" => false,
	);

	public static function add () {
		$me = new self;
		return $me;
	}

	protected function __construct () {
		$this->_id = self::RULE;
		$this->_info = array(
			"title" => __('Show on user click', 'popover'),
			"message" => __('Shows the popover on user click.', 'popover'),
		);
		$this->_action = __('Show', 'popover');
		parent::__construct();
	}

	protected function _add_hooks () {
		parent::_add_hooks();
		add_filter('popover-output-popover', array($this, 'append_data'), 10, 2);
	}

	public function append_data ($data, $popover) {
		$settings = $popover->popover_settings;
		if (empty($settings[$this->_id]) || empty($settings[$this->_id]["selector"])) return $data;
		$data["click_selector"] = $settings[$this->_id]["selector"];
		$data["wait_for_event"] = true;
		$data["multi_open"] = !empty($settings[$this->_id]["multi_open"]);
		return $data;
	}

	public function apply_rule ($show, $popover) { return true; }

	public function get_admin_interface ($data) {
		$data = wp_parse_args($data[$this->_id], $this->_defaults);
		$markup = '';
		
		$markup .= '<label for="' . $this->_get_field_id("selector") . '">' . __('Element selector:', 'popover') . '</label> ';
		$markup .= '<input type="text" name="' . $this->_get_field_name("selector") . '" id="' . $this->_get_field_id("selector") . '" value="' . esc_attr($data["selector"]) . '" />';
		$markup .= '<p><em><small>' . __('The popover won\'t be shown until the user clicks on an element matching this selector', 'popover') . '</small></em></p>';

		$markup .= '<label for="' . $this->_get_field_id("multi_open") . '">' . 
				'<input type="checkbox" name="' . $this->_get_field_name("multi_open") . '" id="' . $this->_get_field_id("multi_open") . '" value="1" ' . checked($data["multi_open"], true, false) . '" />' .
			__('Allow multiple opening?', 'popover') . 
		'</label> ';
		$markup .= '<p><em><small>' . __('If this option is enabled, the message will open every time the selector element is clicked.', 'popover') . '</small></em></p>';

		return $markup;
	}

	public function save_settings ($settings) {
		if (empty($_POST[$this->_id])) return $settings;

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

class Popover_Rules_OnClick {

	private function __construct () {
		if (!(defined('POPOVER_LEGACY_JAVASCRIPT_DIFFERENTIATION') && POPOVER_LEGACY_JAVASCRIPT_DIFFERENTIATION)) {
			Popover_Rules_Rule_OnClick::add();
		} else {
			add_action('admin_notices', array($this, 'legacy_js_notice'));
		}
	}

	public static function serve () {
		$me = new self;
		$me->_add_hooks();
	}

	public function legacy_js_notice () {
		echo '<div class="error"><p>' . __('&quot;Show on click rule&quot; add-on won\'t work with legacy javascript', 'popover') . '</p></div>';
	}

	private function _add_hooks () {
		if (!(defined('POPOVER_LEGACY_JAVASCRIPT_DIFFERENTIATION') && POPOVER_LEGACY_JAVASCRIPT_DIFFERENTIATION)) add_action('wp_footer', array($this, 'inject_script'));
	}

	public function inject_script () {
		echo <<<EOJS
<script>
(function ($) {
$(document).on("popover-init", function (e, popover, data) {
	var data = data || {};
	if (!data.wait_for_event || !data.click_selector) return true;
	var el = $(data.click_selector);
	if (!el.length) return false;

	el.one("click", function (e) {
		popover.resolve();		
		return false;
	});
});
})(jQuery);
</script>
EOJS;
	}
}
Popover_Rules_OnClick::serve();