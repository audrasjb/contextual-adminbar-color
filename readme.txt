=== Contextual Adminbar Color ===
Contributors: whodunitagency, audrasjb
Tags: environment, adminbar, color, scheme, staging, production, preprod
Requires at least: 5.3
Tested up to: 5.3
Stable tag: 0.1
Requires PHP: 5.6
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Use custom admin bar colors to differentiate environments (staging, preprod, production)

== Description ==

This plugins provides custom admin bar colors to differentiate environments (staging, prepared, production). It's really **easy to use** and **developer-friendly**.

The plugin provides a settings screen to choose between several color schemes.

It also offers few PHP constants you can use in your `wp-config.php` file:

* `CONTEXTUAL_ADMINBAR_COLOR` to force color scheme.
* `CONTEXTUAL_ADMINBAR_MESSAGE` to force your custom admin bar message to display.
* `CONTEXTUAL_ADMINBAR_SETTINGS` to remove the settings screen from WordPress Admin, and manage the plugin’s settings directly within the `wp-config.php` file.

Since WordPress Core is probably going to deprecate alternate admin color schemes in mid-term, this plugin is meant to be use by those who rely on colors to know is they are in staging, preproduction or production environment.

Last but not least, all provided color schemes are accessibility-ready!

== Screenshots ==
1. Screenshot
2. Screenshot

== Installation ==

1. XX

== Frequently Asked Questions ==

= How to use the PHP constant in wp-config.php?

* `CONTEXTUAL_ADMINBAR_COLOR` accepts …TODO.
* `CONTEXTUAL_ADMINBAR_MESSAGE` accepts … TODO.
* `CONTEXTUAL_ADMINBAR_SETTINGS` accepts… TODO. 

== Changelog ==

= 0.1 =
* Plugin initial commit. Works fine :)