<?php
class Affiliate_Settings {

    public static function render_settings_page() {
        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';

        echo '<h1>Affiliate Program Settings</h1>';
        echo '<h2 class="nav-tab-wrapper">';
        echo '<a href="?page=affiliate-settings&tab=general" class="nav-tab ' . ($active_tab == 'general' ? 'nav-tab-active' : '') . '">General</a>';
        echo '<a href="?page=affiliate-settings&tab=commission" class="nav-tab ' . ($active_tab == 'commission' ? 'nav-tab-active' : '') . '">Commission</a>';
        echo '<a href="?page=affiliate-settings&tab=registration" class="nav-tab ' . ($active_tab == 'registration' ? 'nav-tab-active' : '') . '">Registration</a>';
        echo '</h2>';

        if ($active_tab == 'general') {
            self::render_general_settings();
        } elseif ($active_tab == 'commission') {
            self::render_commission_settings();
        } else {
            self::render_registration_settings();
        }
    }

    public static function render_general_settings() {
        if (isset($_POST['save_general_settings'])) {
            update_option('affiliate_program_name', sanitize_text_field($_POST['affiliate_program_name']));
            echo '<div class="notice notice-success is-dismissible"><p>General settings saved!</p></div>';
        }

        $affiliate_program_name = get_option('affiliate_program_name', 'Affiliate Program');
        include MY_AFFILIATE_PLUGIN_DIR . 'templates/settings-general.php';
    }

    public static function render_commission_settings() {
        if (isset($_POST['save_commission_settings'])) {
            $affiliate_target = $_POST['affiliate_target'] ?? ['all'];
            $commission_data = [
                'type' => sanitize_text_field($_POST['commission_type_select']),
                'product_id' => $_POST['product_id'] ?? [],
                'quantity_below' => $_POST['product_quantity_below'] ?? [],
                'quantity_above' => $_POST['product_quantity_above'] ?? [],
                'commission_type' => $_POST['product_commission_type'] ?? [],
                'commission_value_below' => $_POST['product_commission_value_below'] ?? [],
                'commission_value_above' => $_POST['product_commission_value_above'] ?? [],
                'order_value_threshold' => sanitize_text_field($_POST['order_value_threshold'] ?? ''),
                'order_commission_type_below' => sanitize_text_field($_POST['order_commission_type_below'] ?? ''),
                'order_commission_value_below' => sanitize_text_field($_POST['order_commission_value_below'] ?? ''),
                'order_commission_type_above' => sanitize_text_field($_POST['order_commission_type_above'] ?? ''),
                'order_commission_value_above' => sanitize_text_field($_POST['order_commission_value_above'] ?? ''),
                'quantity_commission_type' => $_POST['quantity_commission_type'] ?? [],
                'quantity_commission_value_below' => $_POST['quantity_commission_value_below'] ?? [],
                'quantity_commission_value_above' => $_POST['quantity_commission_value_above'] ?? [],
                'custom_msg' => $_POST['quantity_custom_msg'] ?? [],
            ];

            // Save for all or specific affiliates
            if (in_array('all', $affiliate_target)) {
                update_option('global_affiliate_commission_settings', $commission_data);
            } else {
                foreach ($affiliate_target as $affiliate_id) {
                    update_user_meta($affiliate_id, 'individual_commission_settings', $commission_data);
                }
            }

            echo '<div class="notice notice-success is-dismissible"><p>Commission settings saved!</p></div>';
        }

        // Load existing settings, preferring individual settings if available
        $affiliate_target = isset($_GET['affiliate_id']) ? [$_GET['affiliate_id']] : ['all'];
        $commission_data = get_option('global_affiliate_commission_settings', []);

        if ($affiliate_target[0] !== 'all') {
            $commission_data = get_user_meta($affiliate_target[0], 'individual_commission_settings', true) ?: $commission_data;
        }

        include MY_AFFILIATE_PLUGIN_DIR . 'templates/settings-commission.php';
    }


    public static function render_registration_settings() {
        if (isset($_POST['save_registration_settings'])) {
            update_option('affiliate_registration_requires_approval', isset($_POST['affiliate_registration_requires_approval']) ? 'yes' : 'no');
            echo '<div class="notice notice-success is-dismissible"><p>Registration settings saved!</p></div>';
        }

        $requires_approval = get_option('affiliate_registration_requires_approval', 'no');

        include MY_AFFILIATE_PLUGIN_DIR . 'templates/settings-registration.php';
    }
}
?>
