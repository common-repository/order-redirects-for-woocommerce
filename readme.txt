=== Order Redirects for WooCommerce ===
Contributors: wpsunshine
Tags: woocommerce, purchase, order, redirect, thank you, thankyou
Requires at least: 5.0
Tested up to: 6.6.2
Requires PHP: 5.6
Stable tag: 1.0.3
License: GPLv3 or later License
URI: http://www.gnu.org/licenses/gpl-3.0.html

Custom redirects after order for WooCommerce. Allows a global redirect URL for all orders or per product/variation redirect URLs with priority options.

== Description ==

Setup custom redirects after a user submits an order or makes a purchase on your WooCommerce powered store. Options allow you to setup a global redirect URL for all orders or setup redirects on a per product or variation basis. Priority settings allow you to determine which redirect rule takes priority when more than one redirect is found for an order.

This plugin was created as a custom project for a client because we needed to pass the Order ID as a URL parameter to a custom Thank You page which had a form on it and no other plugins seemed to easily support this. We also needed the ability to prioritize a redirect for a specific product over all others if it was in the order.

NEW! Template tags allow you to easily include additional order data in the redirect URL. Available template tags include:

{order_id}
{meta:anymetakeyhere}

Example redirect URL:
https://anysite.com/?order_id={order_id}&state={meta:_billing_state}

[Visit our website for more information](https://wpsunshine.com/plugins/order-redirects-for-woocommerce/?utm_source=wordpress.org&utm_medium=link&utm_campaign=woocommerce-order-redirects-readme)

== Installation ==

1. Upload and activate the plugin
2. Global Options: Go to WooCommerce > Settings > Advanced > Order Redirects
3. Product Options: Edit product > Advanced tab
4. Variation Options: Edit produt > Variations > Edit variation

== Screenshots ==

1. Global redirect settings
2. Product redirect settings
3. Variation redirect settings

== Changelog ==

= 1.0.3 =
* Declare support for HPOS

= 1.0.2 =
* Fix: Saving variation redirection settings

= 1.0.1 =
* Update compatibility notice for WP 6.1.1 and WC 7.1.1
* Adjust documentation link

= 1.0 =
* Update compatibility for WP 6.0
* Stable for 1.0 release!

= 0.8.1 =
* Update compatibility for WP 5.9

= 0.8 =
* Add - Allow any meta data to be included in the URL

= 0.7 =
* Fix - Proper Text Domain

= 0.6 =
* Add - Freemius SDK

= 0.5 =
* Fix - Updates to pass WordPress.org plugin guidelines
* Add - WordPress.org assets

= 0.4 =
* Add - Global default URL and option to enable/disable including the Order ID in the URL

= 0.3 =
* Add - Priority redirect system

= 0.2 =
* Add - Filter for final redirect URL

= 0.1 =
* Initial build
