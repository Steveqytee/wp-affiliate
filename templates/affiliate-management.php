<?php

// Handle form submissions for bulk actions
if (isset($_POST['apply_bulk_action']) && isset($_POST['bulk_action'])) {
    $bulk_action = sanitize_text_field($_POST['bulk_action']);

    if ($bulk_action === 'delete' && isset($_POST['selected_affiliates'])) {
        Affiliate_Handlers::handle_bulk_actions($_POST['selected_affiliates']);
    }

}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process the affiliate creation
    $result = Affiliate_Handlers::handle_form_submission($_POST);

    if (!empty($_POST['enable_commission_settings'])) {
        // Process commission settings only if the checkbox is checked
        if ($_POST['commission_type_select'] === 'product') {
            // Handle product-based commission settings
            // Your existing logic for processing product commissions
        } elseif ($_POST['commission_type_select'] === 'order') {
            // Handle order-based commission settings
            // Your existing logic for processing order commissions
        } elseif ($_POST['commission_type_select'] === 'quantity') {
            // Handle quantity-based commission settings
            // Your existing logic for processing quantity commissions
        }
    }

    if (is_wp_error($result)) {
        echo '<div class="notice notice-error"><p>' . esc_html($result->get_error_message()) . '</p></div>';
    } else {
        echo '<div class="notice notice-success"><p>Action completed successfully!</p></div>';
    }
}



// Display all Affiliate users and their related information
echo '<h1>Affiliate Management</h1>';

echo '<a href="' . admin_url('admin.php?page=add-new-affiliate') . '" id="openAddAffiliate" class="button button-primary">Add New Affiliate</a>';

echo '<form method="post">';


echo '<table class="widefat fixed striped">';
echo '<thead><tr>';
echo '<th><input type="checkbox" id="select-all"></th>';
echo '<th>Affiliate User</th>';
echo '<th>Affiliate Coupons</th>';
echo '<th>Coupon Amount</th>';
echo '<th>Total Sales</th>';
echo '<th>Total Commission</th>';
echo '<th>Usage/Limit</th>';
echo '<th>Actions</th>';
echo '</tr></thead>';
echo '<tbody>';

// Get all Affiliate users and their coupons
$affiliates = Affiliate_Registration::get_all_affiliates();

if (!empty($affiliates)) {
    foreach ($affiliates as $affiliate) {
        // Get user info
        $user_id = $affiliate->user_id;
        $user_info = get_userdata($user_id);
        $user_login = isset($user_info->user_login) ? esc_html($user_info->user_login) : 'N/A';

        // Get coupon info
        $coupon_code = isset($affiliate->coupon_code) ? esc_html($affiliate->coupon_code) : 'N/A';
        $coupon_id = $affiliate->coupon_id;
        $coupon_amount = get_post_meta($coupon_id, 'coupon_amount', true);
        $coupon_amount = !empty($coupon_amount) ? wc_price($coupon_amount) : wc_price(0);

        $usage_limit = get_post_meta($coupon_id, 'usage_limit', true);
        $usage_limit = empty($usage_limit) ? 'Unlimited' : $usage_limit;
        $usage_count = get_post_meta($coupon_id, 'usage_count', true);
        $usage_count = !empty($usage_count) ? $usage_count : 0;

        // Calculate total sales and commission
        $total_sales = Affiliate_Helpers::get_affiliate_total_sales($user_id);
        $total_commission = Affiliate_Helpers::get_affiliate_total_commission($user_id);

        echo '<tr>';
        echo '<td><input type="checkbox" name="selected_affiliates[]" value="' . $user_id . '"></td>';
        echo '<td>' . $user_login . '</td>';
        echo '<td>' . $coupon_code . '</td>';
        echo '<td>' . $coupon_amount . '</td>';
        echo '<td>' . wc_price($total_sales) . '</td>';
        echo '<td>' . wc_price($total_commission) . '</td>';
        echo '<td>' . $usage_count . '/' . $usage_limit . '</td>';
        echo '<td><a href="' . admin_url('admin.php?page=edit-affiliate&affiliate_id=' . esc_attr($user_id)) . '" class="button button-edit">Edit</a>';
        echo '<button type="button" class="button button-delete" data-affiliate-id="' . esc_attr($user_id) . '">Delete</button></td>';
        echo '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="8">No affiliates found.</td></tr>';
}

echo '</tbody>';
echo '</table>';

// Bulk options after the table
echo '<div style="margin-top: 20px;">';
echo '<select name="bulk_action">';
echo '<option value="">Bulk actions</option>';
echo '<option value="delete">Delete</option>';
echo '</select>';
echo '<input type="submit" name="apply_bulk_action" value="Apply" class="button">';
echo '</div>';

echo '</form>';
?>




<!-- JavaScript for AJAX -->
<script>
document.addEventListener('DOMContentLoaded', function() {

    const formContainer = document.getElementById('affiliateFormContainer');




    // Handle Delete Affiliate Action
    document.querySelectorAll('.button-delete').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const affiliateId = this.dataset.affiliateId;
            if (confirm('Are you sure you want to delete this affiliate?')) {
                fetch(`<?php echo admin_url('admin-ajax.php?action=delete_affiliate&affiliate_id='); ?>${affiliateId}`, {
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
    document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.button-delete').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this affiliate?')) {
                const affiliateId = this.dataset.affiliateId;
                fetch(`<?php echo admin_url('admin-ajax.php?action=delete_affiliate'); ?>`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        affiliate_id: affiliateId,
                        nonce: '<?php echo wp_create_nonce('delete_affiliate_nonce'); ?>'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Affiliate deleted successfully.');
                        location.reload();
                    } else {
                        alert('Failed to delete affiliate.');
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        });
    });
});




    // Reinitialize Form Scripts if Needed
    function initializeFormScripts() {
        // Add any additional scripts you need to reinitialize after the form loads
    }
});

</script>
