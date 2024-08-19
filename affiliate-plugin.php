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
function my_affiliate_plugin_enqueue_scripts() {
    wp_enqueue_style('my-affiliate-style', plugins_url('assets/css/style.css', __FILE__));
    wp_enqueue_script('my-affiliate-scripts', plugins_url('assets/js/scripts.js', __FILE__), ['jquery'], null, true);
}
add_action('wp_enqueue_scripts', 'my_affiliate_plugin_enqueue_scripts');
add_action('admin_enqueue_scripts', 'my_affiliate_plugin_enqueue_scripts');

register_activation_hook(__FILE__, ['Affiliate_Helpers', 'sync_existing_affiliates']);
register_activation_hook(__FILE__, ['Affiliate_DB', 'affiliate_custom_db_activate']);

define('MY_AFFILIATE_PLUGIN_URL', plugin_dir_url(__FILE__));


add_action('wp_ajax_get_affiliate_details', 'get_affiliate_details');
add_action('wp_ajax_delete_affiliate', 'delete_affiliate_handler');
function get_affiliate_details() {
    if (!isset($_GET['affiliate_id'])) {
        wp_send_json_error('Missing affiliate ID.');
    }

    $affiliate_id = intval($_GET['affiliate_id']);
    global $wpdb;

    $affiliate = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}affiliates WHERE user_id = %d", $affiliate_id));

    if ($affiliate) {
        wp_send_json_success($affiliate);
    } else {
        wp_send_json_error('Affiliate not found.');
    }
}

add_action('wp_ajax_update_affiliate', 'update_affiliate');

function update_affiliate() {
    if (!isset($_POST['affiliate_id'])) {
        wp_send_json_error('Missing affiliate ID.');
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

add_action('wp_ajax_get_affiliate_details', 'get_affiliate_details_callback');

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

function my_affiliate_scripts() {
    wp_enqueue_script('my-affiliate-js', plugins_url('affiliate.js', __FILE__), array('jquery'), null, true);
    wp_localize_script('my-affiliate-js', 'ajaxurl', admin_url('admin-ajax.php'));

    wp_enqueue_style('my-affiliate-css', plugins_url('affiliate.css', __FILE__));
}
add_action('admin_enqueue_scripts', 'my_affiliate_scripts');
function load_add_affiliate_form() {
    require_once 'templates/add-affiliate-form.php';
    wp_die(); // Required to terminate immediately and return a proper response
}
add_action('wp_ajax_load_add_affiliate_form', 'load_add_affiliate_form');

function load_edit_affiliate_form() {
    $affiliate_id = intval($_GET['affiliate_id']);
    $affiliate = Affiliate_Registration::get_affiliate_details($affiliate_id);
    include 'templates/edit-affiliate-form.php';
    wp_die(); // This is required to terminate immediately and return a proper response
}
add_action('wp_ajax_load_edit_affiliate_form', 'load_edit_affiliate_form');

add_action('wp_ajax_delete_affiliate', 'handle_delete_affiliate_ajax');

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


?>
