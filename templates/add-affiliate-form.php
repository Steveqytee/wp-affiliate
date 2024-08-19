<?php

// Ensure this file is accessed via a valid request
if (!defined('ABSPATH')) {
    exit;
}

$commission_type = isset($commission_type) ? sanitize_text_field($commission_type) : '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_affiliate'])) {
    check_admin_referer('create_affiliate_nonce');

    // Determine whether to handle commission details
    $enable_commission = isset($_POST['enable_commission']) ? true : false;

    if ($enable_commission) {
        // Process commission settings if enabled
        $result = Affiliate_Handlers::handle_add_affiliate_with_commission($_POST);
    } else {
        // Create user with basic details without commission
        $result = Affiliate_Handlers::handle_add_affiliate($_POST);
    }

    if (is_wp_error($result)) {
        echo '<div class="notice notice-error"><p>' . esc_html($result->get_error_message()) . '</p></div>';
    } else {
        echo '<div class="notice notice-success"><p>Affiliate created successfully! User ID: ' . esc_html($result) . '</p></div>';
    }
}

?>
<h1>Add New Affiliate</h1>

<form id="addAffiliateForm" method="post">
    <?php wp_nonce_field('create_affiliate_nonce'); ?>
    <input type="text" name="username" placeholder="Username" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="text" name="first_name" placeholder="First Name" required>
    <input type="text" name="last_name" placeholder="Last Name" required>
    <input type="text" name="coupon_code" placeholder="Coupon Code" required>
    <textarea name="custom_msg" placeholder="Custom Message"></textarea>

    <h2>Commission Settings</h2>

    <input type="checkbox" id="enable_commission" name="enable_commission">
    <label for="enable_commission">Enable Commission Settings</label>

    <div id="commission_settings" style="display: none;">
        <select id="commission_type_select" name="commission_type_select">
            <option value="" <?php selected($commission_type, ''); ?>>Select Commission Type</option>
            <option value="product" <?php selected($commission_type, 'product'); ?>>Commission by Product</option>
            <option value="order" <?php selected($commission_type, 'order'); ?>>Commission by Order Value</option>
            <option value="quantity" <?php selected($commission_type, 'quantity'); ?>>Commission by Quantity Sold</option>
        </select>

         <!-- Commission By Product -->
    <div id="commission_by_product" style="display: <?php echo $commission_type == 'product' ? 'block' : 'none'; ?>;">
        <h3>Commission by Product</h3>
        <div class="product-commission">
            <p><label>Product:</label><br>
            <select name="product_id[]">
                <?php
                $products = wc_get_products(['limit' => -1]);
                foreach ($products as $product) {
                    $selected = $product->get_id() == $affiliate->product_id ? 'selected' : '';
                    echo '<option value="' . esc_attr($product->get_id()) . '" ' . $selected . '>' . esc_html($product->get_name()) . '</option>';
                }
                ?>
            </select></p>

            <p><label>Quantity Below Threshold:</label><br><input type="number" name="product_quantity_below[]" value="<?php echo esc_attr($product_quantity_below); ?>" min="1" required></p>
            <p><label>Quantity Above Threshold:</label><br><input type="number" name="product_quantity_above[]" value="<?php echo esc_attr($product_quantity_above); ?>" min="1" required></p>

            <p><label>Commission Type:</label><br>
            <select name="product_commission_type[]">
                <option value="fixed" <?php selected($product_commission_type, 'fixed'); ?>>Fixed Amount</option>
                <option value="percentage" <?php selected($product_commission_type, 'percentage'); ?>>Percentage</option>
            </select></p>

            <p><label>Commission Value Below Threshold:</label><br><input type="number" step="1" name="product_commission_value_below[]" value="<?php echo esc_attr($product_commission_value_below); ?>" required></p>
            <p><label>Commission Value Above Threshold:</label><br><input type="number" step="1" name="product_commission_value_above[]" value="<?php echo esc_attr($product_commission_value_above); ?>" required></p>
        </div>
        <button type="button" id="add_product_commission">Add Another Product</button>
    </div>

    <!-- Commission By Order Value -->
    <div id="commission_by_order" style="display: <?php echo $commission_type == 'order' ? 'block' : 'none'; ?>;">
        <h3>Commission by Order Value</h3>
        <p><label>Order Value Threshold:</label><br><input type="number" step="1" name="order_value_threshold" value="<?php echo esc_attr($order_value_threshold); ?>" required></p>

        <p><label>Commission Type Below Threshold:</label><br>
        <select name="order_commission_type_below">
            <option value="fixed" <?php selected($order_commission_type_below, 'fixed'); ?>>Fixed Amount</option>
            <option value="percentage" <?php selected($order_commission_type_below, 'percentage'); ?>>Percentage</option>
        </select></p>

        <p><label>Commission Value Below Threshold:</label><br><input type="number" step="1" name="order_commission_value_below" value="<?php echo esc_attr($order_commission_value_below); ?>" required></p>

        <p><label>Commission Type Above Threshold:</label><br>
        <select name="order_commission_type_above">
            <option value="fixed" <?php selected($order_commission_type_above, 'fixed'); ?>>Fixed Amount</option>
            <option value="percentage" <?php selected($order_commission_type_above, 'percentage'); ?>>Percentage</option>
        </select></p>

        <p><label>Commission Value Above Threshold:</label><br><input type="number" step="1" name="order_commission_value_above" value="<?php echo esc_attr($order_commission_value_above); ?>" required></p>
    </div>

    <!-- Commission By Quantity Sold -->
    <div id="commission_by_quantity" style="display: <?php echo $commission_type == 'quantity' ? 'block' : 'none'; ?>;">
        <h3>Commission by Quantity Sold</h3>
        <div class="quantity-commission">
            <p><label>Quantity Below Threshold:</label><br><input type="number" name="quantity_below[]" value="<?php echo esc_attr($quantity_below); ?>" min="1" required></p>
            <p><label>Quantity Above Threshold:</label><br><input type="number" name="quantity_above[]" value="<?php echo esc_attr($quantity_above); ?>" min="1" required></p>

            <p><label>Commission Type:</label><br>
            <select name="quantity_commission_type[]">
                <option value="fixed" <?php selected($quantity_commission_type, 'fixed'); ?>>Fixed Amount</option>
                <option value="percentage" <?php selected($quantity_commission_type, 'percentage'); ?>>Percentage</option>
            </select></p>

            <p><label>Commission Value Below Threshold:</label><br><input type="number" step="1" name="quantity_commission_value_below[]" value="<?php echo esc_attr($quantity_commission_value_below); ?>" required></p>
            <p><label>Commission Value Above Threshold:</label><br><input type="number" step="1" name="quantity_commission_value_above[]" value="<?php echo esc_attr($quantity_commission_value_above); ?>" required></p>

            <p><label>Custom Message:</label><br><textarea name="quantity_custom_msg[]"><?php echo esc_textarea($quantity_custom_msg); ?></textarea></p>
        </div>
        <button type="button" id="add_quantity_commission">Add Another Quantity Rule</button>
    </div>
    </div>

    <input type="submit" name="create_affiliate" class="button button-primary" value="Create Affiliate">
</form>


