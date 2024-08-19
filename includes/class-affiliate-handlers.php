<?php

class Affiliate_Handlers {

    // 常量定义表名
    const AFFILIATE_TABLE = 'affiliates';
    const PRODUCT_COMMISSION_TABLE = 'affiliate_product_commissions';
    const ORDER_COMMISSION_TABLE = 'affiliate_order_commissions';
    const QUANTITY_COMMISSION_TABLE = 'affiliate_quantity_commissions';



    /**
     * 处理批量操作
     */

     public static function handle_bulk_actions() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to perform this action.'));
        }

        if (!empty($_POST['selected_affiliates'])) {
            global $wpdb;

            foreach ($_POST['selected_affiliates'] as $user_id) {
                $affiliate = $wpdb->get_row($wpdb->prepare("SELECT coupon_id FROM {$wpdb->prefix}" . self::AFFILIATE_TABLE . " WHERE user_id = %d", $user_id));
                if ($affiliate) {
                    wp_delete_post($affiliate->coupon_id, true);
                    $wpdb->delete($wpdb->prefix . self::AFFILIATE_TABLE, ['user_id' => $user_id]);
                }
            }

            echo '<div class="notice notice-success is-dismissible"><p>Selected affiliates have been deleted.</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>No affiliates selected.</p></div>';
        }
    }

    /**
     * 处理创建新的 affiliate
     */


     public static function handle_create_affiliate() {
        if (isset($_POST['create_affiliate'])) {
            global $wpdb;

            // Sanitize input data
            $username = sanitize_user($_POST['username']);
            $email = sanitize_email($_POST['email']);
            $first_name = sanitize_text_field($_POST['first_name']);
            $last_name = sanitize_text_field($_POST['last_name']);
            $coupon_code = sanitize_text_field($_POST['coupon_code']);
            $custom_msg = sanitize_textarea_field($_POST['custom_msg']);

            // Generate a password and create the user
            $password = wp_generate_password();
            $user_id = wp_create_user($username, $password, $email);

            // Handle user creation error
            if (is_wp_error($user_id)) {
                return $user_id; // Return the error to display it to the admin
            }

            // Update user meta with first and last name
            wp_update_user([
                'ID' => $user_id,
                'first_name' => $first_name,
                'last_name' => $last_name,
            ]);

            // Get commission settings from the settings page
            $commission_settings = Affiliate_Settings::get_commission_settings();
    // Generate WooCommerce coupon and get its ID
            $coupon_id = Affiliate_Helpers::create_affiliate_coupon($user_id, $coupon_code);

            // Insert affiliate data into the custom affiliate_user table
            $affiliate_data = [
                'user_id' => $user_id,
                'coupon_id' => 0, // You might want to update this with a real coupon ID later
                'commission_type' => $commission_settings['type'] ?? 'order', // Default to order if not set
                'commission_rate' => 5.00, // Default commission rate, adjust as needed
                'custom_msg' => $custom_msg,
                'status' => 'active',
                'created_at' => current_time('mysql'),
            ];

            $wpdb->insert($wpdb->prefix . 'affiliate', $affiliate_data);
            $affiliate_id = $wpdb->insert_id;

            // Handle commission by product, order, or quantity using settings
            self::handle_commission_by_product($affiliate_id, $commission_settings);
            self::handle_commission_by_order($affiliate_id, $commission_settings);
            self::handle_commission_by_quantity($affiliate_id, $commission_settings);

            return true; // Indicate success
        }
    }

    /**
     * 处理产品佣金
     */
    private static function handle_commission_by_product($affiliate_id, $post_data = null) {
        global $wpdb;

        // Retrieve global or individual settings
        $commission_settings = Affiliate_Settings::get_commission_settings();
        $product_ids = $post_data['product_id'] ?? $commission_settings['product_id'];
        $min_quantities = $post_data['product_min_quantity'] ?? $commission_settings['quantity_below'];
        $commission_types = $post_data['product_commission_type'] ?? $commission_settings['commission_type'];
        $commission_values = $post_data['product_commission_value'] ?? $commission_settings['commission_value_below'];

        if ($product_ids) {
            foreach ($product_ids as $index => $product_id) {
                if (!empty($product_id)) {
                    $commission_data = [
                        'affiliate_id' => $affiliate_id,
                        'product_id' => intval($product_id),
                        'min_quantity' => intval($min_quantities[$index] ?? 1),
                        'commission_type' => sanitize_text_field($commission_types[$index] ?? 'fixed'),
                        'commission_value' => floatval($commission_values[$index] ?? 0),
                    ];
                    $wpdb->insert($wpdb->prefix . self::PRODUCT_COMMISSION_TABLE, $commission_data);
                }
            }
        }
    }

    /**
     * 处理订单金额佣金
     */
    private static function handle_commission_by_order($affiliate_id, $post_data = null) {
        global $wpdb;

        // Retrieve global or individual settings
        $commission_settings = Affiliate_Settings::get_commission_settings();

        $order_value_threshold = $post_data['order_value_threshold'] ?? $commission_settings['order_value_threshold'];
        $commission_type_below = $post_data['order_commission_type_below'] ?? $commission_settings['order_commission_type_below'];
        $commission_value_below = $post_data['order_commission_value_below'] ?? $commission_settings['order_commission_value_below'];
        $commission_type_above = $post_data['order_commission_type_above'] ?? $commission_settings['order_commission_type_above'];
        $commission_value_above = $post_data['order_commission_value_above'] ?? $commission_settings['order_commission_value_above'];

        if ($order_value_threshold) {
            $order_commission_data = [
                'affiliate_id' => $affiliate_id,
                'order_value_threshold' => floatval($order_value_threshold),
                'commission_type_below' => sanitize_text_field($commission_type_below),
                'commission_value_below' => floatval($commission_value_below),
                'commission_type_above' => sanitize_text_field($commission_type_above),
                'commission_value_above' => floatval($commission_value_above),
            ];
            $wpdb->insert($wpdb->prefix . self::ORDER_COMMISSION_TABLE, $order_commission_data);
        }
    }


    /**
     * 处理数量佣金
     */

     private static function handle_commission_by_quantity($affiliate_id, $post_data = null) {
        global $wpdb;

        // Retrieve global or individual settings
        $commission_settings = Affiliate_Settings::get_commission_settings();

        $min_quantities = $post_data['quantity_min'] ?? $commission_settings['quantity_below'];
        $commission_types = $post_data['quantity_commission_type'] ?? $commission_settings['quantity_commission_type'];
        $commission_values = $post_data['quantity_commission_value'] ?? $commission_settings['quantity_commission_value_below'];
        $custom_msgs = $post_data['quantity_custom_msg'] ?? $commission_settings['custom_msg'];

        if ($min_quantities) {
            foreach ($min_quantities as $index => $min_quantity) {
                if (!empty($min_quantity)) {
                    $quantity_commission_data = [
                        'affiliate_id' => $affiliate_id,
                        'min_quantity' => intval($min_quantity),
                        'commission_type' => sanitize_text_field($commission_types[$index] ?? 'fixed'),
                        'commission_value' => floatval($commission_values[$index] ?? 0),
                        'custom_msg' => sanitize_textarea_field($custom_msgs[$index] ?? ''),
                    ];
                    $wpdb->insert($wpdb->prefix . self::QUANTITY_COMMISSION_TABLE, $quantity_commission_data);
                }
            }
        }
    }


    /**
     * 处理编辑 affiliate
     */
    public static function handle_edit_affiliate($affiliate_id) {
        global $wpdb;

        if (isset($_POST['update_affiliate'])) {
            // Sanitize input data
            $email = sanitize_email($_POST['email']);
            $first_name = sanitize_text_field($_POST['first_name']);
            $last_name = sanitize_text_field($_POST['last_name']);
            $coupon_code = sanitize_text_field($_POST['coupon_code']);
            $commission_type = sanitize_text_field($_POST['commission_type_select']);
            $custom_msg = sanitize_textarea_field($_POST['custom_msg']);
            $status = sanitize_text_field($_POST['status']);

            // Update user details in wp_users table
            wp_update_user([
                'ID' => $affiliate_id,
                'user_email' => $email,
                'first_name' => $first_name,
                'last_name' => $last_name,
            ]);

            // Update affiliate details in the affiliate_user table
            $wpdb->update(
                $wpdb->prefix . 'affiliate_user',
                [
                    'coupon_id' => 0, // Update with the correct coupon ID if needed
                    'commission_type' => $commission_type,
                    'custom_msg' => $custom_msg,
                    'status' => $status,  // Updating status, if needed
                ],
                ['user_id' => $affiliate_id],
                ['%d', '%s', '%s', '%s'],  // Data types for corresponding fields
                ['%d'] // WHERE condition type
            );

            echo '<div class="notice notice-success"><p>Affiliate updated successfully.</p></div>';
        } else {
            // Fetch existing data for the affiliate
            $affiliate = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}affiliate_user WHERE user_id = %d", $affiliate_id));

            if ($affiliate) {
                include MY_AFFILIATE_PLUGIN_DIR . 'templates/edit-affiliate-form.php';
            } else {
                echo '<div class="notice notice-error"><p>Affiliate not found.</p></div>';
            }
        }
    }

    /**
     * 处理表单提交
     */
    public static function handle_form_submission($post_data) {
        if (!isset($post_data['affiliate_nonce']) || !wp_verify_nonce($post_data['affiliate_nonce'], 'affiliate_action')) {
            return new WP_Error('nonce_verification_failed', 'Nonce verification failed.');
        }

        $affiliate_id = intval($post_data['affiliate_id']);
        $action = sanitize_text_field($post_data['action']);

        switch ($action) {
            case 'approve':
                return self::approve_affiliate($affiliate_id);
            case 'reject':
                return self::reject_affiliate($affiliate_id);
            case 'delete':
                return self::delete_affiliate($affiliate_id);
            default:
                return new WP_Error('unknown_action', 'Unknown action specified.');
        }
    }

    private static function approve_affiliate($affiliate_id) {
        Affiliate_Helpers::update_affiliate_status($affiliate_id, 'accepted');
        $affiliate = Affiliate_Helpers::get_affiliate_user($affiliate_id);

        $user_id = wp_create_user($affiliate->email, wp_generate_password(), $affiliate->email);
        if (!is_wp_error($user_id)) {
            $coupon_id = Affiliate_Helpers::create_affiliate_coupon($user_id, $affiliate->coupon_code);
            global $wpdb;
            $wpdb->insert(
                "{$wpdb->prefix}" . self::AFFILIATE_TABLE,
                [
                    'user_id' => $user_id,
                    'coupon_id' => $coupon_id,
                    'status' => 'active',
                    'created_at' => current_time('mysql')
                ]
            );
        }
        return true;
    }

    private static function reject_affiliate($affiliate_id) {
        return Affiliate_Helpers::update_affiliate_status($affiliate_id, 'rejected');
    }

    private static function delete_affiliate($affiliate_id) {
        return Affiliate_Helpers::delete_affiliate_registration($affiliate_id);
    }



}
