

======================  NOTES ON DEVELOPMENT AND TESTING  ======================

--------------------------------------------------------------------------------
Philipp Stracker                                     Scripts are written for OSX


--------------------------------------------------------------------------------
== PLUGIN SETTINGS

  - @dev/local.config.sh .. All scripts use the settings defined here. This file
                          is not commited to git repository, every Dev can set
                          the options for his own environment here.

Available scripts; run them from the @dev folder!
  $ sh do/clean-installation.sh [multisite]
  $ sh do/install-plugin.sh
  $ sh do/svn-update.sh

Grunt tasks
  $ grunt        Pre-Process source files and remove temp files
  $ grunt watch
  $ grunt test   PHPUnit and JSLint
  $ grunt build  Archive is saved to the release/ folder


--------------------------------------------------------------------------------
== INTERNAL FOLDERS AND FILES

  dev/           Collection of scripts to automate testing*
  tests/         Unit tests
  release/       Clean exports of the plugin
  node_modules/  Grunt modules

  * The dev folder should be removed and scripts integrated as grunt tasks


--------------------------------------------------------------------------------
== GET PLUGIN ZIP ARCHIVE

  - Tools needed for archiving
    - git-archive-all  (https://github.com/Kentzo/git-archive-all)

  1. Edit file dev/local.config.sh

  2. Use terminal to create the zip archive:

     $ cd <plugin-dir>
     $ grunt build
     ## This will generate a clean archive with all plugin files in release/ dir

     $ cd <plugin-dir>/dev
     $ sh do/svn-update.sh
     ## This will copy all plugin files to the SVN folder (defined in local.config)


--------------------------------------------------------------------------------
== TESTING

  - Tools needed for testing:
    - wp-cli   (http://wp-cli.org/#install)
    - PHPUnit  (http://phpunit.de/manual/current/en/installation.html)

  - Important test-cases are documented in dev/testcases.txt

  - PHP Unit tests are in tests

  - To setup a fresh WordPress installation with only this plugin installed use
    one of these commands in the directory <plugin-dir>/@dev:

    $ sh do/clean-installation.sh
    ## Create a new Single-Blog stage installation

    $ sh do/clean-installation.sh multisite
    ## Create a new Multisite stage installation

    $ sh do/install-plugin.sh
    ## Copy the current plugin to the stage installation

