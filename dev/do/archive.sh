#!/bin/bash
# v 2014-08-12 15:28


# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #
#                                                                             #
#                            CREATE EXPORT ARCHIVE                            #
#                                                                             #
# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #

. do/.load-config.sh

# Allow the output filename to be overwritten.
# Example:
#   $ sh do/archive.sh plugin_file.zip
if [ $# -gt 0 ]; then
	EXPORT_ARCHIVE=$1
fi

# Check if the git-archive-all script is available.
if [ ! -f "/usr/local/bin/git-archive-all" ]; then
	error "git-archive-all must be located in folder /usr/local/bin" \
		"See: https://github.com/Kentzo/git-archive-all"
fi

cd ..
python /usr/local/bin/git-archive-all --force-submodules --prefix $EXPORT_FOLDER/ "$EXPORT_ARCHIVE"

# ------------------------------------------------------------------------------
# Depends on:
# https://github.com/Kentzo/git-archive-all
