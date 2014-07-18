<?php
if (!class_exists('popoverpublic')) {

    class popoverpublic {
        
        var $thepopover;


        private $_data;

        function __construct() {

            // Adds the JS to the themes header - this replaces all previous methods of loading
            add_action('init', array(&$this, 'initialise_plugin'));

            $this->_data = Popover_Data::get_instance();
        }

        function popoverpublic() {
            $this->__construct();
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
                    wp_register_script('popover_selective_load', popover_url('popoverincludes/js/popover-load.js'));
                    wp_enqueue_script('popover_selective_load');
                    
                    wp_localize_script('popover_selective_load', 'popover_load_custom', array(
                        'admin_ajax_url' => admin_url( 'admin-ajax.php' ),
                    ));
                } else {
                    wp_enqueue_script('popover-public', popover_url('popoverincludes/js/public.js'), array('jquery'));
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
                    wp_register_script('popover_load_custom', popover_url('popoverincludes/js/popover-load-custom.js'));
                    wp_enqueue_script('popover_load_custom');

                    wp_localize_script('popover_load_custom', 'popover_selective_custom', array(
                        'admin_ajax_url' => admin_url( 'admin-ajax.php' ),
                    ));
                } else {
                    wp_enqueue_script('popover-public', popover_url('popoverincludes/js/public.js'), array('jquery'));
                    wp_localize_script('popover-public', '_popover_data', array(
                        'endpoint' => '',
                        'action' => 'popover_selective_ajax',
                    ));
                }
            }
        }


        function add_popover_files() {
            // Set up the rquest information from here - this is passed in using the standard JS interface so we need to fake it
            $_REQUEST['thereferrer'] = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '';
            $_REQUEST['thefrom'] = $this->myURL();

            $this->thepopover = $this->_data->get_applicable_popover_messages();
            if (
                (isset($this->thepopover['name']) && $this->thepopover['name'] != 'nopopover')
                ||
                count($this->thepopover)
            ) {
                if (defined('POPOVER_LEGACY_JAVASCRIPT_DIFFERENTIATION') && POPOVER_LEGACY_JAVASCRIPT_DIFFERENTIATION) {
                    wp_enqueue_script('jquery');

                    wp_enqueue_script('popoverlegacyjs', popover_url('popoverincludes/js/popoverlegacy.js'), array('jquery'), $this->build);
                    wp_localize_script('popoverlegacyjs', 'popover', array('divname' => $this->thepopover['name'],
                        'usejs' => $this->thepopover['usejs'],
                        'delay' => $this->thepopover['delay']
                    ));
                } else {
                    $data = $this->thepopover;
                    unset($data['style']);
                    unset($data['html']);
                    wp_enqueue_script('popover-public', popover_url('popoverincludes/js/public.js'), array('jquery'));
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
            $popovers = !empty($this->thepopover['name'])
                ? array($this->thepopover)
                : $this->thepopover
            ;
            echo '<style type="text/css">';
            foreach ($popovers as $pop) {
                echo $pop['style'];
            }
            echo '</style>';
        }

        function output_footer_content() {
            $popovers = !empty($this->thepopover['name'])
                ? array($this->thepopover)
                : $this->thepopover
            ;
            foreach ($popovers as $pop) {
                echo $pop['html'];
            }
        }

    }

}