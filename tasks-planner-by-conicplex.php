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
 * Text Domain:       tasks-planner-by-conicplex
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
 * Define plugin path & URL
 */
define('TPCP_PATH', plugin_dir_path( __FILE__ ));
define('TPCP_ADMIN_PATH', plugin_dir_path( __FILE__ ). 'admin/');
define('TPCP_INCLUDES_PATH', plugin_dir_path( __FILE__ ). 'includes/');
define('TPCP_URL', plugin_dir_url( __FILE__ ));
define('TPCP_ADMIN_URL', plugin_dir_url( __FILE__ ). 'admin/');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-tpcp-activator.php
 */
function tpcp_activate()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-tpcp-activator.php';
    Tpcp_Activator::tpcp_activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-tpcp-deactivator.php
 */
function tpcp_deactivate()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-tpcp-deactivator.php';
    Tpcp_Deactivator::tpcp_deactivate();
}

register_activation_hook(__FILE__, 'tpcp_activate');
register_deactivation_hook(__FILE__, 'tpcp_deactivate');

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
function tpcp_run()
{

    $plugin = new Tpcp();
    $plugin->tpcp_run();

    // tpcp_send_stats('activation');

}
tpcp_run();
