<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://conicplex.com
 * @since      1.0.0
 *
 * @package    Tpcp
 * @subpackage Tpcp/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// enqueue admin style & script
wp_enqueue_style($this->plugin_name);
wp_enqueue_script($this->plugin_name);

// Insert custom post programmatically
if (isset($_POST['tpcp_add_new_task_submit']) && isset($_POST['tpcp_task_add_new_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['tpcp_task_add_new_nonce'])), 'tpcp_task_add_new_nonce_action') && !empty($_POST['tpcp_task_title'])) {

    $new_task = array(
        'post_title'   => sanitize_text_field(wp_unslash($_POST['tpcp_task_title'])),
        'post_content' => !empty($_POST['tpcp_task_description']) ? wp_kses_post(wp_unslash($_POST['tpcp_task_description'])) : '',
        'post_status'  => 'tpcp_pending',
        'post_type'    => 'tpcp_task'
    );

    $post_id = wp_insert_post($new_task);

    if ($post_id) {
        /*
            * Update custom field on the new post
            * Add meta data into post
            */
        if (!empty($_POST['tpcp_task_assign_to']) && !empty($_POST['tpcp_task_due_date'])) {
            update_post_meta($post_id, '_tpcp_assign_to', absint(wp_unslash($_POST['tpcp_task_assign_to'])));
            update_post_meta($post_id, '_tpcp_due_date', gmdate('Y-m-d', strtotime(sanitize_text_field(wp_unslash($_POST['tpcp_task_due_date'])))));
        }

        // Set success message
        $tasks_success = true;
    } else {

        // Set error message
        $tasks_error = true;
    }
}
?>

<div class="wrap">

    <!-- Page title & Add New Button -->
    <!-- <h1 class="wp-heading-inline"><?php // esc_html_e('Tasks Planner by ConicPlex', 'tpcp'); 
                                        ?></h1>
    <button class="page-title-action tpcp-add-new-task-btn" data-modal="tpcp-add-new-task-modal"><?php // esc_html_e('Add New', 'tpcp'); 
                                                                                                    ?></button>
    <hr class="wp-header-end"> -->

    <!-- Success Notice -->
    <?php if (!empty($tasks_success)) { ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <?php esc_html_e('Success! The task was added.', 'tpcp'); ?>
            </p>
        </div>
    <?php } ?>

    <!-- Error Notice -->
    <?php if (!empty($tasks_error)) { ?>
        <div class="notice notice-error is-dismissible">
            <p>
                <?php esc_html_e('Something went wrong. The task was not added.', 'tpcp'); ?>
            </p>
        </div>
    <?php } ?>

    <!-- Tasks container -->
    <div class="tpcp-tasks-container">

        <div class="tpcp-tasks">

            <div class="tpcp-tasks-list-container">

                <!-- Tasks Header -->
                <div class="tpcp-tasks-header">

                    <div class="tpcp-header-items">
                        <!-- Logo -->
                        <div class="tpcp-logo-container">
                            <img class="tpcp-logo" src="<?php echo esc_url(plugin_dir_url(dirname(__DIR__)) . 'asset/tpcp-logo.png'); ?>" />
                        </div>
                        <div class="tpcp-tasks-addnew-filter">

                            <!-- Add new task icon for admin -->
                            <?php if (current_user_can('manage_options')) { ?>
                                <span class="tpcp-tasks-header-icon dashicons dashicons-insert tpcp-add-new-task-btn" data-modal="tpcp-add-new-task-modal"></span>
                            <?php } ?>

                            <!-- task filter icon -->
                            <div class="tpcp-tasks-filter-container">
                                <span class="tpcp-tasks-header-icon dashicons dashicons-filter tpcp-filter-task-btn"></span>
                                <div class="tpcp-tasks-filter">
                                    <span class="tpcp-tasks-filter-header"><?php echo esc_html_e('Filters', 'tpcp'); ?></span>
                                    <select class="tpcp-tasks-filter-by" data-tpcp-tasks-filter-by="status">
                                        <option value="" disabled selected><?php echo esc_html_e('Filter by Status', 'tpcp'); ?></option>

                                        <?php

                                        // Get list of status
                                        $status = $this->custom_status_list();

                                        // Check status is not empty
                                        if (!empty($status) && is_array($status)) {

                                            // Status loop
                                            foreach ($status as $status_key => $status_value) {
                                                echo '<option value="' . esc_attr($status_key) . '">' . esc_html($status_value['label']) . '</option>';
                                            }
                                        }

                                        ?>
                                    </select>

                                    <!-- Sort by filter -->
                                    <select class="tpcp-tasks-filter-by" data-tpcp-tasks-filter-by="sortby">
                                        <option value="" disabled selected><?php echo esc_html_e('Sort By', 'tpcp'); ?></option>
                                        <option value="desc"><?php echo esc_html_e('Newest First', 'tpcp'); ?></option>
                                        <option value="asc"><?php echo esc_html_e('Oldest First', 'tpcp'); ?></option>
                                        <option value="due_date_asc"><?php echo esc_html_e('Nerest Due Date', 'tpcp'); ?></option>
                                    </select>

                                </div>
                            </div>

                            <!-- Task notificatin icon -->
                            <div class="tpcp-tasks-notifications-container">
                                <!-- Icon -->
                                <span class="tpcp-tasks-header-icon dashicons dashicons-bell tpcp-notifications-task-btn"></span>

                                <?php
                                // Notification Badge
                                $tpcp_unread_comments_args = array(
                                    'meta_query' => array(
                                        'relation' => 'AND',
                                        array(
                                            'key'   => '_tpcp_notification_for_user',
                                            'value' => get_current_user_id(),
                                            'compare' => '='
                                        ),
                                        array(
                                            'key'     => '_tpcp_notification_read_status',
                                            'value'   => 'unread',
                                            'compare' => '='
                                        ),
                                    ),
                                    'count' => true,  // Only return the count of comments
                                );

                                $tpcp_unread_comments_count = get_comments($tpcp_unread_comments_args);

                                // Display a badge if the notification count is greater than 0
                                if ($tpcp_unread_comments_count > 0) {
                                    echo '<span class="tpcp-tasks-notifications-badge">' . esc_html($tpcp_unread_comments_count) . '</span>';
                                }
                                ?>

                                <!-- Notification List -->
                                <div class="tpcp-tasks-notifications">
                                    <?php
                                    // Get notifications
                                    $tpcp_comments_args = [
                                        'meta_key'     => '_tpcp_notification_for_user',
                                        'meta_value'   => get_current_user_id(),
                                        'meta_compare' => 'EXISTS',
                                    ];

                                    $tpcp_notifications = get_comments($tpcp_comments_args);

                                    if (!empty($tpcp_notifications)) {
                                        foreach ($tpcp_notifications as $tpcp_notification) {

                                            $post_id    = $tpcp_notification->comment_post_ID;
                                            $comment_id = $tpcp_notification->comment_ID;
                                            $user_id    = $tpcp_notification->user_id;

                                            // Get user display name & avatar
                                            $task_user_display_name = get_the_author_meta('display_name', $user_id);
                                            $avatar                 = get_avatar_url($user_id);

                                            // Get task title
                                            $task_title = get_the_title($tpcp_notification->comment_post_ID);

                                            // is unread notifications
                                            $tpcp_unread_notification = get_comment_meta($comment_id, '_tpcp_notification_read_status', true) == 'unread' ? 'tpcp-tasks-notification-unread' : '';

                                            // Notification HTML
                                            echo '<div class="tpcp-tasks-notification ' . esc_html($tpcp_unread_notification) . '" data-tpcp-task-id="' . esc_html($post_id) . '" data-tpcp-comment-id="' . esc_html($comment_id) . '">';
                                            echo '<div class="tpcp-tasks-notification-user-avatar-container">';
                                            echo '<img class="tpcp-tasks-notification-user-avatar" src="' . esc_url($avatar) . '" alt="' . esc_html(ucfirst($task_user_display_name)) . '">';
                                            echo '</div>';

                                            echo '<div class"tpcp-tasks-notification-content">';

                                            echo
                                            // Translators: Used to display a user task comment notification. Uses %1$s for the user name and %2$s for the task name.
                                            sprintf(esc_html__('%1$1s  commented on %2$2s', 'tpcp'),
                                                '<span class="tpcp-tasks-notification-user">' . esc_html(ucfirst($task_user_display_name)) . '</span>',
                                                '<span class="tpcp-tasks-notification-task-title">' . esc_html($task_title) . '</span>',
                                            );

                                            echo '<div class="tpcp-tasks-notification-datetime">' . esc_html(get_date_from_gmt($tpcp_notification->comment_date_gmt, 'd-m-Y h:i:s A')) . '</div>';

                                            echo '</div>';
                                            echo '</div>';
                                        }
                                    } else {
                                        echo '<div class="tpcp-tasks-notification-no-found">' . esc_html__('No Notifications Found', 'tpcp') . '</div>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Search -->
                    <div class="tpcp-tasks-list-search-container">
                        <span class="tpcp-tasks-list-search-icon dashicons dashicons-search"></span>
                        <input class="tpcp-tasks-list-search" type="search" placeholder="<?php esc_html_e('Search Tasks...', 'tpcp'); ?>" />
                    </div>

                    <!-- Tasks filters applied container -->
                    <div class="tpcp-tasks-filters-applied" id="tpcp_tasks_filters_applied"></div>

                </div>

                <!-- Tasks list container -->
                <div class="tpcp-tasks-list"></div>

                <!-- Task list Loader -->
                <?php // for ($i = 0; $i < 1; $i++) : 
                ?>
                <div class="tpcp-loader tpcp-tasks-list-loader tpcp-p-15px">
                    <div class="tpcp-loader-shimmer tpcp-w-100 tpcp-h-20px"></div>
                    <div class="tpcp-d-flex-row tpcp-d-flex-column-gap-15px tpcp-m-t-10px">
                        <div class="tpcp-loader-shimmer tpcp-w-25 tpcp-h-20px"></div>
                        <div class="tpcp-loader-shimmer tpcp-w-25 tpcp-h-20px"></div>
                        <div class="tpcp-loader-shimmer tpcp-w-25 tpcp-h-20px"></div>
                    </div>
                </div>
                <!-- <div class="tpcp-loader-shimmer tpcp-w-100 tpcp-h-1px"></div> -->
                <?php // endfor 
                ?>

            </div>

            <div class="tpcp-task-details-container">

                <!-- Task details empty -->
                <div class="tpcp-task-details-empty-container">
                    <div class="tpcp-task-details-empty-img-container">
                        <img class="tpcp-task-details-empty-img" src="<?php echo esc_url(plugin_dir_url(dirname(__DIR__)) . 'asset/tpcp-logo.png'); ?>" />
                    </div>
                    <div class="tpcp-task-details-empty-text">
                        <?php echo esc_html('Kickstart Your Tasks!'); ?>
                    </div>
                </div>

                <!-- Task details laoder -->
                <div class="tpcp-loader tpcp-task-details-loader">
                    <div class="tpcp-loader-shimmer tpcp-w-100 tpcp-h-20px"></div>
                    <div class="tpcp-loader-shimmer tpcp-w-100 tpcp-h-1px tpcp-m-y-15px"></div>
                    <div class="tpcp-d-flex-column tpcp-d-flex-row-gap-15px">
                        <div class="tpcp-loader-shimmer tpcp-w-50 tpcp-h-20px"></div>
                        <div class="tpcp-loader-shimmer tpcp-w-50 tpcp-h-20px"></div>
                        <div class="tpcp-loader-shimmer tpcp-w-50 tpcp-h-20px"></div>
                    </div>
                    <div class="tpcp-loader-shimmer tpcp-w-100 tpcp-h-1px tpcp-m-y-15px"></div>
                    <div class="tpcp-loader-shimmer tpcp-w-100 tpcp-h-300px"></div>
                </div>

                <div class="tpcp-task-details"></div>
            </div>
        </div>
    </div>

    <!-- Add new task form modal -->
    <div class="tpcp-modal tpcp-add-new-task-modal" id="tpcp_modal">
        <div class="tpcp-add-new-task-container">
            <span class="tpcp-modal-title"><?php esc_html_e('Add New Task', 'tpcp'); ?></span>
            <div class="tpcp-add-new-task">
                <form method="post" id="tpcp_tasks_add_new_form">

                    <?php
                    /** Add nonce for a security */
                    wp_nonce_field('tpcp_task_add_new_nonce_action', 'tpcp_task_add_new_nonce');
                    ?>

                    <table class="form-table" role="presentation">
                        <tbody>

                            <!-- Task -->
                            <tr>
                                <th scope="row">
                                    <label for="tpcp_task_title"><?php esc_html_e('Task:', 'tpcp'); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="tpcp_task_title" class="regular-text" name="tpcp_task_title" required />
                                    <p class="description"><?php esc_html_e('Enter the task', 'tpcp'); ?></p>
                                </td>
                            </tr>

                            <!-- Task Assign To -->
                            <tr>
                                <th scope="row">
                                    <label for="tpcp_task_assign_to"><?php esc_html_e('Task Assign To:', 'tpcp'); ?></label>
                                </th>
                                <td>

                                    <div class="tpcp-add-new-task-assign-to-users-input-container">
                                        <input type="text" id="tpcp_task_assign_to_search" class="regular-text" name="tpcp_task_assign_to_search" autocomplete="off" required />
                                        <input type="hidden" id="tpcp_task_assign_to" class="regular-text" name="tpcp_task_assign_to" />
                                        <span class="tpcp-add-new-task-assign-to-users-input-clear-icon dashicons dashicons-no" id="tpcp_add_new_task_assign_to_users_input_clear_icon"></span>
                                    </div>

                                    <div class="tpcp-add-new-task-assign-to-users-container regular-text">

                                        <!-- Search -->
                                        <div class="regular-text" id="tpcp_add_new_task_assign_to_users_messages"><?php esc_html_e('Searching...', 'tpcp') ?></div>

                                        <div class="tpcp-add-new-task-assign-to-users regular-text" id="tpcp_add_new_task_assign_to_users">

                                        </div>
                                    </div>
                                    <p class="description"><?php esc_html_e('Select the user for task', 'tpcp'); ?></p>
                                </td>
                            </tr>

                            <!-- Task Due Date -->
                            <tr>
                                <th scope="row">
                                    <label for="tpcp_task_due_date"><?php esc_html_e('Task Due Date:', 'tpcp'); ?></label>
                                </th>
                                <td>
                                    <input type="date" id="tpcp_task_due_date" class="regular-text" name="tpcp_task_due_date" required />
                                    <p class="description"><?php esc_html_e('Enter the task due date', 'tpcp'); ?></p>
                                </td>
                            </tr>


                            <!-- Task Description -->
                            <tr>
                                <th scope="row">
                                    <label for="tpcp_task_description"><?php esc_html_e('Task Description:', 'tpcp'); ?></label>
                                </th>
                                <td>
                                    <?php
                                    // Add wordpress rich editor for task description
                                    wp_editor(
                                        '', // Content
                                        'tpcp_task_description', // Editor ID
                                        array(
                                            'textarea_name' => 'tpcp_task_description',
                                            'media_buttons' => false,
                                            'quicktags'     => false,
                                            'textarea_rows' => 10, // Number of rows
                                            'tinymce'       => array(
                                                'toolbar1'      => 'bold,italic,underline,link,unlink,undo,redo',
                                            ),
                                        )
                                    );
                                    ?>
                                    <p class="description"><?php esc_html_e('Enter the task description', 'tpcp'); ?></p>
                                </td>
                            </tr>

                        </tbody>
                    </table>

                    <!-- Submit Button -->
                    <?php submit_button(
                        esc_html__('Add Task', 'tpcp'),
                        'primary',
                        'tpcp_add_new_task_submit',
                    ); ?>
                </form>
            </div>
            <span class="tpcp-modal-close dashicons dashicons-no-alt" id="tpcp_modal_close"></span>
        </div>
    </div>
</div>