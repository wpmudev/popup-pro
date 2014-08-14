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

copy_files() {
	if [ ! -d "$SVN_DIR" ]; then
		error "SVN path not found: $SVN_DIR"
	fi

	if [ -f "$CUR_DIR"/do/archive.sh ]; then
		cd "$CUR_DIR"
		"$CUR_DIR"/do/archive.sh "$CUR_DIR"/plugin.zip
		echo "- Created a clean export of the current plugin"
	else
		error "Could not find do/archive.sh script"
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
	else
		error "Could not find plugin archive"
	fi
}

show_infos
copy_files

echo ""
echo "There you go: Plugin updated at $SVN_DIR!"
echo ""