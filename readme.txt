=== Disabler ===
Tags: disable, options, features
Contributors: Ipstenu, jesin
Requires at least: 3.1
Tested up to: 3.8
Donate link: https://www.wepay.com/donations/halfelf-wp
Stable tag: 3.0.0

Instead of installing a million plugins to turn off features you don't want, why not use ONE plugin?

== Description ==

<strong>Attention: This plugin is not actively being worked on. It should still work, and I'm fiddling with a total re-write to address some doing_it_not_right() bits, but don't expect a whole lot right now. If you're interested in taking it over, let me know.</strong>

I don't like certain things, like curly 'smart' quotes and self pings.  Instead of installing six or seven plugins to do this, I thought I'd make one plugin to cover the usual suspects.  Instead of just disabling everything, Disabler lets you pick and choose what settings you want turned off, in a dead simple UI.

This plugin will let you disable/remove the following features:

**Front End Settings**

* Texturization (including Smart/Curly quotes, EM dash, EN dash, and ellipsis)
* The automatic capitalization of the P in WordPress (WordPress 3.0+ only)
* The &lt;p&gt; that is automatically inserted

**Back End Settings**

* Self Ping
* Autosaving of posts
* Post Revisions
* RSS Feeds
* XML-RPC

**Privacy Settings**

* Outputting WordPress version in your blog headers
* Sending your blog URL to WordPress when checking for updates on core/theme/plugins

All options default to off, and get cleaned up on uninstall.

* [Donate](https://www.wepay.com/donations/halfelf-wp)
* [Plugin Site](http://halfelf.org/disabler/)

==Changelog==
A giant thank you to everyone at http://wptavern.com for suggestions about what to add to this plugin.

=2.3.1=
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

I tried to make it backwards compatible (that is, you can't disable the capital P in WordPress checker if you're NOT on WordPress 3.0 or higher), but I didn't bother coding anything for pre-2.8 WordPress.  You should upgrade it you're not at least on the latest main version (which right now is 3.2.x)

= Will this work on MultiSite? =
In my rough testing, yes.  It even works network wide AND in the mu-plugins, though personally, I'd let people decide if they want it or not for their blogs. Activate it network wide, let people decide how they want to impliment it.

Remember, EVEN if you activate this site wide, the DEFAULT per blog is for the settings to be OFF. So if you want them on for each site without the option to change, then this is NOT the plugin for you. Sorry.

= Why doesn't this disable the admin bar any more? =
Because as of WordPress 3.3 you really need this around to get stuff done.

= Can you add in Feature X? =
Probably. Tell me what you want to add and I'll see if I can do it.

= It's not working!  What do I do? =
Start with turning off your other plugins.  There are a few that mess with formatting.

* "Shortcodes Ultimate" -- You will need to turn on the option in that plugin to "Disable custom formatting - Enable this option if you have some problems with other plugins or content formatting."  From what I can tell, it's overriding the default customs with it's own.

== Screenshots ==
1. Standard View
2. Options checked

== Upgrade Notice ==

= 2.3.1 =
Version 2.3 doesn't like to save settings. You may want to upgrade to fix that.
