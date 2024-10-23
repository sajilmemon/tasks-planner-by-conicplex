<?php


/**
 * Send the website & plugin details to ConicPlex for plugin insight
 * Fired during plugin activation, deactivation and uninstall
 *
 * @link       https://conicplex.com
 * @since      1.0.0
 *
 * @package    Tpcp
 * @subpackage Tpcp/includes
 */

class Tpcp_Insight
{
    public function send_insight($action)
    {

        // Get current plugin details
        $plugin_details = defined('TPCP_PATH') ? get_plugin_data(TPCP_PATH . 'tpcp.php') : get_plugin_data(plugin_dir_path(__DIR__) . 'tpcp.php');

        // return if plugin details is empty
        if (empty($plugin_details) || empty($plugin_details['Name']) || empty($plugin_details['TextDomain']) || empty($plugin_details['Version'])) {
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
        $response = wp_remote_request(
            $url,
            $args
        );
    }
}
