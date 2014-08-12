#!/usr/bin/bash
# v 2014-08-12 15:28
clear

. do/.load-config.sh

# Display a sumary of all parameters for the user.
show_infos() {
	echo "Usage:"
	echo "  sh $0"

	show_config

	echo "Task: Install this plugin to the WordPress installation"
	echo "------------------------------------------"
}

install_plugin() {
	if [ ! -d "$WP_DIR" ]; then
		error "WordPress installation not found. Use this command first:" \
			"$ sh do/clean-install.sh"
	fi
	if [ ! -d "$WP_DIR"/wp-content/plugins ]; then
		error "WordPress installation not found. Use this command first:" \
			"$ sh do/clean-install.sh"
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