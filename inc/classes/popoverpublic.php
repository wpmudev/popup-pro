<?php
if (!class_exists('popoverpublic')) {

    class popoverpublic {

        var $mylocation = '';
        var $build = 5;
        var $db;
        var $tables = array('popover', 'popover_ip_cache');
        var $popover;
        var $popover_ip_cache;
        var $activepopover = false;
        var $thepopover;

        function __construct() {

            global $wpdb;

            $this->db = & $wpdb;

            foreach ($this->tables as $table) {
                $this->$table = popover_db_prefix($this->db, $table);
            }

            // Adds the JS to the themes header - this replaces all previous methods of loading
            add_action('init', array(&$this, 'initialise_plugin'));

            $directories = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
            $this->mylocation = $directories[count($directories) - 1];

            $installed = get_option('popover_installed', false);

            if ($installed === false || $installed != $this->build) {
                $this->install();

                update_option('popover_installed', $this->build);
            }
        }

        function popoverpublic() {
            $this->__construct();
        }

        function install() {

            $charset_collate = '';

            if (!empty($this->db->charset)) {
                $charset_collate = "DEFAULT CHARACTER SET " . $this->db->charset;
            }

            if (!empty($this->db->collate)) {
                $charset_collate .= " COLLATE " . $this->db->collate;
            }

            if ($this->db->get_var("SHOW TABLES LIKE '" . $this->popover . "' ") != $this->popover) {
                $sql = "CREATE TABLE `" . $this->popover . "` (
				  	`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
					  `popover_title` varchar(250) DEFAULT NULL,
					  `popover_content` text,
					  `popover_settings` text,
					  `popover_order` bigint(20) DEFAULT '0',
					  `popover_active` int(11) DEFAULT '0',
					  PRIMARY KEY (`id`)
					) $charset_collate;";

                $this->db->query($sql);
            }

            // Add in IP cache table
            if ($this->db->get_var("SHOW TABLES LIKE '" . $this->popover_ip_cache . "' ") != $this->popover_ip_cache) {
                $sql = "CREATE TABLE `" . $this->popover_ip_cache . "` (
				  	`IP` varchar(12) NOT NULL DEFAULT '',
					  `country` varchar(2) DEFAULT NULL,
					  `cached` bigint(20) DEFAULT NULL,
					  PRIMARY KEY (`IP`),
					  KEY `cached` (`cached`)
					) $charset_collate;";

                $this->db->query($sql);
            }
        }

        function initialise_plugin() {

            $settings = get_popover_option('popover-settings', array('loadingmethod' => 'frontloading'));

            switch ($settings['loadingmethod']) {

                case 'external': $this->add_selective_javascript();
                    break;

                case 'footer': $this->add_popover_files();
                    break;

                case 'frontloading': $this->add_frontend_selective_javascript();
                    break;

                default:
                    do_action('popover-init-loading_method', $settings['loadingmethod']);
                    break;
            }
        }

        function add_selective_javascript() {
            global $pagenow;

            if (!in_array($pagenow, array('wp-login.php', 'wp-register.php'))) {
                if (defined('POPOVER_LEGACY_JAVASCRIPT_DIFFERENTIATION') && POPOVER_LEGACY_JAVASCRIPT_DIFFERENTIATION) {
                    // We need javascript so make sure we load it here
                    wp_enqueue_script('jquery');

                    // Now to register our new js file
                    wp_register_script('popover_selective_load', PO_JS_URL . 'popover-load.js');
                    wp_enqueue_script('popover_selective_load');

                    wp_localize_script('popover_selective_load', 'popover_load_custom', array(
                        'admin_ajax_url' => admin_url( 'admin-ajax.php' ),
                    ));
                } else {
                    wp_enqueue_script('popover-public', PO_JS_URL . 'public.js', array('jquery'));
                    wp_localize_script('popover-public', '_popover_data', array(
                        'endpoint' => admin_url('admin-ajax.php'),
                        'action' => 'popover_selective_ajax',
                    ));
                }
            }
        }

        function add_frontend_selective_javascript() {
            global $pagenow;

            if (!in_array($pagenow, array('wp-login.php', 'wp-register.php'))) {
                if (defined('POPOVER_LEGACY_JAVASCRIPT_DIFFERENTIATION') && POPOVER_LEGACY_JAVASCRIPT_DIFFERENTIATION) {
                    // We need javascript so make sure we load it here
                    wp_enqueue_script('jquery');

                    // Now to register our new js file
                    wp_register_script('popover_load_custom', PO_JS_URL . 'popover-load-custom.js');
                    wp_enqueue_script('popover_load_custom');

                    wp_localize_script('popover_load_custom', 'popover_selective_custom', array(
                        'admin_ajax_url' => admin_url( 'admin-ajax.php' ),
                    ));
                } else {
                    wp_enqueue_script('popover-public', PO_JS_URL . 'public.js', array('jquery'));
                    wp_localize_script('popover-public', '_popover_data', array(
                        'endpoint' => '',
                        'action' => 'popover_selective_ajax',
                    ));
                }
            }
        }


        function add_popover_files() {

            global $popoverajax;

            if (method_exists($popoverajax, 'selective_message_display')) {

                // Set up the rquest information from here - this is passed in using the standard JS interface so we need to fake it
                $_REQUEST['thereferrer'] = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '';
                $_REQUEST['thefrom'] = $this->myURL();

                $this->thepopover = $popoverajax->selective_message_display();

                if (isset($this->thepopover['name']) && $this->thepopover['name'] != 'nopopover') {
                    if (defined('POPOVER_LEGACY_JAVASCRIPT_DIFFERENTIATION') && POPOVER_LEGACY_JAVASCRIPT_DIFFERENTIATION) {
                        wp_enqueue_script('jquery');

                        wp_enqueue_script('popoverlegacyjs', PO_JS_URL . 'popoverlegacy.js', array('jquery'), $this->build);
                        wp_localize_script('popoverlegacyjs', 'popover', array('divname' => $this->thepopover['name'],
                            'usejs' => $this->thepopover['usejs'],
                            'delay' => $this->thepopover['delay']
                        ));
                    } else {
                        $data = $this->thepopover;
                        unset($data['style']);
                        unset($data['html']);
                        wp_enqueue_script('popover-public', PO_JS_URL . 'public.js', array('jquery'));
                        wp_localize_script('popover-public', '_popover_data', array(
                            'endpoint' => '',
                            'action' => 'popover_selective_ajax',
                            'popover' => $data,
                        ));
                    }
                    add_action('wp_head', array(&$this, 'output_header_content'));
                    add_action('wp_footer', array(&$this, 'output_footer_content'));
                }
            }
        }

        function myURL() {

            if (isset($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) == "on") {
                $url .= "https://";
            } else {
                $url = 'http://';
            }

            if ($_SERVER["SERVER_PORT"] != "80") {
                $url .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
            } else {
                $url .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
            }

            return trailingslashit($url);
        }

        function output_header_content() {
            // Output the styles
            ?>
            <style type="text/css">
            <?php
            echo $this->thepopover['style'];
            ?>
            </style>
            <?php
        }

        function output_footer_content() {

            echo $this->thepopover['html'];
        }

    }

}
?>