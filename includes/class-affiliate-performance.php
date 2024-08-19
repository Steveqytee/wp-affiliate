<?php

class Affiliate_Performance {

    public static function get_total_referrals($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'affiliate_sales';
        return $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE affiliate_id = %d", $user_id));
    }

    public static function get_total_sales($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'affiliate_sales';
        $total_sales = $wpdb->get_var($wpdb->prepare("SELECT SUM(amount) FROM $table_name WHERE affiliate_id = %d", $user_id));
        return $total_sales !== null ? floatval($total_sales) : 0;
    }


    public static function get_total_commission($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'affiliate_sales';
        return $wpdb->get_var($wpdb->prepare("SELECT SUM(commission) FROM $table_name WHERE affiliate_id = %d", $user_id));
    }

    public static function get_monthly_sales_count($affiliate_id) {
        global $wpdb;
        $first_day_of_month = date('Y-m-01 00:00:00');
        $last_day_of_month = date('Y-m-t 23:59:59');
        $table_name = $wpdb->prefix . 'affiliate_sales';
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE affiliate_id = %d AND sale_date BETWEEN %s AND %s",
            $affiliate_id, $first_day_of_month, $last_day_of_month));
    }
}
