=== Coreline ===
Contributors: kishanjasani
Tags: security, hardening, performance, login, optimization
Requires at least: 5.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 0.1.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Essential hardening and cleanup for every WordPress site.

== Description ==

Coreline is a lightweight WordPress security and optimization plugin that provides essential hardening features for your WordPress installation. It follows clean code principles, PSR standards, and is designed to be testable, scalable, and maintainable.

= Features =

* **Emoji Script Removal** - Removes WordPress emoji detection scripts to improve performance
* **WordPress Version Hiding** - Removes WordPress version numbers from HTML and RSS feeds for better security
* **Custom Login URL** - Changes wp-login.php to a custom URL (e.g., `/secure-login/`) to prevent automated brute-force attacks
* **Hotlink Protection** - Prevents other websites from hotlinking your images (works on both Apache and Nginx)
* **Disable Pingbacks & Trackbacks** - Disables XML-RPC pingbacks and trackbacks for improved security

= Benefits =

* Lightweight and fast
* No database queries overhead
* Clean, PSR-compliant code
* Fully tested and maintained
* Compatible with all major themes and plugins

= Developer Friendly =

Coreline is built with developers in mind:

* PSR-4 autoloading
* WordPress Coding Standards compliant
* PHPStan Level 8 verified
* Fully filterable and extensible
* Comprehensive inline documentation

== Installation ==

1. Upload the `coreline` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > Coreline to configure your options

== Frequently Asked Questions ==

= Does this plugin slow down my site? =

No, Coreline is designed to be lightweight and actually improves performance by removing unnecessary WordPress features like emoji scripts.

= What happens if I forget my custom login URL? =

You can deactivate the plugin via FTP or access your database to retrieve the custom slug from the options table. We recommend bookmarking your custom login URL.

= Is this plugin compatible with caching plugins? =

Yes, Coreline is fully compatible with all major caching plugins including WP Rocket, W3 Total Cache, and WP Super Cache.

= Does it work with multisite? =

Yes, Coreline is compatible with WordPress multisite installations.

= Can I use this on a production site? =

Absolutely! Coreline follows WordPress VIP Go coding standards and is production-ready.

== Screenshots ==


== Changelog ==

= 0.1.2 =
* Fix deployment workflow and vendor folder inclusion
* Update GitHub Actions for proper releases

= 0.1.1 =
* Improve deployment process

= 0.1.0 =
* Initial release
* Emoji script removal
* WordPress version hiding
* Custom login URL
* Hotlink protection
* Pingback/Trackback disabling

== Support ==

For bug reports and feature requests, please visit:
https://github.com/kishanjasani/coreline/issues

== License ==

This plugin is licensed under the GPLv2 or later.
https://www.gnu.org/licenses/gpl-2.0.html
