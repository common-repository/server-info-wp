<?php

/*
Plugin Name: Server Info WP
Description: Monitor your server from the WordPress Dashboard.
Author: Tyler Gilbert
Author URI: https://tcgilbert.com/
Version: 2.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: server-info-wp

Server Usage is free software you can redistribute
it and/or modify it under the terms of the GNU General Public License
as published by the Free Software Foundation, either version 2 of the
License, or any later version.
Server Usage is distributed in the hope that it
will be useful, but WITHOUT ANY WARRANTY; without even the implied
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See
the GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with Server Usage. If not, see
https://www.gnu.org/licenses/gpl-2.0.html.
*/

if ( is_admin() ) {
    class Server_Info_WP {
        private $memory = array();
        private $is_windows = false;
        private $os;
        private $processes;
        private $windows_cpu_usage;
        private $cpu_usage;
        private $server_hostname;
        private $uptime;
        private $web_server;
        private $php_version;
        private $php_memory_limit;
        private $php_error_log_path;
        private $php_max_execution_time;
        private $php_max_upload;
        private $php_max_post;
        private $php_architecture;
        private $server_ip;
        private $server_port;
        private $outgoing_ip;
        private $server_path;
        private $server_location;
        private $wp_version;
        private $wp_multisite;
        private $wp_active_plugins;
        private $wp_debug_enabled;
        private $wp_users;
        private $wp_posts;

        public function __construct() {
            add_action( 'load-index.php', array( $this, 'init' ) );
            add_action( 'wp_dashboard_setup', array( $this, 'dashboard_widgets' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'load_styles_scripts' ) );
        }

        public function load_styles_scripts() {
            if ( function_exists( 'get_current_screen' ) && "dashboard" == get_current_screen()->id ) {

                wp_enqueue_style( 'main', plugins_url( 'stylesheets/main.css', __FILE__ ) );

                wp_register_script(
                    'main',
                    plugins_url( 'js/main.js', __FILE__ ),
                    array( 'jquery' )
                );

                wp_enqueue_script( 'main' );
            }
        }

        public function dashboard_widgets() {
            wp_add_dashboard_widget(
                'server_info_server',
                __( 'Server Info WP: General', 'server-info-wp' ),
                array( $this, 'display_server' )
            );

            wp_add_dashboard_widget(
                'server_info_php',
                __( 'Server Info WP: PHP', 'server-info-wp' ),
                array( $this, 'display_server_php' )
            );

            wp_add_dashboard_widget(
                'server_info_database',
                __( 'Server Info WP: WordPress', 'server-info-wp' ),
                array( $this, 'display_server_wp' )
            );
        }

        public function init() {
            if ( function_exists( 'get_current_screen' ) && "dashboard" == get_current_screen()->id ) {
                $this->get_usage();
            }
        }

        private function get_usage() {
            global $wpdb;

            // Get the web server.
            $this->web_server = $_SERVER['SERVER_SOFTWARE'];

            // Get PHP version.
            $this->php_version = function_exists( 'phpversion' ) ? phpversion() : __( 'N/A (phpversion function does not exist)', 'server-info-wp' );

            // Get the server's IP address.
            $this->server_ip = $_SERVER['SERVER_ADDR'];

            // Get the server's port.
            $this->server_port = $_SERVER['SERVER_PORT'];

            // Get the uptime
            $this->uptime = function_exists( 'exec' ) ? @exec( 'uptime -p' ) : __( 'N/A (make sure exec function is enabled)', 'server-info-wp' );

            // Get the OS.
            $this->os = function_exists( 'php_uname' ) ? php_uname( 's' ) : __( 'N/A (php_uname function does not exist)', 'server-info-wp' );

            // Get WordPress version.
            $this->wp_version = function_exists( 'get_bloginfo' ) ? get_bloginfo( 'version' ) : __( 'N/A (get_bloginfo function does not exist)', 'server-info-wp' );

            // Get the server path.
            $this->server_path = defined( 'ABSPATH' ) ? ABSPATH : __( 'N/A (ABSPATH constant not defined)', 'server-info-wp' );

            // Get the server location.
            $this->server_location = function_exists( 'file_get_contents' ) && isset( $this->server_ip ) ? unserialize( file_get_contents('http://www.geoplugin.net/php.gp?ip=' . $this->server_ip ) ) : __( 'N/A (file_get_contents function does not exist or the server IP address could not be retrieved)', 'server-info-wp' );

            // Get and count active WordPress plugins.
            $this->wp_active_plugins = function_exists( 'get_option' ) ? count( get_option( 'active_plugins' ) ) : __( 'N/A (get_option function does not exist)', 'server-info-wp' );

            // See if this site is multisite or not.
            $this->wp_multisite = function_exists( 'is_multisite' ) && is_multisite() ? __( 'Yes', 'server-info-wp' ) : __( 'No', 'server-info-wp' );

            // See if WP Debug is enabled.
            $this->wp_debug_enabled = defined( 'WP_DEBUG' ) ? __( 'Yes', 'server-info-wp' ) : __( 'No', 'server-info-wp' );

            // Get the total number of WordPress users on the site.
            $this->wp_users = function_exists( 'count_users' ) ? count_users() : __( 'N/A (count_users function does not exist)', 'server-info-wp' );

            // Get the number of published WordPress posts.
            $this->wp_posts = wp_count_posts()->publish >= 1 ? wp_count_posts()->publish : __( '0', 'server-info-wp' );

            // Get PHP memory limit.
            $this->php_memory_limit = function_exists( 'ini_get' ) ? (int) ini_get( 'memory_limit' ) : __( 'N/A (ini_get function does not exist)', 'server-info-wp' );

            // Get the PHP error log path.
            $this->php_error_log_path = ! ini_get( 'error_log' ) ? __( 'N/A', 'server-info-wp' ) : ini_get( 'error_log' );

            // Get PHP max upload size.
            $this->php_max_upload = function_exists( 'ini_get' ) ? (int) ini_get( 'upload_max_filesize' ) : __( 'N/A (ini_get function does not exist)', 'server-info-wp' );

            // Get PHP max post size.
            $this->php_max_post = function_exists( 'ini_get' ) ? (int) ini_get( 'post_max_size' ) : __( 'N/A (ini_get function does not exist)', 'server-info-wp' );

            // Get the PHP architecture.
            if ( PHP_INT_SIZE == 4 ) {
                $this->php_architecture = "32-bit";
            } elseif ( PHP_INT_SIZE == 8 ) {
                $this->php_architecture = "64-bit";
            } else {
                $this->php_architecture = "N/A";
            }

            // Get server host name.
            $this->server_hostname = function_exists( 'gethostname' ) ? gethostname() : __( 'N/A (gethostname function does not exist)', 'server-info-wp' );

            // Show the number of processes currently running on the server.
            $this->processes = function_exists( 'exec' ) ? @exec( 'ps aux | wc -l' ) : __( 'N/A (make sure exec is enabled)', 'server-info-wp' );

            // Get the memory usage.
            $this->memory['usage'] = function_exists( 'memory_get_peak_usage' ) ? round( memory_get_peak_usage( true ) / 1024 / 1024, 2 ) : 0;

            // Get CPU usage.
            // Check to see if this OS is Windows, if so then use an alternative since sys_getloadavg() won't work.
            if ( stristr( PHP_OS, 'win' ) ) {
                $this->is_windows = true;
                $this->windows_cpu_usage = function_exists( 'exec' ) ? @exec( 'wmic cpu get loadpercentage /all' ) : __( 'N/A (make sure exec is enabled)', 'server-info-wp' );
            }

            $this->cpu_usage = function_exists( 'sys_getloadavg' ) ? sys_getloadavg() : __( 'N/A (sys_getloadavg function does not exist)', 'server-info-wp' );

            // Get the memory limit.
            $this->memory['limit'] = function_exists( 'ini_get' ) ? (int) ini_get( 'memory_limit' ) : __( 'N/A (ini_get function does not exist)', 'server-info-wp' );

            // Display memory usage in friendly format.
            if ( ! empty( $this->memory['usage'] ) && ! empty( $this->memory['limit'] ) ) {
                $this->memory['percent'] = round( $this->memory['usage'] / $this->memory['limit'] * 100, 0 );
            }

            // Get the PHP maximum execution time.
            $this->php_max_execution_time = function_exists( 'ini_get' ) ? ini_get( 'max_execution_time' ) : __( 'N/A (ini_get function does not exist)', 'server-info-wp' );

            // Get outgoing IP address.
            $this->outgoing_ip = function_exists( 'file_get_contents' ) ? file_get_contents( "http://ipecho.net/plain" ) : __( 'N/A (file_get_contents function does not exist)', 'server-info-wp' );
        }

        public function display_server() {
            echo "<table id='siw-server'>";
            
            echo "<tr>";
            echo "<th>" . __( 'CPU Usage', 'server-info-wp' ) . "</th>";
            if ( $this->is_windows ) {
                echo "<td>" . $this->windows_cpu_usage . "</td>";
            } elseif ( is_array( $this->cpu_usage ) ) {
                echo "<td>" . $this->cpu_usage[0] . "</td>";
            } else {
                echo "<td>" . __( 'N/A', 'server-info-wp' ) . "</th>";
            }
            echo "</tr>";

            echo "<tr>";
            echo "<th>" . __( 'Uptime', 'server-info-wp' ) . "</th>";
            echo "<td>" . $this->uptime . "</td>";
            echo "</tr>";

            echo "<tr>";
            echo "<th>" . __( 'Web Server Software', 'server-info-wp' ) . "</th>";
            echo "<td>" . $this->web_server . "</td>";
            echo "</tr>";

            echo "<tr>";
            echo "<th>" . __( 'Operating System', 'server-info-wp' ) . "</th>";
            echo "<td>" . $this->os . "</td>";
            echo "</tr>";

            echo "<tr>";
            echo "<th>" . __( 'Server IP Address', 'server-info-wp' ) . "</th>";
            echo "<td>" . $this->server_ip . "</td>";
            echo "</tr>";

            echo "<tr>";
            echo "<th>" . __( 'Outgoing Server IP Address', 'server-info-wp' ) . "</th>";
            echo "<td>" . $this->outgoing_ip . "</td>";
            echo "</tr>";

            echo "<tr>";
            echo "<th>" . __( 'Server Location', 'server-info-wp' ) . "</th>";
            if ( is_array( $this->server_location ) && ! empty( $this->server_location ) ) {
                /* translators: 1: city, 2: state/region, 3: country */
                echo "<td>" . sprintf( __( '%1$s, %2$s, %3$s', 'server-info-wp' ), $this->server_location['geoplugin_city'], $this->server_location['geoplugin_regionName'], $this->server_location['geoplugin_countryName'] ) . "</td>";
            } else {
                echo "<td>" . __( 'N/A', 'server-info-wp' ) . "</td>";
            }
            echo "</tr>";

            echo "<tr>";
            echo "<th>" . __( 'Server Port', 'server-info-wp' ) . "</th>";
            echo "<td>" . $this->server_port . "</td>";
            echo "</tr>";

            echo "<tr>";
            echo "<th>" . __( 'Server Path', 'server-info-wp' ) . "</th>";
            echo "<td>" . $this->server_path . "</td>";
            echo "</tr>";

            echo "<tr>";
            echo "<th>" . __( 'Server Host Name', 'server-info-wp' ) . "</th>";
            echo "<td>" . $this->server_hostname . "</td>";

            echo "<tr>";
            echo "<th>" . __( 'Number of Server Processes', 'server-info-wp' ) . "</th>";
            echo "<td>" . $this->processes . "</td>";
            echo "</tr>";

            echo "</table>";
        }

        public function display_server_php() {
            echo "<table id='siw-php'>";

            echo "<tr>";
            echo "<th>" . __( 'PHP Version', 'server-info-wp' ) . "</th>";
            echo "<td>" . $this->php_version . "</td>";
            echo "</tr>";

            echo "<tr>";
            echo "<th>" . __( 'PHP Memory Usage', 'server-info-wp' ) . "</th>";
            /* translators: 1: memory usage, 2: memory limit */
            echo "<td>" . sprintf( __( '%1$s %% of %2$s MB',  'server-info-wp' ), $this->memory['usage'], $this->memory['limit'] ) . "</td>";
            echo "</tr>";

            echo "<tr>";
            echo "<th>" . __( 'PHP Architecture', 'server-info-wp' ) . "</th>";
            /* translators: 1: number of bits */
            echo "<td>" . sprintf( __( '%1$s', 'server-info-wp' ), $this->php_architecture ) . "</td>";
            echo "</tr>";

            echo "<tr>";
            echo "<th>" . __( 'PHP Error Log Path', 'server-info-wp' ) . "</th>";
            echo "<td>" . $this->php_error_log_path . "</td>";
            echo "</tr>";

            echo "<tr>";
            echo "<th>" . __( 'PHP Memory Limit', 'server-info-wp' ) . "</th>";
            /* translators: 1: memory limit */
            echo "<td>" . sprintf( __( '%1$s MB', 'server-info-wp' ), $this->php_memory_limit ) . "</td>";
            echo "</tr>";

            echo "<tr>";
            echo "<th>" . __( 'PHP Max Execution Time', 'server-info-wp' ) . "</th>";
            /* translators: 1: php max execution time */
            echo "<td>" . sprintf( __( '%1$s seconds', 'server-info-wp' ), $this->php_max_execution_time ) . "</td>";
            echo "</tr>";

            echo "<tr>";
            echo "<th>" . __( 'PHP Max Upload Size', 'server-info-wp' ) . "</th>";
            /* translators: 1: php max upload size */
            echo "<td>" . sprintf( __( '%1$s MB', 'server-info-wp' ), $this->php_max_upload ) . "</td>";
            echo "</tr>";

            echo "<tr>";
            echo "<th>" . __( 'PHP Max Post Size', 'server-info-wp' ) . "</th>";
            /* translators: 1: php max post size */
            echo "<td>" . sprintf( __( '%1$s MB', 'server-info-wp' ), $this->php_max_post ) . "</td>";
            echo "</tr>";

            echo "</table>";
        }

        public function display_server_wp() {
            echo "<table id='siw-wordpress'>";

            echo "<tr>";
            echo "<th>" . __( 'WordPress Version', 'server-info-wp' ) . "</th>";
            echo "<td>" . $this->wp_version . "</td>";
            echo "</tr>";

            echo "<tr>";
            echo "<th>" . __( 'WordPress Multisite', 'server-info-wp' ) . "</th>";
            echo "<td>" . $this->wp_multisite . "</td>";
            echo "</tr>";

            echo "<tr>";
            echo "<th>" . __( 'Active WordPress Plugins', 'server-info-wp' ) . "</th>";
            echo "<td>" . $this->wp_active_plugins . "</td>";
            echo "</tr>";

            echo "<tr>";
            echo "<th>" . __( 'WordPress Debug Enabled', 'server-info-wp' ) . "</th>";
            echo "<td>" . $this->wp_debug_enabled . "</td>";
            echo "</tr>";

            echo "<tr>";
            echo "<th>" . __( 'Total WordPress Users', 'server-info-wp' ) . "</th>";
            echo "<td>" . $this->wp_users['total_users'] . "</td>";
            echo "</tr>";

            echo "<tr>";
            echo "<th>" . __( 'Published WordPress Posts', 'server-info-wp' ) . "</th>";
            echo "<td>" . $this->wp_posts . "</td>";
            echo "</tr>";

            echo "</table>";
        }
    }
    new Server_Info_WP();
}
