=== Contextual Adminbar Color ===
Contributors: whodunitagency, audrasjb
Tags: environment, adminbar, color, scheme, staging, production, preprod, environments
Requires at least: 5.3
Tested up to: 5.3
Stable tag: 0.2
Requires PHP: 5.6
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Use custom admin bar colors and favicons to differentiate your environments (staging/prod)

== Description ==

This plugins provides custom admin bar colors to differentiate environments (staging, preprod, production). It's really **easy to use** and **developer-friendly**.

The plugin provides a settings screen which several options:

* choose between several color predefined schemes.
* use a favicon to better differentiate your environments in your browser’s tabs.
* add a custom message in your admin bar, like "Production website", "staging version" or whatever your want.
* choose the user roles that will see the color scheme (other roles will see the default admin bar).

It also offers few PHP constants you can use in your `wp-config.php` file (see Frequently Asked Questions below).

Since WordPress Core will probably deprecate alternate admin color schemes in mid-term, this plugin is meant to be use by those who rely on colors to know is they are in staging, preproduction or production environment.

Last but not least, all provided color schemes are accessibility-ready!

== Screenshots ==
1. Plugin settings screen.
2. Use favicons to differentiate your browser tabs.
3. Front-end rendering.

== Installation ==

1. Activate the plugin.
2. Good to Tools > Adminbar settings to configure the plugin.
3. Save your changes and enjoy :)

== Frequently Asked Questions ==

= How to use the PHP constant in wp-config.php?

Use `CONTEXTUAL_ADMINBAR_COLOR` to force color scheme.
Accepted values: `blue`, `red`, `green`, `purple`, `orange` and `darkgray`.
Example: `define( 'CONTEXTUAL_ADMINBAR_COLOR', 'purple' );`

Use `CONTEXTUAL_ADMINBAR_MESSAGE` to force your custom admin bar message to display.
Accepted values: any valid string.
Example: `define( 'CONTEXTUAL_ADMINBAR_MESSAGE', 'This is the staging website' );`

* `CONTEXTUAL_ADMINBAR_FAVICON` to force a favicon.
Accepted values: `0` (don’t force a color based favicon) or `1` (force WordPress Admin to use the favicon that is related to your selected color scheme).
Example: `define( 'CONTEXTUAL_ADMINBAR_FAVICON', 1 );`

* `CONTEXTUAL_ADMINBAR_SETTINGS` to remove the settings screen from WordPress Admin, and manage the plugin’s settings only within the `wp-config.php` file.
Accepted values: `0` (remove the settings screen) or `1` (keep it).
Example: `define( 'CONTEXTUAL_ADMINBAR_SETTINGS', 0 )`

== Changelog ==

= 0.2 =
* Plugin initial version, now on WordPress.org!

= 0.1 =
* Plugin initial version, only released on GitHub.