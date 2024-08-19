<?php

class Affiliate_Dashboard {

    public static function render_dashboard() {
        global $wpdb;

        // Check if the sales table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}affiliate_sales'") !== $wpdb->prefix . 'affiliate_sales') {
            echo 'Sales table does not exist.';
            return;
        }
        // Fetch sales data and calculate totals using SQL aggregate functions
        $totals = $wpdb->get_row("
            SELECT
                SUM(sale_amount) as total_sales,
                SUM(commission) as total_commission,
                COUNT(*) as total_referrals
            FROM {$wpdb->prefix}affiliate_sales
        ");

        // Set default values if no data is returned
        $total_sales = $totals->total_sales ?? 0;
        $total_commission = $totals->total_commission ?? 0;
        $total_referrals = $totals->total_referrals ?? 0;

        // Get the current month and year
        $current_month = date('m');
        $current_year = date('Y');

        // Fetch top affiliates for the current month
        $top_affiliates = $wpdb->get_results($wpdb->prepare(
            "SELECT a.user_id, u.user_login, SUM(s.sale_amount) as total_sales
             FROM {$wpdb->prefix}affiliate_sales s
             JOIN {$wpdb->prefix}affiliates a ON s.affiliate_id = a.id
             JOIN {$wpdb->users} u ON a.user_id = u.ID
             WHERE MONTH(s.sale_date) = %d AND YEAR(s.sale_date) = %d
             GROUP BY a.user_id
             ORDER BY total_sales DESC
             LIMIT 5",
            $current_month, $current_year
        ));

        if (!$top_affiliates) {
            $top_affiliates = [];
        }

        // Include the template file and pass variables
        if (defined('MY_AFFILIATE_PLUGIN_DIR')) {
            include MY_AFFILIATE_PLUGIN_DIR . 'templates/affiliate-dashboard.php';
        } else {
            echo 'Plugin directory constant is not defined.';
        }
    }

    public static function restrict_dashboard_access() {
        if (is_page('affiliate-dashboard') && !current_user_can('affiliate')) {
            wp_redirect(wp_login_url());
            exit;
        }
    }
}

// Restrict access to the dashboard
add_action('template_redirect', ['Affiliate_Dashboard', 'restrict_dashboard_access']);

?>
