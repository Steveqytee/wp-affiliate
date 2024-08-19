<?php


// Ensure direct access is not allowed
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Fetch overall statistics
global $wpdb;
$summary = $wpdb->get_row("
    SELECT
        COUNT(DISTINCT affiliate_id) as total_affiliates,
        SUM(sales_count) as total_sales,
        SUM(total_revenue) as total_revenue,
        SUM(commission_earned) as total_commission,
        AVG(conversion_rate) as avg_conversion_rate
    FROM {$wpdb->prefix}affiliate_performance
");

$top_affiliate = $wpdb->get_row("
    SELECT
        affiliate_id,
        SUM(total_revenue) as total_revenue
    FROM {$wpdb->prefix}affiliate_performance
    GROUP BY affiliate_id
    ORDER BY total_revenue DESC
    LIMIT 1
");

$recent_sales = $wpdb->get_results("
    SELECT
        affiliate_id,
        sale_amount,
        sale_date
    FROM {$wpdb->prefix}affiliate_sales
    ORDER BY sale_date DESC
    LIMIT 5
");

$new_affiliates = $wpdb->get_results("
    SELECT
        affiliate_id,
        registration_date
    FROM {$wpdb->prefix}affiliate_registrations
    ORDER BY registration_date DESC
    LIMIT 5
");

$top_affiliates_this_month = $wpdb->get_results("
    SELECT
        affiliate_id,
        SUM(total_revenue) as total_revenue
    FROM {$wpdb->prefix}affiliate_performance
    WHERE MONTH(last_sale_date) = MONTH(CURDATE())
    GROUP BY affiliate_id
    ORDER BY total_revenue DESC
    LIMIT 5
");
?>

<div class="affiliate-dashboard-overview">
    <h2>Affiliate Program Overview</h2>
    <div class="summary-cards">
        <div class="card">
            <h3>Total Affiliates</h3>
            <p><?php echo isset($summary->total_affiliates) ? $summary->total_affiliates : 0; ?></p>
        </div>
        <div class="card">
            <h3>Total Sales</h3>
            <p><?php echo isset($summary->total_sales) ? $summary->total_sales : 0; ?></p>
        </div>
        <div class="card">
            <h3>Total Revenue</h3>
            <p><?php echo isset($summary->total_revenue) ? wc_price($summary->total_revenue) : wc_price(0); ?></p>
        </div>
        <div class="card">
            <h3>Total Commission Paid</h3>
            <p><?php echo isset($summary->total_commission) ? wc_price($summary->total_commission) : wc_price(0); ?></p>
        </div>
        <div class="card">
            <h3>Average Conversion Rate</h3>
            <p><?php echo isset($summary->avg_conversion_rate) ? number_format($summary->avg_conversion_rate, 2) . '%' : '0%'; ?></p>
        </div>
        <div class="card">
            <h3>Top Affiliate</h3>
            <p>
                <?php
                if (isset($top_affiliate->affiliate_id)) {
                    echo get_affiliate_name($top_affiliate->affiliate_id);
                } else {
                    echo 'Unknown Affiliate';
                }
               ?>
            </p>
        </div>
    </div>

    <h2>Recent Activity</h2>
    <div class="recent-activity">
        <h3>Recent Sales</h3>
        <ul>
            <?php foreach ($recent_sales as $sale): ?>
                <li><?php echo get_affiliate_name($sale->affiliate_id); ?> - <?php echo wc_price($sale->sale_amount); ?> - <?php echo date('Y-m-d', strtotime($sale->sale_date)); ?></li>
            <?php endforeach; ?>
        </ul>

        <h3>New Affiliates</h3>
        <ul>
            <?php foreach ($new_affiliates as $affiliate): ?>
                <li><?php echo get_affiliate_name($affiliate->affiliate_id); ?> - <?php echo date('Y-m-d', strtotime($affiliate->registration_date)); ?></li>
            <?php endforeach; ?>
        </ul>

        <h3>Top Affiliates This Month</h3>
        <ul>
            <?php foreach ($top_affiliates_this_month as $affiliate): ?>
                <li><?php echo get_affiliate_name($affiliate->affiliate_id); ?> - <?php echo wc_price($affiliate->total_revenue); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <h2>Performance Charts</h2>
    <div id="sales-revenue-chart"></div>
    <div id="conversion-rate-chart"></div>
</div>

<script>
// JavaScript to render the performance charts
document.addEventListener('DOMContentLoaded', function () {
    var salesRevenueData = {
        labels: [/* months */],
        datasets: [
            {
                label: 'Total Sales',
                data: [/* data */],
                borderColor: 'rgba(75, 192, 192, 1)',
                fill: false
            },
            {
                label: 'Total Revenue',
                data: [/* data */],
                borderColor: 'rgba(153, 102, 255, 1)',
                fill: false
            }
        ]
    };

    var conversionRateData = {
        labels: [/* months */],
        datasets: [
            {
                label: 'Conversion Rate',
                data: [/* data */],
                borderColor: 'rgba(255, 159, 64, 1)',
                fill: false
            }
        ]
    };

    var ctxSalesRevenue = document.getElementById('sales-revenue-chart').getContext('2d');
    new Chart(ctxSalesRevenue, {
        type: 'line',
        data: salesRevenueData,
        options: {
            responsive: true,
            title: {
                display: true,
                text: 'Monthly Sales & Revenue'
            }
        }
    });

    var ctxConversionRate = document.getElementById('conversion-rate-chart').getContext('2d');
    new Chart(ctxConversionRate, {
        type: 'line',
        data: conversionRateData,
        options: {
            responsive: true,
            title: {
                display: true,
                text: 'Monthly Conversion Rate'
            }
        }
    });
});
</script>
