/**
 * File: affiliate-dashboard.js
 * Description: Renders charts and handles dashboard-specific actions.
 * Used in: admin.php?page=affiliate-dashboard
 */
document.addEventListener('DOMContentLoaded', function() {
    var salesRevenueData = {
        labels: AffiliateDashboard.labels,
        datasets: [
            {
                label: 'Total Sales',
                data: AffiliateDashboard.totalSalesData,
                borderColor: 'rgba(75, 192, 192, 1)',
                fill: false
            },
            {
                label: 'Total Revenue',
                data: AffiliateDashboard.totalRevenueData,
                borderColor: 'rgba(153, 102, 255, 1)',
                fill: false
            }
        ]
    };

    var conversionRateData = {
        labels: AffiliateDashboard.labels,
        datasets: [
            {
                label: 'Conversion Rate',
                data: AffiliateDashboard.conversionRateData,
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
