
# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #
#                                                                             #
#                           - DONT EDIT THIS FILE -                           #
#                MAKE A LOCAL COPY FOR YOUR OWN SETTINGS AT                   #
#                                                                             #
#                               local.config.sh                               #
#                                                                             #
# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #

# ----- Used to create plugin zip-archive -----

# Current plugin version.
VER=1.0

# Name of the plugin folder.
# This allows us to use a different folder name for development, for example
# internal plugin dir is "custom-sidebars-pro" and "custom-sidebars-free" but
# the export folder is always "custom-sidebars"
EXPORT_FOLDER=plugin-name


# ----- Used to create a test installation -----

# The WordPress installation archive will be downloaded to this directory.
WP_INSTALL_DIR=/tmp/wordpress

# The domain where the test installation will be available at
# You have to manually setup your DNS/webserver for this first.
WP_URL=http://local.stage

# WordPress admin user.
WP_USER=test

# WordPress admin password.
WP_PASS=test

# WordPress admin email.
WP_EMAIL=test@local.stage

# The test installation will be copied to this directory.
# Important: This directory will be deleted and created again. All data that
# is inside the directory will be lost!
WP_DIR=/dir/to/wordpress-test

# WordPress version that is installed.
# Can be "latest" or any version number such as "3.1"
WP_VERSION=latest

# Database to use for the test installation.
# Important: This database will be droped and created again. All data that is
# inside the database will be lost!
DB_NAME=local-stage

# Database user.
DB_USER=stage-user

# Database password.
DB_PASS=stage-pass

# Database server.
DB_HOST=localhost


# ----- Used to update the wordpress.org SVN repository -----

# The path of the SVN repository.
SVN_DIR=/dir/to/svn


# ----- No need to change these settings -----

# Defines the default path and name of the exported zip archive.
EXPORT_ARCHIVE=~/Desktop/$EXPORT_FOLDER-pro-$VER.zip

# Generate the filename to the WordPress installation archive.
WP_INSTALL_FILE="$WP_INSTALL_DIR"/wordpress-$WP_VERSION.tar.gz