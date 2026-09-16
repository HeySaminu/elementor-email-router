=== Conditional Email Router for Elementor ===
Contributors: saminu
Tags: elementor, forms, email, conditional logic, routing
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 2.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Route Elementor Pro Form Email and Email 2 actions using submitted field values and per-route email settings.

== Description ==

Conditional Email Router for Elementor adds conditional email variants directly to Elementor Pro Form widgets.

Use one form to send different messages or route notifications to different recipients according to a submitted field value. Routing is available separately for Elementor's Email and Email 2 actions, and the first enabled condition that matches is used.

Each conditional variant can override:

* To
* Subject
* Message
* From Email
* From Name
* Reply-To
* Cc
* Bcc
* Send As

Blank variant settings inherit the normal Email or Email 2 configuration. Elementor field shortcodes such as `[field id="email"]` and `[all-fields]` are supported.

Available conditions include equals, does not equal, contains, does not contain, starts with, ends with, is empty and is not empty. Matching is case-insensitive by default and can be made case-sensitive per variant.

= Requirements =

This plugin requires Elementor Pro because it extends the Elementor Pro Form widget. Elementor Pro is a commercial dependency and is not included with this plugin. The plugin displays an administrator notice and remains inactive when Elementor Pro is unavailable.

This is an independent add-on and is not affiliated with or endorsed by Elementor Ltd.

= Privacy =

This plugin does not create a public endpoint, contact an external service, collect analytics or send form data anywhere on its own. Elementor continues to handle form validation, shortcode replacement and email delivery through the WordPress mail system.

== Installation ==

1. Install and activate Elementor and Elementor Pro.
2. Upload the plugin directory to `/wp-content/plugins/`, or install the ZIP through Plugins > Add New > Upload Plugin.
3. Activate Conditional Email Router for Elementor.
4. Edit an Elementor Pro Form widget.
5. Add Email and/or Email 2 under Actions After Submit.
6. Open the relevant email action and enable Conditional Email Routing.
7. Add conditional email variants in priority order and update the page.

== Frequently Asked Questions ==

= Does this plugin work without Elementor Pro? =

No. Conditional routing is added to Elementor Pro's Form widget, which is not part of the free Elementor plugin.

= What happens when more than one condition matches? =

The first enabled matching variant is used. Arrange variants in priority order.

= What happens when no condition matches? =

Each email action can either send its normal Elementor email or skip that email action.

= Can a route inherit existing email settings? =

Yes. Leave a variant setting blank to inherit the corresponding Email or Email 2 setting.

= Does the plugin send form data to an external service? =

No. It modifies Elementor's email action settings in memory before Elementor sends the message through WordPress.

== Changelog ==

= 2.2.0 =
* Prepared the plugin for public distribution under a trademark-safe name.
* Added an Elementor Pro dependency notice.
* Removed organization-specific sender defaults so routes inherit Elementor settings.
* Added WordPress.org metadata, licensing and privacy documentation.
* Retained mail-header validation and first-match routing behaviour.

== Upgrade Notice ==

= 2.2.0 =
Public directory release preparation. Existing route values continue to inherit normal Elementor email settings when left blank.
