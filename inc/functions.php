<?php


function load_popover_addons() {

	$addons = get_option('popover_activated_addons', array());

	if ( is_dir( PO_INC_DIR . 'addons') ) {
		if ( $dh = opendir( PO_INC_DIR . 'addons') ) {
			$pop_addons = array ();
			while ( ( $addon = readdir( $dh ) ) !== false )
				if ( substr( $addon, -4 ) == '.php' )
					$pop_addons[] = $addon;
			closedir( $dh );
			sort( $pop_addons );

			$pop_addons = apply_filters('popover_available_addons', $pop_addons);

			foreach( $pop_addons as $pop_addon ) {
				if(in_array($pop_addon, (array) $addons)) {
					include_once( PO_INC_DIR . 'addons/'. $pop_addon );
				}
			}

		}
	}
}
