<?php
/*
Addon Name:  Minimum width rule
Plugin URI:  http://premium.wpmudev.org/project/the-pop-over-plugin/
Description: Adds screen maximum width rule.
Author:      Ve (Incsub)
Author URI:  http://premium.wpmudev.org
Type:        Rule
Version:     1.0
*/

class Popover_Rules_Rule_MaxWidth extends Popover_Rules_Rule {

	const RULE = 'max_width';

	protected $_defaults = array(
		"width" => false,
	);

	public static function add () {
		$me = new self;
		return $me;
	}

	protected function __construct () {
		$this->_id = self::RULE;
		$this->_info = array(
			"title" => __('Show on windows wider than', 'popover'),
			"message" => __('Shows the popover on windows wider than threshold value.', 'popover'),
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
		if (empty($settings[$this->_id]) || empty($settings[$this->_id]["width"])) return $data;
		$data["threshold_width"] = (int)$settings[$this->_id]["width"];
		return $data;
	}

	public function apply_rule ($show, $popover) { return true; }

	public function get_admin_interface ($data) {
		$data = wp_parse_args($data[$this->_id], $this->_defaults);
		$markup = '';

		$markup .= '<label for="' . $this->_get_field_id("width") . '">' . __('Threshold width:', 'popover') . '</label> ';
		$markup .= '<input type="text" name="' . $this->_get_field_name("width") . '" id="' . $this->_get_field_id("width") . '" value="' . (int)$data["width"] . '" />px';

		return $markup;
	}

	public function save_settings ($settings) {
		if (empty($_POST[$this->_id])) return $settings;

		$data = stripslashes_deep($_POST[$this->_id]);
		$result = array();
		$keys = array_keys($this->_defaults);
		foreach ($keys as $key) {
			if (empty($data[$key])) continue;
			$result[$key] = (int)$data[$key];
		}
		$settings[$this->_id] = $result;
		return $settings;
	}
}

class Popover_Rules_MaxWidth {

	private function __construct () {
		if (!(defined('POPOVER_LEGACY_JAVASCRIPT_DIFFERENTIATION') && POPOVER_LEGACY_JAVASCRIPT_DIFFERENTIATION)) {
			Popover_Rules_Rule_MaxWidth::add();
		} else {
			add_action('admin_notices', array($this, 'legacy_js_notice'));
		}
	}

	public static function serve () {
		$me = new self;
		$me->_add_hooks();
	}

	public function legacy_js_notice () {
		echo '<div class="error"><p>' . __('&quot;Maximum width rule&quot; add-on won\'t work with legacy javascript', 'popover') . '</p></div>';
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
	if (!data.threshold_width) return true;
	if ($(window).width() > data.threshold_width) return true;
	popover.reject();
});
})(jQuery);
</script>
EOJS;
	}
}
Popover_Rules_MaxWidth::serve();