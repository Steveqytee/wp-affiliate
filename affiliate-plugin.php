<?php
/*
Plugin Name: My Affiliate Plugin
Description: A custom plugin to manage affiliates in WordPress + WooCommerce.
Version: 1.0
Author: Your Name
*/

define('MY_AFFILIATE_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Include necessary classes
require_once MY_AFFILIATE_PLUGIN_DIR . 'includes/class-affiliate-registration.php';
require_once MY_AFFILIATE_PLUGIN_DIR . 'includes/class-affiliate-handlers.php';
require_once MY_AFFILIATE_PLUGIN_DIR . 'includes/class-affiliate-shortcodes.php';
require_once MY_AFFILIATE_PLUGIN_DIR . 'includes/class-affiliate-helpers.php';
require_once MY_AFFILIATE_PLUGIN_DIR . 'includes/class-affiliate-admin.php';
require_once MY_AFFILIATE_PLUGIN_DIR . 'includes/class-affiliate-dashboard.php';
require_once MY_AFFILIATE_PLUGIN_DIR . 'includes/class-affiliate-performance.php';
require_once MY_AFFILIATE_PLUGIN_DIR . 'includes/class-affiliate-settings.php';
require_once MY_AFFILIATE_PLUGIN_DIR . 'includes/class-affiliate-statistics.php';
require_once MY_AFFILIATE_PLUGIN_DIR . 'includes/class-affiliate-db.php';
require_once MY_AFFILIATE_PLUGIN_DIR . 'includes/class-affiliate-user.php';

// Enqueue scripts and styles
function my_affiliate_plugin_enqueue_scripts($hook_suffix) {
    // Load common styles and scripts
    wp_enqueue_style('my-affiliate-style', plugins_url('assets/css/style.css', __FILE__));

    // Load scripts based on the current admin page
    switch ($hook_suffix) {
        case 'toplevel_page_affiliate-management':
            wp_enqueue_script('affiliate-management-scripts', plugins_url('assets/js/affiliate-management.js', __FILE__), ['jquery'], null, true);
            break;

        case 'affiliate_page_affiliate-dashboard':
            wp_enqueue_script('affiliate-dashboard-scripts', plugins_url('assets/js/affiliate-dashboard.js', __FILE__), ['jquery', 'chart-js'], null, true);
            break;

        case 'affiliate_page_affiliate-registration':
            wp_enqueue_script('affiliate-registration-scripts', plugins_url('assets/js/affiliate-registration.js', __FILE__), ['jquery'], null, true);
            break;

        case 'affiliate_page_edit-affiliate':
            wp_enqueue_script('affiliate-edit-scripts', plugins_url('assets/js/affiliate-edit.js', __FILE__), ['jquery'], null, true);
            break;
        case 'affiliate_page_affiliate-settings':  // Assuming your settings page hook suffix is 'affiliate_page_affiliate-settings'
            wp_enqueue_script('affiliate-settings-scripts', plugins_url('assets/js/settings.js', __FILE__), ['jquery'], null, true);
            break;
        case 'affiliate_page_add-new-affiliate':  // Adjust this to match the hook suffix for the Add New Affiliate page
            wp_enqueue_script('affiliate-add-scripts', plugins_url('assets/js/affiliate-add.js', __FILE__), ['jquery'], null, true);
            break;
        }

    // Pass ajax URL and nonce to scripts
    wp_localize_script('affiliate-management-scripts', 'AffiliateManagement', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'security' => wp_create_nonce('my_ajax_nonce')
    ]);
    wp_localize_script('affiliate-dashboard-scripts', 'AffiliateDashboard', [
        'labels' => [], // Add your data
        'totalSalesData' => [], // Add your data
        'totalRevenueData' => [], // Add your data
        'conversionRateData' => [] // Add your data
    ]);
    wp_localize_script('affiliate-edit-scripts', 'AffiliateEdit', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'security' => wp_create_nonce('my_ajax_nonce')
    ]);
    wp_localize_script('affiliate-registration-scripts', 'AffiliateRegistration', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'security' => wp_create_nonce('my_ajax_nonce')
    ]);
}
add_action('admin_enqueue_scripts', 'my_affiliate_plugin_enqueue_scripts');

add_action('wp_enqueue_scripts', 'my_affiliate_plugin_enqueue_scripts');

register_activation_hook(__FILE__, ['Affiliate_Helpers', 'sync_existing_affiliates']);
register_activation_hook(__FILE__, ['Affiliate_DB', 'affiliate_custom_db_activate']);

define('MY_AFFILIATE_PLUGIN_URL', plugin_dir_url(__FILE__));

// Register AJAX actions
add_action('wp_ajax_update_affiliate', 'update_affiliate');
add_action('wp_ajax_get_affiliate_details', 'get_affiliate_details_callback');
add_action('wp_ajax_load_add_affiliate_form', 'load_add_affiliate_form');
add_action('wp_ajax_load_edit_affiliate_form', 'load_edit_affiliate_form');
add_action('wp_ajax_delete_affiliate', 'handle_delete_affiliate_ajax');
add_action('wp_ajax_my_ajax_action', 'my_ajax_action_callback');
add_action('wp_ajax_nopriv_my_ajax_action', 'my_ajax_action_callback');
add_shortcode('affiliate_dashboard', 'affiliate_dashboard_shortcode');


function affiliate_add_roles_on_plugin_activation() {
    add_role('affiliate', 'Affiliate', [
        'read' => true,
        'level_0' => true
    ]);

    add_role('affiliate_manager', 'Affiliate Manager', [
        'read' => true,
        'edit_posts' => true,
        'manage_options' => true,
    ]);
}
register_activation_hook(__FILE__, 'affiliate_add_roles_on_plugin_activation');

// Function to get affiliate details
function get_affiliate_details_callback() {
    if (!isset($_GET['affiliate_id'])) {
        wp_send_json_error('Affiliate ID is required');
        wp_die();
    }

    $affiliate_id = intval($_GET['affiliate_id']);
    $affiliate = Affiliate_Registration::get_affiliate_details($affiliate_id);

    if (!$affiliate) {
        wp_send_json_error('Affiliate not found');
        wp_die();
    }

    wp_send_json_success($affiliate);
}

// Function to update affiliate
function update_affiliate() {
    if (!isset($_POST['affiliate_id']) || !wp_verify_nonce($_POST['security'], 'my_ajax_nonce')) {
        wp_send_json_error('Invalid request');
    }

    $affiliate_id = intval($_POST['affiliate_id']);
    $email = sanitize_email($_POST['email']);
    $first_name = sanitize_text_field($_POST['first_name']);
    $last_name = sanitize_text_field($_POST['last_name']);
    $coupon_code = sanitize_text_field($_POST['coupon_code']);
    $commission_rate = sanitize_text_field($_POST['commission_rate']);
    $custom_msg = sanitize_textarea_field($_POST['custom_msg']);

    // Update user details
    wp_update_user([
        'ID' => $affiliate_id,
        'user_email' => $email,
        'first_name' => $first_name,
        'last_name' => $last_name
    ]);

    global $wpdb;

    $result = $wpdb->update(
        "{$wpdb->prefix}affiliates",
        [
            'coupon_code' => $coupon_code,
            'commission_rate' => $commission_rate,
            'custom_msg' => $custom_msg
        ],
        ['user_id' => $affiliate_id],
        ['%s', '%f', '%s'],
        ['%d']
    );

    if ($result !== false) {
        wp_send_json_success('Affiliate updated successfully.');
    } else {
        wp_send_json_error('Failed to update affiliate.');
    }
}

// Function to handle deleting an affiliate
function handle_delete_affiliate_ajax() {
    if (!isset($_POST['affiliate_id']) || !wp_verify_nonce($_POST['nonce'], 'delete_affiliate_nonce')) {
        wp_send_json_error(['message' => 'Invalid request']);
    }

    $affiliate_id = intval($_POST['affiliate_id']);
    $result = Affiliate_Form_Handler::delete_affiliate($affiliate_id);

    if ($result !== false) {
        wp_send_json_success(['message' => 'Affiliate deleted successfully']);
    } else {
        wp_send_json_error(['message' => 'Failed to delete affiliate']);
    }
}

// Load add affiliate form template
function load_add_affiliate_form() {
    require_once 'templates/add-affiliate-form.php';
    wp_die();
}

// Load edit affiliate form template
function load_edit_affiliate_form() {
    $affiliate_id = intval($_GET['affiliate_id']);
    $affiliate = Affiliate_Registration::get_affiliate_details($affiliate_id);
    include 'templates/edit-affiliate-form.php';
    wp_die();
}

// Example AJAX action handler
function my_ajax_action_callback() {
    // Validate nonce
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'my_ajax_nonce')) {
        wp_send_json_error('Invalid nonce');
    }

    // Process your request here

    wp_send_json_success('Request handled successfully.');
}

?>
