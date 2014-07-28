<?php
/*
Addon Name:  XProfile Fields rule
Plugin URI:  http://premium.wpmudev.org/project/the-pop-over-plugin/
Description: Adds advanced URL matching with regex support.
Author:      Ve (Incsub)
Author URI:  http://premium.wpmudev.org
Type:        Rule
Version:     1.0
*/

abstract class Popover_Rules_Rule_XprofileValue extends IncPopupRule {

	protected $_defaults = array(
		"field" => false,
		"value" => false,
		"correlation" => false,
	);

	public function apply_rule ($show, $popover) {
		if (!function_exists('xprofile_get_field_data')) return $show;

		$data = !empty($popover->popover_settings[$this->_id]) ? $popover->popover_settings[$this->_id] : false;
		if (empty($data)) return $show;

		$data = wp_parse_args($data, $this->_defaults);
		if (empty($data["field"])) return $show;

		$value = xprofile_get_field_data($data["field"], get_current_user_id(), 'comma');

		switch ($data["correlation"]) {
			case "regex_is":
				return preg_match("#{$data['value']}#i", $value);
			case "regex_not":
				return !preg_match("#{$data['value']}#i", $value);
			case "reverse":
				return $value != $data["value"];
			default:
				return $value == $data["value"];
		}

		return $show;
	}

	public function get_admin_interface ($data) {
		if (!class_exists('BP_XProfile_Group')) {
			return '<div class="error below-h2"><p>' .
				__('You need BuddyPress XProfile fields component active.', 'popover') .
			'</p></div>';
		}
		$data = wp_parse_args($data[$this->_id], $this->_defaults);
		$markup = '';

		$xfields = array();
		$xgroups = BP_XProfile_Group::get(array(
			'fetch_fields' => true
		));
		if (!empty($xgroups)) foreach ($xgroups as $xgroup) {
			$xfields[$xgroup->name] = $xgroup->fields;
		}
		if (empty($xfields)) {
			return ''; // No XProfile fields
		}

		$sel_fld .= '<select name="' . $this->_get_field_name("field") . '">';
		foreach ($xfields as $group => $fields) {
			$sel_fld .= '<optgroup label="' . esc_attr($group) . '">';
			foreach ($fields as $field) {
				$sel_fld .= '<option value="' . (int)$field->id . '" ' . selected($field->id, $data["field"], false) . '>' . esc_html($field->name) . '</option>';
			}
			$sel_fld .= '</optgroup>';
		}
		$sel_fld .= '</select>';
		$markup .= '<label for="' . $this->_get_field_id("fields") . '">' .
			esc_html(__('Field:', 'popover')) . '&nbsp;' .
			$sel_fld .
		'</label>';

		$rev_fld = '<select name="' . $this->_get_field_name("correlation") . '">';
		$rev_fld .= '	<option value="" ' . selected($data["correlation"], '', false) . '>' . esc_html(__('equals', 'popover')) . '</option>';
		$rev_fld .= '	<option value="reverse" ' . selected($data["correlation"], 'reverse', false) . '>' . esc_html(__('is not', 'popover')) . '</option>';
		$rev_fld .= '	<option value="regex_is" ' . selected($data["correlation"], 'regex_is', false) . '>' . esc_html(__('matches regex', 'popover')) . '</option>';
		$rev_fld .= '	<option value="regex_not" ' . selected($data["correlation"], 'regex_not', false) . '>' . esc_html(__('does not match regex', 'popover')) . '</option>';
		$rev_fld .= '</select>';

		$markup .= "&nbsp;{$rev_fld}&nbsp;";

		$markup .= '<input type="text" name="' . $this->_get_field_name("value") . '" value="' . esc_attr($data['value']) . '" />';

		return $markup;
	}

	public function save_settings ($settings) {
		if (empty($_POST[$this->_id])) return $settings;
		$data = stripslashes_deep($_POST[$this->_id]);

		$result = array();
		$keys = array_keys($this->_defaults);
		foreach ($keys as $key) {
			$result[$key] = wp_strip_all_tags($data[$key]);
		}
		$settings[$this->_id] = $result;
		return $settings;
	}
}

class Popover_Rules_Rule_NotXprofileValue extends Popover_Rules_Rule_XprofileValue {

	const RULE = 'not-xprofile_value';

	public static function add () {
		$me = new self;
		return $me;
	}

	protected function __construct () {
		$this->_id = self::RULE;
		$this->_info = array(
			"title" => __('Suppress when XProfile field matches condition', 'popover'),
			"message" => __('Suppresses the popover when XProfile field matches condition.', 'popover'),
		);
		$this->_action = __('Suppress', 'popover');
		parent::__construct();
	}

	public function apply_rule ($show, $popover) {
		return !(parent::apply_rule($show, $popover));
	}
}

class Popover_Rules_Rule_OnXprofileValue extends Popover_Rules_Rule_XprofileValue {

	const RULE = 'xprofile_value';

	public static function add () {
		$me = new self;
		return $me;
	}

	protected function __construct () {
		$this->_id = self::RULE;
		$this->_info = array(
			"title" => __('Show when XProfile field matches condition', 'popover'),
			"message" => __('Shows the popover when XProfile field matches condition.', 'popover'),
		);
		$this->_action = __('Show', 'popover');
		parent::__construct();
	}

	public function apply_rule ($show, $popover) {
		return parent::apply_rule($show, $popover);
	}
}


class Popover_Rules_XprofileValue {

	private function __construct () {
		Popover_Rules_Rule_OnXprofileValue::add();
		Popover_Rules_Rule_NotXprofileValue::add();
	}

	public static function serve () {
		$me = new self;
	}
}
Popover_Rules_XprofileValue::serve();