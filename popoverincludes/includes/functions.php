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

function popover_helpimage( $image ) {
	echo "<img src='" . popover_url('popoverincludes/help/images/' . $image) . "' />";
}

function popover_db_prefix(&$wpdb, $table) {

	if( defined('PO_GLOBAL') && PO_GLOBAL == true ) {
		if(!empty($wpdb->base_prefix)) {
			return $wpdb->base_prefix . $table;
		} else {
			return $wpdb->prefix . $table;
		}
	} else {
		return $wpdb->prefix . $table;
	}

}

function get_popover_addons() {
	if ( is_dir( popover_dir('popoverincludes/addons') ) ) {
		if ( $dh = opendir( popover_dir('popoverincludes/addons') ) ) {
			$pop_addons = array ();
			while ( ( $addon = readdir( $dh ) ) !== false )
				if ( substr( $addon, -4 ) == '.php' )
					$pop_addons[] = $addon;
			closedir( $dh );
			sort( $pop_addons );

			return apply_filters('popover_available_addons', $pop_addons);

		}
	}

	return false;
}

function load_popover_addons() {

	$addons = get_option('popover_activated_addons', array());

	if ( is_dir( popover_dir('popoverincludes/addons') ) ) {
		if ( $dh = opendir( popover_dir('popoverincludes/addons') ) ) {
			$pop_addons = array ();
			while ( ( $addon = readdir( $dh ) ) !== false )
				if ( substr( $addon, -4 ) == '.php' )
					$pop_addons[] = $addon;
			closedir( $dh );
			sort( $pop_addons );

			$pop_addons = apply_filters('popover_available_addons', $pop_addons);

			foreach( $pop_addons as $pop_addon ) {
				if(in_array($pop_addon, (array) $addons)) {
					include_once( popover_dir('popoverincludes/addons/' . $pop_addon) );
				}
			}

		}
	}
}

function load_all_popover_addons() {
	if ( is_dir( popover_dir('popoverincludes/addons') ) ) {
		if ( $dh = opendir( popover_dir('popoverincludes/addons') ) ) {
			$pop_addons = array ();
			while ( ( $addon = readdir( $dh ) ) !== false )
				if ( substr( $addon, -4 ) == '.php' )
					$pop_addons[] = $addon;
			closedir( $dh );
			sort( $pop_addons );
			foreach( $pop_addons as $pop_addon )
				include_once( popover_dir('popoverincludes/addons/' . $pop_addon) );
		}
	}
}

function P_style_urls( $styles = array() ) {

	$styles['Default'] = popover_url('popoverincludes/css/default');
	$styles['Default Fixed'] = popover_url('popoverincludes/css/fixed');
	$styles['Dark Background'] = popover_url('popoverincludes/css/fullbackground');
	$styles['Dark Background Fixed'] = popover_url('popoverincludes/css/fullbackgroundfixed');

	return $styles;
}
add_filter( 'popover_available_styles_url', 'P_style_urls');

function P_style_dirs() {
	$styles['Default'] = popover_dir('popoverincludes/css/default');
	$styles['Default Fixed'] = popover_dir('popoverincludes/css/fixed');
	$styles['Dark Background'] = popover_dir('popoverincludes/css/fullbackground');
	$styles['Dark Background Fixed'] = popover_dir('popoverincludes/css/fullbackgroundfixed');

	return $styles;
}
add_filter( 'popover_available_styles_directory', 'P_style_dirs');

?>