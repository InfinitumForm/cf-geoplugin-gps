=== Geo Controller GPS extension  ===
Contributors: ivijanstefan, creativform
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=creativform@gmail.com
Tags: gps, geolocation, cf-geoplugin, geo-controller, geocoding, google-maps, woocommerce, store-locator, seo, geomarketing, geo plugin, geotargeting, geofencing
Requires at least: 4.5
Tested up to: 6.0
Requires PHP: 7.0.0
Stable tag: 2.0.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Enable GPS lookup for the Geo Controller plugin and collect geodata from the mobile visitors. 

== Description ==

This plugin allows all **[Geo Controller](https://wordpress.org/plugins/cf-geoplugin/)** users to locate their visitors using a GPS location. Using this plugin you solve the biggest problem of locating mobile visitors and correcting their location errors.

= How to use =

1. You need to have installed **[Geo Controller](https://wordpress.org/plugins/cf-geoplugin/)** and legally activated Personal, Freelance or Business license.
2. You need to have activated Google Map functionality and you must place valid [Google Map API Key](https://developers.google.com/maps/documentation/javascript/get-api-key) with enabled [Geocoding API](https://developers.google.com/maps/documentation/javascript/geocoding).
3. Then you can install this plugin and activate it.

This plugin works automatically and there are no additional settings.

Easy, isn't it?

Plugin in the background using GoogleGeocoding API services and Geo Controller API that allow you to have the closest GPS information. Therefore, you must follow the instructions above.

== Installation ==

You only need to install the  **Geo Controller GPS extension** through the WordPress plugins screen directly or download ZIP file, unpack and upload plugin folder to the `/wp-content/plugins/` directory.

Afterwards, activate the plugin through the 'Plugins' screen in WordPress, go to your `http://siteexample.com/wp-admin/` area and use the `Geo Controller` to see all available shortcodes and settings.

= On uninstall =

You must know when you uninstall Geo Controller GPS extension you will lose complete setup, license and any other changes you have inside your WordPress installation. On uninstall we clean everything from your database that our plugin generates and you can not return that information back.

== Frequently Asked Questions ==

= Is this plugin free? =

Yes. But you can only use it with a properly licensed version of the Geo Controller. 

= Why must I use Google Map functionality? =

Plugin in the background using Google services to provide proper GPS information.

= Why does the plugin not work on my site? =

There is few reasons:

1. You do not have valid or proper Geo Controller license
2. Your Google Map API Key is not valid or setup properly
3. Your Google Account restricts your domain or is not white listed
4. You have site on the localhost (local computer)
5. Your visitor not have GPS or disable location tracking on the mobile device

= Geo Controller GPS extension slows down my site? =

NO, Geo Controller GPS extension uses the asynchronous data reading from API services.

= Why I can't use GPS on the monthly license or limited lookup? =

It is very complicated to get GPS information and this data is sensitive. We must provide secure and unrestricted access to this information and therefore only annual licenses may use this information.

= Does this plugin do visitors tracking? =

No! This plugin cannot be used to track visitors. Plugin only returns some basic geo information and saves to session on the 5 minutes. 

== Changelog ==

= 2.0.5 =
* Added support for Geo Controller version 8.3.0

= 2.0.4 =
* Added support for WOrdPress version 6.0
* Fixed bugs from previous version
* Optimized PHP code

= 2.0.3 =
* Added support for Geo Controller version 8.2.5
* Added support for WordPress version 6.0

= 2.0.2 =
* Fixed GPS cache
* Fixed objects
* Improved file includes
* Optimized codes
* Improved security

= 2.0.1 =
* Fixed GPS modules
* Fixed JavaScript objects
* Fixed GPS redirections
* Added new shortcodes and objects for the city code, street name and street number

= 2.0.0 =
* Adapted algorithm for new version of Geo Controller 8.0.0 and above

= 1.1.0 =
* Fixed redirection problem
* Improved session control
* Improved GPS localization

= 1.0.10 =
* Fixed issue with `session_write_close()`

= 1.0.9 =
* IMPORTANT UPDATE: Fixed page redirection
* IMPORTANT UPDATE: Fixed API data merging
* IMPORTANT UPDATE: Fixed missing data

= 1.0.8 =
* IMPORTANT UPDATE: Geocode API update
* IMPORTANT UPDATE: Fixed constant refreshing

= 1.0.7 =
* IMPORTANT UPDATE: Multisite support
* IMPORTANT UPDATE: Translation support
* Enhancement: Error logging
* Enhancement: Geo Controller tags

= 1.0.6 =
* Fixed WP_ADMIN_DIR absolute path
* Improved main plugin connection

= 1.0.5 =
* Fixed Geocode API error
* Fixed Geo Controller absolute path
* Fixed JavaScript console log notifications

= 1.0.4 =
* Added WordPress 5.3 support

= 1.0.3 =
* Improved sanitization

= 1.0.4 =
* Added WordPress 5.3 support

= 1.0.3 =
* Improved sanitization

= 1.0.2 =
* Fixed session problem

= 1.0.1 =
* Fixed parent plugin absolute path

= 1.0.0 =
* Adding GPS support for the Geo Controller

== Upgrade Notice ==

= 2.0.5 =
* Added support for Geo Controller version 8.3.0

== Other Notes ==

= Plugin Links =

* [Geo Controller Website](https://cfgeoplugin.com/)
* [Documentation](https://cfgeoplugin.com/documentation/)
* [F.A.Q](https://cfgeoplugin.com/faq/)
* [Blog](https://cfgeoplugin.com/blog/)
* [Contact or Support](https://cfgeoplugin.com/contact/)
* [Terms and Conditions](https://cfgeoplugin.com/terms-and-conditions)
* [Privacy Policy](https://cfgeoplugin.com/privacy-policy)

= DONATION =

Enjoy using *Geo Controller*? Please consider [making a small donation](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=creativform@gmail.com) to support the project's continued development.

= TERMS AND CONDITIONS =

BY INSTALLING THIS PLUGIN WE CONSIDER THAT YOU ARE AUTOMATICALLY ACCEPT TERMS AND CONDITIONS OF OUR SERVICES AND AGREE WITH THE PRIVACY POLICY.

Please read these Terms and Conditions ("Terms", "Terms and Conditions") carefully before using the [www.cfgeoplugin.com](https://cfgeoplugin.com) website and the Geo Controller WordPress application (the "Service") operated by Geo Controller.

[Read about Terms and Conditions](https://cfgeoplugin.com/terms-and-conditions)

= PRIVACY POLICY =
We respect your privacy and take protecting it seriously. This Privacy Policy covers our collection, use and disclosure of information we collect through our website and service, [www.cfgeoplugin.com](https://cfgeoplugin.com) owned and operated by Geo Controller. It also describes the choices available to you regarding our use of your personal information and how you can access and update this information. The use of information collected through our service shall be limited to the purpose of providing the service for which our Clients have engaged us. Also we respect and take care about Europe General Data Protection Regulation (GDPR) and your freedom and private choices.

[Read about Privacy Policy](https://cfgeoplugin.com/privacy-policy)

For further questions and clarifications, do not hesitate to contact us and we will reply back to you within 48 hours.