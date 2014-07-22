<?php
/*
Addon Name: Post Categories rules
Plugin URI:  http://premium.wpmudev.org/project/the-pop-over-plugin/
Description: Adds post category related rules.
Author: Ve (Incsub)
Author URI:  http://premium.wpmudev.org
Version: 1.0
*/


abstract class Popover_Rules_Rule_Categories extends Popover_Rules_Rule {

	protected $_defaults = array(
		"categories" => array(),
		"urls" => array(),
	);

	public function apply_rule ($show, $popover) {
		$data = !empty($popover->popover_settings[$this->_id]) ? $popover->popover_settings[$this->_id] : false;
		if (empty($data)) return $show;

		$data = wp_parse_args($data, $this->_defaults);
		$categories = $is_single = false;

		if (!empty($_REQUEST["categories"])) {
			// Via URL/AJAX
			$categories = !empty($_REQUEST["categories"]) ? $_REQUEST["categories"] : false;
			$categories = $categories
				? json_decode($categories)
				: array()
			;
			$is_single = !empty($_REQUEST["is_single"]) ? (int)$_REQUEST["is_single"] : false;
		} else {
			// Via wp_footer
			$categories = wp_list_pluck(get_the_category(), 'term_id');
			$is_single = is_singular();
		}

		if ($is_single && in_array("singular", $data["urls"])) {
			if (empty($data["categories"])) return true; // Any cat, singular
			if (!empty($categories)) {
				foreach ($categories as $term_id) if (in_array($term_id, $data['categories'])) return true; // We have a cat
			}
		}
		if (!$is_single && in_array("plural", $data["urls"])) {
			if (empty($data["categories"])) return true; // Any cat, plural
			if (!empty($categories)) {
				foreach ($categories as $term_id) if (in_array($term_id, $data['categories'])) return true; // We have a cat
			}
		}

		return $show; // Indeterminate cat, or some other fallback reason. 
	}

	public function get_admin_interface ($data) {
		$data = wp_parse_args($data[$this->_id], $this->_defaults);
		$markup = '';
		$categories = get_terms('category', array(
			'hide_empty' => false,
		), 'objects');
		$url_types = array(
			'singular' => __('Singular', 'popover'),
			'plural' => __('Archive', 'popover'),
		);

		$markup .= '<fieldset><legend>' . sprintf(__('%s on these post categories:', 'popover'), $this->_action) . '</legend>';
		foreach ($categories as $key => $term) {
			$field_id = $this->_get_field_id("categories", $term->slug);
			$field_name = $this->_get_field_name("categories", $term->slug);
			$checked = in_array($key, $data["categories"]) ? 'checked="checked"' : '';
			$markup .= "<input type='checkbox' id='{$field_id}' name='{$field_name}' {$checked} value='{$term->term_id}' />" .
				'&nbsp;' .
				"<label for='{$field_id}'>{$term->name}</label>" .
			'<br />';
		}
		$markup .= '</fieldset>';

		$markup .= '<fieldset><legend>' . sprintf(__('%s on these category type URLs:', 'popover'), $this->_action) . '</legend>';
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

class Popover_Rules_Rule_NotCategories extends Popover_Rules_Rule_Categories {
	
	const RULE = 'not-categories';

	public static function add () {
		$me = new self;
		return $me;
	}

	protected function __construct () {
		$this->_id = self::RULE;
		$this->_info = array(
			"title" => __('Suppress on Categories', 'popover'),
			"message" => __('Suppresses the popover on specific post categories.', 'popover'),
		);
		$this->_action = __('Suppress', 'popover');
		parent::__construct();
	}

	public function apply_rule ($show, $popover) {
		return !(parent::apply_rule($show, $popover));
	}
}

class Popover_Rules_Rule_OnCategories extends Popover_Rules_Rule_Categories {
	
	const RULE = 'categories';

	public static function add () {
		$me = new self;
		return $me;
	}

	protected function __construct () {
		$this->_id = self::RULE;
		$this->_info = array(
			"title" => __('Show on Categories', 'popover'),
			"message" => __('Shows the popover on specific post categories.', 'popover'),
		);
		$this->_action = __('Show', 'popover');
		parent::__construct();
	}

	public function apply_rule ($show, $popover) {
		return parent::apply_rule($show, $popover);
	}
}


class Popover_Rules_Categories {

	private function __construct () {
		Popover_Rules_Rule_OnCategories::add();
		Popover_Rules_Rule_NotCategories::add();
	}

	public static function serve () {
		$me = new self;
		$me->_add_hooks();
	}

	private function _add_hooks () {
		add_action('wp_footer', array($this, 'inject_script'));
	}

	public function inject_script () {
		$categories = json_encode(wp_list_pluck(get_the_category(), 'term_id'));
		$is_singular = is_singular() ? 1 : 0;
		echo <<<EOJS
<script>
jQuery(document).ajaxSend(function(e, xhr, opts) {
	cats = JSON.stringify({$categories});
	if (opts.url.match(/\bpo_[a-z]/)) opts.url += '&categories=' + cats + '&is_single={$is_singular}';
});
</script>
EOJS;
	}
}
Popover_Rules_Categories::serve();