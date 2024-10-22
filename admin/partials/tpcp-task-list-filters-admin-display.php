<?php
// Check if the filter is not empty and is an array.
if (!empty($filters) && is_array($filters)) {

    // loop for filters
    foreach ($filters as $filter_key => $filter) {

        // if filter is not empty then add html
        if (!empty($filter['value'])) {
?>
            <div class="tpcp-tasks-filter-applied" data-tpcp-tasks-filter="<?php echo esc_attr($filter_key); ?>">
                <span class="tpcp-tasks-filter-applied-text"><?php echo esc_html($filter['text']); ?></span>
                <span class="tpcp-tasks-filter-applied-cancel dashicons dashicons-no"></span>
            </div>
<?php
        }
    }
}
