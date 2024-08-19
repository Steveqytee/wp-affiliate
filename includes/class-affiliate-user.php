<?php
class Affiliate_User {

// Retrieve affiliate details by affiliate ID

    public static function get_affiliate_details($identifier, $by_coupon = false) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'affiliates';

        if ($by_coupon) {
            $query = $wpdb->prepare("SELECT * FROM $table_name WHERE coupon_code = %s", $identifier);
        } else {
            $query = $wpdb->prepare("SELECT * FROM $table_name WHERE user_id = %d", $identifier);
        }

        return $wpdb->get_row($query);
    }



// Retrieve affiliate details by WordPress user ID
public static function get_affiliate_by_user_id($user_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'affiliates';
    $query = $wpdb->prepare("SELECT * FROM $table_name WHERE user_id = %d", $user_id);
    return $wpdb->get_row($query);
}

// Create a new affiliate user
public static function create_affiliate($user_id, $coupon_id, $commission_rate = 5.00, $custom_msg = '') {
    global $wpdb;
    $table_name = $wpdb->prefix . 'affiliates';
    $data = [
        'user_id' => $user_id,
        'coupon_id' => $coupon_id,
        'commission_rate' => floatval($commission_rate),
        'custom_msg' => sanitize_textarea_field($custom_msg),
        'created_at' => current_time('mysql')
    ];
    $wpdb->insert($table_name, $data);
    return $wpdb->insert_id;
}

// Update affiliate details
public static function update_affiliate($affiliate_id, $data) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'affiliates';

    // Ensure data is sanitized and validated
    $update_data = [];
    if (isset($data['coupon_id'])) $update_data['coupon_id'] = intval($data['coupon_id']);
    if (isset($data['commission_rate'])) $update_data['commission_rate'] = floatval($data['commission_rate']);
    if (isset($data['custom_msg'])) $update_data['custom_msg'] = sanitize_textarea_field($data['custom_msg']);
    if (isset($data['status'])) $update_data['status'] = sanitize_text_field($data['status']);

    if (!empty($update_data)) {
        $wpdb->update($table_name, $update_data, ['id' => $affiliate_id]);
        return true;
    }
    return false;
}

// Delete an affiliate
public static function delete_affiliate($affiliate_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'affiliates';
    return $wpdb->delete($table_name, ['id' => $affiliate_id], ['%d']);
}

// Retrieve all affiliates
public static function get_all_affiliates() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'affiliates';
    return $wpdb->get_results("SELECT * FROM $table_name");
}

// Get commissions for an affiliate
public static function get_affiliate_commissions($affiliate_id) {
    global $wpdb;
    $product_table = $wpdb->prefix . 'affiliate_product_commissions';
    $order_table = $wpdb->prefix . 'affiliate_order_commissions';
    $quantity_table = $wpdb->prefix . 'affiliate_quantity_commissions';

    $commissions = [
        'product' => $wpdb->get_results($wpdb->prepare("SELECT * FROM $product_table WHERE affiliate_id = %d", $affiliate_id)),
        'order' => $wpdb->get_results($wpdb->prepare("SELECT * FROM $order_table WHERE affiliate_id = %d", $affiliate_id)),
        'quantity' => $wpdb->get_results($wpdb->prepare("SELECT * FROM $quantity_table WHERE affiliate_id = %d", $affiliate_id))
    ];

    return $commissions;
}

// Get all sales for an affiliate
public static function get_affiliate_sales($affiliate_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'affiliate_sales';
    return $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE affiliate_id = %d", $affiliate_id));
}
}
