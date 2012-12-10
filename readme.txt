=== YOURLS Link Creator ===
Contributors: norcross
Website Link: http://andrewnorcross.com/plugins/yourls-link-creator/
Donate link: https://andrewnorcross.com/donate
Tags: YOURLS, shortlink, custom URL
Requires at least: 3.0
Tested up to: 3.5
Stable tag: 1.04
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Creates a custom short URL when saving posts. Requires your own YOURLS install.

== Description ==

Creates a YOURLS generated shortlink on demand or when saving posts.

Features:

*   Optional custom keyword for link creation.
*   Will retrieve existing URL if one has already been created.
*   Click count appears on post menu
*   Available for standard posts and custom post types.
*   Optional filter for wp_shortlink
*   Built in cron job will fetch updated click counts every hour.

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload `yourls-link-creator` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to the "YOURLS Settings" option in the Settings Menu.
4. Enter your YOURLS custom URL and API key
5. Enjoy!

== Frequently Asked Questions ==


= What's this all about? =

This plugin creates a shortlink (stored in the post meta table) for each post that can be used in sharing buttons, etc.

= What is YOURLS? =

YOURLS is a self-hosted PHP based application that allows you to make your own custom shortlinks, similar to bit.ly and j.mp. [Learn more about it here](http://yourls.org/ "YOURLS download")

== Screenshots ==

1. Metabox to create YOURLS link with optional keyword field
2. Example of a post with a created link and click count
3. Post column displaying click count
4. Settings page



== Changelog ==

= 1.04 =
* refactoring the wp_shortlink functionality

= 1.03 =
* Bugfix for post type checking

= 1.02 =
* Option for adding to specific post types
* delay link creation until status is published
* internationalization support

= 1.01 =
* Added option to create link on post save
* code cleanup

= 1.0 =
* First release!


== Upgrade Notice ==

= 1.0 =
* First release!
