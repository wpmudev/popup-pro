#!/usr/bin/bash
# v 2014-08-12 15:28

. do/.load-config.sh

SILENT=0
if [ "silent" == "$1" ]; then
	SILENT=1
fi

# Display a sumary of all parameters for the user.
show_infos() {
	echo "Usage:"
	echo "  sh $0"

	show_config

	echo "Task: Download fresh WordPress zip Archive"
	echo "------------------------------------------"
}

# Remove the existing WordPress folder and create a new empty folder.
create_dir() {
	if [ ! -d "$WP_INSTALL_DIR" ]; then
		mkdir -p "$WP_INSTALL_DIR"
		echo "- Created temporary WordPress directory"
	fi
}

# Download WordPress core files
download_wp() {
	if [ $WP_VERSION == 'latest' ]; then
		local ARCHIVE_NAME='latest'
	else
		local ARCHIVE_NAME="wordpress-$WP_VERSION"
	fi

	echo "- Download WordPress files (version '$WP_VERSION') ..."
	curl -s -o $WP_INSTALL_FILE http://wordpress.org/${ARCHIVE_NAME}.tar.gz
	echo "- File downloaded"
}

# Download the WPMU DEV Dashboard installer
download_dashboard() {
	echo "- Download WPMU DEV Dashboard ..."
	curl -s -o $WP_DASHBOARD_FILE http://premium.wpmudev.org/wdp-un.php?action=install_wpmudev_dash
	echo "- File downloaded"
}

if [ 0 == $SILENT ]; then
	clear
	show_infos
fi
create_dir
download_wp
download_dashboard

echo ""
echo "There you go: Latest WordPress files downloaded!"
echo ""