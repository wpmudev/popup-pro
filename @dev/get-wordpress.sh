#!/usr/bin/bash
# v 2014-08-07 21:04
clear

if [ -f local.config.sh ]; then
	. local.config.sh
else
	echo "There must be a local.config.sh file in the current directory."
	exit 1;
fi

CUR_DIR="$( pwd )"

# Display a sumary of all parameters for the user.
show_infos() {
	echo "Usage:"
	echo "  sh $0"
	echo ""
	echo "------------------------------------------"
	echo "WordPress zip archive"
	echo "  Download Version:   $WP_VERSION"
	echo "  Download Dir:       $WP_INSTALL_DIR"
	echo "  Download File:      $WP_INSTALL_FILE"
	echo "Test installation"
	echo "  WordPress Dir:      $WP_DIR"
	echo "  WordPress URL:      $WP_URL"
	echo "  WordPress User:     $WP_USER"
	echo "  WordPress Pass:     $WP_PASS"
	echo "------------------------------------------"
	echo "Task: Download fresh WordPress zip Archive"
	echo "------------------------------------------"
}

# Remove the existing WordPress folder and create a new empty folder.
create_dir() {
	if [ ! -d "$WP_INSTALL_DIR" ]; then
		mkdir -p "$WP_INSTALL_DIR"
		echo "- Created new WordPress directory"
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

show_infos
create_dir
download_wp

echo ""
echo "There you go: Latest WordPress files downloaded!"
echo ""