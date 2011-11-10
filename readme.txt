=== Plugin Name ===
Contributors: Netlife
Donate link: http://netlife.com.au/2009/06/28/wordle-your-wordpress-flash-based-tag-cloud-plugin/
Tags: tag cloud, flash, plugin, fatcloud, fat cloud, netlife, wordle, word cloud, wordcloud
Requires at least: 2.7.1
Tested up to: 2.8
Stable tag: 0.6

Netlife's Flash Tag Cloud plugin for WordPress - FatCloud! Wordle your WordPress. Beautiful tag clouds that are accessible & SEO friendly.

== Description ==
A flash-based tag cloud plugin for your WordPress blog. FatCloud, by [Netlife](http://www.netlife.com.au "Web Design Blog"), comes with 2 built in themes: 'Simple Skin' and the popular 'Wordle' theme. 
Choose text angle, colour, size ratio and much more. Beautiful tag clouds that are accessible & SEO friendly.

The `wp_fat_cloud()` function is an extension of the built-in WordPress wp\_tag\_cloud() function.
It will accept all the same arguments, and generates almost identical mark-up, with some
added JavaScript to render a SWF on top.

FatCloud requires PHP 5.2+, JavaScript and the Adobe Flash Player 10+ browser plugin.

== Installation ==
1. Upload the entire 'netlifes-tag-cloud-fatcloud' directory to your wp-content/plugins directory.
2. Enable the plugin in the WordPress Plugins menu
3. Configure FatCloud using the WordPress > Settings > FatCloud page.
4. Add the <?php wp_tag_cloud() ?> tag somewhere in your template.

== Frequently Asked Questions ==

= What are the system requirements for using FatCloud? =
FatCloud has been successfully tested with Wordpress 2.7.1 on PHP5.2+. We have not tested it on 5.1.x. There is no support for PHP4 (sorry!). If you have problems on these (or other) configurations, feel free to [submit a comment](http://netlife.com.au/2009/06/28/wordle-your-wordpress-flash-based-tag-cloud-plugin/#comments) on Netlife.

= Why do I get an error after activating version 0.1? =
Our initial release did not allow for a different plugin folder name. If you downloaded version 0.1, parse errors are expected after activation i.e. 'parse error, unexpected =,'. To resolve this temporary issue, FTP into your website and rename the plugin directory from 
'wp-content/plugins/netlifes-tag-cloud-fatcloud' to 'wp-content/plugins/wp\_fat\_cloud'. We have since pushed up revision 0.2 to resolve this issue.

= Why does my browser freeze after I add new tags? =
FatCloud is doing some pretty hard-core rendering back there. Once it renders, it caches
the render data on the server, but it needs to re-render whenever its data changes. A
45 second render time is pretty normal. It will happen only once after a new tag is added to wordpress.

== Screenshots ==
1. An example of a FatCloud tag cloud using the 'Wordle' theme 
2. General settings available
3. Settings available for the built-in 'Wordle' theme

== Changelog ==
= 0.1 =
* Initial release. Hooray!

= 0.2 =
* Changed plug-in directory name to be consistent with SVN path
* Added PHP 5.2+ to system requirements

= 0.3 =
* Some users reported a MySQL invalid resource warning when installing FatCloud. The code in question has been re-written with extra care, so future installations should be error-free.

= 0.4 =
* Added support for user defined wordpress table prefix's. We had previously assumed the options table would always be called wp_options.

= 0.5 =
* Fixed a javascript bug in Internet Explorer browsers that was causing FatCloud tag cloud to appear blank

= 0.6 =
* Plugin now includes a widget, thanks to Roel S.F. Abspoel
