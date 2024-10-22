/*
 * dom loaded event listener
 * @since      1.0.0
 */
document.addEventListener("DOMContentLoaded", function () {
  /*
   * Added javascript wp.i18n for translations
   */
  const { __, _x, _n, _nx, sprintf } = wp.i18n;

  /*
   * predefined variables
   * @since      1.0.0
   */
  var modal = "";
  let tpcp_fetch_request = false;
  var tpcp_tasks_offset = 0;
  var tpcp_tasks_limit = 10;
  var tpcp_pagination_on_scroll = true;

  const tpcp_task_search = document.querySelector(".tpcp-tasks-list-search");
  const tpcp_tasks_filter_by = document.querySelectorAll(".tpcp-tasks-filter-by");
  const tasks_list = document.querySelector(".tpcp-tasks-list");
  const tasks_list_loader = document.querySelector(".tpcp-tasks-list-loader");
  const tpcp_tasks_filters_applied = document.querySelector(".tpcp-tasks-filters-applied");
  const task_details = document.querySelector(".tpcp-task-details");
  const tpcp_notifications_task_btn = document.querySelector('.tpcp-notifications-task-btn');
  const tpcp_tasks_notifications = document.querySelector(".tpcp-tasks-notifications");
  const tasks_filter = document.querySelector(".tpcp-tasks-filter");

  /*
   * open add new task modal on add new button click event listener
   * @since      1.0.0
   */

  // get add new button element
  let add_new_task_btn = document.querySelector(".tpcp-add-new-task-btn");

  // Click event listener for the open modal if add new task button found
  if (add_new_task_btn) {
    add_new_task_btn.addEventListener("click", function () {
      // Get button data attribute for modal
      let modal_data_attr = this.getAttribute("data-modal");

      // get modal element according to button data attribute
      modal = document.querySelector("." + modal_data_attr);

      // change modal display style to flex
      modal.style.display = "flex";
    });
  }

  /*
   * open filters modal on button click event listener
   * @since      1.0.0
   */

  // get filter button element
  const filter_task_btn = document.querySelector(".tpcp-filter-task-btn");

  // Click event listener for the open filter
  filter_task_btn.addEventListener("click", function () {
    // toggle active class
    filter_task_btn.classList.toggle("tpcp-tasks-header-icon-active");

    // toggle filter
    tasks_filter.classList.toggle("tpcp-d-flex-column");
  });

  /*
   * Close modal click event listener
   * @since      1.0.0
   */

  // get modal close element
  let modal_close = document.getElementById("tpcp_modal_close");

  // Click event for the modal close
  modal_close.addEventListener("click", function () {
    // change modal style to display none
    modal.style.display = "none";
  });

  /*
   * Get task list function
   * @since      1.0.0
   */

  // call task list function on page load
  tpcp_get_task_list();

  function tpcp_get_task_list() {

    tasks_list_loader.style.display = "block";

    if (tpcp_tasks_offset == 0) {
      tasks_list.style.display = "none";
    }

    //  Define post data
    let post_data = {
      action: "tpcp_get_tasks",
      filters: JSON.stringify(tpcp_get_filters()),
      search: tpcp_task_search.value,
      offset: tpcp_tasks_offset,
      limit: tpcp_tasks_limit,
    };

    // Call the postDataToAPI function
    postDataToAPI(post_data).then((response) => {

      if (response !== "abort_error") {

        // Activate pagination scroll
        tpcp_pagination_on_scroll = true

        // Hide Task list Loader
        tasks_list_loader.style.display = "none";

        // Filter applied
        tpcp_tasks_filters_applied.innerHTML = response.filters;

        if (tpcp_tasks_offset == 0) {
          tasks_list.innerHTML = response.list;
          tasks_list.style.display = "block";
        }
        else {
          tasks_list.innerHTML += response.list;
        }
      }
    });
  }

  const tpcp_task_list = document.querySelector(".tpcp-tasks-list");

  tpcp_task_list.addEventListener("click", function (event) {
    let taskElement = event.target.closest(".tpcp-task");

    if (taskElement) {

      // Previous Active Task
      previous_active_task = document.querySelector(
        ".tpcp-task.tpcp-active-task"
      );

      if (previous_active_task) {
        previous_active_task.classList.remove("tpcp-active-task");
      }

      // Active clicked task list
      taskElement.classList.add("tpcp-active-task");


      // Get task id
      const task_id = taskElement.getAttribute("data-tpcp-task-id");

      // call task details
      tpcp_get_task_details(task_id);

    }
  });

  /*
 * Get searched tasks function on search or keyup
 * @since      1.0.0
 */
  // Using keyup event
  tpcp_task_search.addEventListener("keyup", function (event) {

    // Reset tasks list params (offset)
    tpcp_reset_tasks_list_params();

    // Get tasks list
    tpcp_get_task_list();
  });

  // Using search event
  tpcp_task_search.addEventListener("search", function (event) {

    // Reset tasks list params (offset)
    tpcp_reset_tasks_list_params();

    // Get tasks list
    tpcp_get_task_list();
  });

  /**
  * Pagination on scroll (infinity scroll)
  */
  tasks_list.addEventListener('scroll', function () {

    // The total height of the list (scroll height)
    const total_height = tasks_list.scrollHeight;

    // The current scroll position (top)
    const current_scroll = tasks_list.scrollTop;

    // The visible height of the list (client height)
    const visible_height = tasks_list.clientHeight;

    // Check if user scrolled near the bottom (within 100px) also pagiantion on scroll is true & tpcp-task-no-found length is 0
    if (total_height - (current_scroll + visible_height) <= 100 && tpcp_pagination_on_scroll === true && document.querySelectorAll(".tpcp-task-no-found").length === 0) {

      // disable pagination on scroll
      tpcp_pagination_on_scroll = false;

      // update offset
      tpcp_tasks_offset = tpcp_tasks_offset + tpcp_tasks_limit;

      // Get tasks list
      tpcp_get_task_list();
    }
  });

  /* Get user */
  const tpcp_task_assign_to_search = document.getElementById(
    "tpcp_task_assign_to_search"
  );
  const tpcp_add_new_task_assign_to_users = document.getElementById(
    "tpcp_add_new_task_assign_to_users"
  );

  tpcp_task_assign_to_search.addEventListener("keyup", function (event) {
    if (tpcp_task_assign_to_search.readOnly == true) {
      return;
    }

    const tpcp_add_new_task_assign_to_users_messages = document.getElementById(
      "tpcp_add_new_task_assign_to_users_messages"
    );
    tpcp_add_new_task_assign_to_users_messages.style.display = "block";

    tpcp_add_new_task_assign_to_users.style.display = "none";

    //  Define post data
    let post_data = {
      action: "tpcp_get_users",
      search: tpcp_task_assign_to_search.value,
    };

    // Call the postDataToAPI function
    postDataToAPI(post_data).then((response) => {
      if (response !== "abort_error") {
        tpcp_add_new_task_assign_to_users_messages.style.display = "none";
        tpcp_add_new_task_assign_to_users.style.display = "flex";
        tpcp_add_new_task_assign_to_users.innerHTML = response;
      }
    });
  });

  const tpcp_task_assign_to_search_clear = document.getElementById(
    "tpcp_add_new_task_assign_to_users_input_clear_icon"
  );

  const tpcp_task_assign_to_id = document.getElementById("tpcp_task_assign_to");

  // Select user for tasks
  tpcp_add_new_task_assign_to_users.addEventListener("click", function (e) {
    const tpcp_task_assign_to_user = e.target.closest(
      ".tpcp-add-new-task-assign-to-user"
    );

    if (tpcp_task_assign_to_user) {
      // Set task assign user name in search
      tpcp_task_assign_to_search.value = tpcp_task_assign_to_user.getAttribute(
        "data-tpcp-user-name"
      );

      // make search readonly
      tpcp_task_assign_to_search.readOnly = true;

      // Set task assign user id in textbox
      tpcp_task_assign_to_id.value = e.target
        .closest(".tpcp-add-new-task-assign-to-user")
        .getAttribute("data-tpcp-user-id");

      tpcp_add_new_task_assign_to_users.style.display = "none";

      tpcp_task_assign_to_search_clear.style.display = "inline-flex";
    }
  });

  // Clear assign user input on click
  tpcp_task_assign_to_search_clear.addEventListener("click", function (e) {
    // Clear task assign user name in search
    tpcp_task_assign_to_search.value = "";

    // make search readonly
    tpcp_task_assign_to_search.readOnly = false;

    tpcp_task_assign_to_id.value = "";

    tpcp_task_assign_to_search_clear.style.display = "none";
  });

  const tpcp_tasks_add_new_form = document.getElementById(
    "tpcp_tasks_add_new_form"
  );

  tpcp_tasks_add_new_form.addEventListener("submit", function (e) {
    if (tpcp_task_assign_to_id.value == "") {
      e.preventDefault();

      tpcp_task_assign_to_search.style.borderColor = "#d63638";
      alert(__("Please select a user to assign the task to.", "tpcp"));
      return false;
    }
  });

  /**
   * 
   * Filters Applied
   */
  tpcp_tasks_filter_by.forEach(filter => {

    filter.addEventListener("change", function (e) {

      // Hide filters box
      document.querySelector(".tpcp-filter-task-btn").classList.toggle("tpcp-tasks-header-icon-active");
      document.querySelector(".tpcp-tasks-filter").classList.toggle("tpcp-d-flex-column");

      // Reset tasks list params (offset)
      tpcp_reset_tasks_list_params();

      // Get task list
      tpcp_get_task_list();

    });
  });

  /**
    * Remove filters on click
    */
  tpcp_tasks_filters_applied.addEventListener('click', (event) => {

    let task_filter = event.target.closest(".tpcp-tasks-filter-applied");

    if (task_filter) {

      let filter = task_filter.getAttribute('data-tpcp-tasks-filter');
      document.querySelector('.tpcp-tasks-filter-by[data-tpcp-tasks-filter-by="' + filter + '"]').value = "";

      task_filter.style.display = "none";

      // Reset tasks list params (offset)
      tpcp_reset_tasks_list_params();

      // Get tasks list
      tpcp_get_task_list();
    }
  });

  /**
   * Task details clicked - Check target event
   * Update task status
   * Add comments
   *
   */
  task_details.addEventListener('click', (event) => {

    // Update task status button
    const tpcp_task_details_status_btn = event.target.closest(".tpcp-task-details-status-btn");

    // Add comment button
    const tpcp_comment_button = event.target.closest(".tpcp-task-details-comment-add-new-button");


    // Check is update status buttn clicked
    if (tpcp_task_details_status_btn) {

      const task_id = tpcp_task_details_status_btn.getAttribute('date-tpcp-task-id');

      // Call task details function
      tpcp_get_task_details(task_id, '', 1);

    }

    // Check is comment button clicked
    if (tpcp_comment_button) {

      // Display comment loader
      document.querySelector('.tpcp-task-details-comment-loader').style.display = "flex";

      // Get comment text
      const tpcp_comment = tpcp_comment_button.closest(".tpcp-task-details-comment-add-new-container").querySelector(".tpcp-task-details-comment-add-new-textarea");

      // Get tasks Id
      let post_data = {
        task_id: tpcp_comment_button.value,
        comment: tpcp_comment.value,
        action: "tpcp_add_task_comment",
      }

      // make comment box readonly & disable button
      tpcp_comment.readOnly = true;
      tpcp_comment_button.disabled = true;

      // Call the postDataToAPI function
      postDataToAPI(post_data).then((response) => {

        // Comment Response
        if (response) {

          // Add Comment into comments container
          document.querySelector(".tpcp-task-details-comments").innerHTML += response;
        }

        // Hide comment loader
        document.querySelector('.tpcp-task-details-comment-loader').style.display = "none";

        // make comment box readonly & disable button
        tpcp_comment.readOnly = false;
        tpcp_comment.value = '';
        tpcp_comment_button.disabled = false;

      });
    }
  });

  /* Get filters */
  function tpcp_get_filters() {

    let filters = {};

    tpcp_tasks_filter_by.forEach(filter_ele => {

      let filter_key = filter_ele.getAttribute('data-tpcp-tasks-filter-by');
      let filter_value = filter_ele.value;

      filters[filter_key] = {
        text: filter_ele.options[filter_ele.selectedIndex].text,
        value: filter_value
      };
    });

    return filters;

  }

  /**
   * Open notification box
   */
  tpcp_notifications_task_btn.addEventListener('click', function (event) {

    this.classList.toggle('tpcp-tasks-header-icon-active');
    tpcp_tasks_notifications.classList.toggle('tpcp-d-block');
  });

  /**
  * Close filter/notification container on outside click
  */

  document.addEventListener('click', function (event) {

    // Close notification container
    if (!tpcp_tasks_notifications.contains(event.target) && !tpcp_notifications_task_btn.contains(event.target)) {
      tpcp_notifications_task_btn.classList.remove('tpcp-tasks-header-icon-active');
      tpcp_tasks_notifications.classList.remove('tpcp-d-block');
    }

    // Close filter container
    if (!tasks_filter.contains(event.target) && !filter_task_btn.contains(event.target)) {
      filter_task_btn.classList.remove('tpcp-tasks-header-icon-active');
      tasks_filter.classList.remove('tpcp-d-flex-column');
    }
  });

  /**
   * Open tasks details when clicked on notification
   */
  const tpcp_task_notification = document.querySelectorAll('.tpcp-tasks-notification');
  if (tpcp_task_notification.length > 0) {

    tpcp_task_notification.forEach(notificaiton_ele => {
      notificaiton_ele.addEventListener('click', function () {

        tpcp_tasks_notifications.classList.toggle('tpcp-d-block');
        tpcp_notifications_task_btn.classList.toggle('tpcp-tasks-header-icon-active');

        const task_id = this.getAttribute('data-tpcp-task-id');
        const comment_id = this.getAttribute('data-tpcp-comment-id');

        // Remove unread class
        this.classList.remove('tpcp-tasks-notification-unread');

        // Call task details
        tpcp_get_task_details(task_id, comment_id);

        const tpcp_tasks_notifications_badge = document.querySelector('.tpcp-tasks-notifications-badge');
        if (tpcp_tasks_notifications_badge) {
          const tpcp_count_task_notification = parseInt(tpcp_tasks_notifications_badge.innerHTML) - 1;
          if (tpcp_count_task_notification > 0) {
            tpcp_tasks_notifications_badge.innerHTML = tpcp_count_task_notification;
          }
          else {
            tpcp_tasks_notifications_badge.style.display = 'none';
          }
        }

      });
    });
  }

  /**
   * Reset tasks list params to default
   */
  function tpcp_reset_tasks_list_params() {
    tpcp_tasks_offset = 0;
  }

  /**
  * Get task details by task id
  */
  function tpcp_get_task_details(task_id, comment_id = '', update_status = '') {

    const tpcp_task_details_empty = document.querySelector(
      ".tpcp-task-details-empty-container"
    );

    tpcp_task_details_empty.style.display = "none";

    const tpcp_task_details_loader = document.querySelector(
      ".tpcp-task-details-loader"
    );

    tpcp_task_details_loader.style.display = "block";
    task_details.style.display = "none";

    //  Define post data
    let post_data = {
      action: "tpcp_get_task_details",
      task_id: task_id,
      comment_id: comment_id,
      update_status: update_status
    };

    // Call the postDataToAPI function
    postDataToAPI(post_data).then((response) => {
      task_details.innerHTML = response;

      tpcp_task_details_loader.style.display = "none";
      task_details.style.display = "block";
    });
  }

  // Function to send data to the API using POST
  async function postDataToAPI(post_data) {
    // Abort the previous request if it exists
    if (tpcp_fetch_request) {
      tpcp_fetch_request.abort();
    }

    // Create a new AbortController for the new request
    tpcp_fetch_request = new AbortController();

    // Add nonce in post data for a security
    post_data["tpcp_nonce"] = tpcp_api.nonce;

    try {
      // Make the fetch request with method and headers
      const response = await fetch(tpcp_api.url, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
        },
        body: new URLSearchParams(post_data),
        signal: tpcp_fetch_request.signal,
      });

      // Check if the response is ok (status in the range 200-299)
      if (!response.ok) {
        return false;
      }

      // Parse the response as JSON
      const data = await response.json();
      if (data.data === undefined) {
        return '';
      }
      return data.data;
    } catch (error) {
      // Handle id equest was aborted
      if (error.name === "AbortError") {
        return "";
        // return "abort_error";
      } else {
        return error;
      }

      // Handle any errors that occurred during the fetch
      // return (false);
    }
  }
});
