<?php

class Affiliate_Admin {

    public static function add_menu() {
        add_menu_page(
            'Affiliate Dashboard',
            'Affiliate Dashboard',
            'manage_options',
            'affiliate-dashboard',
            ['Affiliate_Dashboard', 'render_dashboard'],
            'dashicons-chart-line',
            7
        );
        add_submenu_page(
            'affiliate-dashboard',
            'Top Affiliates',
            'Top Affiliates',
            'manage_options',
            'top-affiliates',
            [self::class, 'render_top_affiliates']
        );
        add_submenu_page(
            'affiliate-dashboard',
            'Affiliate User',
            'Affiliate User',
            'manage_options',
            'affiliate-management',
            [self::class, 'render_affiliate_management']
        );
        add_submenu_page(
            'affiliate-dashboard',
            'Affiliate Registrations',
            'Affiliate Registrations',
            'manage_options',
            'affiliate-registrations',
            [self::class, 'render_affiliate_registrations']
        );
        add_submenu_page(
            'affiliate-dashboard',
            'Add New Affiliate',
            'Add New Affiliate',
            'manage_options',
            'add-new-affiliate',
            [self::class, 'render_add_new_affiliate']
        );
        add_submenu_page(
            'affiliate-dashboard',
            'Affiliate Statistics',
            'Statistics',
            'manage_options',
            'affiliate-statistics',
            ['Affiliate_Statistics', 'render_statistics_page']
        );
        add_submenu_page(
            'affiliate-dashboard',
            'Affiliate Settings',
            'Settings',
            'manage_options',
            'affiliate-settings',
            ['Affiliate_Settings', 'render_settings_page']
        );
        add_submenu_page(
            null, // Parent slug, `null` means no visible parent menu
            'Edit Affiliate', // Page title
            'Edit Affiliate', // Menu title
            'manage_options', // Capability
            'edit-affiliate', // Menu slug
            'Affiliate_Handlers::render_edit_affiliate_page' // Callback function
        );
    }

    public static function render_dashboard() {
        include MY_AFFILIATE_PLUGIN_DIR . 'templates/affiliate-dashboard.php';
    }

    public static function render_top_affiliates() {
        global $wpdb;

        // Query to get overall performance
        $overall_performance = $wpdb->get_row("
            SELECT
                COUNT(DISTINCT affiliate_id) as total_affiliates,
                SUM(sales_count) as overall_sales,
                SUM(total_revenue) as overall_revenue,
                SUM(commission_earned) as overall_commission,
                AVG(conversion_rate) as avg_conversion_rate,
                MAX(last_sale_date) as last_sale_date
            FROM {$wpdb->prefix}affiliate_performance
        ");
        include MY_AFFILIATE_PLUGIN_DIR . 'templates/affiliate-performance.php';
    }

    public static function render_affiliate_management() {
        // 调用页面渲染函数
        include MY_AFFILIATE_PLUGIN_DIR . 'templates/affiliate-management.php';
    }

    public static function render_affiliate_registrations() {
        echo Affiliate_Registration::render_registration_list();
        //include MY_AFFILIATE_PLUGIN_DIR . 'templates/register-list.php';
    }


    public static function render_affiliate_statistics() {
        include MY_AFFILIATE_PLUGIN_DIR . 'templates/affiliate-performance.php';
    }

    public static function render_affiliate_settings() {
        include MY_AFFILIATE_PLUGIN_DIR . 'templates/settings-page.php';
    }

    public static function render_add_new_affiliate() {
        if (isset($_POST['create_affiliate'])) {
            // Sanitize and retrieve form data
            $username = sanitize_text_field($_POST['username']);
            $email = sanitize_email($_POST['email']);
            $first_name = sanitize_text_field($_POST['first_name']);
            $last_name = sanitize_text_field($_POST['last_name']);
            $coupon_code = sanitize_text_field($_POST['coupon_code']);
            $custom_msg = sanitize_textarea_field($_POST['custom_msg']);

            // Create the affiliate user
            $result = self::create_affiliate_user($username, $email, $first_name, $last_name, $coupon_code, $custom_msg);

            if (is_wp_error($result)) {
                // Pass the error to the template if user creation fails
                include MY_AFFILIATE_PLUGIN_DIR . 'templates/add-affiliate-form.php';
                return;
            }

            // Handle commission settings
            if ($_POST['commission_type_select'] === 'product') {
                self::save_product_commission_settings($result);
            } elseif ($_POST['commission_type_select'] === 'order') {
                self::save_order_commission_settings($result);
            } elseif ($_POST['commission_type_select'] === 'quantity') {
                self::save_quantity_commission_settings($result);
            }

            // Pass success message or further data to the template
            echo '<div class="notice notice-success"><p>Affiliate created successfully!</p></div>';

            include MY_AFFILIATE_PLUGIN_DIR . 'templates/add-affiliate-form.php';
        } else {
            // If the form is not submitted, just display the form
            include MY_AFFILIATE_PLUGIN_DIR . 'templates/add-affiliate-form.php';
        }
    }

    private static function create_affiliate_user($username, $email, $first_name, $last_name, $coupon_code, $custom_msg) {
        // 检查用户名是否存在
        if (username_exists($username)) {
            return new WP_Error('username_exists', 'Username already exists.');
        }

        // 检查邮箱是否存在
        if (email_exists($email)) {
            return new WP_Error('email_exists', 'Email already exists.');
        }

        $user_id = wp_create_user($username, wp_generate_password(), $email);
        if (is_wp_error($user_id)) {
            return $user_id; // 返回错误对象
        }

        wp_update_user([
            'ID' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name, // 保存姓氏
        ]);

        $coupon_id = Affiliate_Helpers::create_affiliate_coupon($user_id, $coupon_code);
        if (is_wp_error($coupon_id)) {
            return $coupon_id; // 返回错误对象
        }

        global $wpdb;
        $result = $wpdb->insert(
            $wpdb->prefix . 'affiliates',
            [
                'user_id' => $user_id,
                'coupon_id' => $coupon_id,
                'commission_rate' => 5,
                'created_at' => current_time('mysql'),
                'custom_msg' => $custom_msg
            ]
        );

        if ($result === false) {
            return new WP_Error('db_insert_error', 'Failed to insert affiliate into the database.');
        }

        // Save commission settings based on the selected type
        $affiliate_id = $wpdb->insert_id;

        // Save commission by product
        if ($_POST['commission_type_select'] === 'product') {
            self::save_product_commission_settings($affiliate_id);
        }

        // Save commission by order value
        elseif ($_POST['commission_type_select'] === 'order') {
            self::save_order_commission_settings($affiliate_id);
        }

        // Save commission by quantity sold
        elseif ($_POST['commission_type_select'] === 'quantity') {
            self::save_quantity_commission_settings($affiliate_id);
        }

        return true; // 成功时返回 true
    }
    private static function save_product_commission_settings($affiliate_id) {
        global $wpdb;

        $product_ids = $_POST['product_id'];
        $quantities_below = $_POST['product_quantity_below'];
        $quantities_above = $_POST['product_quantity_above'];
        $commission_types = $_POST['product_commission_type'];
        $commission_values_below = $_POST['product_commission_value_below'];
        $commission_values_above = $_POST['product_commission_value_above'];

        foreach ($product_ids as $index => $product_id) {
            $wpdb->insert(
                $wpdb->prefix . 'affiliate_product_commissions',
                [
                    'affiliate_id' => $affiliate_id,
                    'product_id' => intval($product_id),
                    'quantity_below' => intval($quantities_below[$index]),
                    'quantity_above' => intval($quantities_above[$index]),
                    'commission_type' => sanitize_text_field($commission_types[$index]),
                    'commission_value_below' => floatval($commission_values_below[$index]),
                    'commission_value_above' => floatval($commission_values_above[$index]),
                ]
            );
        }
    }

    private static function save_order_commission_settings($affiliate_id) {
        global $wpdb;

        $order_value_threshold = floatval($_POST['order_value_threshold']);
        $commission_type_below = sanitize_text_field($_POST['order_commission_type_below']);
        $commission_value_below = floatval($_POST['order_commission_value_below']);
        $commission_type_above = sanitize_text_field($_POST['order_commission_type_above']);
        $commission_value_above = floatval($_POST['order_commission_value_above']);

        $wpdb->insert(
            $wpdb->prefix . 'affiliate_order_commissions',
            [
                'affiliate_id' => $affiliate_id,
                'order_value_threshold' => $order_value_threshold,
                'commission_type_below' => $commission_type_below,
                'commission_value_below' => $commission_value_below,
                'commission_type_above' => $commission_type_above,
                'commission_value_above' => $commission_value_above,
            ]
        );
    }

    private static function save_quantity_commission_settings($affiliate_id) {
        global $wpdb;

        $quantities_below = $_POST['quantity_below'];
        $quantities_above = $_POST['quantity_above'];
        $commission_types = $_POST['quantity_commission_type'];
        $commission_values_below = $_POST['quantity_commission_value_below'];
        $commission_values_above = $_POST['quantity_commission_value_above'];
        $custom_msgs = $_POST['quantity_custom_msg'];

        foreach ($quantities_below as $index => $quantity_below) {
            $wpdb->insert(
                $wpdb->prefix . 'affiliate_quantity_commissions',
                [
                    'affiliate_id' => $affiliate_id,
                    'quantity_below' => intval($quantity_below),
                    'quantity_above' => intval($quantities_above[$index]),
                    'commission_type' => sanitize_text_field($commission_types[$index]),
                    'commission_value_below' => floatval($commission_values_below[$index]),
                    'commission_value_above' => floatval($commission_values_above[$index]),
                    'custom_msg' => sanitize_textarea_field($custom_msgs[$index]),
                ]
            );
        }
    }
    public static function render_registration_list() {
        if (isset($_POST['action']) && isset($_POST['affiliate_id'])) {
            $affiliate_id = intval($_POST['affiliate_id']);
            $action = sanitize_text_field($_POST['action']);
            self::handle_registration_action($affiliate_id, $action);
        }

        $filter_status = isset($_GET['filter_status']) ? sanitize_text_field($_GET['filter_status']) : '';

        $affiliates = Affiliate_Helpers::get_affiliate_registrations($filter_status);

        ob_start();
        include MY_AFFILIATE_PLUGIN_DIR . 'templates/registration-list.php';
        return ob_get_clean();
    }

    public static function handle_registration_action($registration_id, $action) {
        switch ($action) {
            case 'approve':
                Affiliate_Helpers::approve_affiliate_registration($registration_id);
                break;

            case 'reject':
                Affiliate_Helpers::reject_affiliate_registration($registration_id);
                break;

            case 'delete':
                Affiliate_Helpers::delete_affiliate_registration($registration_id);
                break;

            default:
                echo '<div class="notice notice-error"><p>Invalid action.</p></div>';
                break;
        }
    }





}


add_action('admin_menu', ['Affiliate_Admin', 'add_menu']);
