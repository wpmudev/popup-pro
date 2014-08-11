#!/usr/bin/bash
# v 2014-07-03 21:04
clear

if [ -f local.config.sh ]; then
	. local.config.sh
else
	echo "There must be a local.config.sh file in the current directory."
	exit 1;
fi

if [ "$1" == "multisite" ]; then
	MULTISITE=1
else
	MULTISITE=0
fi

CUR_DIR="$( pwd )"

# Display a sumary of all parameters for the user.
show_infos() {
	echo "Usage:"
	echo "  sh $0 [multisite]"
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
	echo "  WordPress source:   $WP_INSTALL_FILE"
	echo "Test database"
	echo "  DB Host:            $DB_HOST"
	echo "  DB Name:            $DB_NAME"
	echo "  DB User:            $DB_USER"
	echo "  DB Pass:            $DB_PASS"
	echo "------------------------------------------"
	echo "Task: Setup a fresh WordPress installation and install this plugin"
	if [ $MULTISITE == 1 ]; then
		echo "      Do Multisite Installation"
	fi
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

# Install WordPress core files
install_wp() {
	if [ ! -f $WP_INSTALL_FILE ]; then
		if [ -f "$CUR_DIR/get-wordpress.sh" ]; then
			cd "$CUR_DIR"
			sh ./get-wordpress.sh silent
		else
			echo "- WordPress source not found. Please first download the files using this command:"
			echo "  sh ./get-wordpress.sh"
			exit 1;
		fi
	fi

	echo "- Install WordPress files (version '$WP_VERSION') ..."
	tar --strip-components=1 -zxmf $WP_INSTALL_FILE -C "$WP_DIR"
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

install_multisite() {
	if [ $MULTISITE == 1 ]; then
		echo "- Convert installation to multisite..."
		wp core multisite-convert --title="Testing network"

		echo "- Create 3 more Test-Sites..."
		wp site create --slug="site2" --title="Test Site 2"
		wp site create --slug="site3" --title="Test Site 3"
		wp site create --slug="site4" --title="Test Site 4"
	fi
}

install_dashboard() {
	if [ -f "$WP_DASHBOARD_FILE" ]; then
		echo "- Install and activate WPMU DEV Dashboard."
		wp plugin install $WP_DASHBOARD_FILE --activate

		wp option add wpmudev_apikey $WPMUDEV_APIKEY
		if [ $MULTISITE == 1 ]; then
			wp db query "INSERT INTO test_sitemeta (site_id, meta_key, meta_value) VALUES (1, 'wpmudev_apikey', '$WPMUDEV_APIKEY')"
		fi
	else
		echo "- Did not find the WPMU Dev Dashboard archive..."
	fi
}

install_plugin() {
	echo "- Installing plugin... All changes must be commited in git!"
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

populate() {
	# Error "Can't select database":
	# https://github.com/wp-cli/wp-cli/wiki/FAQ#error-cant-connect-to-the-database

	echo "- Creating some demo posts/pages"
	wp post generate --count=50 --post_type="post"
	wp post generate --count=30 --post_type="page" --max_depth=3
	wp term generate category --count=20 --max_depth=3
	wp term generate post_tag --count=20
	wp user generate --count=15
}

# The .htaccess file is for some reason not created by the above functions...
create_htaccess() {
	# This is a sub-directory setup
	if [ $MULTISITE == 1 ]; then
		cat <<EOF >"$WP_DIR"/.htaccess
		RewriteEngine On
		RewriteBase /
		RewriteRule ^index\.php$ - [L]

		# add a trailing slash to /wp-admin
		RewriteRule ^([_0-9a-zA-Z-]+/)?wp-admin$ $1wp-admin/ [R=301,L]

		RewriteCond %{REQUEST_FILENAME} -f [OR]
		RewriteCond %{REQUEST_FILENAME} -d
		RewriteRule ^ - [L]
		RewriteRule ^([_0-9a-zA-Z-]+/)?(wp-(content|admin|includes).*) $2 [L]
		RewriteRule ^([_0-9a-zA-Z-]+/)?(.*\.php)$ $2 [L]
		RewriteRule . index.php [L]
EOF
	else
		cat <<EOF >"$WP_DIR"/.htaccess
		RewriteEngine On
		RewriteBase /
		RewriteRule ^index\.php$ - [L]

		RewriteCond %{REQUEST_FILENAME} !-f
		RewriteCond %{REQUEST_FILENAME} !-d
		RewriteRule . /index.php [L]
EOF
	fi
}

show_infos
create_dir
install_wp
install_db
populate
install_multisite
install_dashboard
install_plugin
create_htaccess

echo ""
echo "There you go: $WP_URL is a fresh and clean WordPress installation!"
echo ""