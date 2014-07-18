<?php
/*
Addon Name: Membership rules
Plugin URI: http://premium.wpmudev.org/project/the-pop-over-plugin/
Description: Shows/hides popover based on Membership Level and Membership Subscription.
Author: JJ (Incsub)
Author URI: http://premium.wpmudev.org
Version: 1.0
*/

class Popover_Rules_Rule_Membership_Level extends Popover_Rules_Rule_Membership_Base {

    const RULE = 'membership_level';

    protected $_defaults = array(
        "levels" => array()
    );

    public static function add () {
        $me = new self;
        return $me;
    }

    protected function __construct () {
        $this->_id = self::RULE;
        $this->membership_levels_table = defined('MEMBERSHIP_TABLE_LEVELS') ? MEMBERSHIP_TABLE_LEVELS : $this->db_prefix.'m_membership_levels';
        $this->_info = array(
            "title" => __('Membership Levels', 'popover'),
            "message" => __('Shows the popover based on Membership Level.', 'popover'),
        );
        $this->_action = __('Show', 'popover');
        parent::__construct();
    }

    public function apply_rule ($show, $popover) {
        $data = !empty($popover->popover_settings[$this->_id]) ? $popover->popover_settings[$this->_id] : false;

        if (empty($data)) return $show;

        $data = wp_parse_args($data, $this->_defaults);

        $allowedLevels = $data['levels'] ? $data['levels'] : array();

        foreach($allowedLevels as $allowedLevel){
            if(current_user_on_level($allowedLevel)){
                return TRUE;
            }
        }
        return $show;
    }

    public function get_admin_interface ($data) {
        $data = wp_parse_args($data[$this->_id], $this->_defaults);
        $levels = $this->get_membership_levels();

        $markup = '';

        $markup .= '<fieldset><legend>' . sprintf(__('%s popover only for these Membership Levels:', 'popover'), $this->_action) . '</legend>';

        foreach ($levels as $key => $level) {
            $field_id = $this->_get_field_id("levels", $key);
            $field_name = $this->_get_field_name("levels", $key);
            $checked = in_array($level->id, $data["levels"]) ? 'checked="checked"' : '';
            $markup .= "<input type='checkbox' id='{$field_id}' name='{$field_name}' {$checked} value='{$level->id}' />" .
                '&nbsp;' .
                "<label for='{$field_id}'>{$level->level_title}</label>" .
                '<br />';
        }
        $markup .= '</fieldset>';

        return $markup;
    }

    function get_membership_levels() {

        $sql = "SELECT * FROM {$this->membership_levels_table}";// TODO: find a more fancy way to get the membership table name.
        $result = $this->db->get_results($sql);
        return $result;
    }
}

class Popover_Rules_Rule_Membership_Subscription extends Popover_Rules_Rule_Membership_Base {

    const RULE = 'membership_subscription';

    protected $_defaults = array(
        "subscriptions" => array()
    );

    public static function add () {
        $me = new self;
        return $me;
    }

    protected function __construct () {
        $this->_id = self::RULE;
        $this->membership_subscriptions_table = defined('MEMBERSHIP_TABLE_SUBSCRIPTIONS') ? MEMBERSHIP_TABLE_SUBSCRIPTIONS : $this->db_prefix.'m_subscriptions';
        $this->_info = array(
            "title" => __('Membership Subscriptions', 'popover'),
            "message" => __('Shows the popover based on Membership Subscriptions.', 'popover'),
        );
        $this->_action = __('Show', 'popover');
        parent::__construct();
    }

    public function apply_rule ($show, $popover) {
        $data = !empty($popover->popover_settings[$this->_id]) ? $popover->popover_settings[$this->_id] : false;

        if (empty($data)) return $show;

        $data = wp_parse_args($data, $this->_defaults);

        $allowedSubscriptions = $data['subscriptions'] ? $data['subscriptions'] : array();

        foreach($allowedSubscriptions as $allowedSubscription){
            if(current_user_on_subscription($allowedSubscription)){
                return TRUE;
            }
        }
        return $show;
    }

    public function get_admin_interface ($data) {
        $data = wp_parse_args($data[$this->_id], $this->_defaults);
        $subscriptions = $this->get_membership_subscriptions();
        $markup = '';

        $markup .= '<fieldset><legend>' . sprintf(__('%s popover only for these Membership Subscriptions:', 'popover'), $this->_action) . '</legend>';

        foreach ($subscriptions as $key => $subscription) {
            $field_id = $this->_get_field_id("subscriptions", $key);
            $field_name = $this->_get_field_name("subscriptions", $key);
            $checked = in_array($subscription->id, $data["subscriptions"]) ? 'checked="checked"' : '';
            $markup .= "<input type='checkbox' id='{$field_id}' name='{$field_name}' {$checked} value='{$subscription->id}' />" .
                '&nbsp;' .
                "<label for='{$field_id}'>{$subscription->sub_name}</label>" .
                '<br />';
        }
        $markup .= '</fieldset>';

        return $markup;
    }

    function get_membership_subscriptions() {

        $sql = "SELECT * FROM {$this->membership_subscriptions_table}";// TODO: find a more fancy way to get the membership table name.
        $result = $this->db->get_results($sql);
        return $result;
    }
}

abstract class Popover_Rules_Rule_Membership_Base extends Popover_Rules_Rule {
    protected function __construct () {
        global $wpdb;
        $this->db = $wpdb;
        $this->db_prefix = $wpdb->get_blog_prefix();
        parent::__construct();
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

    protected function _get_field_name () {
        $args = func_get_args();
        return esc_attr($this->_id . '[' . join('][', $args) . ']');
    }
    protected function _get_field_id () {
        $args = func_get_args();
        return esc_attr($this->_id . join('-', $args));
    }
}

class Popover_Rules_Membership {

    private function __construct () {
        //Rules that depends on Membership Plugin won't show up if the Membership Plugin is not running. It will alert to admin instead.
        if( (function_exists('M_get_membership_active') && M_get_membership_active()) || is_plugin_active('membership/membershippremium.php')){
            Popover_Rules_Rule_Membership_Level::add();
            Popover_Rules_Rule_Membership_Subscription::add();
        } else {
            add_action('admin_notices', array($this, 'membership_not_active_notice'));
        }
    }

    public static function serve () {
        $me = new self;
    }

    public function membership_not_active_notice () {
        echo '<div class="error"><p>' . __('&quot;Popover Membership Rule&quot; add-on won\'t work if Membership Plugin is not active', 'popover') . '</p></div>';
    }

}
Popover_Rules_Membership::serve();