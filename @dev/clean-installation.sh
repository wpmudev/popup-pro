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
	echo "Task: Setup a fresh WordPress installation and install this plugin"
	echo "------------------------------------------"
}

# Remove the existing WordPress folder and create a new empty folder.
create_dir() {
	if [ -d "$WP_DIR" ]; then
		if [ -L "$WP_DIR" ]; then
			rm "$WP_DIR"
		else
			rm -rf "$WP_DIR"
		fi
		echo "- Removed old WordPress directory"
	fi

	mkdir -p "$WP_DIR"
	cd "$WP_DIR"
	echo "- Created new WordPress directory"
}

# Download WordPress core files
install_wp() {
	if [ $WP_VERSION == 'latest' ]; then
		local ARCHIVE_NAME='latest'
	else
		local ARCHIVE_NAME="wordpress-$WP_VERSION"
	fi

	echo "- Download and install WordPress files (version '$WP_VERSION') ..."
	curl -s -o "$WP_DIR"/wordpress.tar.gz http://wordpress.org/${ARCHIVE_NAME}.tar.gz
	tar --strip-components=1 -zxmf "$WP_DIR"/wordpress.tar.gz -C "$WP_DIR"
	rm  "$WP_DIR"/wordpress.tar.gz
	echo "- Installation finished"
}

# Drop old Database and create a new, empty WordPress database.
install_db() {
	# parse DB_HOST for port or socket references
	local PARTS=(${DB_HOST//\:/ })
	local DB_HOSTNAME=${PARTS[0]};
	local DB_SOCK_OR_PORT=${PARTS[1]};
	local EXTRA=""

	if ! [ -z $DB_HOSTNAME ] ; then
		if [[ "$DB_SOCK_OR_PORT" =~ ^[0-9]+$ ]] ; then
			EXTRA=" --host=$DB_HOSTNAME --port=$DB_SOCK_OR_PORT --protocol=tcp"
		elif ! [ -z $DB_SOCK_OR_PORT ] ; then
			EXTRA=" --socket=$DB_SOCK_OR_PORT"
		elif ! [ -z $DB_HOSTNAME ] ; then
			EXTRA=" --host=$DB_HOSTNAME --protocol=tcp"
		fi
	fi

	mysqladmin drop $DB_NAME --force --silent --user="$DB_USER" --password="$DB_PASS"$EXTRA
	mysqladmin create $DB_NAME --force --silent --user="$DB_USER" --password="$DB_PASS"$EXTRA
	echo "- Created fresh database"

	if [ -f "$WP_DIR"/wp-config.php ]; then
		rm "$WP_DIR"/wp-config.php
	fi

	cd "$WP_DIR"
	wp core config \
		--dbhost=$DB_HOST \
		--dbname=$DB_NAME \
		--dbuser="$DB_USER" \
		--dbpass="$DB_PASS" \
		--dbprefix=test_ \
		--skip-check \
		--extra-php << END
define( 'WP_DEBUG_LOG', true );
if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
	define( 'WP_DEBUG', false );
} else {
	define( 'WP_DEBUG', true );
}
END

	wp core install \
		--url=$WP_URL \
		--title="Testing installation" \
		--admin_user=$WP_USER \
		--admin_password=$WP_PASS \
		--admin_email=$WP_EMAIL
}

install_plugin() {
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
create_dir
install_wp
install_db
install_plugin

echo ""
echo "There you go: $WP_URL is a fresh and clean WordPress installation!"
echo ""