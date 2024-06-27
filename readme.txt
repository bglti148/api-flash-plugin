=== Generate Screenshot ===
Contributors: Ellis LaMay
Tags: screenshot, featured image, apiflash
Requires at least: 5.0
Tested up to: 5.8
Requires PHP: 7.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Generate screenshots for posts using the apiflash.com API and set them as featured images.

== Description ==

This plugin adds a "Generate Screenshot" option to specified post types. It uses the apiflash.com API to capture screenshots of posts and set them as the featured image.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/wp-generate-screenshot` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Define the post types in `class-generate-screenshot.php` where you want the "Generate Screenshot" functionality to be applied.

== File Structure ==
wp-generate-screenshot/
├── includes/
│   ├── admin/
│   │   ├── class-admin-settings.php
│   │   ├── class-api-key-tab.php
│   │   └── class-post-types-tab.php
│   ├── class-generate-screenshot.php
│   └── class-api-handler.php
└── wp-generate-screenshot.php

== Changelog ==

== 1.2.0 ==
* Added settings options to be able to select which post types the plugin hooks into.

= 1.1.0 =
* Removing API key from code and storing in database for better security and so users can save their own API keys.

= 1.0.0 =
* Initial release.
