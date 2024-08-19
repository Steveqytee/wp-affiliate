/**
 * File: affiliate-management.js
 * Description: Handles the JavaScript functionality for the affiliate management page.
 * Used in: admin.php?page=affiliate-management
 */
document.addEventListener('DOMContentLoaded', function() {
    // Handle Delete Affiliate Action
    document.querySelectorAll('.button-delete').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const affiliateId = this.dataset.affiliateId;
            if (confirm('Are you sure you want to delete this affiliate?')) {
                fetch(`${AffiliateManagement.ajaxUrl}?action=delete_affiliate&affiliate_id=${affiliateId}`, {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Affiliate deleted successfully.');
                        location.reload(); // Refresh to reflect changes
                    } else {
                        alert('Failed to delete affiliate. Please try again.');
                    }
                })
                .catch(error => console.error('Error deleting affiliate:', error));
            }
        });
    });

    // Handle Select All Checkbox
    const selectAllCheckbox = document.getElementById('select-all');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('click', function(event) {
            const checkboxes = document.querySelectorAll('input[name="affiliate_ids[]"]');
            checkboxes.forEach(checkbox => checkbox.checked = event.target.checked);
        });
    }
});
