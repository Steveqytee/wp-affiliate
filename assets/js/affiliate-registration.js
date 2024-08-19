/**
 * File: affiliate-registration.js
 * Description: Handles actions related to affiliate registrations, like approval, rejection, and deletion.
 * Used in: admin.php?page=affiliate-registration
 */
document.addEventListener('DOMContentLoaded', function() {
    // Handle bulk actions
    const selectAllCheckbox = document.getElementById('select-all');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('click', function(event) {
            const checkboxes = document.querySelectorAll('input[name="affiliate_ids[]"]');
            checkboxes.forEach(checkbox => checkbox.checked = event.target.checked);
        });
    }
});
