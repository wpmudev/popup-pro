<?php

/**
 * Main rule abstraction.
 * All rules should extend from this.
 */
abstract class Popover_Rules_Rule {

	abstract public function apply_rule ($show, $popover);
	abstract public static function add ();
	
	protected $_id;
	protected $_defaults = array();
	protected $_info = array(
		"title" => '',
		"message" => '',
	);

	protected function __construct () {
		$this->_add_hooks();
	}

	protected function _get_field_name () {
		$args = func_get_args();
		return esc_attr($this->_id . '[' . join('][', $args) . ']');
	}
	protected function _get_field_id () {
		$args = func_get_args();
		return esc_attr($this->_id . join('-', $args));
	}

	protected function _add_hooks () {
		if (!$this->_id) return false;
		add_filter('popover_nice_rule_name', array($this, 'add_rule_column'), 10, 2);
		add_action('popover_active_rule_' . $this->_id, array($this, 'add_main_active_rule'), 10, 2); // Shown
		add_action('popover_additional_rules_main', array($this, 'add_active_rule'), 10, 2); // Hidden
		add_action('popover_additional_rules_sidebar', array($this, 'add_draggable_rule'));

		add_filter('popover-data-save', array($this, 'save_settings'));

		add_filter('popover_process_rule_' . $this->_id, array($this, 'apply_rule'), 10, 2);
	}
	
	public function get_admin_interface ($data) {
		return '';
	}
	
	public function save_settings ($settings) {
		if (empty($_POST[$this->_id])) return $settings;

		$data = stripslashes_deep($_POST[$this->_id]);
		$result = array();
		$keys = array_keys($this->_defaults);
		foreach ($keys as $key) {
			if (empty($data[$key])) continue;
			$result[$key] = array_filter(array_map('wp_strip_all_tags', $data[$key]));
		}
		$settings[$this->_id] = $result;
		return $settings;
	}

	public function add_rule_column ($rule, $key) {
		if ($key != $this->_id) return $rule;
		return esc_html($this->_info["message"]);
	}

	public function add_main_active_rule ($popover, $check) {
		$in_use = !empty($check[$this->_id]) ? $check[$this->_id] : false;
		if (!$in_use) return false;
		$this->add_active_rule($popover, $check);
	}

	public function add_active_rule ($popover, $check) {
		$data = !empty($popover->popover_settings) ? $popover->popover_settings : false;
		?>
<div class='popover-operation' id='main-<?php echo $this->_id; ?>'>
	<h2 class='sidebar-name'><?php echo esc_html($this->_info["title"]); ?><span><a href='#remove' class='removelink' id='remove-<?php echo $this->_id; ?>' title='<?php _e("Remove %s tag from this rules area.",'popover', esc_html($this->_info["title"])); ?>'><?php _e('Remove','popover'); ?></a></span></h2>
	<div class='inner-operation'>
		<p><?php echo esc_html($this->_info["message"]); ?></p>
		<?php echo $this->get_admin_interface($data); ?>
		<input type='hidden' name='popovercheck[<?php echo $this->_id; ?>]' value='yes' />
	</div>
</div>
		<?php
	}

	public function add_draggable_rule ($check) {
		if (isset($check[$this->_id])) return false;
?>
<li class='popover-draggable' id='<?php echo $this->_id; ?>'>
	<div class='action action-draggable'>
		<div class='action-top closed'>
		<a href="#available-actions" class="action-button hide-if-no-js"></a>
		<?php echo esc_html($this->_info["title"]); ?>
		</div>
		<div class='action-body closed'>
			<?php if(!empty($this->_info["message"])) { ?>
				<p>
					<?php echo esc_html($this->_info["message"]); ?>
				</p>
			<?php } ?>
			<p>
				<a href='#addtopopover' class='action-to-popover' title='<?php _e('Add this rule to the popover.','popover'); ?>'><?php _e('Add this rule to the popover.','popover'); ?></a>
			</p>
		</div>
	</div>
</li>
<?php
	}
}


/**
 * Not logged in rule.
 * Popover is to be shown if the user is not logged in.
 */
class Popover_Rules_NotLoggedIn extends Popover_Rules_Rule {

	const RULE = 'loggedin';

	protected function __construct () {
		$this->_id = self::RULE;
		$this->_info = array(
			"title" => __('Visitor is not logged in', 'popover'),
			"message" => __('Shows the popover if the user is not logged in to your site.', 'popover'),
		);
		$this->_action = $this->_info['title'];
		parent::__construct();
	}

	public static function add () {
		$me = new self;
		return $me;
	}

	public function apply_rule ($show, $popover) {
		$check = !empty($popover->popover_settings['popover_check']) ? $popover->popover_settings['popover_check'] : array();
		if (!in_array(self::RULE, array_keys($check))) return $show;

		return !is_user_logged_in();
	}
}
Popover_Rules_NotLoggedIn::add();


/**
 * Logged in rule.
 * Popover is to be shown if the user is logged in.
 */
class Popover_Rules_LoggedIn extends Popover_Rules_Rule {

	const RULE = 'isloggedin';

	protected function __construct () {
		$this->_id = self::RULE;
		$this->_info = array(
			"title" => __('Visitor is logged in', 'popover'),
			"message" => __('Shows the popover if the user is logged in to your site.', 'popover'),
		);
		$this->_action = $this->_info['title'];
		parent::__construct();
	}

	public static function add () {
		$me = new self;
		return $me;
	}

	public function apply_rule ($show, $popover) {
		$check = !empty($popover->popover_settings['popover_check']) ? $popover->popover_settings['popover_check'] : array();
		if (!in_array(self::RULE, array_keys($check))) return $show;

		return is_user_logged_in();
	}
}
Popover_Rules_LoggedIn::add();


/**
 * Never commented rule.
 * Popover is to be shown if the user has never commented on the site.
 */
class Popover_Rules_NeverCommented extends Popover_Rules_Rule {

	const RULE = 'commented';

	protected function __construct () {
		$this->_id = self::RULE;
		$this->_info = array(
			"title" => __('Visitor has never commented', 'popover'),
			"message" => __('Shows the popover if the user has never left a comment.', 'popover'),
		);
		$this->_action = $this->_info['title'];
		parent::__construct();
	}

	public static function add () {
		$me = new self;
		return $me;
	}

	public function apply_rule ($show, $popover) {
		$check = !empty($popover->popover_settings['popover_check']) ? $popover->popover_settings['popover_check'] : array();
		if (!in_array(self::RULE, array_keys($check))) return $show;

		return isset($_COOKIE['comment_author_'.COOKIEHASH]);
	}
}
Popover_Rules_NeverCommented::add();


/**
 * Search engine rule.
 * Popover is to be shown when a visitor comes from a search engine.
 */
class Popover_Rules_ViaSearchEngine extends Popover_Rules_Rule {

	const RULE = 'searchengine';

	protected function __construct () {
		$this->_id = self::RULE;
		$this->_info = array(
			"title" => __('Visit via a search engine', 'popover'),
			"message" => __('Shows the popover if the user arrived via a search engine.', 'popover'),
		);
		$this->_action = $this->_info['title'];
		parent::__construct();
	}

	public static function add () {
		$me = new self;
		return $me;
	}

	public function apply_rule ($show, $popover) {
		$check = !empty($popover->popover_settings['popover_check']) ? $popover->popover_settings['popover_check'] : array();
		if (!in_array(self::RULE, array_keys($check))) return $show;

		return $this->is_fromsearchengine($_REQUEST['thereferrer']);
	}

	public function is_fromsearchengine ($ref = '') {
		$SE = array('/search?', '.google.', 'web.info.com', 'search.', 'del.icio.us/search', 'soso.com', '/search/', '.yahoo.', '.bing.');

		foreach ($SE as $url) {
			if (strpos( $ref, $url) !== false ) {
				if($url == '.google.') {
					if( $this->is_googlesearch( $ref) ) {
						return true;
					} else {
						return false;
					}
				} else {
					return true;
				}
			}
		}
		return false;
	}
	function is_googlesearch( $ref = '' ) {
		$SE = array('.google.');

		foreach ($SE as $url) {
			if (strpos($ref,$url) !== false ) {
				// We've found a google referrer - get the query strings and check its a web source
				$qs = parse_url( $ref, PHP_URL_QUERY );
				$qget = array();
				foreach(explode('&', $qs) as $keyval) {
				    list( $key, $value ) = explode('=', $keyval);
				    $qget[ trim($key) ] = trim($value);
				}
				if(array_key_exists('source', $qget) && $qget['source'] == 'web') {
					return true;
				}
			}
		}
		return false;
	}
}
Popover_Rules_ViaSearchEngine::add();


/**
 * Not via na internal link.
 * Popover is to be shown if the vistor came from an external link.
 */
class Popover_Rules_NotViaInternalLink extends Popover_Rules_Rule {

	const RULE = 'internal';

	protected function __construct () {
		$this->_id = self::RULE;
		$this->_info = array(
			"title" => __('Visit not via an Internal link', 'popover'),
			"message" => __('Shows the popover if the user did not arrive on this page via another page on your site.', 'popover'),
		);
		$this->_action = $this->_info['title'];
		parent::__construct();
	}

	public static function add () {
		$me = new self;
		return $me;
	}

	public function apply_rule ($show, $popover) {
		$check = !empty($popover->popover_settings['popover_check']) ? $popover->popover_settings['popover_check'] : array();
		if (!in_array(self::RULE, array_keys($check))) return $show;

		$internal = str_replace('^http://', '', get_option('home'));
		return !preg_match('#' . $internal . '#i', $_REQUEST['thereferrer']);
	}
}
Popover_Rules_NotViaInternalLink::add();


/**
 * Referrer rule.
 * Popover is to be shown if the vistor came via a specific referrer.
 */
class Popover_Rules_ViaReferrer extends Popover_Rules_Rule {

	const RULE = 'referrer';

	protected function __construct () {
		$this->_id = self::RULE;
		$this->_info = array(
			"title" => __('Visit via specific referer', 'popover'),
			"message" => __('Shows the popover if the user arrived via a specific referrer.', 'popover'),
		);
		$this->_action = $this->_info['title'];
		parent::__construct();
	}

	public static function add () {
		$me = new self;
		return $me;
	}

	public function get_admin_interface ($data) {
		$popover_ereg = !empty($data['popover_ereg'])
			? esc_html($data['popover_ereg'])
			: ''
		;
		
		return "<input type='text' name='popoverereg' id='popoverereg' style='width: 10em;' value='{$popover_ereg}' />";
	}

	public function apply_rule ($show, $popover) {
		$check = !empty($popover->popover_settings['popover_check']) ? $popover->popover_settings['popover_check'] : array();
		if (!in_array(self::RULE, array_keys($check))) return $show;

		$referrer = !empty($popover->popover_settings['popover_ereg'])
			? $popover->popover_settings['popover_ereg']
			: false
		;
		if (empty($referrer)) return $show;

		return preg_match('#' . $referrer . '#i', $_REQUEST['thereferrer']);
	}
}
Popover_Rules_ViaReferrer::add();


/**
 * Views limit rule.
 * Popover is to be shown less than X times.
 */
class Popover_Rules_ViewsLimit extends Popover_Rules_Rule {

	const RULE = 'count';

	protected function __construct () {
		$this->_id = self::RULE;
		$this->_info = array(
			"title" => __('Popover shown less than', 'popover'),
			"message" => __('Shows the popover if the user has only seen it less than the following number of times:', 'popover'),
		);
		$this->_action = $this->_info['title'];
		parent::__construct();
	}

	public static function add () {
		$me = new self;
		return $me;
	}

	public function get_admin_interface ($data) {
		$count = !empty($data['popovercount'])
			? esc_html($data['popovercount'])
			: ''
		;
		
		return sprintf(
			__('%s &nbsp;times', 'popover'),
			"<input type='text' name='popovercount' id='popovercount' style='width: 5em;' value='{$count}' />"
		);
	}

	public function apply_rule ($show, $popover) {
		$check = !empty($popover->popover_settings['popover_check']) ? $popover->popover_settings['popover_check'] : array();
		if (!in_array(self::RULE, array_keys($check))) return $show;

		$count = !empty($popover->popover_settings['popover_count'])
			? $popover->popover_settings['popover_count']
			: false
		;
		if (empty($count)) return $show;

		return !(
			isset($_COOKIE['popover_view_'.COOKIEHASH]) && 
			addslashes($_COOKIE['popover_view_'.COOKIEHASH]) >= $count
		);
	}
}
Popover_Rules_ViewsLimit::add();


/**
 * On URL rule.
 * Popover is to be shown on a specific URL.
 */
class Popover_Rules_OnUrl extends Popover_Rules_Rule {

	const RULE = 'onurl';

	protected function __construct () {
		$this->_id = self::RULE;
		$this->_info = array(
			"title" => __('On specific URL', 'popover'),
			"message" => __('Shows the popover if the user is on a certain URL (enter one URL per line)', 'popover'),
		);
		$this->_action = $this->_info['title'];
		parent::__construct();
	}

	public static function add () {
		$me = new self;
		return $me;
	}

	public function get_admin_interface ($data) {
		$onurl = !empty($data['onurl'])
			? esc_html(join("\n", $data['onurl']))
			: ''
		;
		
		return "<textarea name='popoveronurl' id='popoveronurl' style=''>{$onurl}</textarea>";
	}

	public function apply_rule ($show, $popover) {
		$check = !empty($popover->popover_settings['popover_check']) ? $popover->popover_settings['popover_check'] : array();
		if (!in_array(self::RULE, array_keys($check))) return $show;

		$onurl = !empty($popover->popover_settings['onurl'])
			? $popover->popover_settings['onurl']
			: false
		;
		$onurl = array_filter(array_map('trim', $onurl));
		if (empty($onurl)) return $show;

		foreach ($onurl as $url) {
			if (preg_match( '#^' . $url . '$#i', $_REQUEST['thefrom'])) return true;
		}

		return $show;
	}
}
Popover_Rules_OnUrl::add();


/**
 * Not on URL rule.
 * Popover is to *not* be shown on a specific URL.
 */
class Popover_Rules_NotOnUrl extends Popover_Rules_Rule {

	const RULE = 'notonurl';

	protected function __construct () {
		$this->_id = self::RULE;
		$this->_info = array(
			"title" => __('Not on specific URL', 'popover'),
			"message" => __('Shows the popover if the user is not on a certain URL (enter one URL per line)', 'popover'),
		);
		$this->_action = $this->_info['title'];
		parent::__construct();
	}

	public static function add () {
		$me = new self;
		return $me;
	}

	public function get_admin_interface ($data) {
		$onurl = !empty($data['notonurl'])
			? esc_html(join("\n", $data['notonurl']))
			: ''
		;

		return "<textarea name='popovernotonurl' id='popovernotonurl' style=''>{$onurl}</textarea>";
	}

	public function apply_rule ($show, $popover) {
		$check = !empty($popover->popover_settings['popover_check']) ? $popover->popover_settings['popover_check'] : array();
		if (!in_array(self::RULE, array_keys($check))) return $show;

		$onurl = !empty($popover->popover_settings['notonurl'])
			? $popover->popover_settings['notonurl']
			: false
		;
		$onurl = array_filter(array_map('trim', $onurl));
		if (empty($onurl)) return $show;

		foreach ($onurl as $url) {
			if (preg_match( '#^' . $url . '$#i', $_REQUEST['thefrom'])) return false;
		}

		return true;
	}
}
Popover_Rules_NotOnUrl::add();