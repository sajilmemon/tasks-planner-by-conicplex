<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$assign_to = get_post_meta($task_id, '_tpcp_assign_to', true);

$get_user = get_user_by('id', !empty($assign_to) ? $assign_to : 1);
$assign_to_name   = $get_user->display_name;
$assign_to_avatar = get_avatar_url($assign_to);

$status   = get_post_status($task_id);
$due_date = get_date_from_gmt(esc_html(get_post_meta($task_id, '_tpcp_due_date', true)), 'd-m-Y');

?>
<div class="tpcp-d-flex-row tpcp-d-flex-gap-10px">
    <div class="tpcp-task-details-title tpcp-d-flex-1">
        <?php echo esc_html(get_the_title($task_id)); ?>
    </div>

    <div class="tpcp-task-details-status-btn-container">
        <?php
        if ($status == 'tpcp_pending') {
            echo '<button type="button" class="tpcp-task-details-status-btn tpcp-task-details-status-in-progress button button-primary" date-tpcp-task-id="' . esc_html($task_id) . '">' . esc_html__('Mark in Progress', 'tpcp') . '</button>';
        } elseif ($status == 'tpcp_in_progress') {
            echo '<button type="button" class="tpcp-task-details-status-btn tpcp-task-details-status-completed button button-primary" date-tpcp-task-id="' . esc_html($task_id) . '">' . esc_html__('Mark as Completed', 'tpcp') . '</button>';
        }
        ?>
    </div>

</div>
<hr class="tpcp-task-details-divider" />
<div class="tpcp-task-details-items">
    <div class="tpcp-task-details-item">

        <span class="tpcp-task-details-item-label"><?php esc_html_e('Task Status:', 'tpcp'); ?></span>
        <div class="tpcp-task-details-item-text-container tpcp-task-badge tpcp-task-badge-secondary">
            <span class="tpcp-task-details-item-text-icon dashicons dashicons-clock"></span>
            <span class="tpcp-task-details-item-text"><?php echo esc_html($this->custom_status_list($status)); ?></span>
        </div>

    </div>
    <div class="tpcp-task-details-item">
        <span class="tpcp-task-details-item-label"><?php esc_html_e('Task Assign To:', 'tpcp'); ?></span>
        <div class="tpcp-task-details-item-text-container tpcp-task-badge tpcp-task-badge-primary">
            <img class="tpcp-task-details-item-text-icon" src="<?php echo esc_url($assign_to_avatar); ?>" />
            <span class="tpcp-task-details-item-text"><?php echo esc_html(ucfirst($assign_to_name)); ?></span>
        </div>
    </div>

    <div class="tpcp-task-details-item">
        <span class="tpcp-task-details-item-label"><?php esc_html_e('Task Due Date:', 'tpcp'); ?></span>
        <div class="tpcp-task-details-item-text-container tpcp-task-badge tpcp-task-badge-danger">
            <span class="tpcp-task-details-item-text-icon dashicons dashicons-calendar-alt"></span>
            <span class="tpcp-task-details-item-text"><?php echo esc_html($due_date); ?></span>
        </div>
    </div>
</div>
<hr class="tpcp-task-details-divider" />

<!-- Task Description -->
<div class="tpcp-task-details-description">
    <?php echo wp_kses_post(apply_filters('the_content', get_post_field('post_content', $task_id))); ?>
</div>

<hr class="tpcp-task-details-divider" />

<!-- Task Comment -->
<div class="tpcp-task-details-tpcp_comments-container">
    <div class="tpcp-task-details-comments">

        <?php
        $task_comments = get_comments(
            [
                'post_id' => $task_id,
                'status'  => 'approve',
                'order'   => 'ASC',
            ]
        );
        if (!empty($task_comments)) {
            foreach ($task_comments as $task_comment) {

                // Get user info
                $user = get_userdata($task_comment->user_id);
                $display_name = $user->display_name;
                $email        = $user->user_email;
                $avatar       = get_avatar_url($task_comment->user_id);
        ?>
                <div class="tpcp-task-details-comment">
                    <div class="tpcp-task-details-comment-author-avatar-container">
                        <img class="tpcp-task-details-comment-author-avatar" src="<?php echo esc_url($avatar); ?>" alt="<?php echo esc_html($display_name); ?>" />
                    </div>
                    <div class="tpcp-task-details-comment-author-datetime">
                        <span class="tpcp-task-details-comment-author">
                            <?php echo esc_html(ucfirst($display_name)); ?>
                        </span>
                        <span class="tpcp-task-details-comment-datetime">
                            <?php echo esc_html(gmdate('d-m-Y h:i:s A', strtotime(esc_html($task_comment->comment_date)))); ?>
                        </span>
                        <div class="tpcp-task-details-comment-content">
                            <?php echo nl2br(wp_kses_post($task_comment->comment_content)); ?>
                        </div>
                    </div>
                </div>
        <?php
            }
        }
        ?>
    </div>

    <!-- Comment Loader -->
    <div class="tpcp-loader tpcp-task-details-comment-loader">
        <div class="tpcp-w-35px">
            <div class="tpcp-loader-shimmer tpcp-w-35px tpcp-h-35px tpcp-border-radius-50"></div>
        </div>
        <div class="tpcp-w-100 tpcp-d-flex-column tpcp-d-flex-row-gap-10px">
            <div class="tpcp-loader-shimmer tpcp-w-25 tpcp-h-20px"></div>
            <div class="tpcp-loader-shimmer tpcp-w-25 tpcp-h-10px"></div>
            <div class="tpcp-loader-shimmer tpcp-w-100 tpcp-h-50px"></div>
        </div>
    </div>

    <div class="tpcp-task-details-comment-add-new-container">
        <textarea class="tpcp-task-details-comment-add-new-textarea" name="tpcp_task_details_comment_add_new" id="tpcp_task_details_comment_add_new" rows="4" placeholder="<?php esc_html_e('Write a comment...', 'tpcp'); ?>"></textarea>
        <button class="tpcp-task-details-comment-add-new-button button button-primary" id="tpcp_task_details_comment_add_new_button" type="button" value="<?php echo esc_attr($task_id); ?>"><?php esc_html_e('Comment', 'tpcp'); ?></button>
    </div>
</div>