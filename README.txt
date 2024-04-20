=== Disabler ===
Contributors: saas
Tags: disable, options, features
Requires at least: 6.0
Tested up to: 6.5
Requires PHP: 8.0
Stable tag: 4.0.1
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Instead of installing a million plugins to disable features you don't want, why not use just ONE plugin?

== Description ==

I don't like certain things, such as curly "smart" quotes and self-pings. Instead of installing six or seven plugins to handle this, I decided to create one plugin to address the common issues. Disabler allows you to select and deactivate specific settings rather than disabling everything, all through a straightforward UI.

This plugin enables you to disable/remove the following features:

**Back-End**

* Self Ping
* Autosaving of Posts etc.

**Front-End**

* Texturization (including Smart/Curly quotes, EM dash, EN dash, and ellipsis)
* The automatic capitalization of the P in WordPress (WordPress 3.0+ only)
* The `<p>` that is automatically inserted
* Disable links to WordPress' internal 'shortlink' URLs for your posts. E.g., <link rel="shortlink" href="https://www.example.com/?p=1" />

**Performance**

* Disable emojis
* Prevent unauthorized embedding and remove JavaScript requests related to WordPress embeds
* Control heartbeat rate: Disable it completely, etc.
* Disable dashboard widgets

**Revisions**

* Disable revisions completely or selectively for each post type.

**Feeds**

WordPress outputs your content in various formats across different URLs, including feeds like RSS and Atom. It's advisable to disable unused formats for better control over your content distribution.

* Redirect feeds (if disabled): Redirects feeds to prevent access.
* Disable global feed: Removes URLs providing an overview of recent posts.
* Disable global comment feeds: Removes URLs providing an overview of recent comments.
* Disable post comments feeds: Removes URLs providing recent comments on each post.
* Disable post authors feeds: Removes URLs providing recent posts by specific authors.
* Disable post type feeds: Removes URLs providing recent posts for each post type.
* Disable category feeds: Removes URLs providing recent posts for each category.
* Disable tag feeds: Removes URLs providing recent posts for each tag.
* Disable custom taxonomy feeds: Removes URLs providing recent posts for each custom taxonomy.
* Disable search results feeds: Removes URLs providing search result information.
* Disable Atom / RDF feeds: Removes URLs providing alternative formats of the above.

**Rest API**

The REST API in WordPress provides powerful functionality for interacting with your site's data. However, there may be cases where you want to restrict or disable access to this API for security or privacy reasons.

* Disable REST API for visitors: Prevents access to the REST API endpoints for non-authenticated visitors.
* Disable REST API links: Removes the link tag in the HTML header that points to the REST API endpoint.
* Disable REST API RSD link: Removes the RSD (Really Simple Discovery) link tag that specifies the REST API endpoint.
* Disable REST API link in HTTP headers: Removes the HTTP header link that specifies the REST API endpoint.

**Privacy Settings**

* Outputting WordPress version in your blog headers
* Sending your blog URL to WordPress when checking for updates on core/theme/plugins

**XML-RPC**

* XML-RPC Control: Choose between completely disabling XML-RPC or selectively enabling it.
* Whitelist IP Addresses: Option to whitelist additional IP addresses.
* XML RPC Methods: Control which XML-RPC methods are allowed.
* XML-RPC HTTP Headers: Ability to add or remove custom XML-RPC HTTP headers.
* Remove RSD, WLW Manifest, Pingback Links: Option to remove these XML-RPC-related links from the website.

**Updates**

* Plugin Updates: Users can choose between manual or automatic updates, or disable updates entirely.
* Theme Updates: Similar options as plugin updates.
* Auto and Async Translations Updates: Options to enable auto updates, disable them, or keep the default setting.
* WordPress Core Updates: Various options to manage updates, including disabling updates, allowing minor or major auto updates, or enabling updates for development installations.
* Enable updates for VCS Installations: Users can choose to enable or disable updates for installations under version control systems.
* Updates nags only for Admin: Hides WordPress core update notices for users without update capabilities.

**Usage Tracking**

* This setting enables anonymous usage data collection for the plugin, including WordPress information, installed plugins/themes, and server details.

All options default to off and are cleaned up upon uninstall.

* [Plugin Site](https://wordpress.org/plugins/disabler/)

==Changelog==

= 4.0.1 =
* Added current_user_can check to PluginInstall::install_actions

= 4.0.0 =
* Revamped the code, now utilizes Composer autoloading and PHP Namespaces
* Now compatible with WordPress v6.0+ and dropping pre WordPress v6.0 support
* Added disable embeds control
* Added disable heartbeat control(s)
* Added disable dashboard widget(s) control
* Enhanced disable revisions control(s)
* Enhanced disable feeds control(s)
* Added disable rest api control(s)
* Enhanced disable xml-rpc control(s)
* Added disable updates (core, theme, plugin and translation) control(s)

= 3.0.3 =
* Fixed various issues
* Now compatible with WordPress v4.9+ and dropping pre WordPress v4.9 support
* Opt in option, for collection usage data

= 3.0.2 =
* Some refactoring and fixes for Coding Standards

= 3.0.1 =
* Some refactoring and tweaks done

= 3.0.0 =
* Plugin gets a new lease on life notice
* Admin page redesigned with the [Settings API](http://codex.wordpress.org/Settings_API)

= 2.3.1 =
* 29 September, 2012 by Ipstenu
* You'd like to save options, no?

= 2.3 =
* 28 September, 2012 by Ipstenu
* XML-RPC

= 2.2 =
* 24 April, 2012 by Ipstenu
* i18n - Probably still doing it wrong.
* Un/installing the <em>right</em> way.

= 2.1 =
* 17 April, 2012 by Ipstenu
* Readme cleanup, fixing URLs etc.

= 2.0 =
* 03 October, 2011 by Ipstenu
* Dropping pre 3.0 support
* Dropping disabling Admin Bar (since as of 3.3 you really need it)

= 1.3 =
* 17 July, 2011 by Ipstenu
* Added in removal of 'smart quotes' from RSS as well (thanks Ben Smith for the point that I missed that).

= 1.2 =
* 2 December, 2010 by Ipstenu
* Fixed no-RSS option so it would actually uncheck!

= 1.0 =
* 24 November, 2010 by Ipstenu
* Making it copacetic for 3.1!
* Re-renamed Typography back to Front End
* Added in removal of Admin Bar for 3.1 (thanks Ozh!)

= 0.4 =
* 14 July, 2010 by Ipstenu
* Typo! Post revisions wouldn't stay checked.  Thanks to utdemir for the catch!

= 0.3 =
* 13 July, 2010  by Ipstenu
* Renamed Front End to Typography

= 0.2 =
* 9 July, 2010 by Ipstenu
* Added in privacy and backend settings.

= 0.1 =
* 8 July, 2010 by Ipstenu
* Initial version.

== Installation ==

No special instructions needed.

== Frequently Asked Questions ==

= Will this work on older versions of WordPress? =

Its compatible with v6.0+. You should upgrade it, if you're not at least on the latest main version (which right now is v6.5.x)

= Will this work on MultiSite? =
In my rough testing, yes.  It even works network wide AND in the mu-plugins, though personally, I'd let people decide if they want it or not for their blogs. Activate it network wide, let people decide how they want to impliment it.

Remember, EVEN if you activate this site wide, the DEFAULT per blog is for the settings to be OFF. So if you want them on for each site without the option to change, then this is NOT the plugin for you. Sorry.

= Why doesn't this disable the admin bar any more? =
Because as of WordPress 3.3 you really need this around to get stuff done.

= Can you add in Feature X? =
Probably. Tell me what you want to add and I'll see if I can do it.

= It's not working!  What do I do? =
Start with turning off your other plugins. There are a few that mess with formatting.

* "Shortcodes Ultimate" -- You will need to turn on the option in that plugin to "Disable custom formatting - Enable this option if you have some problems with other plugins or content formatting."  From what I can tell, it's overriding the default customs with it's own.

== Screenshots ==
1. Back-end settings
2. Front-end settings
3. Performance settings
4. Revisions settings
5. Feeds settings
6. Rest API settings
7. Privacy settings
8. XML-RPC settings
9. Updates settings
10. Usage Tracking setting

== Upgrade Notice ==

= 2.3.1 =
Version 2.3 doesn't like to save settings. You may want to upgrade to fix that.
