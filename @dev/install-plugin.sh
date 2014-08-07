#!/usr/bin/bash
# v 2014-07-03 21:04
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
	echo "Current Plugin"
	echo "  Plugin version:     $VER"
	echo "  Install Dir:        wp-content/plugins/$EXPORT_FOLDER"
	echo "  Script Dir:         $CUR_DIR"
	echo "Test installation"
	echo "  WordPress Dir:      $WP_DIR"
	echo "  WordPress URL:      $WP_URL"
	echo "  WordPress User:     $WP_USER"
	echo "  WordPress Pass:     $WP_PASS"
	echo "  WordPress version:  $WP_VERSION"
	echo "Test database"
	echo "  DB Host:            $DB_HOST"
	echo "  DB Name:            $DB_NAME"
	echo "  DB User:            $DB_USER"
	echo "  DB Pass:            $DB_PASS"
	echo "------------------------------------------"
	echo "Task: Install this plugin to the WordPress installation"
	echo "------------------------------------------"
}

install_plugin() {
	if [ ! -d "$WP_DIR" ]; then
		echo "WordPress installation not found"
		echo "use `clean-install.sh` first"
		exit 1;
	fi
	if [ ! -d "$WP_DIR"/wp-content/plugins ]; then
		echo "WordPress installation not found"
		echo "use `clean-install.sh` first"
		exit 1;
	fi

	if [ -f "$CUR_DIR"/archive.sh ]; then
		cd "$CUR_DIR"
		"$CUR_DIR"/archive.sh "$CUR_DIR"/plugin.zip
		echo "- Created a clean export of the current plugin"
	fi
	if [ -f "$CUR_DIR"/plugin.zip ]; then
		unzip -o -q plugin.zip -d "$WP_DIR"/wp-content/plugins/
		echo "- Plugin extracted to new WordPress installation"
		rm "$CUR_DIR"/plugin.zip
	fi
}

show_infos
install_plugin

echo ""
echo "There you go: Plugin installed to WordPress installation at $WP_URL!"
echo ""