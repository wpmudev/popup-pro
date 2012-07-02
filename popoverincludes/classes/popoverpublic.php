<?php

if(!class_exists('popoverpublic')) {

	class popoverpublic {

		var $mylocation = '';
		var $build = 5;
		var $db;

		var $tables = array( 'popover', 'popover_ip_cache' );
		var $popover;
		var $popover_ip_cache;

		var $activepopover = false;

		function __construct() {

			global $wpdb;

			$this->db =& $wpdb;

			foreach($this->tables as $table) {
				$this->$table = popover_db_prefix($this->db, $table);
			}

			// Adds the JS to the themes header - this replaces all previous methods of loading
			add_action( 'init', array( &$this, 'add_selective_javascript') );

			add_action( 'plugins_loaded', array(&$this, 'load_textdomain'));

			$directories = explode(DIRECTORY_SEPARATOR,dirname(__FILE__));
			$this->mylocation = $directories[count($directories)-1];

			$installed = get_option('popover_installed', false);

			if($installed === false || $installed != $this->build) {
				$this->install();

				update_option('popover_installed', $this->build);
			}

		}

		function popoverpublic() {
			$this->__construct();
		}

		function install() {

			if($this->db->get_var( "SHOW TABLES LIKE '" . $this->popover . "' ") != $this->popover) {
				 $sql = "CREATE TABLE `" . $this->popover . "` (
				  	`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
					  `popover_title` varchar(250) DEFAULT NULL,
					  `popover_content` text,
					  `popover_settings` text,
					  `popover_order` bigint(20) DEFAULT '0',
					  `popover_active` int(11) DEFAULT '0',
					  PRIMARY KEY (`id`)
					)";

				$this->db->query($sql);

			}

			// Add in IP cache table
			if($this->db->get_var( "SHOW TABLES LIKE '" . $this->popover_ip_cache . "' ") != $this->popover_ip_cache) {
				 $sql = "CREATE TABLE `" . $this->popover_ip_cache . "` (
				  	`IP` varchar(12) NOT NULL DEFAULT '',
					  `country` varchar(2) DEFAULT NULL,
					  `cached` bigint(20) DEFAULT NULL,
					  PRIMARY KEY (`IP`),
					  KEY `cached` (`cached`)
					)";

				$this->db->query($sql);

			}

		}

		function load_textdomain() {

			$locale = apply_filters( 'popover_locale', get_locale() );
			$mofile = popover_dir( "popoverincludes/languages/popover-$locale.mo" );

			if ( file_exists( $mofile ) )
				load_textdomain( 'popover', $mofile );

		}

		function add_selective_javascript() {
			global $pagenow;

			if(!in_array($pagenow, array('wp-login.php', 'wp-register.php'))) {
				// We need javascript so make sure we load it here
				wp_enqueue_script('jquery');

				// Now to register our new js file
				wp_register_script( 'popoverselective', popover_url('popover-load-js.php') );
				wp_enqueue_script( 'popoverselective' );
			}

		}

	}

}

?>