<?php

class Affiliate_Handlers {

    public static function handle_bulk_actions() {
        if (!empty($_POST['selected_affiliates'])) {
            global $wpdb;

            foreach ($_POST['selected_affiliates'] as $user_id) {
                // 获取关联的优惠券ID
                $affiliate = $wpdb->get_row($wpdb->prepare("SELECT coupon_id FROM {$wpdb->prefix}affiliates WHERE user_id = %d", $user_id));
                if ($affiliate) {
                    // 删除优惠券
                    wp_delete_post($affiliate->coupon_id, true);

                    // 删除Affiliate记录
                    $wpdb->delete($wpdb->prefix . 'affiliates', ['user_id' => $user_id]);

                    // 可选：删除用户
                    // wp_delete_user($user_id);
                }
            }
            echo '<div class="notice notice-success is-dismissible"><p>Selected affiliates have been deleted.</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>No affiliates selected.</p></div>';
        }
    }
   // public static function render_edit_affiliate_page() {
   //     if (!isset($_GET['user_id'])) {
   //         return;
   //     }
//
   //     $user_id = intval($_GET['user_id']);
   //     $affiliate = Affiliate_Registration::get_affiliate_by_user_id($user_id);
//
   //     if ($_POST['update_affiliate']) {
   //         // Handle the form submission
   //         self::update_affiliate_details($user_id);
   //     }
//
   //     include MY_AFFILIATE_PLUGIN_DIR . 'templates/edit-affiliate-form.php';
   // }
   public static function render_edit_affiliate_page() {
    global $wpdb;
    $affiliate_id = isset($_GET['affiliate_id']) ? intval($_GET['affiliate_id']) : 0;
    $affiliate = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}affiliates WHERE user_id = $affiliate_id");

    if ($affiliate) {
        include MY_AFFILIATE_PLUGIN_DIR . 'templates/edit-affiliate-form.php';
    } else {
        echo '<div class="notice notice-error"><p>Affiliate not found.</p></div>';
    }
    }
    public static function handle_create_affiliate() {
        if (isset($_POST['create_affiliate'])) {
            global $wpdb;

            // Step 1: Create the affiliate user
            $username = sanitize_user($_POST['username']);
            $email = sanitize_email($_POST['email']);
            $first_name = sanitize_text_field($_POST['first_name']);
            $coupon_code = sanitize_text_field($_POST['coupon_code']);
            $custom_msg = sanitize_textarea_field($_POST['custom_msg']);

            // Generate a random password for the new user
            $password = wp_generate_password();
            $user_id = wp_create_user($username, $password, $email);

            if (is_wp_error($user_id)) {
                return $user_id; // Return the error to display it to the admin
            }

            // Step 2: Update user meta
            wp_update_user([
                'ID' => $user_id,
                'first_name' => $first_name,
            ]);

            // Step 3: Save affiliate details in custom affiliate table
            $affiliate_data = [
                'user_id' => $user_id,
                'coupon_code' => $coupon_code,
                'custom_msg' => $custom_msg,
                'created_at' => current_time('mysql'),
            ];

            $wpdb->insert("{$wpdb->prefix}affiliates", $affiliate_data);
            $affiliate_id = $wpdb->insert_id;

            // Step 4: Handle commission by product
            if (isset($_POST['product_id'])) {
                $product_ids = $_POST['product_id'];
                $min_quantities = $_POST['product_min_quantity'];
                $commission_types = $_POST['product_commission_type'];
                $commission_values = $_POST['product_commission_value'];

                foreach ($product_ids as $index => $product_id) {
                    if (!empty($product_id)) {
                        $commission_data = [
                            'affiliate_id' => $affiliate_id,
                            'product_id' => intval($product_id),
                            'min_quantity' => intval($min_quantities[$index]),
                            'commission_type' => sanitize_text_field($commission_types[$index]),
                            'commission_value' => floatval($commission_values[$index]),
                        ];
                        $wpdb->insert("{$wpdb->prefix}affiliate_product_commissions", $commission_data);
                    }
                }
            }

            // Step 5: Handle commission by order value
            if (isset($_POST['order_value_threshold'])) {
                $order_value_threshold = floatval($_POST['order_value_threshold']);
                $order_commission_type_below = sanitize_text_field($_POST['order_commission_type_below']);
                $order_commission_value_below = floatval($_POST['order_commission_value_below']);
                $order_commission_type_above = sanitize_text_field($_POST['order_commission_type_above']);
                $order_commission_value_above = floatval($_POST['order_commission_value_above']);

                $order_commission_data = [
                    'affiliate_id' => $affiliate_id,
                    'order_value_threshold' => $order_value_threshold,
                    'commission_type_below' => $order_commission_type_below,
                    'commission_value_below' => $order_commission_value_below,
                    'commission_type_above' => $order_commission_type_above,
                    'commission_value_above' => $order_commission_value_above,
                ];
                $wpdb->insert("{$wpdb->prefix}affiliate_order_commissions", $order_commission_data);
            }

            // Step 6: Handle commission by quantity sold
            if (isset($_POST['quantity_min'])) {
                $min_quantities = $_POST['quantity_min'];
                $commission_types = $_POST['quantity_commission_type'];
                $commission_values = $_POST['quantity_commission_value'];
                $custom_msgs = $_POST['quantity_custom_msg'];

                foreach ($min_quantities as $index => $min_quantity) {
                    if (!empty($min_quantity)) {
                        $quantity_commission_data = [
                            'affiliate_id' => $affiliate_id,
                            'min_quantity' => intval($min_quantity),
                            'commission_type' => sanitize_text_field($commission_types[$index]),
                            'commission_value' => floatval($commission_values[$index]),
                            'custom_msg' => sanitize_textarea_field($custom_msgs[$index]),
                        ];
                        $wpdb->insert("{$wpdb->prefix}affiliate_quantity_commissions", $quantity_commission_data);
                    }
                }
            }

            return true; // Indicate success
        }
    }


    private static function update_affiliate_details($user_id) {
        // Process the form data and update the affiliate details
        $coupon_code = sanitize_text_field($_POST['coupon_code']);
        $custom_msg = sanitize_textarea_field($_POST['custom_msg']);
        $commission_rate = floatval($_POST['commission_rate']);

        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . 'affiliates',
            [
                'coupon_code' => $coupon_code,
                'custom_msg' => $custom_msg,
                'commission_rate' => $commission_rate
            ],
            ['user_id' => $user_id],
            ['%s', '%s', '%f'],
            ['%d']
        );
        wp_redirect(admin_url('admin.php?page=affiliate-management'));
    exit;

    }

    public static function handle_edit_affiliate($affiliate_id) {
        global $wpdb;

        // Log the affiliate ID being edited
        error_log("Editing affiliate ID: " . $affiliate_id);

        if (isset($_POST['update_affiliate'])) {
            // If the form is submitted, update the affiliate information
            $email = sanitize_email($_POST['email']);
            $first_name = sanitize_text_field($_POST['first_name']);
            $last_name = sanitize_text_field($_POST['last_name']);
            $coupon_code = sanitize_text_field($_POST['coupon_code']);
            $commission_type = sanitize_text_field($_POST['commission_type_select']);
            $custom_msg = sanitize_textarea_field($_POST['custom_msg']);

            // Update user details in wp_users
            wp_update_user([
                'ID' => $affiliate_id,
                'user_email' => $email,
                'first_name' => $first_name,
                'last_name' => $last_name,
            ]);

            // Update affiliate details in your custom affiliate table
            $wpdb->update(
                "{$wpdb->prefix}affiliates",
                [
                    'coupon_code' => $coupon_code,
                    'commission_type' => $commission_type,
                    'custom_msg' => $custom_msg,
                ],
                ['user_id' => $affiliate_id],
                ['%s', '%s', '%s'], // format specifiers for the updated fields
                ['%d']
            );

            // Display a success message
            echo '<div class="notice notice-success"><p>Affiliate updated successfully.</p></div>';
        } else {
            // Fetch existing data for the affiliate
            $affiliate = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}affiliates WHERE user_id = %d", $affiliate_id));

            if ($affiliate) {
                // Include or display the edit form
                include MY_AFFILIATE_PLUGIN_DIR . 'templates/edit-affiliate-form.php';
            } else {
                echo '<div class="notice notice-error"><p>Affiliate not found.</p></div>';
            }
        }
    }

    // 添加这个方法来处理新增 affiliate 的表单提交
    public static function handle_add_affiliate($data) {
        global $wpdb;

        // Sanitize and validate input data
        $username = sanitize_text_field($data['username']);
        $email = sanitize_email($data['email']);
        $first_name = sanitize_text_field($data['first_name']);
        $last_name = sanitize_text_field($data['last_name']);
        $coupon_code = sanitize_text_field($data['coupon_code']);
        $custom_msg = sanitize_textarea_field($data['custom_msg']);

        // Create the user
        $user_id = wp_create_user($username, wp_generate_password(), $email);

        if (is_wp_error($user_id)) {
            return $user_id; // Return the error if user creation failed
        }

        // Update user meta with first and last name
        wp_update_user([
            'ID' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name
        ]);

        // Insert affiliate data into the affiliates table
        $table_name = $wpdb->prefix . 'affiliates';
        $result = $wpdb->insert(
            $table_name,
            [
                'user_id' => $user_id,
                'coupon_id' => 0,  // You might want to update this with a real coupon ID
                'commission_rate' => 5.00,  // Default commission rate
                'custom_msg' => $custom_msg,
                'created_at' => current_time('mysql')
            ]
        );

        if ($result === false) {
            return new WP_Error('db_error', 'Failed to insert affiliate data into the database.');
        }

        return $user_id; // Return the user ID on success
    }
    private static function reject_affiliate($affiliate_id) {
        return Affiliate_Helpers::update_affiliate_status($affiliate_id, 'rejected');
    }

    private static function delete_affiliate($affiliate_id) {
        return Affiliate_Helpers::delete_affiliate_registration($affiliate_id);
    }
    public static function handle_form_submission($post_data) {
        if (!isset($post_data['affiliate_nonce']) || !wp_verify_nonce($post_data['affiliate_nonce'], 'affiliate_action')) {
            return new WP_Error('nonce_verification_failed', 'Nonce verification failed.');
        }

        $affiliate_id = intval($post_data['affiliate_id']);
        $action = sanitize_text_field($post_data['action']);

        if ($action === 'approve') {
            return self::approve_affiliate($affiliate_id);
        } elseif ($action === 'reject') {
            return self::reject_affiliate($affiliate_id);
        } elseif ($action === 'delete') {
            return self::delete_affiliate($affiliate_id);
        }

        return new WP_Error('unknown_action', 'Unknown action specified.');
    }
    private static function approve_affiliate($affiliate_id) {
        Affiliate_Helpers::update_affiliate_status($affiliate_id, 'accepted');
        $affiliate = Affiliate_Helpers::get_affiliate_user($affiliate_id);

        // 逻辑: 创建用户并生成优惠券
        $user_id = wp_create_user($affiliate->email, wp_generate_password(), $affiliate->email);
        if (!is_wp_error($user_id)) {
            $coupon_id = Affiliate_Helpers::create_affiliate_coupon($user_id, $affiliate->coupon_code);
            global $wpdb;
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
        return true;
    }
}

