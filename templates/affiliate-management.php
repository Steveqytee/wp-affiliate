<?php

// Handle form submissions for bulk actions
if (isset($_POST['apply_bulk_action']) && isset($_POST['bulk_action'])) {
    $bulk_action = sanitize_text_field($_POST['bulk_action']);

    if ($bulk_action === 'delete' && isset($_POST['selected_affiliates'])) {
        Affiliate_Handlers::handle_bulk_actions($_POST['selected_affiliates']);
    }
}

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = Affiliate_Handlers::handle_form_submission($_POST);

    if (!empty($_POST['enable_commission_settings'])) {
        if ($_POST['commission_type_select'] === 'product') {
            // Handle product-based commission settings
        } elseif ($_POST['commission_type_select'] === 'order') {
            // Handle order-based commission settings
        } elseif ($_POST['commission_type_select'] === 'quantity') {
            // Handle quantity-based commission settings
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

$affiliates = Affiliate_Registration::get_all_affiliates();

if (!empty($affiliates)) {
    foreach ($affiliates as $affiliate) {
        echo generate_affiliate_table_row($affiliate);
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


// Function to generate a table row for an affiliate
function generate_affiliate_table_row($affiliate) {
    global $wpdb;

    $user_id = $affiliate->user_id;
    $user_info = get_userdata($user_id);
    $user_login = isset($user_info->user_login) ? esc_html($user_info->user_login) : 'N/A';

    // Fetch coupon details
    $coupon_code = 'N/A';
    $coupon_amount = wc_price(0);
    $usage_count = 0;
    $usage_limit = 'Unlimited';

    if ($affiliate->coupon_id) {
        $coupon = new WC_Coupon($affiliate->coupon_id);
        $coupon_code = esc_html($coupon->get_code());
        $coupon_amount_value = get_post_meta($affiliate->coupon_id, 'coupon_amount', true);
        $coupon_amount = !empty($coupon_amount_value) ? wc_price($coupon_amount_value) : wc_price(0);
        $usage_count = get_post_meta($affiliate->coupon_id, 'usage_count', true);
        $usage_limit_value = get_post_meta($affiliate->coupon_id, 'usage_limit', true);
        $usage_limit = empty($usage_limit_value) ? 'Unlimited' : $usage_limit_value;
    }

    // Fetch sales and commission data
    $total_sales = Affiliate_Helpers::get_affiliate_total_sales($user_id);
    $total_commission = Affiliate_Helpers::get_affiliate_total_commission($user_id);

    // Build the table row HTML
    $output = '<tr>';
    $output .= '<td><input type="checkbox" name="selected_affiliates[]" value="' . esc_attr($user_id) . '"></td>';
    $output .= '<td>' . $user_login . '</td>';
    $output .= '<td>' . $coupon_code . '</td>';
    $output .= '<td>' . $coupon_amount . '</td>';
    $output .= '<td>' . wc_price($total_sales) . '</td>';
    $output .= '<td>' . wc_price($total_commission) . '</td>';
    $output .= '<td>' . $usage_count . '/' . $usage_limit . '</td>';
    $output .= '<td><a href="' . admin_url('admin.php?page=edit-affiliate&affiliate_id=' . esc_attr($user_id)) . '" class="button button-edit">Edit</a>';
    $output .= '<button type="button" class="button button-delete" data-affiliate-id="' . esc_attr($user_id) . '">Delete</button></td>';
    $output .= '</tr>';

    return $output;
}
?>
