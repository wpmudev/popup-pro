<?php
/*
Addon Name: WP Roles rules
Plugin URI: http://premium.wpmudev.org/project/the-pop-over-plugin/
Description: Shows/hides popover based on WP Role.
Author: JJ (Incsub)
Author URI: http://premium.wpmudev.org
Version: 1.0
*/


class Popover_Rules_Rule_WP_Roles extends Popover_Rules_Rule_WP_Roles_Base {

    const RULE = 'wp_roles_rule';

    protected $_defaults = array(
        "roles" => array()
    );

    public static function add () {
        $me = new self;
        return $me;
    }

    protected function __construct () {
        $this->_id = self::RULE;
        $this->_info = array(
            "title" => __('WP Roles', 'popover'),
            "message" => __('Shows the popover based on user\'s role.', 'popover'),
        );
        $this->_action = __('Show', 'popover');
        parent::__construct();
    }

    public function apply_rule ($show, $popover) {
        $data = !empty($popover->popover_settings[$this->_id]) ? $popover->popover_settings[$this->_id] : false;

        if (empty($data)) return $show;

        $data = wp_parse_args($data, $this->_defaults);

        $allowedRoles = $data['roles'] ? $data['roles'] : array();

        $currentUser = wp_get_current_user();
        $currentUserRoles = $currentUser->roles;
        //Can a user have more than one Role?. Just in Case, we iterate over the $currentUserRoles array.
        foreach($currentUserRoles as $key => $userRole){
            if(in_array($userRole, $allowedRoles)){
                return TRUE;
            }
        }
        return $show;
    }

    public function get_admin_interface ($data) {
        global $wp_roles;
        $roles = $wp_roles->get_names();
        $data = wp_parse_args($data[$this->_id], $this->_defaults);

        $markup = '';
        $markup .= '<fieldset><legend>' . sprintf(__('%s popover only for these roles:', 'popover'), $this->_action) . '</legend>';
        foreach ($roles as $roleName => $displayName) {
            $field_id = $this->_get_field_id("roles", $roleName);
            $field_name = $this->_get_field_name("roles", $roleName);
            $checked = in_array($roleName, $data["roles"]) ? 'checked="checked"' : '';
            $markup .= "<input type='checkbox' id='{$field_id}' name='{$field_name}' {$checked} value='{$roleName}' />" .
                '&nbsp;' .
                "<label for='{$field_id}'>{$displayName}</label>" .
                '<br />';
        }
        $markup .= '</fieldset>';

        return $markup;
    }
}

abstract class Popover_Rules_Rule_WP_Roles_Base extends Popover_Rules_Rule {
    protected function __construct () {
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

class Popover_Rules_WP_Roles {

    private function __construct () {
        Popover_Rules_Rule_WP_Roles::add();
    }

    public static function serve () {
        $me = new self;
    }

}
Popover_Rules_WP_Roles::serve();