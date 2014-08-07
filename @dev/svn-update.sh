#!/usr/bin/bash
# v 2014-07-09 17:44
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

copy_files() {
	if [ ! -d "$SVN_DIR" ]; then
		echo "SVN path not found: $SVN_DIR"
		exit 1;
	fi

	if [ -f "$CUR_DIR"/archive.sh ]; then
		cd "$CUR_DIR"
		"$CUR_DIR"/archive.sh "$CUR_DIR"/plugin.zip
		echo "- Created a clean export of the current plugin"
	fi
	if [ -f "$CUR_DIR"/plugin.zip ]; then
		rm -rf "$CUR_DIR/plugin"
		unzip -o -q plugin.zip -d "./plugin"
		rm "$CUR_DIR"/plugin.zip

		printf "-"
		printf " Copy files"
		FILES=$(find "./plugin" -type f)
		for f in $FILES
		do
			if [[ $f != *.svn* ]]; then
				t=${f/.\/plugin\/$EXPORT_FOLDER/$SVN_DIR}
				cp "$f" "$t"
				printf "."
			fi
		done
		echo ""
		rm -rf "$CUR_DIR/plugin"
	fi
}

show_infos
copy_files

echo ""
echo "There you go: Plugin updated at $SVN_DIR!"
echo ""