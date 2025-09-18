=== WooValidate Email Verification ===
Contributors: woovalidate
Tags: woocommerce, email, validation, checkout
Requires at least: 5.9
Tested up to: 6.5
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Enforce high quality customer email addresses during WooCommerce checkout.

== Description ==

WooValidate prevents orders from using invalid, disposable, and high-risk email addresses. The plugin performs:

* Syntax validation to block malformed addresses.
* Domain verification using DNS lookups to ensure the address can receive mail.
* Disposable domain detection to prevent throwaway inboxes.
* Reputation screening against a curated list of risky addresses.

When a problem is detected, customers see a clear message during checkout and account creation along with suggested corrections for common typos. This ensures orders are placed using a valid inbox before payment is processed.

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/woo-validate` or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Make sure WooCommerce is active. WooValidate will immediately begin validating checkout and registration email fields.

== Frequently Asked Questions ==

= Does this plugin connect to external services? =

No. WooValidate performs validation locally using PHP and DNS lookups. Disposable and risky email lists can be customized by developers via filters.

= Can I customize the lists of risky or disposable addresses? =

Yes. Developers can hook into the `woo_validate_disposable_domains` and `woo_validate_risky_addresses` filters to change the behavior.

== Changelog ==

= 1.0.0 =
* Initial release.
