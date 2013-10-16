<?php
/*
Addon Name: Anonymous loading method
Plugin URI: http://premium.wpmudev.org/project/the-pop-over-plugin/
Description: Yet another loading method.
Author: Ve (Incsub)
Author URI: http://premium.wpmudev.org
Version: 1.0
*/

class Popover_Anonymous_Loading {

	const METHOD = 'anonymous';

	private function __construct () {
		$this->_slug = $this->_generate_slug();
	}

	public static function serve () {
		$me = new self;
		$me->_add_hooks();
	}

	private function _add_hooks () {
		add_action('popover-settings-loading_method', array($this, 'settings'));
		add_action('popover-init-loading_method', array($this, 'init'));
		add_action('popover-ajax-loading_method', array($this, 'init_ajax'), 10, 2);
	}

	public function init ($method) {
		if (self::METHOD != $method) return false;
		add_action('wp_enqueue_scripts', array($this, 'enqueue'));
		add_action('template_redirect', array($this, 'apply'));
	}

	public function init_ajax ($method, $ajax) {
		if ($method != self::METHOD) return false;
		add_action('wp_ajax_popover_selective_ajax', array($ajax, 'ajax_selective_message_display'));
		add_action('wp_ajax_nopriv_popover_selective_ajax', array($ajax, 'ajax_selective_message_display'));
		add_action('popover-output-popover', array($this, 'filter_popover'));
	}

	public function filter_popover ($pop) {
		if (!empty($pop["html"]) && !empty($pop["style"])) {
			$pop["html"] = $this->_filter($pop["html"], true);
			$pop["style"] = $this->_filter($pop["style"]);
		}
		return $pop;
	}

	private function _filter ($html, $is_markup=false) {
		$selectors = array(
			"closebox",
			"message",
			"clearforever"
		);
		$salt = home_url();
		$pfx = $is_markup ? '' : '#';
		$opening_delimiter = $is_markup ? '[\'"]' : '#';
		$closing_delimiter = $is_markup ? $opening_delimiter : '\b';
		foreach ($selectors as $selector) {
			$hash = md5("{$selector}{$salt}");
			$len = strlen($salt);
			$len = $len < 5 
				? 5 
				: ($len >= 32 ? (int)$len/2 : $len)
			;
			$value = 'p' . $this->_rot($hash, $len);
			$value = $is_markup
				? "'{$value}'"
				: "#{$value}"
			;
			$html = preg_replace('/' . $opening_delimiter . preg_quote($selector, '/') . $closing_delimiter . '/', $value, $html);
		}
		return $html;
	}

	public function enqueue () {
		$slug = $this->get_slug();
		$val = $this->_rot(time(), rand(1, 22));
		wp_enqueue_script($slug, add_query_arg(array($slug => $val), home_url()), array('jquery'));
	}

	public function apply () {
		if (!$this->has_fragment()) return false;
		$this->render_script();
		die;
	}

	public function render_script () {
		$file = defined('POPOVER_LEGACY_JAVASCRIPT_DIFFERENTIATION') && POPOVER_LEGACY_JAVASCRIPT_DIFFERENTIATION
			? popover_dir('popoverincludes/js/popover-load.js')
			: popover_dir('popoverincludes/js/public.js')
		;
		$data = defined('POPOVER_LEGACY_JAVASCRIPT_DIFFERENTIATION') && POPOVER_LEGACY_JAVASCRIPT_DIFFERENTIATION
			? sprintf('var popover_load_custom=%s;', json_encode(array(
				'admin_ajax_url' => admin_url('admin-ajax.php')
			)))
			: sprintf('var _popover_data=%s', json_encode(array(
				'endpoint' => admin_url('admin-ajax.php'),
				'action' => 'popover_selective_ajax',
			)))
		;
		if (!file_exists($file) && !is_readable($file)) return false;
		header("Content-type: text/javascript");
		echo "{$data}\n";
		echo $this->_filter(file_get_contents($file));
	}


	public function settings ($method) {
		echo '<option value="' . esc_attr(self::METHOD) . '" ' . selected($method, self::METHOD) . '>' . __('Anonymous', 'popover') . '</option>';
	}

	public function get_slug () {
		return $this->_slug;
	}

	public function has_fragment () {
		$slug = $this->get_slug();
		return !empty($_GET[$slug]);
	}

	private function _generate_slug () {
		$info = str_split(home_url());
		$raw = serialize($info);
		$len = count($info);
		$len = $len < 5 
			? 5 
			: ($len >= 32 ? (int)$len/2 : $len)
		;
		return substr($this->_rot(md5($raw), $len), 0, $len);
	}

	private function _rot ($str, $len) {
		// We're not interested in having the exact thing back
		$letters = join('', range('a','z')) . join('', range(0,9));
		return strtr($str, $letters, substr($letters, $len) . substr($letters, 0, $len));
	}
}
Popover_Anonymous_Loading::serve();