#!/usr/bin/bash
# v 2014-08-12 15:28

# show an error message and stop the script.
error() {
	clear
	echo "Error:"
	for var in "$@"; do
		echo "$var"
	done
	echo ""
	exit 1;
}
# Display the current configuration details.
show_config() {
	echo ""
	echo "------------------------------------------"
	echo "Current Plugin"
	echo "  Install Dir:        wp-content/plugins/$EXPORT_FOLDER"
	echo "  Script Dir:         $CUR_DIR"
	echo "WordPress zip archive"
	echo "  Download Version:   $WP_VERSION"
	echo "  Download Dir:       $WP_INSTALL_DIR"
	echo "  Download File:      $WP_INSTALL_FILE"
	echo "  Dashboard File:     $WP_DASHBOARD_FILE"
	echo "Test installation"
	echo "  WordPress Dir:      $WP_DIR"
	echo "  WordPress URL:      $WP_URL"
	echo "  WordPress User:     $WP_USER"
	echo "  WordPress Pass:     $WP_PASS"
	echo "Test database"
	echo "  DB Host:            $DB_HOST"
	echo "  DB Name:            $DB_NAME"
	echo "  DB User:            $DB_USER"
	echo "  DB Pass:            $DB_PASS"
	echo "WordPress SVN"

	if [ "" == "$SVN_DIR" ]; then
		echo "  (No SVN repository available)"
	else
		echo "  SVN Dir:            $SVN_DIR"
	fi

	echo "------------------------------------------"
}


if [ -f "./local.config.sh" ]; then
	. local.config.sh
else
	error "There must be a local.config.sh file in the current directory."
fi