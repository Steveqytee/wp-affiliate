<?php

class Affiliate_Helpers {

    public static function get_affiliate_user($affiliate_id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}affiliate_registrations WHERE id = %d", $affiliate_id));
    }
    public static function create_affiliate_coupon($user_id, $coupon_code) {
        $amount = '10';
        $discount_type = 'percent';

        $coupon = [
            'post_title' => $coupon_code,
            'post_content' => '',
            'post_status' => 'publish',
            'post_author' => $user_id,
            'post_type' => 'shop_coupon'
        ];

        $new_coupon_id = wp_insert_post($coupon);

        if (is_wp_error($new_coupon_id)) {
            error_log('Failed to create coupon for user ' . $user_id . ': ' . $new_coupon_id->get_error_message());
            return $new_coupon_id;
        }

        update_post_meta($new_coupon_id, 'discount_type', $discount_type);
        update_post_meta($new_coupon_id, 'coupon_amount', $amount);
        update_post_meta($new_coupon_id, 'individual_use', 'no');
        update_post_meta($new_coupon_id, 'usage_limit', '');
        update_post_meta($new_coupon_id, 'expiry_date', '');
        update_post_meta($new_coupon_id, 'apply_before_tax', 'yes');
        update_post_meta($new_coupon_id, 'free_shipping', 'no');

        return $new_coupon_id;
    }


    public static function get_affiliate_registrations($status = '') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'affiliate_registrations';

        $query = "SELECT * FROM $table_name";
        if (!empty($status)) {
            $query .= $wpdb->prepare(" WHERE status = %s", $status);
        }

        return $wpdb->get_results($query);
    }

    public static function approve_affiliate_registration($registration_id) {
        global $wpdb;
        $registration = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}affiliate_registrations WHERE id = %d AND status = 'pending'",
            $registration_id
        ));

        if ($registration) {
            $username_base = sanitize_user($registration->first_name . $registration->last_name);
            $username = $username_base;
            $suffix = 1;
            while (username_exists($username)) {
                $username = $username_base . $suffix;
                $suffix++;
            }

            $user_id = wp_create_user($username, wp_generate_password(), $registration->email);
            if (!is_wp_error($user_id)) {
                update_user_meta($user_id, 'first_name', $registration->first_name);
                update_user_meta($user_id, 'last_name', $registration->last_name);
                update_user_meta($user_id, 'phone', $registration->phone);
                update_user_meta($user_id, 'address', $registration->address);
                update_user_meta($user_id, 'state', $registration->state);

                $user = new WP_User($user_id);
                $user->set_role('affiliate');

                $coupon_id = self::create_affiliate_coupon($user_id, $registration->coupon_code);

                $wpdb->insert(
                    $wpdb->prefix . 'affiliates',
                    [
                        'user_id' => $user_id,
                        'coupon_id' => $coupon_id,
                        'status' => 'accepted',
                        'created_at' => current_time('mysql')
                    ]
                );

                $wpdb->update(
                    $wpdb->prefix . 'affiliate_registrations',
                    ['status' => 'approved'],
                    ['id' => $registration_id]
                );

                echo '<div class="notice notice-success"><p>Affiliate approved successfully.</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>Failed to create user: ' . esc_html($user_id->get_error_message()) . '</p></div>';
            }
        } else {
            echo '<div class="notice notice-error"><p>Invalid registration or already processed.</p></div>';
        }
    }

    public static function reject_affiliate_registration($registration_id) {
        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . 'affiliate_registrations',
            ['status' => 'rejected'],
            ['id' => $registration_id]
        );

        echo '<div class="notice notice-warning"><p>Affiliate registration rejected successfully.</p></div>';
    }

    public static function delete_affiliate_registration($registration_id) {
//        global $wpdb;
//        $table_name = $wpdb->prefix . 'affiliate_registrations';
//        $wpdb->delete($table_name, ['id' => $registration_id], ['%d']);
//        echo '<div class="notice notice-success"><p>Affiliate registration deleted.</p></div>';
    global $wpdb;
    return $wpdb->delete("{$wpdb->prefix}affiliate_registrations", ['id' => $affiliate_id], ['%d']);
    }
    public static function get_affiliate_total_sales($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'affiliate_sales';

        // Query to get the total sales for the given affiliate
        $total_sales = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(sale_amount) FROM $table_name WHERE affiliate_id = %d",
            $user_id
        ));

        return $total_sales !== null ? $total_sales : 0; // Return 0 if no sales data is found
    }

    public static function get_affiliate_coupon_id($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'affiliates';

        // Query to get the coupon ID associated with the given user ID
        $coupon_id = $wpdb->get_var($wpdb->prepare(
            "SELECT coupon_id FROM $table_name WHERE user_id = %d",
            $user_id
        ));

        return $coupon_id !== null ? $coupon_id : ''; // Return an empty string if no coupon ID is found
    }
    public static function get_affiliate_total_commission($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'affiliate_sales';

        return $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(commission) FROM $table_name WHERE affiliate_id = %d",
            $user_id
        ));
    }
    public static function sync_existing_affiliates() {
        global $wpdb;
        $users = get_users(['role' => 'affiliate']);

        foreach ($users as $user) {
            $user_id = $user->ID;
            $coupon_code = 'AFFILIATE-' . $user_id;
            $status = 'accepted';

            $existing_affiliate = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}affiliates WHERE user_id = %d",
                $user_id
            ));

            if (!$existing_affiliate) {
                $coupon_id = self::create_affiliate_coupon($user_id, $coupon_code);

                if (!is_wp_error($coupon_id)) {
                    $wpdb->insert(
                        $wpdb->prefix . 'affiliates',
                        [
                            'user_id' => $user_id,
                            'coupon_id' => $coupon_id,
                            'commission_rate' => 5,
                            'status' => $status,
                            'created_at' => current_time('mysql'),
                        ]
                    );
                } else {
                    error_log("Failed to create coupon for user ID $user_id: " . $coupon_id->get_error_message());
                }
            }
        }
    }

    public static function get_affiliate_name($affiliate_id) {
        global $wpdb;
        $affiliate_name = $wpdb->get_var($wpdb->prepare("
            SELECT display_name
            FROM {$wpdb->prefix}affiliate_registrations
            WHERE id = %d
        ", $affiliate_id));

        return $affiliate_name ? $affiliate_name : 'Unknown Affiliate';
    }

    public static function handle_edit_affiliate($affiliate_id) {
        // Ensure array keys exist before accessing them
        $affiliate_id = isset($_POST['affiliate_id']) ? intval($_POST['affiliate_id']) : $affiliate_id;
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
        $last_name = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
        $coupon_code = isset($_POST['coupon_code']) ? sanitize_text_field($_POST['coupon_code']) : '';
        $custom_msg = isset($_POST['custom_msg']) ? sanitize_textarea_field($_POST['custom_msg']) : '';

        // Prevent processing if required fields are missing
        if (empty($affiliate_id) || empty($email) || empty($first_name) || empty($last_name)) {
            echo '<div class="notice notice-error"><p>Required fields are missing. Please fill all fields.</p></div>';
            return;
        }

        // Update user information
        wp_update_user([
            'ID' => $affiliate_id,
            'user_email' => $email,
            'first_name' => $first_name,
            'last_name' => $last_name
        ]);

        // Update affiliate-specific information
        global $wpdb;
        $wpdb->update(
            "{$wpdb->prefix}affiliates",
            [
                'coupon_code' => $coupon_code,
                'custom_msg' => $custom_msg
            ],
            ['user_id' => $affiliate_id],
            ['%s', '%s'],
            ['%d']
        );

        // Return success message
        echo '<div class="notice notice-success"><p>Affiliate updated successfully.</p></div>';
    }
    public static function delete_affiliate_handler() {
    $affiliate_id = intval($_REQUEST['affiliate_id']);
    if ( ! current_user_can( 'delete_users' ) ) {
        wp_send_json_error( array( 'message' => 'You are not allowed to delete users.' ) );
        return;
    }

    // 在此处添加删除 affiliate 的逻辑
    if ($affiliate_id) {
        // 删除逻辑，比如：wp_delete_user($affiliate_id);
        // 或者删除你自定义表中的记录
        $deleted = delete_user($affiliate_id); // 示例代码

        if ($deleted) {
            wp_send_json_success();
        } else {
            wp_send_json_error();
        }
    }
    wp_send_json_error();
}
    // 提取获取 affiliate_id 的方法
    public static function get_affiliate_id($request) {
        if (isset($request['affiliate_id'])) {
            return intval($request['affiliate_id']);
        }
        return 0;
    }
    public static function validate_affiliate_data($data) {
        $errors = [];

        if (empty($data['email']) || !is_email($data['email'])) {
            $errors['email'] = 'Valid email address is required.';
        }

        if (empty($data['first_name'])) {
            $errors['first_name'] = 'First name is required.';
        }

        if (empty($data['last_name'])) {
            $errors['last_name'] = 'Last name is required.';
        }

        // 其他需要的校验
        return $errors;
    }
    public static function update_affiliate_status($affiliate_id, $status) {
        global $wpdb;
        return $wpdb->update(
            "{$wpdb->prefix}affiliate_registrations",
            ['status' => $status],
            ['id' => $affiliate_id],
            ['%s'],
            ['%d']
        );
    }
}
