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
        check_admin_referer('save_general_settings_action');

        $program_name = sanitize_text_field($_POST['affiliate_program_name']);
        update_option('affiliate_program_name', $program_name);

        echo '<div class="notice notice-success is-dismissible"><p>General settings saved!</p></div>';
    }

    $affiliate_program_name = get_option('affiliate_program_name', 'Affiliate Program');
    include MY_AFFILIATE_PLUGIN_DIR . 'templates/settings-general.php';
}

public static function render_commission_settings() {
    if (isset($_POST['save_commission_settings'])) {
        check_admin_referer('save_commission_settings_action');

        $commission_data = [
            'type' => sanitize_text_field($_POST['commission_type_select']),
            'product_id' => array_map('intval', $_POST['product_id'] ?? []),
            'quantity_below' => array_map('intval', $_POST['product_quantity_below'] ?? []),
            'quantity_above' => array_map('intval', $_POST['product_quantity_above'] ?? []),
            'commission_type' => array_map('sanitize_text_field', $_POST['product_commission_type'] ?? []),
            'commission_value_below' => array_map('floatval', $_POST['product_commission_value_below'] ?? []),
            'commission_value_above' => array_map('floatval', $_POST['product_commission_value_above'] ?? []),
            'order_value_threshold' => floatval($_POST['order_value_threshold'] ?? ''),
            'order_commission_type_below' => sanitize_text_field($_POST['order_commission_type_below'] ?? ''),
            'order_commission_value_below' => floatval($_POST['order_commission_value_below'] ?? ''),
            'order_commission_type_above' => sanitize_text_field($_POST['order_commission_type_above'] ?? ''),
            'order_commission_value_above' => floatval($_POST['order_commission_value_above'] ?? ''),
            'quantity_commission_type' => array_map('sanitize_text_field', $_POST['quantity_commission_type'] ?? []),
            'quantity_commission_value_below' => array_map('floatval', $_POST['quantity_commission_value_below'] ?? []),
            'quantity_commission_value_above' => array_map('floatval', $_POST['quantity_commission_value_above'] ?? []),
            'custom_msg' => array_map('sanitize_textarea_field', $_POST['quantity_custom_msg'] ?? []),
        ];

        update_option('global_affiliate_commission_settings', $commission_data);
        echo '<div class="notice notice-success is-dismissible"><p>Commission settings saved!</p></div>';
    }

    $commission_data = get_option('global_affiliate_commission_settings', []);
    include MY_AFFILIATE_PLUGIN_DIR . 'templates/settings-commission.php';
}

public static function render_registration_settings() {
    if (isset($_POST['save_registration_settings'])) {
        check_admin_referer('save_registration_settings_action');
        update_option('affiliate_registration_requires_approval', isset($_POST['affiliate_registration_requires_approval']) ? 'yes' : 'no');
        echo '<div class="notice notice-success is-dismissible"><p>Registration settings saved!</p></div>';
    }

    $requires_approval = get_option('affiliate_registration_requires_approval', 'no');
    include MY_AFFILIATE_PLUGIN_DIR . 'templates/settings-registration.php';
}

public static function get_commission_settings() {
    return get_option('global_affiliate_commission_settings', []);
}

public static function get_registration_settings() {
    return get_option('affiliate_registration_requires_approval', 'no');
}
}


?>
