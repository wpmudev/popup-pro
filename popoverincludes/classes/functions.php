<?php
/* -------------------- Update Notifications Notice -------------------- */
if ( !function_exists( 'wdp_un_check' ) ) {
  add_action( 'admin_notices', 'wdp_un_check', 5 );
  add_action( 'network_admin_notices', 'wdp_un_check', 5 );
  function wdp_un_check() {
    if ( !class_exists( 'WPMUDEV_Update_Notifications' ) && current_user_can( 'edit_users' ) )
      echo '<div class="error fade"><p>' . __('Please install the latest version of <a href="http://premium.wpmudev.org/project/update-notifications/" title="Download Now &raquo;">our free Update Notifications plugin</a> which helps you stay up-to-date with the most stable, secure versions of WPMU DEV themes and plugins. <a href="http://premium.wpmudev.org/wpmu-dev/update-notifications-plugin-information/">More information &raquo;</a>', 'wpmudev') . '</a></p></div>';
  }
}
/* --------------------------------------------------------------------- */

function set_popover_url($base) {

	global $popover_url;

	if(defined('WPMU_PLUGIN_URL') && defined('WPMU_PLUGIN_DIR') && file_exists(WPMU_PLUGIN_DIR . '/' . basename($base))) {
		$popover_url = trailingslashit(WPMU_PLUGIN_URL);
	} elseif(defined('WP_PLUGIN_URL') && defined('WP_PLUGIN_DIR') && file_exists(WP_PLUGIN_DIR . '/popover/' . basename($base))) {
		$popover_url = trailingslashit(WP_PLUGIN_URL . '/popover');
	} else {
		$popover_url = trailingslashit(WP_PLUGIN_URL . '/popover');
	}

}

function set_popover_dir($base) {

	global $popover_dir;

	if(defined('WPMU_PLUGIN_DIR') && file_exists(WPMU_PLUGIN_DIR . '/' . basename($base))) {
		$popover_dir = trailingslashit(WPMU_PLUGIN_URL);
	} elseif(defined('WP_PLUGIN_DIR') && file_exists(WP_PLUGIN_DIR . '/popover/' . basename($base))) {
		$popover_dir = trailingslashit(WP_PLUGIN_DIR . '/popover');
	} else {
		$popover_dir = trailingslashit(WP_PLUGIN_DIR . '/popover');
	}


}

function popover_url($extended) {

	global $popover_url;

	return $popover_url . $extended;

}

function popover_dir($extended) {

	global $popover_dir;

	return $popover_dir . $extended;


}

function get_popover_plugins() {
	if ( is_dir( popover_dir('popoverincludes/plugins') ) ) {
		if ( $dh = opendir( popover_dir('popoverincludes/plugins') ) ) {
			$pop_plugins = array ();
			while ( ( $plugin = readdir( $dh ) ) !== false )
				if ( substr( $plugin, -4 ) == '.php' )
					$pop_plugins[] = $plugin;
			closedir( $dh );
			sort( $pop_plugins );

			return apply_filters('autoblog_available_plugins', $pop_plugins);

		}
	}

	return false;
}

function load_popover_plugins() {

	$plugins = get_option('popover_activated_plugins', array());

	if ( is_dir( popover_dir('popoverincludes/plugins') ) ) {
		if ( $dh = opendir( popover_dir('popoverincludes/plugins') ) ) {
			$pop_plugins = array ();
			while ( ( $plugin = readdir( $dh ) ) !== false )
				if ( substr( $plugin, -4 ) == '.php' )
					$pop_plugins[] = $plugin;
			closedir( $dh );
			sort( $pop_plugins );
			foreach( $pop_plugins as $pop_plugin )
				include_once( popover_dir('popoverincludes/plugins/' . $pop_plugin) );
		}
	}
}

function load_all_popover_plugins() {
	if ( is_dir( popover_dir('popoverincludes/plugins') ) ) {
		if ( $dh = opendir( popover_dir('popoverincludes/plugins') ) ) {
			$pop_plugins = array ();
			while ( ( $plugin = readdir( $dh ) ) !== false )
				if ( substr( $plugin, -4 ) == '.php' )
					$pop_plugins[] = $plugin;
			closedir( $dh );
			sort( $pop_plugins );
			foreach( $pop_plugins as $pop_plugin )
				include_once( popover_dir('popoverincludes/plugins/' . $pop_plugin) );
		}
	}
}

?>