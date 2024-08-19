<h1>Affiliate Statistics</h1>
<p>Select the type of chart and data you want to display:</p>

<form id="chart-options-form">
    Chart Type:
    <select id="chart-type">
        <option value="bar">Bar</option>
        <option value="pie">Pie</option>
    </select>
    Data to Display:
    <select id="chart-data-type">
        <option value="sales">Sales</option>
        <option value="commission">Commission</option>
    </select>
    <input type="button" value="Generate Chart" id="generate-chart">
</form>

<canvas id="affiliateChart"></canvas>
<?php
// Enqueue the script
wp_enqueue_script('affiliate-statistics', MY_AFFILIATE_PLUGIN_URL . 'assets/js/scripts.js', ['jquery'], null, true);

?>
<script src="<?php echo MY_AFFILIATE_PLUGIN_URL . 'assets/js/scripts.js'; ?>"></script>
