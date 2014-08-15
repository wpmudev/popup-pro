

======================  NOTES ON DEVELOPMENT AND TESTING  ======================

--------------------------------------------------------------------------------
Philipp Stracker                                     Scripts are written for OSX


--------------------------------------------------------------------------------
== PLUGIN SETTINGS

  - @dev/local.config.sh .. All scripts use the settings defined here. This file
                          is not commited to git repository, every Dev can set
                          the options for his own environment here.

Available scripts; run them from the @dev folder!
  $ sh do/archive.sh
  $ sh do/get-wordpress.sh
  $ sh do/clean-installation.sh [multisite]
  $ sh do/install-plugin.sh
  $ sh do/svn-update.sh


--------------------------------------------------------------------------------
== INTERNAL FOLDERS AND FILES

  The "@dev*" folders are internal folders, they are not included in the git-archive.
  -> check the .gitattributes file for detailled infos which files are excluded

  @dev-css .. These are SASS files that should be compiled into /css
  @dev-js .. These are js files that are minified into /js

  @tests .. any PHP Unit tests are collected in this folder.

-- Prepros

  For compilation and minifing files use Prepros (http://alphapixels.com/prepros/)
  In case you have the Pro version you can import the prepros.json file which
  already contains all the correct project settings.

  These files are used by Prepros:
  - config.rb
  - prepros.json


--------------------------------------------------------------------------------
== GET PLUGIN ZIP ARCHIVE

  - Tools needed for archiving
    - git-archive-all  (https://github.com/Kentzo/git-archive-all)

  1. Edit file @dev/local.config.sh and make sure the version-number is correct

  2. Use terminal to create the zip archive:

     $ cd <plugin-dir>/@dev
     $ sh do/archive.sh
     ## This will generate a clean archive with all plugin files on your desktop.

     $ sh do/svn-update.sh
     ## This will copy all plugin files to the SVN folder (defined in local.config)


--------------------------------------------------------------------------------
== TESTING

  - Tools needed for testing:
    - wp-cli   (http://wp-cli.org/#install)
    - PHPUnit  (http://phpunit.de/manual/current/en/installation.html)

  - Important test-cases are documented in @dev/testcases.txt

  - PHP Unit tests are in @tests

  - To setup a fresh WordPress installation with only this plugin installed use
    one of these commands in the directory <plugin-dir>/@dev:

    $ sh do/clean-installation.sh
    ## Create a new Single-Blog stage installation

    $ sh do/clean-installation.sh multisite
    ## Create a new Multisite stage installation

    $ sh do/install-plugin.sh multisite
    ## Copy the current plugin to the stage installation

