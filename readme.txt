=== WordPress PopUp - Popover Maker ===
Contributors: WPMUDEV
Tags: Popup, Pop-up, Pop Over, popover, Responsive Popup, Advertise, Promotion, Marketing, Lightbox, Mailing list pop-up
Requires at least: 3.1
Tested up to: 4.5.3
Stable tag: trunk

Fully-responsive, 100% customizable popups for your WordPress site or network.

== Description ==

<strong>Create targeted marketing campaigns, keep customers from leaving your site, and build your mailing list with WordPress PopUp – it’s an incredibly easy to use and completely free popup plugin for WordPress.</strong>

Use the WordPress PopUp builder and create beautiful popups without touching a line of code. Choose style, color, and position, select your target audience, embed images and signup forms, and set when your popup should appear.

[youtube  https://www.youtube.com/watch?v=lxyomzQkQKc]

Create an unlimited number of popups with different looks and configurations. Run up to 3 different popups at a time.

★★★★★<br />
“The plugin works great! It is amazingly fast, easy to install and very flexible.” - <a href="http://profiles.wordpress.org/spkane">spkane</a>

★★★★★<br />
“Just awesome! Easy to use, simple install, and great support.” - <a href="https://profiles.wordpress.org/josblo">JosBlo</a>

With over 20 included display conditions and behaviors to work with, you can target just about any audience – create special deals for logged in users, referral link discounts, first time visitor promotions – build the perfect popup for every audience.

Plus, with the "Never see this message again" option, there’s no need to worry about annoying regular visitors with the same signup prompt or ads over-and-over again.

If you are looking to build your mailing list, increase sales, or promote an event, use WordPress PopUp.

<blockquote>
<strong>See What WordPress PopUp Can Do For You:</strong>

<ul>
<li>Design popups in minutes with the builder</li>
<li>Popup preview see your design without leaving the builder</li>
<li>Create unlimited popup styles</li>
<li>8 included reveal and hide animations</li>
<li>Fully-responsive design options for popups on any device</li>
<li>Included customizable popup template</li>
<li>20+ conditions and behaviors to control who see popups</li>
<li>Set when a popup appears based on time</li>
<li>Allow visitors to hide a pop-up from ever displaying again</li>
<li>3 load methods to optimize performance</li>
</ul>
</blockquote>

<strong>Want even more power?</strong><br />
If you like WordPress PopUp, you’ll love WordPress PopUp Pro. Get everything in the free version plus:

<ul>
<li>Unlimited active popups</li>
<li>2 additional popup templates</li>
<li>Scroll location and CSS selector popup triggers</li>
<li>Extended reveal and hide animation set</li>
<li>Popup blocker expiration control</li>
<li>Special integration with Membership 2 and Pro Sites</li>
<li>Post type, category, and taxonomy condition triggers</li>
<li>Stop popups from triggering on mobile devices</li>
<li>Location-based triggers</li>
<li>Display popups based on user role</li>
<li>Bypass ad-blockers with URL masking</li>
<li>Reach an entire Multisite network</li>
<li>24/7/365 support from the best WordPress support team on the planet</li>
<li>100+ other premium plugins, services and themes included with a WPMU DEV membership</li>
</ul>

Upgrade to <a href="http://premium.wpmudev.org/project/the-pop-over-plugin/">WordPress PopUp Pro</a> and get access to greater design controls to help you reach your target market.

== Installation ==

WordPress Installation Instructions:

----------------------------------------------------------------------

1) Place the popover directory in the plugins directory
2) Activate the plugin


WPMU Installation Instructions:

----------------------------------------------------------------------

1) Place the popover directory in the plugins directory
2) Network Activate the plugin

For blog by blog, leave as is.

For network wide control - add the line define('PO_GLOBAL', true); to your wp-config.php file.

* You can find <a href='http://premium.wpmudev.org/manuals/installing-regular-plugins-on-wpmu/'>in-depth setup and usage instructions with screenshots here &raquo;</a>

== Screenshots ==

1. The PopUp Builder lets you design and preview popups as they are being made. 
2. Quickly add display conditions for targeted marketing.
3. Edit content, style, behaviors, and conditions using the builder.

== Changelog ==

= 4.8.0.0 =
* Improve the popup javascript to display popups.
* Update and clean up background code.
* Fix issue, that prevented users from deleting popups.
* Fix issue with extra "\" slashes in admin preview.
* Fix permissions of the Popup custom post type.
* Fix issues with twentysixteen theme.
* Many small bugfixes in the background.

= 4.7.1.1 =
* Fix compatibility issues caused by WordPress 4.3 changes
* Fix a PHP notice about invalid foreach value
* Fix bug that removed backslashs "\" from popup contents upon saving
* Remove debug output when saving a PopUp

= 4.7.1.0 =
* Fix incompatibility with ACF Pro plugin
* Fix issue that made rules inaccessible (not clickable in editor)
* Fix several PHP warnings and notices

= 4.7.0.9 =
We added a lot of PRO features to the free version:
* Free version can activate up to 3 PopUps at the same time!
* Free version can now use the custom CSS editor for PopUps!
* Free version now suppors PopUp Animations!
* Free version can use all Form Submit actions!
* Free version unlocked the Color options for PopUps!
* Small code improvements to avoid PHP notices

= 4.7.0.7 =
* Add a link target option for CTA Button (use _blank to open CTA in new tab)
* Add JS hook 'popup-submit-process' to allow manual updating of popup contents after form submit
* Simplify PopUp template structure to encourage creation of custom templates
* Fix XSS vulnerability (add_query_arg/remove_query_arg)
* Fix several small issues with different rules

= 4.7.0.5 =
* Fix incompatibility with Custom Sidebars plugin

= 4.7.0.3 =
* Fix several small bugs
* Fix URL rules that check for https:// protocol
* Improve "Full URL rule" to check all protocols

= 4.7.0.2 =
* Fix incompatibility with Custom Sidebars plugin

= 4.7.0.1 =
* Fix small JavaScript error

= 4.7.0 =
* Add PopUp Animations (PRO version)
* Add Behavior option: Form submit behavior (PRO version)
* Add new meta box: Custom CSS for individual PopUps (PRO version)
* Add validation of PopUp shortcodes for the current loading method
* Fix an issue where the PopUp closes when Gravity Forms is submitted
* Fix the on-URL rules in Ajax loading methods
* Fix the Ajax loading methods when Strict-Mime-Check is enabled

= 4.6.1.5 =
* Fix error on servers that run older php version than 5.3

= 4.6.1.4 =
* Better: Improved handling of forms inside PopUps
* Better: Ajax calls improved to prevent security errors by iThemes, etc.

= 4.6.1.3 =
* New: Allow page to be scrolled while PopUp is open.
* Fix: Prevent PopUps from staying open after submitting a form to external URL.
* Fix: PopUps without content can be displayed now.

= 4.6.1.2 =
* New: Two new WordPress filters allow custom positioning and styling of PopUps.
* Fix: Correctly display Meta-boxes of other plugins in the popup-editor.
* Fix: Plugins that use custom URL rewriting are working now (e.g. NextGen Gallery)
* Fix: PopUps can be edited even on servers with memcache/similar caching extensions.
* Fix: Resolve "Strict Standards" notes in PHP 5.4
* Fix: Rule "Not internal link" now works correctly when opening page directly.
* Fix: Rule "Specific Referrer" handles empty referrers correctly.
* Better: Forms inside PopUps will only refresh the PopUp and not reload the page.
* Better: Detection of theme compatibility for loading method "Page Footer" improved.

= 4.6.1.1 =
* New: Added Contextual Help to the PopUp editor to show supported shortcodes.
* Fix: Logic of rule "[Not] On specific URL" corrected.
* Fix: Close forever now works also via click on background layer.
* Better: Improved info on supported shortcodes.

= 4.6.1 =
* Fix: For some users the plugin was not loading after update to 4.6
* Fix: Old Popups will now replace shortcodes correctly.

= 4.6 =

* Completely re-build the UI from ground up!
* Migrated PopUps to a much more flexible data structure.
* Merged sections "Add-Ons" and "Settings" to a single page.
* Removed old legacy code; plugin is cleaner and faster.
* New feature: Preview PopUp inside the Editor!
* Three new, modern PopUp styles added.
* Featured Image support for new PopUp styles.

= 4.4.5.4 =

* Performance improvements
* Fixed issue with dynamic JavaScript loading
* Added PO_PLUGIN_DIR in config for changing plugin directory name

= 4.4.5.2 =

* Added missing translatable strings
* Updated language file

= 4.4.5.1 =

* added collation to tables creation code
* updated require calls to include directory path
* moved custom loading out of experimental status
* set default loading method to custom loading

= 4.4.5 =

* Added different custom loading method that should be cache resistant and remove issues with other ajax loading method.
* Made On URL rule more specific so that it doesn't match child pages when the main page is specified

= 4.4.4 =

* Added option to switch from JS loading to standard loading of pop ups.
* Added ability to use regular expressions in the referrers and on url conditions.
* Prepared code to make it easy to upgrade interface for future releases.

= 4.4.3 =

* Updated for WP 3.5
* Added initial attempt to distinguish referrers from Google search and referrers from Google custom site search.

= 4.4.2 =

* Removed unneeded css and js files
* Updated language file

= 4.4.1 =

* Moved popover loading js to be created by a php file due to needing extra processing.
* Fixed issue with directory based sites loading popover script from main site.
* Fixed issue of popover loading on login and register pages.

= 4.4 =

* Updated Popover to load via ajax call rather than page creation for cache plugin compatibility

= 4.3.2 =

* Major rewrite
* Multiple PopUps can be created
* Fixed issue of network activation not creating tables until admin area visited
* Updated code to remove all notifications, warnings and depreciated function calls ready for WP 3.4

= 3.1.4 =

* WP3.3 style updating

= 3.0 =

* Initial release
