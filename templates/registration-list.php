<h1>Affiliate Registrations</h1>

<?php
global $wpdb;

// Handle form actions (approval, rejection, deletion)
if (isset($_POST['action']) && isset($_POST['affiliate_id']) && check_admin_referer('affiliate_action_nonce')) {
    $affiliate_id = intval($_POST['affiliate_id']);
    $action = sanitize_text_field($_POST['action']);

    switch ($action) {
        case 'approve':
            $wpdb->update(
                "{$wpdb->prefix}affiliate_registrations",
                ['status' => 'accepted'],
                ['id' => $affiliate_id],
                ['%s'],
                ['%d']
            );
            // Add the approved affiliate to the affiliate list
            $affiliate = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}affiliate_registrations WHERE id = %d", $affiliate_id));
            if ($affiliate) {
                $user_id = wp_create_user($affiliate->email, wp_generate_password(), $affiliate->email);
                if (!is_wp_error($user_id)) {
                    // Assuming `create_affiliate_coupon` creates the coupon and returns the ID
                    $coupon_id = Affiliate_Helpers::create_affiliate_coupon($user_id, $affiliate->coupon_code);
                    $wpdb->insert(
                        "{$wpdb->prefix}affiliates",
                        [
                            'user_id' => $user_id,
                            'coupon_id' => $coupon_id,
                            'status' => 'active',
                            'created_at' => current_time('mysql')
                        ]
                    );
                }
            }
            break;
        case 'reject':
            $wpdb->update(
                "{$wpdb->prefix}affiliate_registrations",
                ['status' => 'rejected'],
                ['id' => $affiliate_id],
                ['%s'],
                ['%d']
            );
            break;
        case 'delete':
            $wpdb->delete("{$wpdb->prefix}affiliate_registrations", ['id' => $affiliate_id], ['%d']);
            break;
    }
}

// Retrieve registrations based on filter
$filter_status = isset($_GET['filter_status']) ? sanitize_text_field($_GET['filter_status']) : '';
$query = "SELECT * FROM {$wpdb->prefix}affiliate_registrations";
if (!empty($filter_status)) {
    $query .= $wpdb->prepare(" WHERE status = %s", $filter_status);
}
$affiliates = $wpdb->get_results($query);
?>

<!-- Filter Form -->
<form method="get">
    <select name="filter_status">
        <option value="" <?php selected($filter_status, '', false); ?>>All</option>
        <option value="accepted" <?php selected($filter_status, 'accepted', false); ?>>Accepted</option>
        <option value="pending" <?php selected($filter_status, 'pending', false); ?>>Pending</option>
        <option value="rejected" <?php selected($filter_status, 'rejected', false); ?>>Rejected</option>
    </select>
    <input type="submit" value="Filter" class="button">
</form>

<!-- Bulk Action Form -->
<form method="post">
    <?php wp_nonce_field('affiliate_bulk_action_nonce'); ?>
    <div style="margin-top: 20px;">
        <select name="bulk_action">
            <option value="">Bulk actions</option>
            <option value="delete">Delete</option>
        </select>
        <input type="submit" name="apply_bulk_action" value="Apply" class="button">
    </div>

    <!-- Registrations Table -->
    <table class="widefat fixed striped">
        <thead>
            <tr>
                <th><input type="checkbox" id="select-all"></th>
                <th>Affiliate Coupons</th>
                <th>Username</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($affiliates)) : ?>
                <?php foreach ($affiliates as $affiliate) : ?>
                    <?php
                    $user_info = get_userdata($affiliate->user_id);
                    $username = $user_info ? $user_info->user_login : 'Unknown User';
                    ?>
                    <tr>
                        <td><input type="checkbox" name="affiliate_ids[]" value="<?php echo esc_attr($affiliate->id); ?>"></td>
                        <td><?php echo esc_html($affiliate->coupon_code); ?></td>
                        <td><?php echo esc_html($username); ?></td>
                        <td><?php echo esc_html($affiliate->status); ?></td>
                        <td>
                            <?php if ($affiliate->status === 'pending') : ?>
                                <form method="post" style="display:inline;">
                                    <?php wp_nonce_field('affiliate_action_nonce'); ?>
                                    <input type="hidden" name="affiliate_id" value="<?php echo esc_attr($affiliate->id); ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <input type="submit" value="Approve" class="button button-primary">
                                </form>
                                <form method="post" style="display:inline;">
                                    <?php wp_nonce_field('affiliate_action_nonce'); ?>
                                    <input type="hidden" name="affiliate_id" value="<?php echo esc_attr($affiliate->id); ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <input type="submit" value="Reject" class="button button-secondary">
                                </form>
                            <?php endif; ?>
                            <form method="post" style="display:inline;">
                                <?php wp_nonce_field('affiliate_action_nonce'); ?>
                                <input type="hidden" name="affiliate_id" value="<?php echo esc_attr($affiliate->id); ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="submit" value="Delete" class="button button-link-delete" onclick="return confirm('Are you sure you want to delete this registration?');">
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr><td colspan="5">No affiliate registrations found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</form>


