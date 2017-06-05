=== HTTP Headers ===
Contributors: zinoui
Donate link: https://zinoui.com/donation
Tags: custom headers, http headers, headers, http, http header, header, cross domain, cors, xss, clickjacking, mitm, cross origin, cross site, privacy, p3p, hsts, referrer, csp
Requires at least: 3.2
Tested up to: 4.7.5
Stable tag: 1.3.0
License: GPLv2 or later

HTTP Headers adds CORS & security HTTP headers to your website.

== Description ==

HTTP Headers gives your control over the http headers returned by your blog or website.

Headers supported by HTTP Headers includes:

* X-Frame-Options
* X-XSS-Protection
* X-Content-Type-Options
* X-UA-Compatible
* Strict-Transport-Security
* Public-Key-Pins
* Access-Control-Allow-Origin
* Access-Control-Allow-Credentials
* Access-Control-Max-Age
* Access-Control-Allow-Methods
* Access-Control-Allow-Headers
* Access-Control-Expose-Headers
* P3P
* Referrer-Policy
* Content-Security-Policy

The [getting started tutorial](https://zinoui.com/blog/http-headers-for-wordpress) describes a typical configuration of this plugin.

== Installation ==

Upload the HTTP Headers plugin to your blog. Then activate it.

That's all.

== Frequently Asked Questions ==

= Why to use this plugin? =

Nowadays security of your social data at the web is essential. This plugin helps you to improve your website overall security. 

= Who use these headers? =

These HTTP headers are being used in production services by popular websites as Facebook, Google+, Twitter, LinkedIn, YouTube, Yahoo, Amazon, Instagram, Pinterest. 

== Screenshots ==

1. This screenshot shows up the dashboard where you can see a brief preview of headers current values.
2. This screenshot shows up the settings page where you can adjust the security headers.
3. This screenshot shows up the response headers returned by the web server.

== Upgrade Notice ==

Updates are on they way, so stay tuned at [@DimitarIvanov](https://twitter.com/DimitarIvanov)

== Changelog ==

= 1.3.0 =
*Release Date - 3rd June, 2017*

* Added support of Content-Security-Policy header
* Added dashboard

= 1.2.0 =
*Release Date - 28th April, 2017*

* Added support of Referrer-Policy header

= 1.1.2 =
*Release Date - 13th February, 2017*

* Added support of 'preload' directive to HSTS header

= 1.1.1 =
*Release Date - 8th November, 2016*

* Fixed typo in the X-Frame-Options header

= 1.1.0 =
*Release Date - 20th May, 2016*

* Added support of P3P header

= 1.0.0 =
*Release Date - 10th May, 2016*

* Initial version
