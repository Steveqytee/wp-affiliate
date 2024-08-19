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
        $submenus = [
            'top-affiliates' => ['Top Affiliates', 'render_top_affiliates'],
            'affiliate-management' => ['Affiliate User', 'render_affiliate_management'],
            'affiliate-registrations' => ['Affiliate Registrations', 'render_affiliate_registrations'],
            'add-new-affiliate' => ['Add New Affiliate', 'render_add_new_affiliate'],
            'affiliate-statistics' => ['Statistics', 'render_affiliate_statistics'],
            'affiliate-settings' => ['Settings', 'render_affiliate_settings'],
        ];
        foreach ($submenus as $slug => $details) {
            add_submenu_page(
                'affiliate-dashboard',
                $details[0],
                $details[0],
                'manage_options',
                $slug,
                [self::class, $details[1]]
            );
        }
        add_submenu_page(
            null,
            'Edit Affiliate',
            'Edit Affiliate',
            'manage_options',
            'edit-affiliate',
            [self::class, 'render_edit_affiliate_page']
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
        //include MY_AFFILIATE_PLUGIN_DIR . 'templates/affiliate-performance.php';
        include MY_AFFILIATE_PLUGIN_DIR . 'templates/statistics.php';

    }

    public static function render_affiliate_settings() {
        echo Affiliate_Settings::render_settings_page();
    }

    public static function render_edit_affiliate_page() {
        global $wpdb;

        // Check if affiliate_id is provided
        $affiliate_id = isset($_GET['affiliate_id']) ? intval($_GET['affiliate_id']) : 0;
        $affiliate = null;

        // Try to fetch the affiliate by user_id
        if ($affiliate_id) {
            $affiliate = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}affiliates WHERE user_id = $affiliate_id");
        }

        // If affiliate is not found, try fetching by coupon_id
        if (!$affiliate && isset($_GET['coupon_code'])) {
            $coupon_code = sanitize_text_field($_GET['coupon_code']);
            $affiliate = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}affiliates WHERE coupon_code = %s",
                $coupon_code
            ));
        }

        // If still not found, show error message
        if ($affiliate) {
            include MY_AFFILIATE_PLUGIN_DIR . 'templates/edit-affiliate-form.php';
        } else {
            echo '<div class="notice notice-error"><p>Affiliate not found.</p></div>';
        }
    }



   public static function render_add_new_affiliate() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_affiliate'])) {
        $result = self::create_affiliate_user($_POST);

        if (is_wp_error($result)) {
            include MY_AFFILIATE_PLUGIN_DIR . 'templates/add-affiliate-form.php';
            return;
        }

        echo '<div class="notice notice-success"><p>Affiliate created successfully!</p></div>';
    }

    include MY_AFFILIATE_PLUGIN_DIR . 'templates/add-affiliate-form.php';
}

    private static function create_affiliate_user($data) {
        $username = sanitize_text_field($data['username']);
        $email = sanitize_email($data['email']);
        $first_name = sanitize_text_field($data['first_name']);
        $last_name = sanitize_text_field($data['last_name']);
        $coupon_code = sanitize_text_field($data['coupon_code']);
        $custom_msg = sanitize_textarea_field($data['custom_msg']);

        $user_id = wp_create_user($username, wp_generate_password(), $email);
        if (is_wp_error($user_id)) return $user_id;

        wp_update_user([
            'ID' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
        ]);

        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'affiliates',
            [
                'user_id' => $user_id,
                'coupon_id' => Affiliate_Helpers::create_affiliate_coupon($user_id, $coupon_code),
                'commission_rate' => 5,
                'created_at' => current_time('mysql'),
                'custom_msg' => $custom_msg
            ]
        );

        $affiliate_id = $wpdb->insert_id;

        if (isset($data['commission_type_select'])) {
            if ($data['commission_type_select'] === 'product') {
                self::save_product_commission_settings($affiliate_id, $data);
            } elseif ($data['commission_type_select'] === 'order') {
                self::save_order_commission_settings($affiliate_id, $data);
            } elseif ($data['commission_type_select'] === 'quantity') {
                self::save_quantity_commission_settings($affiliate_id, $data);
            }
        }

        return true;
    }

    private static function save_product_commission_settings($affiliate_id, $data) {
        global $wpdb;

        foreach ($data['product_id'] as $index => $product_id) {
            $wpdb->insert(
                $wpdb->prefix . 'affiliate_product_commissions',
                [
                    'affiliate_id' => $affiliate_id,
                    'product_id' => intval($product_id),
                    'quantity_below' => intval($data['product_quantity_below'][$index]),
                    'quantity_above' => intval($data['product_quantity_above'][$index]),
                    'commission_type' => sanitize_text_field($data['product_commission_type'][$index]),
                    'commission_value_below' => floatval($data['product_commission_value_below'][$index]),
                    'commission_value_above' => floatval($data['product_commission_value_above'][$index]),
                ]
            );
        }
    }

   private static function save_order_commission_settings($affiliate_id, $data) {
    global $wpdb;

    $wpdb->insert(
        $wpdb->prefix . 'affiliate_order_commissions',
        [
            'affiliate_id' => $affiliate_id,
            'order_value_threshold' => floatval($data['order_value_threshold']),
            'commission_type_below' => sanitize_text_field($data['order_commission_type_below']),
            'commission_value_below' => floatval($data['order_commission_value_below']),
            'commission_type_above' => sanitize_text_field($data['order_commission_type_above']),
            'commission_value_above' => floatval($data['order_commission_value_above']),
        ]
    );
}

    private static function save_quantity_commission_settings($affiliate_id, $data) {
        global $wpdb;

        foreach ($data['quantity_below'] as $index => $quantity_below) {
            $wpdb->insert(
                $wpdb->prefix . 'affiliate_quantity_commissions',
                [
                    'affiliate_id' => $affiliate_id,
                    'quantity_below' => intval($quantity_below),
                    'quantity_above' => intval($data['quantity_above'][$index]),
                    'commission_type' => sanitize_text_field($data['quantity_commission_type'][$index]),
                    'commission_value_below' => floatval($data['quantity_commission_value_below'][$index]),
                    'commission_value_above' => floatval($data['quantity_commission_value_above'][$index]),
                    'custom_msg' => sanitize_textarea_field($data['quantity_custom_msg'][$index]),
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
