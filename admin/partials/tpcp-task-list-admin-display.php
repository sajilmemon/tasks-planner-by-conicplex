<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Create a new query
$query = new WP_Query($args);

// Check if there are any posts to display
if ($query->have_posts()) :

    // The Loop
    while ($query->have_posts()) :

        $query->the_post();

        $task_id = get_the_ID();

        $assign_to = get_post_meta($task_id, '_tpcp_assign_to', true);

        $get_user = get_user_by('id', !empty($assign_to) ? $assign_to : 1);

        $assign_to_name = $get_user->display_name;

        $due_date = get_date_from_gmt(esc_html(get_post_meta($task_id, '_tpcp_due_date', true)), 'd-m-Y');

?>
        <!-- Tasks List -->
        <div class="tpcp-task" data-tpcp-task-id="<?php echo esc_html($task_id); ?>">

            <!-- Tasks title -->
            <div class="tpcp-task-title"><?php echo esc_html(get_the_title()); ?></div>

            <!-- Tasks items -->
            <div class="tpcp-task-items">

                <!-- Tasks status -->
                <div class="tpcp-task-status tpcp-task-item" title="<?php esc_html_e('Task Status', 'tpcp'); ?>">
                    <span class="tpcp-task-status-icon dashicons dashicons-clock"></span>
                    <span class="tpcp-task-status-text"><?php echo esc_html($this->custom_status_list(get_post_status())); ?></span>
                </div>

                <!-- Tasks assign to -->
                <div class="tpcp-task-assign-to tpcp-task-item" title="<?php esc_html_e('Task Assign To', 'tpcp'); ?>">
                    <img class="tpcp-task-assign-to-avatar" src="<?php echo esc_url(get_avatar_url($assign_to)); ?>" />
                    <span class="tpcp-task-assign-to-name"><?php echo esc_html($assign_to_name); ?></span>
                </div>

                <!-- Tasks due date -->
                <div class="tpcp-task-due-date tpcp-task-item" title="<?php esc_html_e('Task Due Date', 'tpcp'); ?>">
                    <span class="tpcp-task-due-date-icon dashicons dashicons-calendar-alt"></span>
                    <span class="tpcp-task-due-date-text"><?php echo esc_html($due_date); ?></span>
                </div>

            </div>
            <!-- End tasks items -->

        </div>
        <!-- End tasks List -->
    <?php
    endwhile;
    wp_reset_postdata();

else:
    ?>
    <!-- Tasks List -->
    <div class="tpcp-task-no-found">
        <?php $args['offset'] > 0 ? esc_html_e('No More Tasks Found', 'tpcp') : esc_html_e('No Tasks Found', 'tpcp'); ?>
    </div>
<?php
endif;
?>