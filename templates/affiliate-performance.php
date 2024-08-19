<?php
// Ensure direct access is not allowed
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Fetch top 10 affiliate data from the database
global $wpdb;
$top_affiliates = $wpdb->get_results("
    SELECT
        affiliate_id,
        SUM(sales_count) as total_sales,
        SUM(total_revenue) as total_revenue,
        AVG(conversion_rate) as conversion_rate,
        SUM(commission_earned) as total_commission,
        AVG(order_value) as avg_order_value,
        MAX(last_sale_date) as last_sale_date
    FROM {$wpdb->prefix}affiliate_performance
    GROUP BY affiliate_id
    ORDER BY total_sales DESC
    LIMIT 10
");

// Fetch all affiliate data
$all_affiliates = $wpdb->get_results("
    SELECT
        affiliate_id,
        SUM(sales_count) as total_sales,
        SUM(total_revenue) as total_revenue,
        AVG(conversion_rate) as conversion_rate,
        SUM(commission_earned) as total_commission,
        AVG(order_value) as avg_order_value,
        MAX(last_sale_date) as last_sale_date
    FROM {$wpdb->prefix}affiliate_performance
    GROUP BY affiliate_id
    ORDER BY total_sales DESC
");

// Fetch monthly performance data for charts
$monthly_performance = $wpdb->get_results("
    SELECT
        MONTH(last_sale_date) as month,
        SUM(sales_count) as total_sales,
        SUM(total_revenue) as total_revenue,
        SUM(commission_earned) as total_commission
    FROM {$wpdb->prefix}affiliate_performance
    WHERE YEAR(last_sale_date) = YEAR(CURDATE())
    GROUP BY MONTH(last_sale_date)
");
?>

<div class="affiliate-performance-overview">
<h3>Overall Performance Summary</h3>
    <ul>
        <li>Total Affiliates: <?php echo $overall_performance->total_affiliates; ?></li>
        <li>Overall Sales: <?php echo $overall_performance->overall_sales; ?></li>
        <li>Overall Revenue: <?php echo wc_price( $overall_performance->overall_revenue ); ?></li>
        <li>Overall Commission Paid: <?php echo wc_price( $overall_performance->overall_commission ); ?></li>
    </ul>

    <h2>Top 10 Affiliate Performers</h2>
    <table class="affiliate-performance-table">
        <thead>
            <tr>
                <th>Affiliate Name</th>
                <th>Total Sales</th>
                <th>Total Revenue</th>
                <th>Conversion Rate</th>
                <th>Commission Earned</th>
                <th>Average Order Value</th>
                <th>Date of Last Sale</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $top_affiliates as $affiliate ) : ?>
                <tr>
                    <td><?php echo get_affiliate_name( $affiliate->affiliate_id ); ?></td>
                    <td><?php echo $affiliate->total_sales; ?></td>
                    <td><?php echo wc_price( $affiliate->total_revenue ); ?></td>
                    <td><?php echo number_format( $affiliate->conversion_rate, 2 ) . '%'; ?></td>
                    <td><?php echo wc_price( $affiliate->total_commission ); ?></td>
                    <td><?php echo wc_price( $affiliate->avg_order_value ); ?></td>
                    <td><?php echo date( 'Y-m-d', strtotime( $affiliate->last_sale_date ) ); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>All Affiliate Performance</h2>
    <table class="affiliate-performance-table">
        <thead>
            <tr>
                <th>Affiliate Name</th>
                <th>Total Sales</th>
                <th>Total Revenue</th>
                <th>Conversion Rate</th>
                <th>Commission Earned</th>
                <th>Average Order Value</th>
                <th>Date of Last Sale</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $all_affiliates as $affiliate ) : ?>
                <tr>
                    <td><?php echo get_affiliate_name( $affiliate->affiliate_id ); ?></td>
                    <td><?php echo $affiliate->total_sales; ?></td>
                    <td><?php echo wc_price( $affiliate->total_revenue ); ?></td>
                    <td><?php echo number_format( $affiliate->conversion_rate, 2 ) . '%'; ?></td>
                    <td><?php echo wc_price( $affiliate->total_commission ); ?></td>
                    <td><?php echo wc_price( $affiliate->avg_order_value ); ?></td>
                    <td><?php echo date( 'Y-m-d', strtotime( $affiliate->last_sale_date ) ); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Monthly Performance Overview</h2>
    <div id="monthly-performance-chart"></div>
</div>

<script>
// JavaScript to render the monthly performance chart
document.addEventListener('DOMContentLoaded', function () {
    var chartData = {
        labels: [<?php foreach($monthly_performance as $data) { echo '"' . date("F", mktime(0, 0, 0, $data->month, 10)) . '", '; } ?>],
        datasets: [
            {
                label: 'Total Sales',
                data: [<?php foreach($monthly_performance as $data) { echo $data->total_sales . ', '; } ?>],
                borderColor: 'rgba(75, 192, 192, 1)',
                fill: false
            },
            {
                label: 'Total Revenue',
                data: [<?php foreach($monthly_performance as $data) { echo $data->total_revenue . ', '; } ?>],
                borderColor: 'rgba(153, 102, 255, 1)',
                fill: false
            },
            {
                label: 'Total Commission',
                data: [<?php foreach($monthly_performance as $data) { echo $data->total_commission . ', '; } ?>],
                borderColor: 'rgba(255, 159, 64, 1)',
                fill: false
            }
        ]
    };

    var ctx = document.getElementById('monthly-performance-chart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: chartData,
        options: {
            responsive: true,
            title: {
                display: true,
                text: 'Monthly Performance Overview'
            },
            scales: {
                xAxes: [{
                    display: true
                }],
                yAxes: [{
                    display: true
                }]
            }
        }
    });
});
</script>
