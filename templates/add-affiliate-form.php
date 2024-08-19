<?php

// Ensure this file is accessed via a valid request
if (!defined('ABSPATH')) {
    exit;
}
$commission_type = isset($commission_type) ? $commission_type : '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_affiliate'])) {
    $result = Affiliate_Handlers::handle_add_affiliate($_POST);

    if (is_wp_error($result)) {
        echo '<div class="notice notice-error"><p>' . esc_html($result->get_error_message()) . '</p></div>';
    } else {
        echo '<div class="notice notice-success"><p>Affiliate created successfully! User ID: ' . esc_html($result) . '</p></div>';
    }
}
// Include necessary headers or dependencies if needed
?>
<h1>Add New Affiliate</h1>


<form id="addAffiliateForm" method="post">
<input type="text" name="username" placeholder="Username" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="text" name="first_name" placeholder="First Name" required>
    <input type="text" name="last_name" placeholder="Last Name" required>
    <input type="text" name="coupon_code" placeholder="Coupon Code" required>
    <textarea name="custom_msg" placeholder="Custom Message"></textarea>

    <h2>Commission Settings</h2>

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
                    $selected = isset($affiliate->product_id) && $affiliate->product_id == $product->get_id() ? 'selected' : '';
                    echo '<option value="' . esc_attr($product->get_id()) . '" ' . $selected . '>' . esc_html($product->get_name()) . '</option>';
                }
                ?>
            </select></p>

            <p><label>Quantity Below Threshold:</label><br><input type="number" name="product_quantity_below[]" value="<?php echo isset($affiliate->product_quantity_below) ? esc_attr($affiliate->product_quantity_below) : ''; ?>" min="1" required></p>
            <p><label>Quantity Above Threshold:</label><br><input type="number" name="product_quantity_above[]" value="<?php echo isset($affiliate->product_quantity_above) ? esc_attr($affiliate->product_quantity_above) : ''; ?>" min="1" required></p>

            <p><label>Commission Type:</label><br>
            <select name="product_commission_type[]">
                <option value="fixed" <?php selected(isset($affiliate->product_commission_type) ? $affiliate->product_commission_type : '', 'fixed'); ?>>Fixed Amount</option>
                <option value="percentage" <?php selected(isset($affiliate->product_commission_type) ? $affiliate->product_commission_type : '', 'percentage'); ?>>Percentage</option>
            </select></p>

            <p><label>Commission Value Below Threshold:</label><br><input type="number" step="1" name="product_commission_value_below[]" value="<?php echo isset($affiliate->product_commission_value_below) ? esc_attr($affiliate->product_commission_value_below) : ''; ?>" required></p>
            <p><label>Commission Value Above Threshold:</label><br><input type="number" step="1" name="product_commission_value_above[]" value="<?php echo isset($affiliate->product_commission_value_above) ? esc_attr($affiliate->product_commission_value_above) : ''; ?>" required></p>
        </div>
        <button type="button" id="add_product_commission">Add Another Product</button>
    </div>

    <!-- Commission By Order Value -->
    <div id="commission_by_order" style="display: <?php echo $commission_type == 'order' ? 'block' : 'none'; ?>;">
    <h3>Commission by Order Value</h3>
        <p><label>Order Value Threshold:</label><br><input type="number" step="1" name="order_value_threshold" value="<?php echo isset($affiliate->order_value_threshold) ? esc_attr($affiliate->order_value_threshold) : ''; ?>" required></p>

        <p><label>Commission Type Below Threshold:</label><br>
        <select name="order_commission_type_below">
            <option value="fixed" <?php selected(isset($affiliate->order_commission_type_below) ? $affiliate->order_commission_type_below : '', 'fixed'); ?>>Fixed Amount</option>
            <option value="percentage" <?php selected(isset($affiliate->order_commission_type_below) ? $affiliate->order_commission_type_below : '', 'percentage'); ?>>Percentage</option>
        </select></p>

        <p><label>Commission Value Below Threshold:</label><br><input type="number" step="1" name="order_commission_value_below" value="<?php echo isset($affiliate->order_commission_value_below) ? esc_attr($affiliate->order_commission_value_below) : ''; ?>" required></p>

        <p><label>Commission Type Above Threshold:</label><br>
        <select name="order_commission_type_above">
            <option value="fixed" <?php selected(isset($affiliate->order_commission_type_above) ? $affiliate->order_commission_type_above : '', 'fixed'); ?>>Fixed Amount</option>
            <option value="percentage" <?php selected(isset($affiliate->order_commission_type_above) ? $affiliate->order_commission_type_above : '', 'percentage'); ?>>Percentage</option>
        </select></p>

        <p><label>Commission Value Above Threshold:</label><br><input type="number" step="1" name="order_commission_value_above" value="<?php echo isset($affiliate->order_commission_value_above) ? esc_attr($affiliate->order_commission_value_above) : ''; ?>" required></p>
    </div>

    <!-- Commission By Quantity Sold -->
    <div id="commission_by_quantity" style="display: <?php echo $commission_type == 'quantity' ? 'block' : 'none'; ?>;">
    <h3>Commission by Quantity Sold</h3>
        <div class="quantity-commission">
            <p><label>Quantity Below Threshold:</label><br><input type="number" name="quantity_below[]" value="<?php echo isset($affiliate->quantity_below) ? esc_attr($affiliate->quantity_below) : ''; ?>" min="1" required></p>
            <p><label>Quantity Above Threshold:</label><br><input type="number" name="quantity_above[]" value="<?php echo isset($affiliate->quantity_above) ? esc_attr($affiliate->quantity_above) : ''; ?>" min="1" required></p>

            <p><label>Commission Type:</label><br>
            <select name="quantity_commission_type[]">
                <option value="fixed" <?php selected(isset($affiliate->quantity_commission_type) ? $affiliate->quantity_commission_type : '', 'fixed'); ?>>Fixed Amount</option>
                <option value="percentage" <?php selected(isset($affiliate->quantity_commission_type) ? $affiliate->quantity_commission_type : '', 'percentage'); ?>>Percentage</option>
            </select></p>

            <p><label>Commission Value Below Threshold:</label><br><input type="number" step="1" name="quantity_commission_value_below[]" value="<?php echo isset($affiliate->quantity_commission_value_below) ? esc_attr($affiliate->quantity_commission_value_below) : ''; ?>" required></p>
            <p><label>Commission Value Above Threshold:</label><br><input type="number" step="1" name="quantity_commission_value_above[]" value="<?php echo isset($affiliate->quantity_commission_value_above) ? esc_attr($affiliate->quantity_commission_value_above) : ''; ?>" required></p>

            <p><label>Custom Message:</label><br><textarea name="quantity_custom_msg[]"><?php echo isset($affiliate->quantity_custom_msg) ? esc_textarea($affiliate->quantity_custom_msg) : ''; ?></textarea></p>
        </div>
        <button type="button" id="add_quantity_commission">Add Another Quantity Rule</button>
    </div>

    <p><input type="submit" name="create_affiliate" class="button button-primary" value="Create Affiliate"></p>
</form>


<script>
   document.addEventListener('DOMContentLoaded', function () {
    const commissionTypeSelect = document.getElementById('commission_type_select');
    const commissionByProduct = document.getElementById('commission_by_product');
    const commissionByOrder = document.getElementById('commission_by_order');
    const commissionByQuantity = document.getElementById('commission_by_quantity');


    commissionTypeSelect.addEventListener('change', function () {
        handleCommissionTypeChange(this.value);
        console.log('Commission type changed to: ', this.value);
    });

    function handleCommissionTypeChange(selectedType) {
        // Hide all sections and remove required attribute from their inputs
        hideAndRemoveRequired(commissionByProduct);
        hideAndRemoveRequired(commissionByOrder);
        hideAndRemoveRequired(commissionByQuantity);

        // Show the selected section and add required attributes to its inputs
        if (selectedType === 'product') {
            showAndAddRequired(commissionByProduct);
        } else if (selectedType === 'order') {
            showAndAddRequired(commissionByOrder);
        } else if (selectedType === 'quantity') {
            showAndAddRequired(commissionByQuantity);
        }
    }

    function hideAndRemoveRequired(section) {
        section.style.display = 'none';
        section.querySelectorAll('input, select').forEach(input => {
            input.removeAttribute('required');
        });
    }

    function showAndAddRequired(section) {
        section.style.display = 'block';
        section.querySelectorAll('input, select').forEach(input => {
            input.setAttribute('required', 'required');
        });
    }

    commissionTypeSelect.addEventListener('change', function() {
        handleCommissionTypeChange(this.value);
    });

    // Initial check when the page loads
    handleCommissionTypeChange(commissionTypeSelect.value);



    document.getElementById('add_product_commission').addEventListener('click', function () {
        const productCommissionDiv = document.querySelector('.product-commission').cloneNode(true);
        productCommissionDiv.querySelector('.remove-product-commission').style.display = 'inline';
        document.getElementById('commission_by_product').appendChild(productCommissionDiv);

        productCommissionDiv.querySelector('.remove-product-commission').addEventListener('click', function () {
            productCommissionDiv.remove();
        });
    });

    document.getElementById('add_quantity_commission').addEventListener('click', function () {
        const quantityCommissionDiv = document.querySelector('.quantity-commission').cloneNode(true);
        quantityCommissionDiv.querySelector('.remove-quantity-commission').style.display = 'inline';
        document.getElementById('commission_by_quantity').appendChild(quantityCommissionDiv);

        quantityCommissionDiv.querySelector('.remove-quantity-commission').addEventListener('click', function () {
            quantityCommissionDiv.remove();
        });
    });

});

</script>
