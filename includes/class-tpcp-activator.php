<?php

/**
 * Fired during plugin activation
 *
 * @link       https://conicplex.com
 * @since      1.0.0
 *
 * @package    Tpcp
 * @subpackage Tpcp/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Tpcp
 * @subpackage Tpcp/includes
 * @author     ConicPlex <hello@conicplex.com>
 */
class Tpcp_Activator
{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function tpcp_activate()
	{
		// Plugin insight
		require_once TPCP_INCLUDES_PATH . 'class-tpcp-insight.php';
		$tpcp_insight = new Tpcp_Insight();
		$tpcp_insight->send_insight('activation');
	}
}
