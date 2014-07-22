#!/bin/bash
# v 2014-07-03 21:04


# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #
#                                                                             #
#                            CREATE EXPORT ARCHIVE                            #
#                                                                             #
# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #

if [ -f "local.config.sh" ]; then
	. local.config.sh
else
	echo "There must be a local.config.sh file in the current directory."
	exit 1;
fi

# Allow the output filename to be overwritten
if [ $# -gt 0 ]; then
	EXPORT_ARCHIVE=$1
fi

cd ..
python /usr/local/bin/git-archive-all --force-submodules --prefix $EXPORT_FOLDER/ "$EXPORT_ARCHIVE"

# ------------------------------------------------------------------------------
# Depends on:
# https://github.com/Kentzo/git-archive-all
