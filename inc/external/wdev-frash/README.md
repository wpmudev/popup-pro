# WDEV Frash module #

WPMU DEV Free Dashboard module (short wdev-frash) is used in our free plugins hosted on wordpress.org
It will display a welcome message upon plugin activation that offers the user a 5-day introduction email course for the plugin. After 7 days the module will display another message asking the user to rate the plugin on wordpress.org

# How to use it #

1. Insert this repository as **sub-module** into the existing project

2. Include the file `module.php` in your main plugin file.

3. Call the action `wdev-register-plugin` with the params mentioned below.

4. Done!


## Code Example (from Membership 2) ##

```
#!php

<?php
// Load the WDev-Frash module.
include_once 'lib/wdev-frash/module.php';

// Register the current plugin.
do_action(
	'wdev-register-plugin',
	/* 1             Plugin ID */ plugin_basename( __FILE__ ),
	/* 2          Plugin Title */ 'Membership 2',            
	/* 3 https://wordpress.org */ '/plugins/membership/',
	/* 4      Email Button CTA */ __( 'Get Members!', MYD_TEXT_DOMAIN ),  
	/* 5  getdrip Plugin param */ 'Membership'
);
// All done!
```

1. Always same, do not change
2. The plugin title, same as in the plugin header (no translation!)
3. The wordpress.org plugin-URL
4. Optional: Title of the Email-subscription button. If empty no email message is displayed.
5. Optional: getdrip plugin name (defined in the getdrip rule). If empty no email message is displayed