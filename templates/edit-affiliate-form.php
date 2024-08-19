<?php
// 确保这是从有效的请求访问的文件
if (!defined('ABSPATH')) {
    exit;
}

// 获取 affiliate 详细信息
$affiliate_id = isset($_GET['affiliate_id']) ? intval($_GET['affiliate_id']) : 0;
$affiliate = Affiliate_User::get_affiliate_details($affiliate_id);

// 检查是否找到 affiliate
if (!$affiliate) {
    echo '<div class="notice notice-error"><p>Affiliate not found.</p></div>';
    return;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_affiliate'])) {
    Affiliate_Handlers::handle_update_affiliate($affiliate_id, $_POST);
}



// Assuming $affiliate is the object fetched from the "Affiliate User" table
$email = isset($affiliate->email) ? $affiliate->email : '';
$first_name = isset($affiliate->first_name) ? $affiliate->first_name : '';
$last_name = isset($affiliate->last_name) ? $affiliate->last_name : '';
$coupon_code = isset($affiliate->coupon_code) ? $affiliate->coupon_code : '';
$commission_type = isset($affiliate->commission_type) ? $affiliate->commission_type : '';
$product_quantity_below = isset($affiliate->product_quantity_below) ? $affiliate->product_quantity_below : '';
$product_quantity_above = isset($affiliate->product_quantity_above) ? $affiliate->product_quantity_above : '';
$product_commission_type = isset($affiliate->product_commission_type) ? $affiliate->product_commission_type : '';
$product_commission_value_below = isset($affiliate->product_commission_value_below) ? $affiliate->product_commission_value_below : '';
$product_commission_value_above = isset($affiliate->product_commission_value_above) ? $affiliate->product_commission_value_above : '';
$order_value_threshold = isset($affiliate->order_value_threshold) ? $affiliate->order_value_threshold : '';
$order_commission_type_below = isset($affiliate->order_commission_type_below) ? $affiliate->order_commission_type_below : '';
$order_commission_value_below = isset($affiliate->order_commission_value_below) ? $affiliate->order_commission_value_below : '';
$order_commission_type_above = isset($affiliate->order_commission_type_above) ? $affiliate->order_commission_type_above : '';
$order_commission_value_above = isset($affiliate->order_commission_value_above) ? $affiliate->order_commission_value_above : '';
$quantity_below = isset($affiliate->quantity_below) ? $affiliate->quantity_below : '';
$quantity_above = isset($affiliate->quantity_above) ? $affiliate->quantity_above : '';
$quantity_commission_type = isset($affiliate->quantity_commission_type) ? $affiliate->quantity_commission_type : '';
$quantity_commission_value_below = isset($affiliate->quantity_commission_value_below) ? $affiliate->quantity_commission_value_below : '';
$quantity_commission_value_above = isset($affiliate->quantity_commission_value_above) ? $affiliate->quantity_commission_value_above : '';
$quantity_custom_msg = isset($affiliate->quantity_custom_msg) ? $affiliate->quantity_custom_msg : '';

?>

<form method="post">
    <!-- Pre-fill with existing data -->
    <input type="hidden" name="affiliate_id" value="<?php echo esc_attr($affiliate->user_id); ?>">
    <p><label>Username:</label><br><input type="text" name="username" value="<?php echo esc_attr(get_userdata($affiliate->user_id)->user_login); ?>" readonly></p>
    <p><label>Email:</label><br><input type="email" name="email" value="<?php echo esc_attr(get_userdata($affiliate->user_id)->user_email); ?>" required></p>
    <p><label>First Name:</label><br><input type="text" name="first_name" value="<?php echo esc_attr($affiliate->first_name); ?>" required></p>
    <p><label>Last Name:</label><br><input type="text" name="last_name" value="<?php echo esc_attr($affiliate->last_name); ?>" required></p>
    <p><label>Coupon Code:</label><br><input type="text" name="coupon_code" value="<?php echo esc_attr($affiliate->coupon_code); ?>" required></p>
    <p><label>Custom Message:</label><br><textarea name="custom_msg"><?php echo esc_textarea($affiliate->custom_msg); ?></textarea></p>
    <h2>Commission Settings</h2>

    <!-- Commission Type Selection -->
    <p>
        <label for="commission_type_select">Commission Type:</label><br>
        <select id="commission_type_select" name="commission_type">
            <option value="">Select Commission Type</option>
            <option value="product" <?php selected($commission_type, 'product'); ?>>Commission by Product</option>
            <option value="order" <?php selected($commission_type, 'order'); ?>>Commission by Order Value</option>
            <option value="quantity" <?php selected($commission_type, 'quantity'); ?>>Commission by Quantity Sold</option>
        </select>
    </p>

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

    <p><input type="submit" name="update_affiliate" class="button button-primary" value="Update Affiliate"></p>
</form>


