<?php
/*
Addon Name: Show on scroll percentage
Plugin URI: http://premium.wpmudev.org/project/the-pop-over-plugin/
Description: Hides popover initially and shows it when visitor scrolls a certain percentage.
Author: Ve (Incsub)
Author URI: http://premium.wpmudev.org
Version: 1.0
*/

class Popover_Rules_Rule_PagePercentage extends Popover_Rules_Rule {

	const RULE = 'page_percentage';

	protected $_defaults = array(
		"percentage" => 50,
	);

	public static function add () {
		$me = new self;
		return $me;
	}

	protected function __construct () {
		$this->_id = self::RULE;
		$this->_info = array(
			"title" => __('Show on percentage', 'popover'),
			"message" => __('Shows the popover when visitor views certain percentage.', 'popover'),
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
		if (empty($settings[$this->_id]) || empty($settings[$this->_id]["percentage"])) return $data;
		$data["page_percentage"] = $settings[$this->_id]["percentage"];
		$data["wait_for_event"] = true;
		return $data;
	}

	public function apply_rule ($show, $popover) { return true; }

	public function get_admin_interface ($data) {
		$data = wp_parse_args($data[$this->_id], $this->_defaults);
		$markup = '';
		
		$markup .= '<label for="' . $this->_get_field_id("percentage") . '">' . __('Percentage viewed:', 'popover') . '</label> ';
		$markup .= '<input type="text" name="' . $this->_get_field_name("percentage") . '" id="' . $this->_get_field_id("percentage") . '" value="' . esc_attr($data["percentage"]) . '" />';
		$markup .= '<p><em><small>' . __('The popover won\'t be shown until the user scrolls through the defined percentage', 'popover') . '</small></em></p>';

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

class Popover_Rules_PagePercentage {

	private function __construct () {
		if (!(defined('POPOVER_LEGACY_JAVASCRIPT_DIFFERENTIATION') && POPOVER_LEGACY_JAVASCRIPT_DIFFERENTIATION)) {
			Popover_Rules_Rule_PagePercentage::add();
		} else {
			add_action('admin_notices', array($this, 'legacy_js_notice'));
		}
	}

	public static function serve () {
		$me = new self;
		$me->_add_hooks();
	}

	public function legacy_js_notice () {
		echo '<div class="error"><p>' . __('&quot;Show read percentage rule&quot; add-on won\'t work with legacy javascript', 'popover') . '</p></div>';
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
	if (!data.wait_for_event || !data.page_percentage) return true;

	$(window).on("scroll.popover_page_percentage", function () {
		var body = $(document),
			win = $(window)
		;
		if (
			parseInt(data.page_percentage, 10) <= (((win.scrollTop() + win.height()) / body.height()) * 100)
		) popover.resolve();
	});
});
})(jQuery);
</script>
EOJS;
	}
}
Popover_Rules_PagePercentage::serve();