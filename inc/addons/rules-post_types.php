<?php
/*
Addon Name:  Post Types rules
Plugin URI:  http://premium.wpmudev.org/project/the-pop-over-plugin/
Description: Adds post type-related rules.
Author:      Ve (Incsub)
Author URI:  http://premium.wpmudev.org
Type:        Rule
Version:     1.0
*/


abstract class Popover_Rules_Rule_PostTypes extends Popover_Rules_Rule {

	protected $_defaults = array(
		"types" => array(),
		"urls" => array(),
	);

	public function apply_rule ($show, $popover) {
		$data = !empty($popover->popover_settings[$this->_id]) ? $popover->popover_settings[$this->_id] : false;
		if (empty($data)) return $show;

		$data = wp_parse_args($data, $this->_defaults);
		$post_type = $is_single = false;

		if (!empty($_REQUEST["thefrom"])) {
			// Via URL/AJAX
			$post_type = !empty($_REQUEST["post_type"]) ? $_REQUEST["post_type"] : false;
			$is_single = !empty($_REQUEST["is_single"]) ? (int)$_REQUEST["is_single"] : false;
		} else {
			// Via wp_footer
			$post_type = get_post_type();
			$is_single = is_singular();
		}

		if ($is_single && in_array("singular", $data["urls"])) {
			if (empty($data["types"])) return true; // Any post type, singular
			if (!empty($post_type)) return in_array($post_type, $data["types"]); // We have a post type
		}
		if (!$is_single && in_array("plural", $data["urls"])) {
			if (empty($data["types"])) return true; // Any post type, plural
			if (!empty($post_type)) return in_array($post_type, $data["types"]); // We have a post type
		}

		return $show; // Indeterminate post type, or some other fallback reason.
	}

	public function get_admin_interface ($data) {
		$data = wp_parse_args($data[$this->_id], $this->_defaults);
		$markup = '';
		$post_types = get_post_types(array(
			'public' => true,
		), 'objects');
		$url_types = array(
			'singular' => __('Singular', 'popover'),
			'plural' => __('Archive', 'popover'),
		);

		$markup .= '<fieldset><legend>' . sprintf(__('%s on these post types:', 'popover'), $this->_action) . '</legend>';
		foreach ($post_types as $key => $type) {
			$field_id = $this->_get_field_id("types", $key);
			$field_name = $this->_get_field_name("types", $key);
			$checked = in_array($key, $data["types"]) ? 'checked="checked"' : '';
			$markup .= "<input type='checkbox' id='{$field_id}' name='{$field_name}' {$checked} value='{$key}' />" .
				'&nbsp;' .
				"<label for='{$field_id}'>{$type->labels->name}</label>" .
			'<br />';
		}
		$markup .= '</fieldset>';

		$markup .= '<fieldset><legend>' . sprintf(__('%s on these post type URLs:', 'popover'), $this->_action) . '</legend>';
		foreach ($url_types as $url => $label) {
			$field_id = $this->_get_field_id("urls", $url);
			$field_name = $this->_get_field_name("urls", $url);
			$checked = in_array($url, $data["urls"]) ? 'checked="checked"' : '';
			$markup .= "<input type='checkbox' id='{$field_id}' name='{$field_name}' {$checked} value='{$url}' />" .
				'&nbsp;' .
				"<label for='{$field_id}'>{$label}</label>" .
			'<br />';
		}
		$markup .= '</fieldset>';

		return $markup;
	}
}

class Popover_Rules_Rule_NotPostTypes extends Popover_Rules_Rule_PostTypes {

	const RULE = 'not-post_types';

	public static function add () {
		$me = new self;
		return $me;
	}

	protected function __construct () {
		$this->_id = self::RULE;
		$this->_info = array(
			"title" => __('Suppress on Post Types', 'popover'),
			"message" => __('Suppresses the popover on specific post types.', 'popover'),
		);
		$this->_action = __('Suppress', 'popover');
		parent::__construct();
	}

	public function apply_rule ($show, $popover) {
		return !(parent::apply_rule($show, $popover));
	}
}

class Popover_Rules_Rule_OnPostTypes extends Popover_Rules_Rule_PostTypes {

	const RULE = 'post_types';

	public static function add () {
		$me = new self;
		return $me;
	}

	protected function __construct () {
		$this->_id = self::RULE;
		$this->_info = array(
			"title" => __('Show on Post Types', 'popover'),
			"message" => __('Shows the popover on specific post types.', 'popover'),
		);
		$this->_action = __('Show', 'popover');
		parent::__construct();
	}

	public function apply_rule ($show, $popover) {
		return parent::apply_rule($show, $popover);
	}
}


class Popover_Rules_PostTypes {

	private function __construct () {
		Popover_Rules_Rule_OnPostTypes::add();
		Popover_Rules_Rule_NotPostTypes::add();
	}

	public static function serve () {
		$me = new self;
		$me->_add_hooks();
	}

	private function _add_hooks () {
		add_action('wp_footer', array($this, 'inject_script'));
	}

	public function inject_script () {
		$post_type = esc_js(get_post_type());
		$is_singular = is_singular() ? 1 : 0;
		echo <<<EOJS
<script>
jQuery(document).ajaxSend(function(e, xhr, opts) {
	if (opts.url.match(/po_onsuccess/)) opts.url += '&post_type={$post_type}&is_single={$is_singular}';
});
</script>
EOJS;
	}
}
Popover_Rules_PostTypes::serve();