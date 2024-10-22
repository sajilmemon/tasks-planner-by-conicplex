<?php

// Prepare the query arguments
$args = array(
    'search'         => '*' . $search_user . '*',
    'search_columns' => array(
        'user_login',
        'user_nicename',
        'user_email',
        'user_url',
        'display_name',
        'first_name',
        'last_name'
    ),
    'number'         => 2,
);

// Create the WP_User_Query object
$user_query = new WP_User_Query($args);

// Get the results
$users = $user_query->get_results();

// Check if users were found
if (!empty($users)) {
    foreach ($users as $user) {
?>
        <span class="tpcp-add-new-task-assign-to-user" data-tpcp-user-name="<?php echo esc_html(ucfirst($user->display_name)); ?>" data-tpcp-user-id="<?php echo esc_html($user->id); ?>"><?php echo esc_html(ucfirst($user->display_name)); ?> <i class="tpcp-add-new-task-assign-to-user-roles">(<?php echo esc_html(ucfirst(implode(', ', $user->roles))); ?>)</i></span>
<?php
    }
} else {
    echo '<span class="tpcp-add-new-task-assign-to-user-no-found">' . esc_html__('No Users Found', 'tpcp') . '</span>';
}
