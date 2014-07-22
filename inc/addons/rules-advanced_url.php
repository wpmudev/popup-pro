<?php
/*
Addon Name: Advanced URL rules
Plugin URI:  http://premium.wpmudev.org/project/the-pop-over-plugin/
Description: Adds advanced URL matching with regex support.
Author: Ve (Incsub)
Author URI:  http://premium.wpmudev.org
Version: 1.0
*/

abstract class Popover_Rules_Rule_AdvancedUrl extends Popover_Rules_Rule {

	protected $_defaults = array(
		"urls" => array(),
	);

	public function apply_rule ($show, $popover) {
		$data = !empty($popover->popover_settings[$this->_id]) ? $popover->popover_settings[$this->_id] : false;
		if (empty($data)) return $show;

		$data = wp_parse_args($data, $this->_defaults);
		if (empty($data["urls"])) return $show;

		global $wp;
		$current_url = !empty($_REQUEST["thefrom"])
			? $_REQUEST["thefrom"]
			: home_url($wp->request) // Yeah, match footer loading too
		;

		foreach ($data["urls"] as $url) {
			if (preg_match("#{$url}#i", $current_url)) return true;
		}

		return $show;
	}

	public function get_admin_interface ($data) {
		$data = wp_parse_args($data[$this->_id], $this->_defaults);
		$markup = '';
		
		$markup .= '<textarea class="widefat" name="' . $this->_get_field_name("urls") . '">' .
			esc_textarea(join("\n", $data["urls"])) .
		'</textarea>';
		$markup .= '<em><small>' . __('One URL regex per line', 'popover') . '</small></em>';

		return $markup;
	}

	public function save_settings ($settings) {
		if (empty($_POST[$this->_id])) return $settings;

		$data = stripslashes_deep($_POST[$this->_id]);
		$result = array();
		$keys = array_keys($this->_defaults);
		foreach ($keys as $key) {
			if (empty($data[$key])) continue;
			$raw = !empty($data[$key]) ? trim($data[$key]) : '';
			$urls = array_filter(array_map('trim', explode("\n", $raw)));
			$result[$key] = $urls;
		}
		$settings[$this->_id] = $result;
		return $settings;
	}
}

class Popover_Rules_Rule_NotAdvancedUrl extends Popover_Rules_Rule_AdvancedUrl {
	
	const RULE = 'not-advanced_urls';

	public static function add () {
		$me = new self;
		return $me;
	}

	protected function __construct () {
		$this->_id = self::RULE;
		$this->_info = array(
			"title" => __('Suppress on approximate URL', 'popover'),
			"message" => __('Suppresses the popover on matched URLs.', 'popover'),
		);
		$this->_action = __('Suppress', 'popover');
		parent::__construct();
	}

	public function apply_rule ($show, $popover) {
		return !(parent::apply_rule($show, $popover));
	}
}

class Popover_Rules_Rule_OnAdvancedUrl extends Popover_Rules_Rule_AdvancedUrl {
	
	const RULE = 'advanced_urls';

	public static function add () {
		$me = new self;
		return $me;
	}

	protected function __construct () {
		$this->_id = self::RULE;
		$this->_info = array(
			"title" => __('Show on approximate URL', 'popover'),
			"message" => __('Shows the popover on matched URLs.', 'popover'),
		);
		$this->_action = __('Show', 'popover');
		parent::__construct();
	}

	public function apply_rule ($show, $popover) {
		return parent::apply_rule($show, $popover);
	}
}


class Popover_Rules_AdvancedUrl {

	private function __construct () {
		Popover_Rules_Rule_OnAdvancedUrl::add();
		Popover_Rules_Rule_NotAdvancedUrl::add();
	}

	public static function serve () {
		$me = new self;
	}
}
Popover_Rules_AdvancedUrl::serve();