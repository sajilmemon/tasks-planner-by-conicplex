<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://conicplex.com
 * @since      1.0.0
 *
 * @package    Tpcp
 * @subpackage Tpcp/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Tpcp
 * @subpackage Tpcp/admin
 * @author     ConicPlex <hello@conicplex.com>
 */
class Tpcp_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{
		wp_register_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/tpcp-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		wp_register_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/tpcp-admin.js', array('jquery'), $this->version, false);

		// localize script
		$tpcp_api = array(
			'nonce' => wp_create_nonce('tpcp_ajax_nonce'),
			'url' 	=> admin_url('admin-ajax.php'),
		);

		wp_localize_script($this->plugin_name, 'tpcp_api', $tpcp_api);
	}

	/**
	 * Register the tpcp menu for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function admin_menu()
	{
		// Get admin menu icon
		$tpcp_get_svg_response = wp_remote_get(plugin_dir_url(__DIR__) . 'asset/tpcp-icon-20-20.svg');

		$tpcp_menu_icon = 'dashicons-list-view';

		if (!is_wp_error($tpcp_get_svg_response)) {
			$tpcp_menu_icon = 'data:image/svg+xml;base64,' . base64_encode(wp_remote_retrieve_body($tpcp_get_svg_response));
		}

		// Add admin page
		add_menu_page(
			__('Tasks Planner by ConicPlex', 'tpcp'),
			__('Tasks Planner', 'tpcp'),
			'edit_posts',
			'tasks-planner-by-conicplex',
			[$this, 'admin_menu_callback'],
			$tpcp_menu_icon,
			25
		);
	}

	/**
	 * tpcp menu callback for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function admin_menu_callback()
	{
		include_once(plugin_dir_path(__FILE__) . 'partials/tpcp-admin-display.php');
	}

	/**
	 * create tasks post type.
	 *
	 * @since    1.0.0
	 */
	public function create_task_post_type()
	{
		$labels = array(
			'name'               => _x('Tasks', 'post type general name', 'tpcp'),
			'singular_name'      => _x('Task', 'post type singular name', 'tpcp'),
			'menu_name'          => _x('Tasks', 'admin menu', 'tpcp'),
			'name_admin_bar'     => _x('Task', 'add new on admin bar', 'tpcp'),
			'add_new'            => _x('Add New', 'task', 'tpcp'),
			'add_new_item'       => __('Add New Task', 'tpcp'),
			'new_item'           => __('New Task', 'tpcp'),
			'edit_item'          => __('Edit Task', 'tpcp'),
			'view_item'          => __('View Task', 'tpcp'),
			'all_items'          => __('All Tasks', 'tpcp'),
			'search_items'       => __('Search Tasks', 'tpcp'),
			'parent_item_colon'  => __('Parent Tasks:', 'tpcp'),
			'not_found'          => __('No tasks found.', 'tpcp'),
			'not_found_in_trash' => __('No tasks found in Trash.', 'tpcp'),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => false,
			'show_in_menu'       => false,
			'query_var'          => false,
			'rewrite'            => array('slug' => 'tpcp-task'),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array('title', 'comments'),
		);

		register_post_type('tpcp_task', $args);
	}

	public function custom_status_list($status_label = '')
	{

		// custom status
		$status = array(
			'tpcp_pending' => array(
				'label' 	  =>  _x('Pending', 'tpcp'),
				// Translators: Used to display the count of pending tasks.
				'label_count' =>  _n_noop('Pending <span class="count">(%s)</span>', 'Pending <span class="count">(%s)</span>', 'tpcp')
			),
			'tpcp_completed' => array(
				'label' 	  =>  _x('Completed', 'tpcp'),
				// Translators: Used to display the count of completed tasks.
				'label_count' => _n_noop('Completed <span class="count">(%s)</span>', 'Completed <span class="count">(%s)</span>', 'tpcp')
			),
			'tpcp_in_progress' => array(
				'label' 	  =>  _x('In Progress', 'tpcp'),
				// Translators: Used to display the count of in progress tasks.
				'label_count' => _n_noop('In Progress <span class="count">(%s)</span>', 'In Progress <span class="count">(%s)</span>', 'tpcp')
			),
		);

		if (!empty($status_label)) {
			return $status[$status_label]['label'];
		}

		return $status;
	}

	public function register_task_statuses()
	{
		// Get list of status
		$status = $this->custom_status_list();

		// Check status is not empty
		if (!empty($status) && is_array($status)) {

			// Status loop
			foreach ($status as $status_key => $status_value) {

				// Register custom status
				register_post_status($status_key, array(
					'label'                     => $status_value['label'],
					'public'                    => false,
					'exclude_from_search'       => true,
					'show_in_admin_all_list'    => false,
					'show_in_admin_status_list' => false,
					'label_count'               => $status_value['label_count'],
				));
			}
		}
	}

	public function get_tasks()
	{
		// Verify the tpcp nonce
		check_ajax_referer('tpcp_ajax_nonce', 'tpcp_nonce');

		// Query array
		$args = array(
			'post_type'   => 'tpcp_task',
			'post_status' => ['tpcp_pending', 'tpcp_in_progress'],
		);

		/* Tasks Filter */
		if (!empty($_POST['filters'])) {

			// Convert json object to array
			$filters = json_decode(sanitize_text_field(wp_unslash($_POST['filters'])), true);

			// Check if the filter is an array.
			if (!empty($filters) && is_array($filters)) {

				/* filters by Status */
				$args['post_status'] = !empty($filters['status']['value']) ? sanitize_text_field($filters['status']['value']) : ['tpcp_pending', 'tpcp_in_progress'];

				/* Sort By */
				if (!empty($filters['sortby']['value'])) {

					switch ($filters['sortby']['value']) {
						case 'asc':
							$args['order'] = 'ASC';
							$args['orderby'] = 'ID';
							break;

						case 'desc':
							$args['order'] = 'DESC';
							$args['orderby'] = 'ID';
							break;

						case 'due_date_asc':
							$args['order'] 	   = 'ASC';
							$args['orderby']   = 'meta_value';
							$args['meta_key']  = '_tpcp_due_date';
							$args['meta_type'] = 'DATETIME';
							break;
					}
				}
			}
		}

		/* Search */
		$args['s'] = !empty($_POST['search']) ? sanitize_text_field(wp_unslash($_POST['search'])) : '';

		/** Limit & Offset */
		$args['limit'] = !empty($_POST['limit']) ? absint($_POST['limit']) : 10;
		$args['offset'] = !empty($_POST['offset']) ? absint($_POST['offset']) : 0;

		/* Add a meta query for non-admin user roles */
		if (!current_user_can('manage_options')) {
			$args['meta_query'] = array(
				'relation' => 'AND',
				array(
					'key'   => '_tpcp_assign_to',
					'value' => get_current_user_id(),
					'compare' => '='
				),
			);
		}

		// Open a buffer
		ob_start();

		// Tasks Filters
		include_once(plugin_dir_path(__FILE__) . 'partials/tpcp-task-list-filters-admin-display.php');

		// Set tass list filter HTML into buffer
		$task_filter = ob_get_clean();

		// Open a buffer
		ob_start();

		// Tasks List
		include_once(plugin_dir_path(__FILE__) . 'partials/tpcp-task-list-admin-display.php');

		// Set tasks list HTML inro buffer
		$task_list = ob_get_clean();

		// Return Tasks List
		wp_send_json_success([
			'filters' => $task_filter,
			'list'	  => $task_list
		]);
	}

	public function get_task_details()
	{
		// Verify the tpcp nonce
		check_ajax_referer('tpcp_ajax_nonce', 'tpcp_nonce');

		// Check task id is not empty
		if (!empty($_POST['task_id'])) {

			// Get task id
			$task_id = absint($_POST['task_id']);

			// If the update status is set, then update the task status
			if (!empty($_POST['update_status'])) {

				// get tast status
				$task_status = get_post_status($task_id);

				switch ($task_status) {
					case 'tpcp_pending':
						$task_status = 'tpcp_in_progress';
						break;

					case 'tpcp_in_progress':
						$task_status = 'tpcp_completed';
						break;
				}

				// update post status
				if ($task_status == 'tpcp_in_progress' || $task_status == 'tpcp_completed');
				wp_update_post([
					'ID' => $task_id,
					'post_status' => $task_status
				]);
			}


			// If comment id is set the mark comment as read
			if (!empty($_POST['comment_id'])) {
				$comment_id = absint($_POST['comment_id']);
				update_comment_meta($comment_id, '_tpcp_notification_read_status', 'read', 'unread');
			}


			ob_start();
			include_once(plugin_dir_path(__FILE__) . 'partials/tpcp-task-details-admin-display.php');
			$task_details = ob_get_clean();

			wp_send_json_success($task_details);
		}
	}

	// Get tasks status text
	public function get_task_status_text($status)
	{

		if (empty($status)) {
			return;
		}

		$task_status = [
			'tpcp_pending' 	   => _x('Pending', 'tpcp'),
			'tpcp_completed'   => _x('Completed', 'tpcp'),
			'tpcp_in_progress' => _x('In Progress', 'tpcp'),
		];

		return !empty($task_status[$status]) ? $task_status[sanitize_text_field($status)] : $status;
	}

	public function get_users()
	{
		// Verify the tpcp nonce
		check_ajax_referer('tpcp_ajax_nonce', 'tpcp_nonce');
		$search_user = !empty($_POST['search']) ? sanitize_text_field(wp_unslash($_POST['search'])) : '';

		ob_start();
		include_once(plugin_dir_path(__FILE__) . 'partials/tpcp-admin-task-users-display.php');
		$task_users = ob_get_clean();

		wp_send_json_success($task_users);
	}

	public function add_task_comment()
	{
		// Verify the tpcp nonce
		check_ajax_referer('tpcp_ajax_nonce', 'tpcp_nonce');

		// Return of task id or comment are empty
		if (empty($_POST['task_id']) && empty($_POST['comment'])) {
			wp_send_json_error();
		}

		// Get post data
		$task_id = absint($_POST['task_id']);
		$comment = sanitize_textarea_field(wp_unslash($_POST['comment']));

		// Get user info
		$user_id 	  = get_current_user_id();
		$user 		  = get_userdata($user_id);
		$display_name = $user->display_name;
		$email  	  = $user->user_email;
		$avatar 	  = get_avatar_url($user_id);

		// Get user ip
		$ip = '';
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_CLIENT_IP']));
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR']));
		} elseif (!empty($_SERVER['REMOTE_ADDR'])) {
			$ip = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
		}
		$user_ip = $ip;

		$comment_id = wp_insert_comment(
			[
				'comment_post_ID' 	   => $task_id,
				'comment_content' 	   => $comment,
				'user_id'		  	   => $user_id,
				'comment_author'  	   => $display_name,
				'comment_author_email' => $email,
				'comment_author_IP'    => $user_ip,
				'comment_agent'		   => 'tpcp',
			]
		);

		if (!empty($comment_id)) {

			// Add comemnt meta for the notifications
			$notification_for_user = get_post_meta($task_id, '_tpcp_assign_to', true) != $user_id ? get_post_meta($task_id, '_tpcp_assign_to', true) : get_post_field('post_author', $task_id);

			add_comment_meta($comment_id, '_tpcp_notification_for_user', $notification_for_user);
			add_comment_meta($comment_id, '_tpcp_notification_read_status', 'unread');

			$response = '<div class="tpcp-task-details-comment">';
			$response .= '<div class="tpcp-task-details-comment-author-avatar-container">';
			$response .= '<img class="tpcp-task-details-comment-author-avatar" src="' . esc_url($avatar) . '" alt="' . esc_html($display_name) . '" />';
			$response .= '</div>';
			$response .= '<div class="tpcp-task-details-comment-author-datetime">';
			$response .= '<span class="tpcp-task-details-comment-author">' . esc_html(ucfirst($display_name)) . '</span>';
			$response .= '<span class="tpcp-task-details-comment-datetime">' . current_time('d-m-Y h:i:s A') . '</span>';
			$response .= '<div class="tpcp-task-details-comment-content">' . nl2br(wp_kses_post($comment)) . '</div>';
			$response .= '</div>';
			$response .= '</div>';

			wp_send_json_success($response);
		}
	}

	public function get_dynamic_content_from_picp_api()
	{
		// PICP Get HTML Content
		$url = 'http://test-membership.local/wp-json/picp/v1/dynamic-content';

		$args = [
			'method'    => 'GET',
		];

		// Make the request
		$tpcp_api_response = wp_remote_request(
			$url,
			$args
		);

		if (!is_wp_error($tpcp_api_response)) {

			if (wp_remote_retrieve_response_code($tpcp_api_response)) {
				$tpcp_api_body = json_decode(wp_remote_retrieve_body($tpcp_api_response), true);

				if (!empty($tpcp_api_body['success']) && $tpcp_api_body['success'] == true && !empty($tpcp_api_body['data'])) {
					echo wp_kses_post($tpcp_api_body['data']);
				}
			}
		}
	}
}
