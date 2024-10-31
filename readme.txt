=== Server Info WP ===
Contributors: tylerthedude
Tags: server information, server monitor, server monitoring, server info, server usage, server stats, server statistics
Tested up to: 5.4
Requires PHP: 5.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Easily monitor your server by watching your server usage and resources with three widgets.

== Description ==
Take the hassle out of server administration with Server Info WP. This simple plugin will add three dashboard widgets to easily allow you to monitor your server. These widgets will display important information about your server such as CPU usage, PHP memory usage, and other necessities needed for properly running your server.

== Changelog ==
1.0 - 2020-03-07 - Tyler Gilbert
    Initial release of Server Info WP
1.1 - 2020-03-09 - Tyler Gilbert
    Feature: Internationalization support.
    Enhancement: Update php_uname() method to use php_uname( 's' ) for server's operating system.
    Enhancement: Add CSS styles to table.layout for allowing full server path to be displayed.
    Bug fix: Update README file to fix typo.
    Bug fix: Remove server.png (not in use)
1.2 - 2020-03-12 - Tyler Gilbert
    Feature: Display server information in real time with AJAX!
    Feature: Add server's outgoing IP address.
    Feature: Add server port.
    Feature: Add PHP error log path.
    Feature: Add PHP max upload size.
    Feature: Add PHP max post size.
    Feature: Add functionality to determine whether or not the WordPress site is multisite.
    Feature: Add number of total WordPress users.
    Feature: Add profile icon to WordPress repo.
    Enhancement: Utilize get_current_screen to only load code when on main WordPress dashboard.
    Enhancement: Load get_usage() method with 'load-index.php' hook so the strings can be properly translated.
    Ehancement: Use sprintf() for variable data so the string can be properly translated.
    Enhancement: Update main.css to follow WP standards.
    Enhancement: Add js/main.js.
    Enhancement: Add index.php for security reasons.
    Bug fix: Remove unused plugin_url property.
2.0 - 2020-03-12 - Tyler Gilbert
    Changing version number and removing unused assets.
2.1 - 2020-04-16 - Tyler Gilbert
    Feature: Add server location.
    Feature: Add PHP architecture.
    Feature: Add number of published WordPress pages.