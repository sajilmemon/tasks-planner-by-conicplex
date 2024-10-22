<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://conicplex.com
 * @since             1.0.0
 * @package           Tpcp
 *
 * @wordpress-plugin
 * Plugin Name:       Tasks Planner By ConicPlex
 * Plugin URI:        https://conicplex.com
 * Description:       Tasks Planner By ConicPlex helps admins efficiently assign tasks to editors, authors, contributors, and other team members.
 * Version:           1.0.0
 * Author:            ConicPlex
 * Author URI:        https://conicplex.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       tpcp
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('TPCP_VERSION', '1.0.0');

/**
 * Send the website domain & admin email to ConicPlex
 * for the get stats
 */
function tpcp_send_stats($action)
{

    // Get current plugin details
    $plugin_details = get_plugin_data(__FILE__);

    // return if plugin details is empty
    if(empty($plugin_details) || empty($plugin_details['Name']) || empty($plugin_details['TextDomain']) || empty($plugin_details['Version'])){
        return;
    }

    $site_info = [
        'action'            => $action,
        'site_url'          => get_site_url(),
        'admin_email'       => get_option('admin_email'),
        'plugin_name'       => $plugin_details['Name'],
        'plugin_textdomain' => $plugin_details['TextDomain'],
        'plugin_version'    => $plugin_details['Version'],
        'wp_version'        => get_bloginfo('version'),
        'site_language'     => get_bloginfo('language'),
        'site_location'     => get_option('timezone_string') ?: date_default_timezone_get(),
    ];

    // PICP API URL
    $url = 'https://conicplex.com/wp-json/picp/v1/track';

    $args = [
        'method'    => 'POST',
        'body'      => $site_info,
    ];

    // Make the request
    $response = wp_remote_request($url,
            $args
        );
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-tpcp-activator.php
 */
function activate_tpcp()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-tpcp-activator.php';
    Tpcp_Activator::activate();
    tpcp_send_stats('activation');
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-tpcp-deactivator.php
 */
function deactivate_tpcp()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-tpcp-deactivator.php';
    Tpcp_Deactivator::deactivate();
    tpcp_send_stats('deactivation');
}

register_activation_hook(__FILE__, 'activate_tpcp');
register_deactivation_hook(__FILE__, 'deactivate_tpcp');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-tpcp.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_tpcp()
{

    $plugin = new Tpcp();
    $plugin->run();

    // tpcp_send_stats('activation');

}
run_tpcp();
