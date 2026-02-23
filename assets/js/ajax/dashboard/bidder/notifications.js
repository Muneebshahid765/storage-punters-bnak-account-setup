$(document).ready(function () {
    // Fetch notifications from API
    fetchNotifications();
    fetchVaultNotifications(); // <--- Added this

    function fetchVaultNotifications() {
        $.ajax({
            url: SITE_URL + "api/vault/notifications/get",
            type: "GET",
            dataType: "json",
            success: function(response) {
                if (response.status === "success") {
                    renderVaultRows(response.data);
                }
            }
        });
    }

    function renderVaultRows(data) {
        $(".notifications-table tbody").each(function(index, tbody) {
            let html = `
                <tr class="bg-light">
                    <td colspan="4" class="text-start ps-3 fw-bold text-muted">The Vault (Giveaways)</td>
                </tr>
                <tr>
                    <td class="text-center"><p>Vault Drop Reminders</p></td>
                    <td class="text-center">Email / SMS</td>
                    <td class="text-center">
                        <input type="radio" name="vault_reminders_${index}" value="true" 
                            class="vault-toggle" data-key="vault_reminders" ${data.vault_reminders ? 'checked' : ''}>
                    </td>
                    <td class="text-center">
                        <input type="radio" name="vault_reminders_${index}" value="false" 
                            class="vault-toggle" data-key="vault_reminders" ${!data.vault_reminders ? 'checked' : ''}>
                    </td>
                </tr>
                <tr>
                    <td class="text-center"><p>Winner Announcements</p></td>
                    <td class="text-center">Email</td>
                    <td class="text-center">
                        <input type="radio" name="winner_announcements_${index}" value="true" 
                            class="vault-toggle" data-key="winner_announcements" ${data.winner_announcements ? 'checked' : ''}>
                    </td>
                    <td class="text-center">
                        <input type="radio" name="winner_announcements_${index}" value="false" 
                            class="vault-toggle" data-key="winner_announcements" ${!data.winner_announcements ? 'checked' : ''}>
                    </td>
                </tr>
            `;
            $(tbody).append(html);
        });
    }

    // Handle Vault Toggles
    $(document).on("change", ".vault-toggle", function() {
        let key = $(this).data("key");
        let value = $(this).val() === "true";

        $.ajax({
            url: SITE_URL + "api/vault/notifications/update",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify({ key: key, value: value }),
            success: function(response) {
                if (response.status === "success") {
                    showNotification("Settings updated", "success");
                }
            }
        });
    });

    function fetchNotifications() {
        $.ajax({
            url: SITE_URL + "api/users/bidder/notifications/get", // Change this to your backend URL
            type: "POST",
            contentType: "application/json",
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    showNotification(response.message, "success");
                    populateNotificationsTable(response.data);
                } else {
                    showNotification(response.message, "error");
                }
            },
            error: function (jqXHR) {
                let errorMessage = "Something went wrong. Try again.";
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMessage = jqXHR.responseJSON.message;
                }
                showNotification(errorMessage, "error");
            }
        });
    }

    function populateNotificationsTable(notifications) {
        $(".notifications-table").each(function (tableIndex, table) {
            let tableBody = $(table).find("tbody");
            tableBody.empty(); // Clear only the current table's rows

            notifications.forEach((notification, index) => {
                let yesChecked = notification.status_type === "Active" ? "checked" : "";
                let noChecked = notification.status_type === "Inactive" ? "checked" : "";
                let alertSettingId = notification.alert_setting_id; // Assuming API provides ID

                let row = `
                    <tr>
                        <td class="text-center" >
                            <p>${notification.notification_type}</p>
                        </td>
                        <td class="text-center" >${notification.preference_type}</td>
                        <td class="text-center" >
                            <input type="radio" name="notif-${tableIndex}-${index}" value="Active" ${yesChecked}
                                data-alert-setting-id="${alertSettingId}" data-status-type-id="1" data-prev="${notification.status_type}">
                        </td>
                        <td class="text-center">
                            <input type="radio" name="notif-${tableIndex}-${index}" value="Inactive" ${noChecked}
                                data-alert-setting-id="${alertSettingId}" data-status-type-id="2" data-prev="${notification.status_type}">
                        </td>
                    </tr>
                `;

                tableBody.append(row);
            });
        });
    }


    // Attach the onchange event to all radio buttons inside the notifications table
    $(document).on("change", ".notifications-table input[type='radio']", function () {

        // Call the confirm function
        confirmChange(this);
    });






    // Function to confirm change before AJAX update
    function confirmChange(element) {
        let alertSettingId = $(element).data("alert-setting-id");
        let statusTypeId = $(element).data("status-type-id");
        let newStatus = $(element).val();
        let prevStatus = $(element).data("prev");

        Swal.fire({
            title: "Are you sure?",
            text: `Do you want to change the notification to ${newStatus}?`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, change it!"
        }).then((result) => {
            if (result.isConfirmed) {
                updateNotificationStatus(alertSettingId, statusTypeId);
            } else {
                // Revert to previous selection if canceled
                $(`input[data-id="${alertSettingId}"][value="${prevStatus}"]`).prop("checked", true);
            }
        });
    }

    // Function to update notification status via AJAX
    function updateNotificationStatus(alertSettingId, statusTypeId) {
        $.ajax({
            url: SITE_URL + "api/users/bidder/notifications/update",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify({ alert_setting_id: alertSettingId, status_type_id: statusTypeId }),
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    showNotification(response.message, "success");
                    fetchNotifications();
                } else {
                    showNotification(response.message, "error");
                }
            },
            error: function () {
                showNotification("Failed to update. Try again.", "error");
            }
        });
    }
});