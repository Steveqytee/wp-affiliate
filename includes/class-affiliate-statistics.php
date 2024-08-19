<?php

class Affiliate_Statistics {

    public static function render_statistics_page() {
        // 获取所有 Affiliate 数据
        $affiliates = self::get_all_affiliates_with_stats();

        // 准备数据用于生成图表
        $regions = [];
        $sales_data = [];
        $commission_data = [];

        foreach ($affiliates as $affiliate) {
            if (!isset($regions[$affiliate->region])) {
                $regions[$affiliate->region] = [
                    'sales' => 0,
                    'commission' => 0,
                ];
            }

            $regions[$affiliate->region]['sales'] += $affiliate->total_sales;
            $regions[$affiliate->region]['commission'] += $affiliate->total_commission;
        }

        // 将数据转换为用于 Chart.js 的数组
        $region_labels = array_keys($regions);
        $sales_values = array_column($regions, 'sales');
        $commission_values = array_column($regions, 'commission');

        // 将数据传递给模板文件
        include MY_AFFILIATE_PLUGIN_DIR . 'templates/statistics-page.php';
    }

    private static function get_all_affiliates_with_stats() {
        global $wpdb;
        // 实现用于获取 affiliate 数据的查询逻辑
        // return $wpdb->get_results(...);
        return []; // 示例：实际中应返回从数据库中查询到的数据
    }

    public static function get_affiliate_total_sales($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'affiliate_sales';
        return $wpdb->get_var($wpdb->prepare("SELECT SUM(amount) FROM $table_name WHERE affiliate_id = %d", $user_id));
    }

    public static function get_affiliate_total_commission($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'affiliate_sales';
        return $wpdb->get_var($wpdb->prepare("SELECT SUM(commission) FROM $table_name WHERE affiliate_id = %d", $user_id));
    }

    public static function get_monthly_sales_count($affiliate_id) {
        global $wpdb;
        $first_day_of_month = date('Y-m-01 00:00:00');
        $last_day_of_month = date('Y-m-t 23:59:59');

        $table_name = $wpdb->prefix . 'affiliate_sales';
        $sales_count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE affiliate_id = %d AND sale_date BETWEEN %s AND %s",
                $affiliate_id, $first_day_of_month, $last_day_of_month
            )
        );

        return $sales_count;
    }

}

