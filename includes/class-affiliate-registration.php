<?php

class Affiliate_Registration {
// In your main plugin file or class method
    public static function render_affiliate_registrations_page() {
        // Handle form actions (approval, rejection, deletion)
        if (isset($_POST['action']) && isset($_POST['affiliate_id']) && check_admin_referer('affiliate_action_nonce')) {
            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have sufficient permissions to perform this action.'));
            }
            $affiliate_id = intval($_POST['affiliate_id']);
            $action = sanitize_text_field($_POST['action']);
            self::handle_registration_action($affiliate_id, $action);
        }

        // Get filter status
        $filter_status = isset($_GET['filter_status']) ? sanitize_text_field($_GET['filter_status']) : '';

        // Fetch all affiliate registrations
        $affiliates = self::get_all_registrations($filter_status);

        // Load the template file, passing in the data
        include MY_AFFILIATE_PLUGIN_DIR . 'templates/registration-list.php';
    }


    public static function render_registration_form() {
        $errors = [];

        if (isset($_POST['affiliate_register']) && wp_verify_nonce($_POST['affiliate_register_nonce'], 'affiliate_register_action')) {
            // 数据校验逻辑
            $errors = self::validate_registration_form($_POST);

            if (empty($errors)) {
                // 插入注册信息到数据库
                $result = self::save_registration($_POST);
                if ($result) {
                    return self::show_success_popup();
                } else {
                    $errors['registration'] = 'Registration failed. Please try again later.';
                }
            } else {
                return self::show_error_popup();
            }
        }

        ob_start();
        include MY_AFFILIATE_PLUGIN_DIR . 'templates/registration-form.php';
        return ob_get_clean();
    }
    public static function render_registration_list() {
        // 获取所有的注册列表
        $registrations = self::get_all_registrations();
    // Get the filter status
        $filter_status = isset($_GET['filter_status']) ? sanitize_text_field($_GET['filter_status']) : '';

        ob_start();
    // Include the registration list template
        include plugin_dir_path(__FILE__) . '../templates/registration-list.php';

        return ob_get_clean();
    }


    public static function get_all_registrations($status = '') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'affiliate_registrations';

        $query = "SELECT * FROM $table_name";
        if (!empty($status)) {
            $query .= $wpdb->prepare(" WHERE status = %s", $status);
        }

        return $wpdb->get_results($query);
    }

    public static function validate_registration_form($data) {
        $errors = [];

        if (empty($data['first_name'])) {
            $errors['first_name'] = 'First name is required.';
        }

        if (empty($data['last_name'])) {
            $errors['last_name'] = 'Last name is required.';
        }

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'A valid email address is required.';
        }

        if (empty($data['phone']) || !ctype_digit($data['phone']) || empty($data['country_code'])) {
            $errors['phone'] = 'A valid phone number is required.';
        }

        if (empty($data['password']) || strlen($data['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters long.';
        }

        if ($data['password'] !== $data['confirm_password']) {
            $errors['confirm_password'] = 'Passwords do not match.';
        }

        if (empty($data['address'])) {
            $errors['address'] = 'Address is required.';
        }

        if (empty($data['postcode']) || empty($data['city']) || empty($data['state'])) {
            $errors['address'] = 'Postcode, City, and State are required.';
        }

        if (empty($data['coupon_code'])) {
            $errors['coupon_code'] = 'Preferred Coupon Code is required.';
        }

        if (empty($data['followers'])) {
            $errors['followers'] = 'Please enter the number of followers.';
        }

        if (!isset($data['agree_terms'])) {
            $errors['agree_terms'] = 'You must agree to the terms and privacy policy.';
        }

        return $errors;
    }

    public static function save_registration($data) {
        global $wpdb;

        $result = $wpdb->insert(
            $wpdb->prefix . 'affiliate_registrations',
            [
                'first_name' => sanitize_text_field($data['first_name']),
                'last_name' => sanitize_text_field($data['last_name']),
                'email' => sanitize_email($data['email']),
                'phone' => sanitize_text_field($data['phone']),
                'address' => sanitize_text_field($data['address']),
                'state' => sanitize_text_field($data['state']),
                'coupon_code' => sanitize_text_field($data['coupon_code']),
                'followers' => intval($data['followers']),
                'social_media' => implode(', ', array_map('sanitize_text_field', $data['social_media'])),
                'social_id' => sanitize_text_field($data['social_id']),
                'promotion_plan' => sanitize_textarea_field($data['promotion']),
                'how_hear' => sanitize_textarea_field($data['referral']),
                'status' => 'pending',
                'created_at' => current_time('mysql')
            ]
        );

        if ($result === false) {
            error_log('Failed to insert affiliate registration: ' . $wpdb->last_error);
            return false;
        }

        return $wpdb->insert_id ? true : false;
    }


    public static function show_success_popup() {
        return '<script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    icon: "success",
                    title: "Registration Successful",
                    text: "Your application is pending review."
                });
            });
        </script>';
    }

    public static function show_error_popup() {
        return '<script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    icon: "error",
                    title: "Registration Failed",
                    text: "Please correct the errors and try again."
                });
            });
        </script>';
    }

    public static function get_all_affiliates() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'affiliates';

        // Fetch all affiliates from the database
        $results = $wpdb->get_results("SELECT * FROM $table_name");

        return $results;
    }
    public static function handle_registration_action($affiliate_id, $action) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'affiliate_registrations';

        if ($action === 'approve') {
            $wpdb->update($table_name, ['status' => 'accepted'], ['id' => $affiliate_id]);
                // Redirect to the edit affiliate form
            wp_redirect(admin_url('admin.php?page=affiliate-management&edit_affiliate=' . $affiliate_id));
            exit;
        } elseif ($action === 'reject') {
            $wpdb->update($table_name, ['status' => 'rejected'], ['id' => $affiliate_id]);
        } elseif ($action === 'delete') {
            $wpdb->delete($table_name, ['id' => $affiliate_id]);
        }
    }

    public static function enqueue_scripts() {
        wp_enqueue_script('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@10', [], null, true);
        wp_add_inline_script('sweetalert2', "
            document.addEventListener('DOMContentLoaded', function() {
                var selectAllCheckbox = document.getElementById('select-all');
                if (selectAllCheckbox) {
                    selectAllCheckbox.addEventListener('click', function(event) {
                        var checkboxes = document.querySelectorAll('input[name=\"affiliate_ids[]\"]');
                        for (var checkbox of checkboxes) {
                            checkbox.checked = event.target.checked;
                        }
                    });
                } else {
                    console.warn('Select All checkbox not found.');
                }
            });
        ");
    }

    public static function get_affiliate_details($affiliate_id) {
        global $wpdb;

        // Query to get the affiliate details from the database
        $query = $wpdb->prepare("
            SELECT u.*, a.*
            FROM {$wpdb->prefix}users u
            INNER JOIN {$wpdb->prefix}affiliates a ON u.ID = a.user_id
            WHERE u.ID = %d
        ", $affiliate_id);

        // Fetch the affiliate details
        $affiliate = $wpdb->get_row($query);

        return $affiliate;
    }

}

add_action('wp_enqueue_scripts', ['Affiliate_Registration', 'enqueue_scripts']);
add_shortcode('affiliate_registration_form', ['Affiliate_Registration', 'render_registration_form']);

?>
