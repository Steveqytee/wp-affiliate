<form method="post">
    <table class="form-table">
        <!-- Target Affiliates -->
        <tr>
            <th><label for="affiliate_target">Target Affiliate(s)</label></th>
            <td>
                <select name="affiliate_target[]" id="affiliate_target" multiple>
                    <option value="all" <?php selected(in_array('all', $affiliate_target), true); ?>>All Affiliates</option>
                    <?php
                    $affiliates = get_users(['role' => 'affiliate']);
                    foreach ($affiliates as $affiliate) {
                        echo '<option value="' . esc_attr($affiliate->ID) . '"' . selected(in_array($affiliate->ID, $affiliate_target), true, false) . '>' . esc_html($affiliate->display_name) . '</option>';
                    }
                    ?>
                </select>
                <p class="description">Hold down the Ctrl (Windows) / Command (Mac) button to select multiple options.</p>
            </td>
        </tr>

        <!-- Commission Type Selection -->
        <tr>
            <th><label for="commission_type_select">Commission Type</label></th>
            <td>
                <select id="commission_type_select" name="commission_type_select">
                    <option value="" <?php selected($commission_type, ''); ?>>Select Commission Type</option>
                    <option value="product" <?php selected($commission_type, 'product'); ?>>Commission by Product</option>
                    <option value="order" <?php selected($commission_type, 'order'); ?>>Commission by Order Value</option>
                    <option value="quantity" <?php selected($commission_type, 'quantity'); ?>>Commission by Quantity Sold</option>
                </select>
            </td>
        </tr>

        <!-- Commission by Product -->
        <tr id="commission_by_product" style="display: <?php echo $commission_type == 'product' ? 'table-row' : 'none'; ?>;">
            <th><label>Commission by Product</label></th>
            <td>
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
            </td>
        </tr>

        <!-- Commission by Order Value -->
        <tr id="commission_by_order" style="display: <?php echo $commission_type == 'order' ? 'table-row' : 'none'; ?>;">
            <th><label>Commission by Order Value</label></th>
            <td>
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
            </td>
        </tr>

        <!-- Commission by Quantity Sold -->
        <tr id="commission_by_quantity" style="display: <?php echo $commission_type == 'quantity' ? 'table-row' : 'none'; ?>;">
            <th><label>Commission by Quantity Sold</label></th>
            <td>
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
            </td>
        </tr>
    </table>
    <input type="submit" name="save_commission_settings" value="Save Settings" class="button button-primary">
</form>
