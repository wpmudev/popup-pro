# README

*Similar structure as: Membership 2, Popup, Custom Sidebars, CoursePress*

The **only** development branch for Popup is `master`. This branch ultimately is responsible for creating the production branches that are finally published.

Production branches are automatically built, based on the master branch. Any changes made to those other branches will be **overwritten**!

**Remember:** `master` is the ONLY branch that should be edited and forked!

**Notes:** 

1. ONLY fork and submit pull-requests to the master branch `master`!
2. NEVER fork the production branches (below)!
3. NEVER publish/release the master branch `master` anywhere!

-----

# PRODUCTION BRANCHES

Production branches are always supposed to be stable and can be released/published at any time.


## Popup Pro (popup-pro)  

This is the official premium plugin that lives on WPMU DEV.

It uses libraries that are not included in the free version (like WPMU DEV Notification integration) and has all features.


## Popup (popup-free)  

This is the free limited version that gets published to the WordPress plugin directory. 

It includes a special module to display up to 2 notifications to the user: Right after installation (sign up to a newsletter) and after seven days (rate plugin on wp.org)


-----

# DEVELOPMENT

As mentioned above: Only directly edit the branch `master`. Other branches should be only updated via grunt tasks (see section "Automation" below).

Important: Do not let your IDE change the **source order** of the code. Fixing up formatting is fine, but moving code blocks around is not! It will confuse grunt and produce problems.

## Implement version differences

As mentioned, we will only update the master branch with all changes, even if those changes only relate to one specific version (i.e. pro only changes). 

There is one way to add code that is specific to a single product only:

1. Wrap code in product conditions.
2. Pro-Version of /views template.

### Product conditions

There are special comments in the `master` branch will make sure some code only end up on the pro plugin and some code only end up in the free plugin.

Those are:

```
#!php 
/* start:pro */
echo 'This is only in popup-pro';  
/* end:pro */
  
/* start:free */
echo 'This is only in popup-free';  
/* end:free */
```

### Pro-Version of /views template

All direct output of the admin UI is made by files inside the /views folder.
Those templates are loaded by the popup plugin and have a built in condition:

If a "template-premium.php" file exists, the pro version will load this file (instead of "template.php").

So to create custom layout for free version make sure that both "whatever.php" and "whatever-premium.php" exist, and then modify the according file to make the changes.

* views/whatever.php ... Always loaded by free version; loaded by pro version if no -premium.php is found.
* views/whatever-premium.php ... Loaded by pro version; grunt removes this file in the free plugin.

*For details review function: IncPopupBase::load_view()*


## Working with the branches

### Cloning

Popup uses submodules, so use the `--recursive` flag if you clone from command line:  

```
#!bash 
$ git clone git@bitbucket.org:incsub/popover.git --recursive  
```

If you already have a cloned repo, you will need to *init* the submodule.  

```
#!bash 
$ git submodule init --
$ git submodule update  
```

### JS and CSS files

Only edit/create javascript and css files inside the `/src` folders:

* `js/src/*` for javascript.
* `css/src/*` for css. Use .scss extension (SASS)!

Important: Those folders are scanned and processed when running grunt. Files in base of `js/` and `css/` are **overwritten** by grunt.

*Note:*
There is a hardcoded list of js and scss files that are monitored and compiled by grunt. If you add a new js or scss file then you need to edit `Gruntfile.js` and add the new file to the file list in `js_files_concat` or `css_files_compile`.


-----

# AUTOMATION

See notes below on how to correctly set up and use grunt. 

Many tasks as well as basic quality control are done via grunt. Below is a list of supported tasks.

**Important**: Before making a pull-request to the master branch always run the task `grunt` - this ensures that all .php, .js and .css files are validated. If an problems are reported then fix those problems before submitting the pull request.

### Grunt Task Runner  

**ALWAYS** use Grunt to build the production branches. Use the following commands:  

Category | Command | Action
---------| ------- | ------
Edit | `grunt watch` | Watch js and scss files, auto process them when changed. Similar as running `grunt` after each js/css change.
Build | `grunt` | Run all default tasks: lint, compile js/css. **Run this task before submitting a pull-request**.
Build | `grunt build` | Runs all default tasks + lang, builds pro + free product versions.
Build | `grunt build:pro` | Same as build, but only build the pro plugin version.
Build | `grunt build:free` | Same as build, but only build the free plugin version.


### Set up grunt

#### 1. npm

First install node.js from: <http://nodejs.org/>  

```
#!bash 
# Test it:
$ npm -v

# Install it system wide (optional but recommended):
$ npm install -g npm
```

#### 2. grunt

Install grunt by running this command in command line:

```
#!bash 
# Install grunt:
$ npm install -g grunt-cli
```

#### 3. Setup project

In command line switch to the `plugins/popover` plugin folder. Run this command to set up grunt for the plugin:

```
#!bash 
# Install automation tools for plugin:
$ cd <path-to-wordpress>/wp-content/plugins/popover
$ npm install

# Test it:
$ grunt hello
```

#### 4. Install required tools

Same as 3: Run commands in the `plugins/popover` plugin folder:

```
#!bash 
$ cd <path-to-wordpress>/wp-content/plugins/popover

# Config git with your Name/Email
$ git config user.email "<your email>"
$ git config user.name "<your name>"
```

----

# RELEASE

### 1. Build the release version

1.) Switch to `master` branch.

2.) Make sure the version number in **main plugin file** is correct and that the version in file `package.json` matches the plugin version. (in package.json you have x.y.z format, so "1.2.3.4" becomes "1.2.34" here)

3.) Then run `grunt build` (or `grunt build:pro` / free). This will create a .zip archive of the release files and update the `popup-pro`/`-free` branches.

4.) Only in `master` branch: There is a folder called `release/` which contains the release files as .zip archive.

5.a) **PRO**: Simply upload the zip file from the `release/` folder. The `popup-pro` branch is not even needed.

5.b) **FREE**: (First set up a mixed repo as described below) After you built the free version, switch to the `popup-free` branch and then commit those files to wp.org repository using SVN.


##### Setting up the mixed repo in same folder (SVN + GIT)

For wp.org releases I found the easiest solution is to have a "mixed" working copy, that contains both .git and .svn files. This way we only have one place where code is stored. Bitbucket is our main version control. SVN is only used/updated when a new version of the free version should be published.

This is the one-time setup routine I used to create this mixed working copy:

1. Get a working copy of the GIT repo in local folder `.../popup`
2. Get a working copy of the SVN repo in local folder `.../popup-svn`
3. Now copy all files/folders (also hidden ones) from `popup-svn` into `popup`. Important: Only add/overwrite files. Do not delete the .git folder/files!!
4. Verify in SVN that the popup folder now is a valid SVN repo. Now you can delete the poup-svn folder again.
5. Now make sure that the .gitignore file contians the entry `.svn`
6. When .gitignore is correct then revert all files in git to restore the master-branch. This will cause a lot of edits show up in SVN, but ignore those. The only time you want to use SVN is after you switched to the `popup-free` branch. ONLY THEN commit changes to SVN/wp.org!!

### 2. Update product versions

The example shows how to update the Pro-version, but the process for free version is identical.

1. **Switch** to branch `master`
1. Run **grunt** command `$ grunt build:pro`
1. **Switch** to branch `popup-pro`
1. Do a git **pull**, *possibly some conflicts are identified!*
1. Do NOT resolve the conflicts, but **revert** the conflicting files to last version!!
> Grunt already committed the correct file version to git. The conflicts are irrelevant!
1. Now **commit** and **push** the changes to bitbucket
