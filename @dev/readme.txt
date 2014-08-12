

======================  NOTES ON DEVELOPMENT AND TESTING  ======================
                                - POP UP PRO -

--------------------------------------------------------------------------------
Philipp Stracker                                     Scripts are written for OSX


--------------------------------------------------------------------------------
== PLUGIN SETTINGS

  - changelog.txt .. Plugin version
  - customsidebars.php .. Plugin version (2x)
  - @dev/config.txt .. Options for clean-installation.sh and install-plugin.sh
                       Create a local copy and name it @dev/local.config.txt !!

Available scripts; run them from the @dev folder!
  - sh do/archive.sh
  - sh do/get-wordpress.sh
  - sh do/clean-installation.sh [multisite]
  - sh do/install-plugin.sh
  - sh do/svn-update.sh


--------------------------------------------------------------------------------
== INTERNAL FOLDERS AND FILES

  The "@dev*" folders are internal folders, they are not included in the git-archive.
  -> check the .gitattributes file for detailled infos which files are excluded

  @dev-css .. These are SASS files that should be compiled into /css
  @dev-js .. These are js files that are minified into /js

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

  1. Edit file @dev/archive.sh and make sure the version-number is correct

  2. Use terminal to create the zip archive:
     > cd <plugin-dir>/@dev
     > sh ./archive.sh
     This will generate a clean zip archive with all plugin files on your desktop.


  Deploy the exported zip archive to wordpress SVN!


--------------------------------------------------------------------------------
== TESTING

  - Tools needed for testing:
    - wp-cli   (http://wp-cli.org/#install)
    - PHPUnit  (http://phpunit.de/manual/current/en/installation.html)

  - Important test-cases are documented in @dev/testcases.txt

  - To setup a fresh WordPress installation with only this plugin installed use
    the script `clean-installation.sh`

